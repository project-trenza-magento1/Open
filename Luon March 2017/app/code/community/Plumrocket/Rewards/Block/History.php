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
?>
<?php  
  
class Plumrocket_Rewards_Block_History extends Mage_Core_Block_Template
{   
	private $_pagerHtml = NULL;
	private $_pagerBlock;
	private $_collection;
	
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('rewards/history.phtml');
	}

	public function getList(){
		$_groupIds = Mage::getModel('rewards/points')->getStoreGroupsId();
		$this->_collection = Mage::getModel('rewards/history')
			->getByCustomer( Mage::helper('rewards')->getCurrentCustomerId())
			->addFieldToFilter('store_group_id', array('in', $_groupIds));
		$this->_collection->getSelect()->order('created_at DESC');
		$this->_pagerBlock = $this->getLayout()->createBlock('page/html_pager');
		$this->_pagerBlock->setCollection($this->_collection);

		return $this->_collection;
	}
	
	public function getPagerHtml(){
		if ($this->_pagerHtml === NULL){
			$this->_pagerHtml = $this->_pagerBlock->toHtml();
		}
		return $this->_pagerHtml;
	}
}
