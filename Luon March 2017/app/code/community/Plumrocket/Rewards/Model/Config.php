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

class Plumrocket_Rewards_Model_Config /* extends Mage_Core_Model_Abstract */
{
	
	protected $_storeId	= null;
	protected $_prefix	= 'rewards/';
	
	public function setStoreId($storeId)
	{
		$this->_storeId = $storeId;
		return $this;
	}
	
	protected function _getConfigValue($option)
	{
		return Mage::getStoreConfig($this->_prefix.$option, $this->_storeId); 
	}
	
	public function modulEnabled()
	{
		return Mage::helper('rewards')->moduleEnabled($this->_storeId);
	}
	
	public function isGlobalCredits()
	{
		return $this->_getConfigValue('general/global_credits');
	}

	public function getPointsExpiration()
	{
		return (int) $this->_getConfigValue('general/expiration');	
	}
	
	public function getMinRedeemableCredit()
	{
		$v = (int) $this->_getConfigValue('points_redemption/min_redeemable_credit');
		if (empty($v)){ $v = 0; }
		return $v;
	}
	
	public function getMaxRedeemableCredit()
	{
		$v = (int) $this->_getConfigValue('points_redemption/max_redeemable_credit');
		if (empty($v)){ $v = 0; }
		return $v;
	}

	
	public function getRedeemPointsRate()
	{
		$v = (int) $this->_getConfigValue('points_redemption/redeem_points_rate');
		if (empty($v) || $v < 0){ $v = 1; }
		return $v;
	}

	public function getRedeemPointsWithCoupon()
	{
		return (bool) $this->_getConfigValue('points_redemption/with_coupon_code');
	}
	
	public function getEarnPointsRate()
	{
		$v = (int) $this->_getConfigValue('order_credits/earn_points_rate');
		if (empty($v) || $v < 0){ $v = 0; }
		return $v;
	}

	public function getEarnPointsIncTax()
	{
		return (bool) $this->_getConfigValue('order_credits/include_tax');
	}

	public function customerCanEarnPoints($customer)
	{
		if (!$customer) {
			return false;
		}

		if ($groups = $this->_getConfigValue('order_credits/customer_groups')) {
			if (!is_object($customer)) {
				$customer = Mage::getModel('customer/customer')->load($customer);
			}
			$groups = explode(',', $groups);
			return in_array($customer->getGroupId(), $groups);
		}
		return true;
	}

	public function getInviteAwardReason()
	{
		return $this->_getConfigValue('invitations_credits/award_reason');
	}

	public function getInviteCredit()
	{
		return $this->_getConfigValue('invitations_credits/credits');
	}
	
	public function getRegistrationCredit()
	{
		return $this->_getConfigValue('registration_credits/credits');
	}

	public function getRegistrationRestriction()
	{
		return $this->_getConfigValue('registration_credits/restriction');
	}
	
	public function getReviewsCredit()
	{
		return $this->_getConfigValue('reviews_credits/credits');
	}
	
	public function getDoubleReview()
	{
		return $this->_getConfigValue('reviews_credits/double_review');
	}
	
	public function getReviewsCreditOnlyForBuyers()
	{
		return $this->_getConfigValue('reviews_credits/only_for_buyers');
	}

	public function isNotificationsEnabled()
	{
		return $this->_getConfigValue('notification/enabled');
	}


	public function getPointsExpirationEmailBefore()
	{
		return $this->_getConfigValue('notification/points_expiration_email_before');
	}

	public function getPointsBalanceEmailTemplate($storeId = null){
		return $this->_getEmailTemplate($this->_getConfigValue('notification/points_balance_email_template', $storeId));
	}

	public function getPointsExpirationEmailTemplate($storeId = null){
		return $this->_getEmailTemplate($this->_getConfigValue('notification/points_expiration_email_template', $storeId));
	}


	protected function _getEmailTemplate($emailTemplateCode){
		if (is_numeric($emailTemplateCode)){
			return Mage::getModel('core/email_template')->load($emailTemplateCode); 
		} else {
			return Mage::getModel('core/email_template')->loadDefault($emailTemplateCode); 
		}
	}
}
	 
