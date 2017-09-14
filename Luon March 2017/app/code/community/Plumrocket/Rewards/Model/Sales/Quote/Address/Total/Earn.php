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
?>
<?php

class Plumrocket_Rewards_Model_Sales_Quote_Address_Total_Earn extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	protected $_runed = false;

	protected $_code	= 'ernpoints';


	public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
	}

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
    	if ($this->_runed) {
    		return;
    	}

    	$_helper = Mage::helper('rewards');

        $amount = $_helper->getCartPotentialPoints();

        if ($amount) {

			$title = Mage::helper('sales')->__('Cash back in reward points');

            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>$title,
                'value'=> $amount,
            ));

            $this->_runed = true;
        }
        return $this;
    }

}
