<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Reward_Points
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Block_Adminhtml_Points_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected $_storeGroupId = null;
	protected $_title = 'General';

    protected function _prepareForm()
    {
		$customerPoints = $this->getCustomerPoints();
		$storeId 		= $this->getRequest()->getParam('store');

        $isElementDisabled = false;

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('customerPoints_');

        $fieldset = $form->addFieldset('general_fieldset', array('legend' => Mage::helper('rewards')->__($this->_title), 'class' => 'fieldset-wide'));

		$fieldset->addField('id', 'hidden', array(
			'name'		=> 'id',
			'disabled'	=> $isElementDisabled,
			'value'		=> $customerPoints->getId(),
		));

		$fieldset->addField('store', 'hidden', array(
			'name'		=> 'store',
			'class'		=> 'required-entry',
			'required'	=> true,
			'disabled'	=> $isElementDisabled,
			'value'		=> $this->getStoreGroupId(),
		));

		$fieldset->addField('customer_id', 'hidden', array(
			'name'		=> 'customer_id',
			'class'		=> 'required-entry',
			'required'	=> true,
			'disabled'	=> $isElementDisabled,
			'value'		=> $customerPoints->getCustomerId(),
		));

		$fieldset->addField('available1', 'note', array(
			'text'		=> $customerPoints->getAvailable() ? $customerPoints->getAvailable() : 0,
			'label'		=> Mage::helper('rewards')->__('Available Points'),
		));

		$fieldset->addField('add_points', 'text', array(
			'name'		=> 'add_points',
			'label'		=> Mage::helper('rewards')->__('Add / Subtract Points'),
			'class'		=> 'validate-number',
			'required'	=> true,
			'disabled'	=> $isElementDisabled,
			'value'		=> 0,
			'note'	=> $this->__('Enter positive or negative number and click on "Save" to add/remove reward points to/from customer account. For example: enter "-100" to subtract 100 points.'),
		));

		$fieldset->addField('description', 'textarea', array(
			'name'		=> 'description',
			'label'		=> Mage::helper('rewards')->__('Comment'),
			'required'	=> true,
			'disabled'	=> $isElementDisabled,
			'value'		=> '',
			'note'	=> $this->__('Please provide details about this change. This comment is visible to customer.'),
		));

        if ($storeId) {
            Mage::getSingleton('rewards/config')->setStoreId($storeId);
			$expiration = Mage::app()->getGroup($storeId)->getConfig('rewards/general/expiration');
        } else {
            $customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));
            $expiration = Mage::getSingleton('rewards/config')->setStoreId($customer->getData('store_id'))->getPointsExpiration();
        }

		$fieldset->addField('expiration', 'label', array(
			'name'		=> 'expiration',
			'label'		=> Mage::helper('rewards')->__('Points expiration'),
			'class'		=> 'validate-not-negative-number',
			'disabled'	=> $isElementDisabled,
			'value'		=> $expiration ? date('F d, Y', Mage::getSingleton('core/date')->timestamp() + $expiration * 86400).' '.$this->__('(%s days)', $expiration) : $this->__('No Expire'),
			//'value'		=> 0,
			//'note'	=> $this->__('Specify the number of days after reward points will expire. Enter "0" to disable this option.'),
		));

        $this->setForm($form);

        return parent::_prepareForm();
    }


    protected function getCustomerPoints()
	{
		return Mage::registry('customerPoints');
	}


    public function getStoreGroupId()
    {
		if (is_null($this->_storeGroupId)){
			$this->_storeGroupId = $this->getRequest()->getParam('store');
			if (!$this->_storeGroupId){
				$this->_storeGroupId = $this->getDefaultGroup()->getId();
			}
		}
		return $this->_storeGroupId;
	}


	protected function getDefaultGroup()
	{
		foreach(Mage::app()->getWebsites() as $website){
			foreach($website->getGroups() as $group){
				return $group;
			}
		}
	}


    public function getTabLabel(){
        return Mage::helper('rewards')->__($this->_title);
    }

    public function getTabTitle(){
        return Mage::helper('rewards')->__($this->_title);
    }

    public function canShowTab(){
        return true;
    }

    public function isHidden(){
        return false;
    }

}
