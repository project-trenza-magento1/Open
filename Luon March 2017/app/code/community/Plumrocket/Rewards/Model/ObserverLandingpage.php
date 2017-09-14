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

class Plumrocket_Rewards_Model_ObserverLandingpage
{
	public function customerRegisterSuccess($landingpageId, $customer)
	{
		$page 			= Mage::getModel('signup/page')->load($landingpageId);
		$pointsCount	= (int) $page->getRegistrationPoints();
		if (!$pointsCount || $pointsCount < 0) {
			return $this;
		}

		if ($customer && ($customerId = $customer->getId())) {
			
			$desc = array(
				'objId' => $customerId,
				'objType' => Plumrocket_Rewards_Model_History::OBJ_TYPE_REGISTRATION,
				'text'	=> '',
			); 

			Mage::getModel('rewards/points')->getByCustomer($customerId, Mage::app()->getGroup()->getId())->add($pointsCount, $desc);
		}
		
		return $this;
	}
	

}

