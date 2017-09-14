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
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Newsletterpopup_Helper_Data extends Plumrocket_Newsletterpopup_Helper_Main
{
	const VISITOR_ID_PARAM_NAME = 'nsp_v';

	protected $_defaultValues = array(
		'status' => 1,
		'display_popup' => 'after_time_delay',
		'delay_time' => 0,
		'text_title' => 'GET $10 OFF YOUR FIRST ORDER',
		'text_description' => '<p>Join Magento Store List and Save!<br />Subscribe Now &amp; Receive a $10 OFF coupon in your email!</p>',
		'text_success' => '<p>Thank you for your subscription.</p>',
		'text_submit' => 'Sign Up Now',
		'text_cancel' => 'Hide',
		'animation' => 'fadeInDownBig',
	);

	protected $_templatePlaceholders = array(
		'{{text_cancel}}',
		'{{text_title}}',
		'{{text_description}}',
		'{{form_fields}}',
		'{{mailchimp_fields}}',
		'{{text_submit}}',
	);

	public function moduleEnabled($store = null)
	{
		return (bool)Mage::getStoreConfig('newsletterpopup/general/enable', $store);
	}

	public function disableExtension()
	{
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_write');
		$connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('newsletterpopup/general/enable', 'newsletterpopup/general/enable_analytics', 'newsletterpopup/general/enable_history', 'newsletterpopup/general/erase_history', 'newsletterpopup/general/cookies_usage', 'newsletterpopup/mailchimp/enable', 'newsletterpopup/mailchimp/key',))));
		$config = Mage::getConfig();
		$config->reinit();
		Mage::app()->reinitStores();
	}

	public function getCurrentPopup()
	{
		return ($this->moduleEnabled() && !Mage::app()->getStore()->isAdmin())
			? Mage::helper('newsletterpopup/dataEncoded')->getCurrentPopup()
			: Mage::getModel('newsletterpopup/popup');
	}

	public function getLockedPopupIds()
	{
		return ($this->moduleEnabled() && !Mage::app()->getStore()->isAdmin())
			? Mage::helper('newsletterpopup/dataEncoded')->getLockedPopupIds()
			: array();
	}

	public function validateUrl($url)
	{
		if (! Mage::app()->getStore()->isCurrentlySecure()) {
			$url = str_replace('https://', 'http://', $url);
		} else {
			$url = str_replace('http://', 'https://', $url);
		}
		return $url;
	}

	public function getPopupMailchimpList($popup_id, $justActive)
	{
		return $this->_getCollectionData($popup_id, $justActive, 'mailchimpList');
	}

	public function getPopupMailchimpListKeys($popup_id, $justActive)
	{
		return $this->_getCollectionData($popup_id, $justActive, 'mailchimpList', true);
	}

	public function getPopupFormFields($popup_id, $justActive)
    {
    	return $this->_getCollectionData($popup_id, $justActive, 'formField');
    }

    public function getPopupFormFieldsKeys($popup_id, $justActive)
    {
    	return $this->_getCollectionData($popup_id, $justActive, 'formField', true);
    }

    private function _getCollectionData($popup_id, $justActive, $model, $justKeys = false)
    {
    	$collection = Mage::getModel('newsletterpopup/' . $model)
			->getCollection()
			->addFieldToFilter('popup_id', $popup_id);
		if ($justActive) {
			$collection = $collection->addFieldToFilter('enable', 1);
		}
		$collection->getSelect()->order(array('sort_order', 'label'));

		$result = array();
		foreach ($collection as $item) {
			if ($justKeys) {
				$result[] = $item->getName();
			} else {
				$result[ $item->getName() ] = $item;
			}
		}
		return $result;
    }

    public function getPopupById($id)
	{
		$item = Mage::getModel('newsletterpopup/popup')->load($id);
		// load coupon code
		return $this->assignCoupon($item);
	}

	public function assignCoupon($item)
	{
		$item->setCoupon(
			Mage::getModel('salesrule/rule')->load( (int)$item->getCouponCode() )
		);
		return $item;
	}

	public function getPopupTemplateById($id)
	{
		if($item = Mage::getSingleton('newsletterpopup/template')->load($id)) {
			if(!$defaultValues = @unserialize($item->getData('default_values'))) {
				$defaultValues = array();
			}
			$item->addData(array_merge($this->_defaultValues, $defaultValues));
			Mage::app()->getRequest()->setParams($defaultValues);
		}

		return $item;
	}

	public function getTemplatePlaceholders($withAdditional = false)
	{
		$additional = array(
			'{{media url="wysiwyg/image.png"}}',
			'{{skin url="images/plumrocket/newsletterpopup/image.png"}}',
			'{{store direct_url="privacy-policy-cookie-restriction-mode"}}',
		);

		if($withAdditional) {
			return array_merge($this->_templatePlaceholders, $additional);
		}

		return $this->_templatePlaceholders;
	}

	public function getNString($str)
	{
		return str_replace("\r\n", "\n", $str);
	}

	public function visitorId($id = null)
	{
		$helper = Mage::helper('core');
		$cookie = Mage::getSingleton('core/cookie');
		if($prevId = $cookie->get(self::VISITOR_ID_PARAM_NAME)) {
			// $prevId = (int)substr($helper->decrypt($prevId), 32, -32);
			$prevId = (int)$helper->decrypt($prevId);
		}

		if($id) {
			$id = $helper->encrypt($id);
			$cookie->set(self::VISITOR_ID_PARAM_NAME, $id, (3600 * 24 * 30));
		}

		return $prevId;
	}

}