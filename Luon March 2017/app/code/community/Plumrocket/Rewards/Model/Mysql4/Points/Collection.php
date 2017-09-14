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


class Plumrocket_Rewards_Model_Mysql4_Points_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_useCustomerCountAsSize 	= false;
	protected $_useWebsiteId 			= null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('rewards/points');
    }
    
	
	public function useCustomerCountAsSize($value = true, $websiteId = null){
		$this->_useCustomerCountAsSize = $value;
		if (!is_null($websiteId)) {
			$this->_useWebsiteId = $websiteId;
		}

		return $this;
	}
	
	public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $totalRecords = $this->getConnection()->fetchAll($sql, $this->_bindParams);
            if ($this->_useCustomerCountAsSize){
				$customers = Mage::getModel('customer/customer')->getCollection();
				if (!is_null($this->_useWebsiteId)) {
					$customers->getSelect()->where('e.website_id = ?', $this->_useWebsiteId);	
				}
				$this->_totalRecords = $customers->getSize();
			} else {
				$this->_totalRecords = intval($totalRecords);
			}
        }
        return $this->_totalRecords;
    }
}
