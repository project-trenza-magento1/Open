<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

/**
 * @author Amasty
 */ 
class Amasty_Followup_Model_Mysql4_History_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amfollowup/history');
    }

    public function addOrderData()
    {
        $this->getSelect()->joinLeft( 
                array('order' => $this->getTable('sales/order')), 
                'main_table.order_id = order.entity_id',
                array('order_status' => 'order.status')
        );
        return $this;
    }

    public function exculdeOrderIds($ids)
    {
        if (count($ids) > 0)
            $this->addFieldToFilter('main_table.order_id', array('nin' => $ids));
        
        return $this;
    }


    public function addScheduleData()
    {
        $this->getSelect()->join( 
                array('schedule' => $this->getTable('amfollowup/schedule')), 
                'main_table.schedule_id = schedule.schedule_id',
                array('schedule.delayed_start')
        );
        return $this;
    }


    public function addRuleData($withRule = false)
    {
        $join = $withRule ? 'join' : 'joinLeft';
        $this->getSelect()->$join(
            array('rule' => $this->getTable('amfollowup/rule')),
            'rule.rule_id = main_table.rule_id',
            array(
                'rulename' => 'rule.name'
            )
        );
        return $this;
    }

    public function addReadyFilter($date)
    {
        $this->addFieldToFilter('main_table.scheduled_at', array('lteq' => $date));
        $this->addPendingStatusFilter();
        return $this;
    }

    public function addPendingStatusFilter($cond = 'eq')
    {
        $this->addFieldToFilter('main_table.status', array($cond => Amasty_Followup_Model_History::STATUS_PENDING));
        return $this;
    }

    public function addBlacklistData()
    {
        $this->getSelect()->join(
            array('blacklist' => $this->getTable('amfollowup/blacklist')), 
            'main_table.email = blacklist.email', 
            array('blacklist.blacklist_id')
        );
        return $this;
    }

    public function addCouponData()
    {
//        $this->getSelect()->joinLeft(
//                array('coupon' => $this->getTable('salesrule/coupon')),
//                'main_table.sales_rule_id = coupon.rule_id',
//                array('coupon.code as coupon_code', 'coupon.times_used')
//        );
        return $this;
    }
}
