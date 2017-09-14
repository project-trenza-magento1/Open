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
 * @package     Plumrocket_Invitations
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php

class Plumrocket_Invitations_Model_Observer
{
	public function getEncodedModel()
	{
		return Mage::getSingleton('invitations/observerEncoded');
	}

	public function customerRegisterSuccess($observer)
	{
		if (!(Mage::helper('invitations')->moduleEnabled())){
			return $this;
		}
		$this->getEncodedModel()->customerRegisterSuccess($observer->getCustomer());	
	}
	
	public function salesOrderPlaceAfter($observer)
	{
		if (!(Mage::helper('invitations')->moduleEnabled())){
			return $this;
		}
		
		$order = $observer->getOrder();
		$quote = $order->getQuote();
		if ($quote->getCheckoutMethod(true) == 'register'){
			$this->getEncodedModel()->customerRegisterSuccess($order->getCustomer());	
		}

		$this->getEncodedModel()->salesOrderPlaceAfter($observer);
		
		return $this;
	}
	
	
	public function controllerFrontInitBefore($observer)
	{
		if (!(Mage::helper('invitations')->moduleEnabled())){
			return $observer;
		}
		
		if (!(Mage::getModel('invitations/config')->invitationsViaShares())){
			return $observer;
		}
		
		if ($customerId = Mage::app()->getRequest()->getParam('inviter')){
			//Mage::getSingleton('core/session')->setData('invitationAcceptId', $customerId);
			Mage::getModel('core/cookie')->set('invitationAcceptId', $customerId, 61516800);
		}
	}
	
	public function salesOrderInvoiceRegister($observer)
    {
    	$order = $observer->getEvent()->getOrder();
		if (!(Mage::helper('invitations')->moduleEnabled($order->getStoreId()))){
			return $observer;
		}

		$this->getEncodedModel()->salesOrderInvoiceRegister($observer);
    }
    
}

