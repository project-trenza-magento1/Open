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
?>
<?php  

class Plumrocket_Rewards_Block_Adminhtml_Landingpages_Edit_Tabs_Rewards
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


    protected function _prepareForm()
    {
		$page = Mage::registry('page'); 

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('signup_');
		
        $fieldset = $form->addFieldset('rewards_fieldset', array('legend' => Mage::helper('rewards')->__('Reward Points'), 'class' => 'fieldset-wide'));
        
		$fieldset->addField('registration_points', 'text', array(
			'name'		=> 'registration_points',
			'label'		=> Mage::helper('rewards')->__('Registration Points'),
		));
		
		
        $form->setValues($page->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }


    public function getTabLabel(){
        return Mage::helper('rewards')->__('Reward Points');
    }

    public function getTabTitle(){
        return Mage::helper('rewards')->__('Reward Points');
    }

    public function canShowTab(){
        return true;
    }

    public function isHidden(){
        return false;
    }

}
