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


class Plumrocket_Newsletterpopup_Model_Observer
{	
	public function adminSystemConfigChangedSection($observer)
	{
		$data = $observer->getEvent()->getData();

		if ($data['section'] == 'newsletterpopup') {
			$groups = Mage::app()->getRequest()->getParam('groups');
			foreach (array('desktop', 'tablet', 'mobile') as $type) {
				$groups['size']['fields'][$type]['value'][1] = preg_replace("/[^0-9]/", "", $groups['size']['fields'][$type]['value'][1]);
			}

	        Mage::getSingleton('adminhtml/config_data')
	            ->setSection($data['section'])
	            ->setWebsite($data['website'])
	            ->setStore($data['store'])
	            ->setGroups($groups)
	            ->save();
	        Mage::getConfig()->reinit();
    	}
    	return $observer;
	}

	public function saveOrder($observer)
	{
		if (!Mage::helper('newsletterpopup')->moduleEnabled()) {
			return $this;
		}

		$order = $observer->getEvent()->getOrder();

		if ($code = $order->getCouponCode()) {
			$email = $order->getCustomerEmail();
			$historyItem = NULL;

			if ($email) {
				$historyItem = Mage::getModel('newsletterpopup/history')
					->getCollection()
					->addFieldToFilter('customer_email', $email)
					->addFieldToFilter('coupon_code', $code)
					->getFirstItem();
			}

			if (is_null($historyItem) || ! $historyItem->getId()) {
				$historyItem = Mage::getModel('newsletterpopup/history')->load($code, 'coupon_code');
			}

			if ($historyItem->getId()) {
				if (($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED)
					|| ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED)
				) {
					$this->_save($historyItem, 0, 0, 0);
				} else {
					$this->_save(
						$historyItem,
						$order->getIncrementId(),
						$order->getId(),
						$order->getGrandTotal()
					);
				}
			}
		}
	}

	private function _save($historyItem, $incrementId, $orderId, $grandTotal)
	{
		$boolHistoryGT = $historyItem->getData('grand_total') > 0;
		$boolGT = $grandTotal > 0;

		if ($boolGT != $boolHistoryGT) {
			$popup = Mage::getModel('newsletterpopup/popup')->load( $historyItem->getPopupId() );
			// check if linked popup exists
			if ($popup->getId()) {
				$tr = ($grandTotal)? 
					$popup->getData('total_revenue') + $grandTotal:
					$popup->getData('total_revenue') - $historyItem->getData('grand_total');
				$addOrdersCount = ($grandTotal)? 1: -1;

				$popup
					->setData('orders_count', $popup->getData('orders_count') + $addOrdersCount)
					->setData('total_revenue', $tr)
					->save();
			}

			$historyItem
				->setData('increment_id', $incrementId)
				->setData('order_id', $orderId)
				->setData('grand_total', $grandTotal)
				->save();
		}
	}

	public function customerLogin($observer)
	{
		$helper = Mage::helper('newsletterpopup');
		if (!$helper->moduleEnabled()) {
			return $this;
		}

		if ($customer = $observer->getCustomer()) {
			$helper->visitorId($customer->getId());
		}
	}

}
