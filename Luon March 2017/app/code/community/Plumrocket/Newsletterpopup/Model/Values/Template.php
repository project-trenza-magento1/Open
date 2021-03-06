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


class Plumrocket_Newsletterpopup_Model_Values_Template extends Plumrocket_Newsletterpopup_Model_Values_Base
{

	public function toOptionHash()
    {
        $templates = array(
        	'' => Mage::helper('newsletterpopup')->__(' - '),
        );
        
        $items = Mage::getModel('newsletterpopup/template')->getCollection();
        foreach ($items as $item) {
            $templates[ $item->getId() ] = $item->getName();
        }
        return $templates;
    }
}