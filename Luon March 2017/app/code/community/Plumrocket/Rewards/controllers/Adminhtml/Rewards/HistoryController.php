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
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Adminhtml_Rewards_HistoryController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
		$this->_title($this->__('Reward Points History'));
		$this->loadLayout();
		$this->renderLayout();	
	}


	public function customergridAction()
	{
		$customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('customer_id'));
		Mage::register('current_customer', $customer);

		$this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->getBlock('customer_edit_tab_rewards_history')->toHtml()
        );
	}


	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('plumrocket/rewards/history');
    }
}
