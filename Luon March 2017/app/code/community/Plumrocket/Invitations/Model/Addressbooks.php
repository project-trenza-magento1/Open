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

class Plumrocket_Invitations_Model_Addressbooks extends Mage_Core_Model_Abstract
{
	const STATUS_ENABLED = 'ENABLED';
	const STATUS_DISABLED = 'DISABLED';
	
	protected $_settingsValues			= NULL;
	protected $_lastCustomerInvitations	= array();
	protected $_baseUrl					= NULL;
	protected $_defaultHiddenValue		= 'plumrocket_hidden_value';

	public function _construct()
    {
        parent::_construct();
        $this->_init('invitations/addressbooks');
    }
	
	public function getDefaultHiddenValue(){
		return $this->_defaultHiddenValue;
	}
	
	public function getEnabled()
	{
		return $this
			->getCollection()
			->addFieldToFilter('status', self::STATUS_ENABLED)
			->setOrder('position', 'asc')
			->setOrder('name', 'asc');
	}
	
	public function loadEnabled(){
		return $this->getEnabled();
	}
	
	public function isEnabled()
	{
		return ($this->getStatus() == self::STATUS_ENABLED);
	}

	public function getByKey($key)
	{
		return  $this
			->getCollection()
			->addFieldToFilter('ad_key', $key)
			->setPageSize(1)
			->getFirstItem();
	}
	
	public function getSettingByKey($key)
	{
		if ($this->_settingsValues == NULL)
		{
			$settingsArray = $this->getSettingsArray();
			foreach($settingsArray as $name => $params) {
				$this->_settingsValues[$name] = $params['value'];
			}
		}
		//var_dump($this->getSettings());
		if (isset($this->_settingsValues[$key]))
			return $this->_settingsValues[$key];
		else
			return NULL;
	}
	
	public function getSettingsArray()
	{
		$settings	= json_decode($this->getSettings(), true);
		$bUrl		= $this->_getBaseUrl();
		foreach($settings as $name => $params)
			$settings[$name]['value'] = str_replace('{{base_url}}', $bUrl,  $params['value']);
		return $settings;
	}
	
	protected function _getBaseUrl()
	{
		if (is_null($this->_baseUrl))
		{
			$this->_baseUrl = Mage::getBaseUrl();
			if (Mage::getStoreConfig('web/seo/use_rewrites'))
				$this->_baseUrl = str_replace('/index.php', '', $this->_baseUrl);
		}
		return $this->_baseUrl;
	}

	public function getTypes()
	{
		return array(
			'EMAIL' => Mage::helper('invitations')->__('Email Service'),
			'SOCIAL' => Mage::helper('invitations')->__('Social Network'),
		);
	}
	
	public function getStatuses()
	{
		return array(
			'ENABLED' => Mage::helper('invitations')->__('Enabled'),
			'DISABLED' => Mage::helper('invitations')->__('Disabled'),
		);
	}
	
	public function getCustomerInvCountAvailable($passedInvCount, $passedSeconds = 86400)
	{
		$max = $this->getSettingByKey('max_invitations_per_day');
		if (empty($max))
			$max = 100;
		$result = (int) ($max - $passedInvCount);
		if ($result < 0)
			$result = 0;
		return $result;
	}

	public function getKey()
	{
		return $this->getAdKey();
	}

}
