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
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Block_Adminhtml_Customer_Edit_Tab_Rewards_Fields extends Plumrocket_Rewards_Block_Adminhtml_Points_Edit_Tabs_General
{
	protected $_title = 'Reward Points';

	protected function getCustomerPoints()
	{
		return Mage::getModel('rewards/points')->getByCustomer(Mage::registry('current_customer')->getId(), $this->getStoreGroupId());
	}

	public function getStoreGroupId()
    {
		if (is_null($this->_storeGroupId)){
			$storeId = Mage::registry('current_customer')->getStoreId();
			$this->_storeGroupId = Mage::getModel('core/store')->load($storeId)->getGroupId();
			if (!$this->_storeGroupId){
				$this->_storeGroupId = $this->getDefaultGroup()->getId();
			}
		}

		return $this->_storeGroupId;
	}
}
