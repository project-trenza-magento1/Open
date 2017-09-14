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
 * @package	 Plumrocket_Reward_Points
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license	 http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Rewards_Model_Cron
{
	protected $_testEmail = '';

	public function removeExpiredPoints()
	{
		if (!Mage::getModel('rewards/config')->modulEnabled()) {
			return;
		}

		$collection = Mage::getModel('rewards/points')->getCollection()
			->addFieldToFilter(
				array('spent', 'available'),
				array(
					array('eq' => 0),
					array('neq' => 0),
				)
			)
			->addFieldToFilter('expire_at', array('neq' => '0000-00-00'))
			->addFieldToFilter('expire_at', array('lt' => Mage::getModel('core/date')->date('Y-m-d')));

		$objType 	= Plumrocket_Rewards_Model_History::OBJ_TYPE_EXPIRED;
		$createdAt 	= Mage::getModel('core/date')->date('Y-m-d H:i:s');

 		foreach($collection as $item){

 			$data = array(
				'customer_id' 		=> $item->getCustomerId(),
				'store_group_id' 	=> $item->getStoreGroupId(),
				'obj_type'	  		=> $objType,
				'points'	  		=> - $item->getAvailable(),
				'created_at'  		=> $createdAt,
			);

			if ($item->getSpent()){
				$item->setAvailable(0)->save();
			} else {
				$item->delete();
			}

			Mage::getModel('rewards/history')->add($data);
		}
	}


	public function pointsBalanceNotifications()
	{
		$config = Mage::getModel('rewards/config');
		if (!$config->modulEnabled() || !$config->isNotificationsEnabled()){
			return;
		}

		$senderEmail	= Mage::getStoreConfig('trans_email/ident_general/email');
		$senderName		= Mage::getStoreConfig('trans_email/ident_general/name');

		$collection = Mage::getModel('rewards/points')->getCollection()
			->addFieldToFilter('notify', 1)
			->addFieldToFilter('available', array('gt' => 0));

		$customers = array();
		foreach($collection as $item){
			$customers[$item->getCustomerId()][] = $item;
		}

		$sendEmailHistoryEnabled = Mage::helper('core')->isModuleEnabled('Plumrocket_SendEmailHistory');

		foreach($customers as $customerId => $items){
			$customer = Mage::getModel('customer/customer')->load($customerId);

			if ($customer->getId()){

				if ($this->_testEmail) {
					$customer->setEmail($this->_testEmail);
				}

				$appEmulation = Mage::getSingleton('core/app_emulation');
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($customer->getStoreId());

				try {

					$earnedPoints = 0;
					foreach($items as $item){
						$earnedPoints += $item->getAvailable();
					}

					$points = Mage::getModel('rewards/points')->getByCustomer($customer->getId(), $item->getStoreGroupId());
					$redeemRate = $points->getConfig()->getRedeemPointsRate();

					$availablePoints = $points->getAvailable();
					$previousPoints = $availablePoints - $earnedPoints;

					$emailTemplateVariables = array(
						'customer'		=> $customer,
						'available'		=> $earnedPoints, //deprecated

						'earned_points' => $earnedPoints,
						'available_points' => $availablePoints,
						'previous_points' => $previousPoints,

						'earned_amount' => Mage::helper('core')->currency(
							$earnedPoints / $redeemRate,
							true, false
						),
						'available_amount' => Mage::helper('core')->currency(
							$availablePoints / $redeemRate,
							true, false
						),
						'previous_amount' => Mage::helper('core')->currency(
							$previousPoints / $redeemRate,
							true, false
						),
						'expire_at' 	=> $item->getExpireAt() ? date('F d, Y',strtotime($item->getExpireAt())) : '',
						'store'			=> $customer->getStore(),
					);

					$emailTemplate = $config->getPointsBalanceEmailTemplate($customer->getStoreId())
						->setSenderEmail($senderEmail)
						->setSenderName($senderName)
						->setReplyTo($senderEmail);

					//echo $emailTemplate->getProcessedTemplate($emailTemplateVariables); exit();

					if ($sendEmailHistoryEnabled) {
						$send = Mage::getModel('sendemailhistory/history')->sendAndSave(
							$emailTemplate,
							$customer->getEmail(),
							$customer->getName(),
							$emailTemplateVariables,
							$customer
						);

					} else {
						$send = $emailTemplate->send($customer->getEmail(), $customer->getName(), $emailTemplateVariables);
					}

				} catch (Exception $e) {
					Mage::log($e->getMessage(), null, 'plumrocket_rewards.log');
				}

				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			}

			if (!$this->_testEmail) {
				if (!$customer->getId() || $send){
					foreach($items as $item){
						$item->setNotify(0)->save();
					}
				}
			}
		}
	}


	public function pointsExpirationNotifications()
	{
		$config = Mage::getModel('rewards/config');
		if (!$config->modulEnabled() || !$config->isNotificationsEnabled()){
			return;
		}

		$daysBefore = $config->getPointsExpirationEmailBefore();

		if ($daysBefore <= 0){
			return;
		}

		$sendEmailHistoryEnabled = Mage::helper('core')->isModuleEnabled('Plumrocket_SendEmailHistory');

		$senderEmail	= Mage::getStoreConfig('trans_email/ident_general/email');
		$senderName		= Mage::getStoreConfig('trans_email/ident_general/name');

		$expireAt = date('Y-m-d', Mage::getModel('core/date')->timestamp() + $daysBefore * 86400);

		$collection = Mage::getModel('rewards/points')->getCollection()
			->addFieldToFilter('expire_at', $expireAt)
			->addFieldToFilter('available', array('gt' => 0));

		$customers = array();
		foreach($collection as $item){
			$customers[$item->getCustomerId()][] = $item;
		}

		foreach($customers as $customerId => $items){
			$customer = Mage::getModel('customer/customer')->load($customerId);

			if ($customer->getId()){

				if ($this->_testEmail) {
					$customer->setEmail($this->_testEmail);
				}

				$appEmulation = Mage::getSingleton('core/app_emulation');
				$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($customer->getStoreId());

				try {

					$availablePoints = 0;
					foreach($items as $item){
						$availablePoints += $item->getAvailable();
					}

					$redeemRate = $items[0]->getConfig()->getRedeemPointsRate();

					$emailTemplateVariables = array(
						'customer'		=> $customer,
						'available'		=> $availablePoints, //deprecated
						'available_points' => $availablePoints,
						'available_amount' => Mage::helper('core')->currency(
							$availablePoints / $redeemRate,
							true, false
						),
						'store'			=> $customer->getStore(),
						'days_before'	=> $daysBefore,
						'expire_at' 	=> date('F d, Y',strtotime($expireAt)),
					);

					$emailTemplate = $config->getPointsExpirationEmailTemplate($customer->getStoreId())
						->setSenderEmail($senderEmail)
						->setSenderName($senderName)
						->setReplyTo($senderEmail);

					//echo $emailTemplate->getProcessedTemplate($emailTemplateVariables); exit();

					if ($sendEmailHistoryEnabled) {
						$send = Mage::getModel('sendemailhistory/history')->sendAndSave(
							$emailTemplate,
							$customer->getEmail(),
							$customer->getName(),
							$emailTemplateVariables,
							$customer
						);

					} else {
						$send = $emailTemplate->send($customer->getEmail(), $customer->getName(), $emailTemplateVariables);
					}

					/*if ($this->_testEmail) {
						break;
					}*/

				} catch (Exception $e) {
					Mage::log($e->getMessage(), null, 'plumrocket_rewards.log');
				}

				$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
			}
		}
	}

}

