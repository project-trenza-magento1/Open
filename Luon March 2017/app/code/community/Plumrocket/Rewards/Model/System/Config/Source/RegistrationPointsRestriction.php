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

class Plumrocket_Rewards_Model_System_Config_Source_RegistrationPointsRestriction
{


    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Every new user (no restrictions)')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Only invited users (via invitation link)')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Everyone except invited users')),
        );

    }

    public function toArray()
    {
        $res = array();
        foreach($this->toOptionArray() as  $v) {
            $res[$v['value']] = $v['label'];
        }
        return $res;
    }

}
