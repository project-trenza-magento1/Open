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

class Plumrocket_Rewards_Model_History extends Mage_Core_Model_Abstract
{
	const OBJ_TYPE_REFFERRAL		= 'INVITEE';
	const OBJ_TYPE_REFFERRAL_1		= 'INVITEE_1';
	const OBJ_TYPE_ORDER			= 'ORDER';
	const OBJ_TYPE_ORDER_CREDITS	= 'ORDER_CREDITS';
	const OBJ_TYPE_ORDER_ITEM_REFUND	= 'ORDER_ITEM_REFUND';
	const OBJ_TYPE_ADMIN			= 'ADMIN_CHANGE';
	const OBJ_TYPE_REGISTRATION		= 'CUSTOMER_REGISTRATION';
	const OBJ_TYPE_REVIEW			= 'REVIEW';
	const OBJ_TYPE_EXPIRED			= 'EXPIRED';
	
	public function _construct()
    {
        parent::_construct();
        $this->_init('rewards/history');
    }
    
    public function getTypes()
    {
		$h = Mage::helper('rewards');
		return array(
			self::OBJ_TYPE_REFFERRAL			=> $h->__('Invitation Points'),
			self::OBJ_TYPE_REFFERRAL_1			=> $h->__('Invitation Points'),
			self::OBJ_TYPE_ORDER				=> $h->__('Redeemed Points'),
			self::OBJ_TYPE_ORDER_CREDITS		=> $h->__('Purchase Points'),
			self::OBJ_TYPE_ORDER_ITEM_REFUND	=> $h->__('Refunded Points'),
			self::OBJ_TYPE_ADMIN				=> $h->__('Modified by Admin'),
			self::OBJ_TYPE_REGISTRATION			=> $h->__('Registration Points'),
			self::OBJ_TYPE_REVIEW				=> $h->__('Review Points'),
			self::OBJ_TYPE_EXPIRED				=> $h->__('Points Expired'),
		);
	}

	protected function _getPointsExpireDate(){
		
		$expiration = null;
		$config = Mage::getSingleton('rewards/config');

		switch($this->getObjType()){
			/*
			case self::OBJ_TYPE_REFFERRAL :
				$expiration = $config->getInvitePointsExpiration();
				break;
			case self::OBJ_TYPE_ORDER_CREDITS :
				$expiration = $config->getEarnPointsExpiration();
				break;
			case self::OBJ_TYPE_ORDER_ITEM_REFUND :
				$expiration = $config->getEarnPointsExpiration();
				break;
			case self::OBJ_TYPE_ADMIN :
				$expiration = $this->getExpiration();
				break;
			case self::OBJ_TYPE_REGISTRATION :
				$expiration = $config->getRegistrationPointsExpiration();
				break;
			case self::OBJ_TYPE_REVIEW :
				$expiration = $config->getReviewsPointsExpiration();
				break;
			*/
			/*case self::OBJ_TYPE_ADMIN :
				$expiration = $this->getExpiration();
				break;*/
			default :
				$expiration = $config->getPointsExpiration();
				break;
		}	

		if ($expiration){
			return date('Y-m-d', Mage::getSingleton('core/date')->timestamp() + $expiration * 86400);
		}

		return null;
	}
	
	public function getItem($objId, $objType, $datum = -1)
	{
		if (!is_null($datum)){
			if ($datum < 0)
				$term = array('lt' => 0);
			else
				$term = array('gt' => 0);
		}
			
		$collection =  $this
			->getCollection()
			->addFieldToFilter('obj_id', $objId)
			->addFieldToFilter('obj_type', $objType);
			
		if (!is_null($datum)){
			$collection->addFieldToFilter('points', $term);
		}
		
		return $collection->setPageSize(1)->getFirstItem();
	}
	
	public function getOrderPoints($order)
	{
		if (!$order || !$order->getId())
			return NULL;
			
		$item = $this
			->getCollection()
			->addFieldToFilter('obj_id', $order->getId())
			->addFieldToFilter('obj_type', self::OBJ_TYPE_ORDER)
			->setPageSize(1)
			->getFirstItem();
			
		return $item->getPoints();
	}

	/*
	public function getOrderEarnPoints($order)
	{
		$orderId = (is_object($order)) ? $order->getId() : $order; 
		
		$item = $this
			->getCollection()
			->addFieldToFilter('obj_id', $order->getId())
			->addFieldToFilter('obj_type', self::OBJ_TYPE_ORDER_CREDITS)
			->addFieldToFilter('points', array('gt' => 0))
			->setPageSize(1)
			->getFirstItem();

		return $item->getPoints(); 
	}
	*/
	
	public function getByCustomer($customerId, $start = NULL, $limit = NULL)
	{
		$collection = $this->getCollection()->addFieldToFilter('customer_id', $customerId);
		return $collection;
	}
	
	public function add($data)
	{
		return $this->setData($data)
			->setExpireAt($this->_getPointsExpireDate())
			->save();
	}

	
}
