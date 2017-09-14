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
?>
<?php

class Plumrocket_Invitations_Block_Page extends Mage_Core_Block_Template
{   
	protected $_config = null;
	protected $_customer;
	
	public function getConfig()
	{
		if (is_null($this->_config)){
			$this->_config = Mage::getModel('invitations/config');
		}
		return $this->_config;
	}
	
	public function getCustomer()
	{
		if (is_null($this->_customer)){
			$this->_customer = $this->getRequest()->getParam('customer');
		}
		return $this->_customer;
	}

	public function getStoreName()
	{
		return Mage::getStoreConfig('general/store_information/name');
	}
	
	public function getLogoUrl()
	{
		return $this->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'));
	}
	
	public function canInvite()
	{
		$guestInvites	= $this->getConfig()->getGuestsCanMakeInvites();
		
		return ($this->getCustomer() || $guestInvites);
	}
	
	public function isGuestInvite()
	{
		$guestInvites	= $this->getConfig()->getGuestsCanMakeInvites();
		
		return (!$this->getCustomer() && $guestInvites);
	}
	
	public function neadMigration()
	{
		$customer = $this->getCustomer();
		$guest = Mage::getModel('invitations/guest')->loadByEmail($customer->getEmail());

		if ($guest->getId()){
			return $guest->neadMigration();
		}
		return false;
	}

	public function hasEnabledBooks()
	{
		return (int)Mage::getModel('invitations/addressbooks')
			->getEnabled()
			->addFieldToFilter('ad_key', array('neq' => 'facebook'))
			->getSize();
	}

	public function getUrl($route = '', $params = array())
	{
		$url = parent::getUrl($route, $params);
		if (strpos($route, 'invitations/') === 0 || strpos($route, '*/') === 0) {
			if (Mage::app()->getStore()->isCurrentlySecure()) {
				$url = str_replace('http://', 'https://', $url);
			}
		}

		return $url;
	}
}
