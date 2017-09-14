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

class Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_HistoryDescription extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	protected $_customers = array();
	
	
	protected function _getCustomer($id)
	{

		if (empty($this->_customers[$id])){
			$this->_customers[$id] = Mage::getModel('customer/customer')->load($id);
		}
		return $this->_customers[$id];
	}

	protected  function _getOrderLink($orderId, $text){
		$order = Mage::getModel('sales/order')->load($orderId);
		return '<a onClick="window.open(this.href); return false;" href="'.$this->getUrl('adminhtml/sales_order/view/',array('order_id' => $orderId)).'" title="'.$this->__('View Order').'">'.$text.' #'.$order->getIncrementId().'</a>';
	}

    public function render(Varien_Object $row)
    {
        $rowData = $row->getData();
		$desc = '';
		$objId = $rowData['obj_id'];
	//	var_dump($rowData);
		switch ($rowData['obj_type'])
		{
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER  :
				if ($row->getPoints() < 0){
					$desc = $this->__('Reward Points redeemed on purchase (%1$s).', $this->_getOrderLink($objId, 'order'));
				} else {
					$desc = $this->__('Reward Points refunded due to the %1$s cancellation.', $this->_getOrderLink($objId, 'order'));
				}
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_REFFERRAL :
				if(
					($customer = $this->_getCustomer($objId))
					&& $customer->getId()
				)
					$desc = $this->__('Reward Points earned for inviting a friend (%s) who made a first purchase.', $customer->getEmail()); 
				else
					$desc = $this->__('Reward Points earned for inviting a friend who made a first purchase.'); 
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_REFFERRAL_1 :
				$desc = $this->__('Reward Points earned for inviting a friend.');
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_ADMIN :
				$desc = $this->__('Adjusted reward balance by store admin. Comment: "%s"', $rowData['description']);
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_REGISTRATION :
				 $desc = $this->__('Reward Points earned for registration.');
				 break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_REVIEW :
				$review = Mage::getModel('review/review')->load($objId);
				switch($review->getEntityId()) {
					case 1 :
						$title = 'product';
						$url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $review->getEntityPkValue()));
						break;
					case 3 :
						$title = 'category';
						$url = $this->getUrl('adminhtml/catalog_category/index', array('id' => $review->getEntityPkValue(), 'clear' => 1));
						break;
					case 2 :
						$title = 'customer';
						$url = $this->getUrl('adminhtml/customer/edit', array('id' => $review->getEntityPkValue()));
						break;
				}

				$desc = $this->__('Reward Points earned for <a href="%s" target="_blank">'.$title.' review</a>.', $url);
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_CREDITS :
				if ($row->getPoints() > 0){
					$desc = $this->__('Reward Points earned for purchasing a product or service (%1$s).', $this->_getOrderLink($objId, 'order'));
				} else {
					$desc = $this->__('Reward Points earned on an %1$s that was later cancelled or refunded are no longer valid.', $this->_getOrderLink($objId, 'order'));
				}
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_ITEM_REFUND :
				$desc = $this->__('Reward Points refunded due to the partial or full %1$s refund.', $this->_getOrderLink($objId, 'order'));
				break;
			case Plumrocket_Rewards_Model_History::OBJ_TYPE_EXPIRED :
				$desc = $this->__('Reward Points have been expired.');
				break;
			default:
				$desc = $rowData['description'];
				break;
		}
		
		return $desc;
		
    }
}
