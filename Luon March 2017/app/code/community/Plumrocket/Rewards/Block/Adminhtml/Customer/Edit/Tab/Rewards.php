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


class Plumrocket_Rewards_Block_Adminhtml_Customer_Edit_Tab_Rewards
    extends Mage_Adminhtml_Block_Text_List
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel(){
        return Mage::helper('rewards')->__('Reward Points');
    }

    public function getTabTitle(){
        return Mage::helper('rewards')->__('Reward Points');
    }

    public function canShowTab(){
        return true;
    }

    public function isHidden(){
        return false;
    }


    protected function _toHtml()
    {
        if (Mage::registry('current_customer')->getId()) {
            return parent::_toHtml();
        } else {
            echo 'Create New Customer First.';
        }
    }
}