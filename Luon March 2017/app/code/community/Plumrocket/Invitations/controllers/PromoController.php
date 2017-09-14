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
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Invitations_PromoController extends Mage_Core_Controller_Front_Action
{
	private $_customer = null;
	private $_helper;
	private $_promoPage = null;
	
	public function preDispatch()
    {
		parent::preDispatch();
		
		$this->_helper = Mage::helper('invitations');
		if (!($this->_helper->moduleEnabled())){
			$this->_toBaseUrl();
		}
		
		$this->_promoPage = Mage::getModel('invitations/config')->getPromoPage();
    }
	
	private function _toBaseUrl()
	{
		 header('Location: '.Mage::getUrl('invitations'));
		 exit();
	}
	
	public function indexAction()
	{
		if (!$this->_promoPage['enabled']){
			$this->_toBaseUrl();
		}
		
		$this->_customer = $this->_helper->getCurrentCustomer();
			
		$this->loadLayout();
		$headerBlock = $this->getLayout()->getBlock('head');

		if ($headerBlock) {
			$headerBlock->setTitle(isset($this->_promoPage['meta_title']) ? htmlspecialchars($this->_promoPage['meta_title']) : '');
			$headerBlock->setDescription(isset($this->_promoPage['meta_description']) ? strip_tags($this->_promoPage['meta_description']) : '');
			$headerBlock->setKeywords(isset($this->_promoPage['meta_keywords']) ? htmlspecialchars($this->_promoPage['meta_keywords']) : '');
		}
		
		$this->getRequest()->setParam('customer', $this->_customer);  
      	$this->renderLayout(); 
    }
    
    //guest validate
    public function validateAction()
	{
		$_helper	= Mage::helper('invitations');
		$code		= $this->getRequest()->getParam('code');
		$guest		= Mage::getModel('invitations/guest')->loadByCode($code);
		
		if ($guest->getId()){
			$guest->migration();
			Mage::getSingleton('customer/session')->addSuccess($_helper->__('Email Verification Succes.'));
		} else {
			Mage::getSingleton('customer/session')->addNotice($_helper->__('Verification code is outdated or invalid.'));
		}
		
		$this->_redirect('customer/account');
	}
	
	public function sendGuestVerificationEmailAction()
	{
		$result = false;
		try{
			$_customer = $this->_helper->getCurrentCustomer();
			if ($_customer->getId()){
				$guest = Mage::getModel('invitations/guest')->loadByEmail($_customer->getEmail());
				if ($guest->getId()){
					$guest->setCustomer($_customer);
					if ($guest->neadMigration()){
						$result = $guest->sendVerificationEmail();
					}
				}
			}
		} catch (Exception $e) {
			//echo $e->getMessage();
		}
		
		$this->getResponse()->setBody(json_encode(array('success' => $result)));
	}
	
	public function guestIdAction()
	{
		$_request = $this->getRequest();
		$id = null;
		
		if ($_request->isXmlHttpRequest() && $this->_promoPage['guest_invites']){
			if ($email = $_request->getParam('email')){
				$id = Mage::getModel('invitations/guest')->getGuestId($email);
			}
		}
		$this->getResponse()->setBody(json_encode(array(
			'id' => $id
		)));
	}
}
