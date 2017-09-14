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
  
class Plumrocket_Invitations_Block_Open extends Mage_Core_Block_Template{   
	
	protected $_pagerHtml = NULL;
	protected $_pagerBlock;
	protected $_collection;
	
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('invitations/open.phtml');
	}
	
	
	protected function _beforeToHtml()
	{
		$fbAddressBook = Mage::getModel('invitations/addressbooks')->getByKey('facebook');
		$customerId = $this->getData('customerId');
		$this->_collection = Mage::getModel('invitations/invitations')
			->getCustomerOpenInvitations($customerId)
			->addFieldToFilter('addressbook_id', array('neq' => $fbAddressBook->getId()))
			->addFieldToFilter('deactivated', 0)
			->addFieldToFilter('website_id', Mage::app()->getWebsite()->getId());
		
		$this->_pagerBlock = $this->getLayout()->createBlock('page/html_pager')->setTemplate('invitations/pager.phtml');
		$this->_pagerBlock->setCollection($this->_collection);
		
		$this->setData('invitations', $this->_collection);
		
		return parent::_beforeToHtml();
	}
	
	public function getPagerHtml(){
		if ($this->_pagerHtml === NULL){
			$this->_pagerHtml = $this->_pagerBlock->toHtml();
		}
		return $this->_pagerHtml;
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
