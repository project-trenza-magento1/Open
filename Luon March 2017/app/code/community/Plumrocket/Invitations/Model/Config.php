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
 * @package     Plumrocket_Invitations
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php

class Plumrocket_Invitations_Model_Config extends Mage_Core_Model_Abstract
{
	public function showReferralLink()
	{
		return Mage::getStoreConfig('invitations/invitee/show_referral_link');
	}
	
	public function getDefaultReferralLink()
	{
		return '/invitations/index/accept/';
	}
	
	public function getReferralLink()
	{
		$result = strtolower(Mage::getStoreConfig('invitations/invitee/referral_link'));
		if (!$result || $result == '/')
			$result = $this->getDefaultReferralLink();
			
		return $result;
	}

	public function invitationsViaShares()
	{
		return Mage::getStoreConfig('invitations/invitee/invitations_via_shares');
	}

	public function invitationPerOne()
	{
		return Mage::getStoreConfig('invitations/invitee/invitation_per_one');
	}

	public function editInviteeText()
	{
		return Mage::getStoreConfig('invitations/email/edit_invitee_text');
	}

	public function getEmailFromStore()
	{
		return Mage::getStoreConfig('invitations/email/email_from_store');
	}
	
	public function getInviteEmailTemplate()
	{
		return Mage::getStoreConfig('invitations/email/template');
	}
	
	public function getInviteeEmailText()
	{
		return Mage::getStoreConfig('invitations/email/text');
	}
	
	public function getInviteeEmailSubject()
	{
		return Mage::getStoreConfig('invitations/email/subject');
	}

	public function getInvitationCouponRule()
	{
		return Mage::getStoreConfig('invitations/email/coupon_rule');
	}

	public function getFirstOrderCriteriaMaxDays()
	{
		return Mage::getStoreConfig('rewards/invitations_credits/max_days');
	}
	
	public function getFirstOrderCriteriaMinSubTotal()
	{
		return Mage::getStoreConfig('rewards/invitations_credits/min_sub_total');
	}
	
	public function getFirstOrderCriteria()
	{
		return array(
			'max_days' => $this->getFirstOrderCriteriaMaxDays(),
			'min_sub_total' => $this->getFirstOrderCriteriaMinSubTotal(),
		);
	}
	
	
	public function getGuestsCanMakeInvites()
	{
		return Mage::getStoreConfig('invitations/promo_page/guest_invites');
	}
	
	public function validateGuestOwnership()
	{
		return Mage::getStoreConfig('invitations/promo_page/validate_guest_ownership');
	}

	public function getGuestVerificationEmailTemplate()
	{
		return Mage::getStoreConfig('invitations/promo_page/guest_verification_email_template');
	}

	public function sendInvitationConfirmationEmail()
	{
		return Mage::getStoreConfig('invitations/invitation_confirmation_email/enabled');
	}

	public function sendInvitationConfirmationEmailOnRegistration()
	{
		return $this->sendInvitationConfirmationEmail()
			&& Mage::getStoreConfig('invitations/invitation_confirmation_email/term') == 1;
	}

	public function sendInvitationConfirmationEmailOnPlaceOrder()
	{
		return $this->sendInvitationConfirmationEmail()
			&& Mage::getStoreConfig('invitations/invitation_confirmation_email/term') == 0;
	}

	public function sendInvitationConfirmationEmailTemplate()
	{
		return Mage::getStoreConfig('invitations/invitation_confirmation_email/template');
	}

	public function getInvitationConfirmationCouponRule()
	{
		return Mage::getStoreConfig('invitations/invitation_confirmation_email/coupon_rule');
	}

	public function getPromoPage()
	{
		$result = array(
			'enabled'				=> Mage::getStoreConfig('invitations/promo_page/enabled'),
			'guest_invites'			=> $this->getGuestsCanMakeInvites(),
			
			'meta_title'			=> Mage::getStoreConfig('invitations/promo_page/meta_title'),
			'meta_keywords'			=> Mage::getStoreConfig('invitations/promo_page/meta_keywords'),
			'meta_description'		=> Mage::getStoreConfig('invitations/promo_page/meta_description'),
			
			'title'					=> Mage::getStoreConfig('invitations/promo_page/title'),
			'description'			=> Mage::getStoreConfig('invitations/promo_page/description'),
			'bg_image'				=> Mage::getStoreConfig('invitations/promo_page/bg_image'),
		);
		
		if ($result['bg_image'])
			$result['bg_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'invitations/'.$result['bg_image'];
			
		return $result;
		
	}

	public function getInviteeSendLimit()
	{
		$data = explode('/', Mage::getStoreConfig('invitations/invitee/send_limit'));
		if (count($data) < 2){
			$data = array(0, 0);
		}

		return $data;
	}

}
	 
