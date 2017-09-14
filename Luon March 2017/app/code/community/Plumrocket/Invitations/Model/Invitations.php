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


class Plumrocket_Invitations_Model_Invitations extends Mage_Core_Model_Abstract
{
	/*
   public $STATUS_DISABLED	= 'DISABLED';
   public $STATUS_ENABLED	= 'ENABLED';
	*/
	protected $_lastCustomerInvs = array();
	
	public function _construct()
    {
    	if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
        	parent::_construct();
        	$this->_init('invitations/invitations');
        }
    }

	public function getCustomerInvitations($customerId)
	{
		return $this
			->getCollection()
			->addFieldToFilter('customer_id', $customerId);
	}
	
	public function getCustomerOpenInvitations($customerId)
	{
		return $this->getCustomerInvitations($customerId)
			->addFieldToFilter('invitee_customer_id', 0)
			->setOrder('updated_at', 'DESC');
	}
	
	public function getCustomerAcceptedInvitations($customerId)
	{
		return $this->getCustomerInvitations($customerId)
			->addFieldToFilter('invitee_customer_id', array('neq' => 0))
			->setOrder('updated_at', 'DESC');
	}
	
	public function getCustomerInvitationsCount($customerId)
	{
		return $this->getCustomerInvitations($customerId)->count();
	}

	public function loadInvitation($inviteeConnectCode, $customerId, $guestId, $websiteId)
	{
		$collection = $this
			->getCollection()
			->addFieldToFilter('invitee_connect_code', $inviteeConnectCode)
			//->addFieldToFilter('addressbook_id', $addressBookId)
			->addFieldToFilter('website_id', $websiteId)
			->setPageSize(1);

		if ($guestId){
			$collection->addFieldToFilter('guest_id', $guestId);
		} else {
			$collection->addFieldToFilter('customer_id', $customerId);
		}

		$item = $collection->getFirstItem();


		if (!$item->getId()) {
			$item->addData(array(
				'website_id'			=> $websiteId,
				'invitee_connect_code'	=> $inviteeConnectCode,
			));

			if ($guestId){
				$item->setData('guest_id', $guestId);
			} else {
				$item->setData('customer_id', $customerId);
			}
		}

		return $item;
	}

	public function saveInvitation($inviteeName, $addressBookId)
	{
		if ($this->getInviteeCustomerId()){
			return false;
		}

		$date = Mage::getSingleton('core/date')->gmtDate();
		$data = array(
			'invitee_name'			=> $inviteeName,
			'addressbook_id'		=> $addressBookId,
			'updated_at'			=> $date,
		);

		if (!$this->getId()) {
			$data['created_at'] = $date;
		}

		return $this->addData($data)->save();

	}

	public function invitee($inviteeConnectCode, $inviteeName, $addressBookId, $customerId, $guestId, $websiteId)
	{
		$ivitation = $this->loadInvitation($inviteeConnectCode, $customerId, $guestId, $websiteId);
		return $ivitation->saveInvitation($inviteeName, $addressBookId);
	}

	public function isAccepted($inviteCustomer, $websiteId)
	{
		return $this
			->getCollection()
			->addFieldToFilter('invitee_connect_code', $inviteCustomer->getEmail())
			->addFieldToFilter('invitee_customer_id', array('neq' => 0))
			->addFieldToFilter('website_id', $websiteId)
			->count();
	}

	public function alreadyInvited($inviteeConnectCode, $customerId, $guestId, $websiteId)
	{
		$collection = $this
			->getCollection()
			->addFieldToFilter('invitee_connect_code', $inviteeConnectCode)
			->addFieldToFilter('website_id', $websiteId);

		if ($customerId) {
			$collection->addFieldToFilter('customer_id', array('neq' => $customerId));
		} else {
			$collection->addFieldToFilter('guest_id', array('neq' => $guestId));
		}

		return $collection->count();
	}

	public function acceptInvite($inviteCustomer, $invitationAcceptId, $websiteId)
	{
		if ($this->isAccepted($inviteCustomer, $websiteId)){
			return false;
		}

		$collection = $this
			->getCollection()
			->addFieldToFilter('website_id', $websiteId)
			->addFieldToFilter('invitee_connect_code', $inviteCustomer->getEmail())
			->addFieldToFilter('deactivated', 0)
			->setPageSize(1);

		if ($invitationAcceptId{0} == Plumrocket_Invitations_Helper_Data::GUEST_KEY_PREFIX){
			$guest_id		= substr($invitationAcceptId, 1);

			//customer can not invite himself
			$guest = Mage::getModel('invitations/guest')->loadByEmail($inviteCustomer->getEmail());
			if ($guest->getId() == $guest_id){
				return false;
			}

			$guest = Mage::getModel('invitations/guest')->load($guest_id);
			if (!$guest->getId()) {
				return false;
			}

			$customer = $guest->getCustomer();
			if ($customer->getId()) {
				$guest_id		= 0;
				$customer_id	= $customer->getId();
				$collection->addFieldToFilter('customer_id', $customer_id);
			} else {
				$customer_id	= 0;
				$collection->addFieldToFilter('guest_id', $guest_id);
			}

		} else {
			$guest_id		= 0;
			$customer_id	= $invitationAcceptId;
			$collection->addFieldToFilter('customer_id', $customer_id);
		}

		$invitation = $collection->getFirstItem();

		$result = false;
		$date = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
		if ($invitation && $invitation->getId())
		{
			$data = array(
				'confirmed_at' => $date,
				'invitee_customer_id' => $inviteCustomer->getId(),
			);
			if ($invitation->getInviteeName() == $invitation->getInviteeConnectCode())
				$data['invitee_name'] = $inviteCustomer->getName();
			$result =  $invitation->addData($data)->save();
		}
		else
		{
			$data = array(
				'website_id'			=> $websiteId,
				'invitee_name'			=> $inviteCustomer->getName(),
				'invitee_connect_code'	=> $inviteCustomer->getEmail(),
				'created_at'			=> $date,
				'updated_at' 			=> $date,
				'confirmed_at' 			=> $date,
				'invitee_customer_id'	=> $inviteCustomer->getId(),
			);
			if ($guest_id) {
				$data['guest_id'] = $guest_id;
			} else {
				$data['customer_id'] = $customer_id;
			}
			$result = $this->setData($data)->save();
		}

		if ($result && $result->getId())
		{
			//remove this customer form other invitations
			$invitations = $this
				->getCollection()
				->addFieldToFilter('invitee_connect_code', $inviteCustomer->getEmail())
				->addFieldToFilter('website_id', $websiteId)
				->addFieldToFilter('id', array('neq' => $result->getId()));
			
			foreach($invitations as $invitation){
				$invitation->addData(array('deactivated' => 1))->save();
			}
		}
		return $result;
	}
	
	public function deleteInvites($ids, $customerId)
	{
		$invitations = $this
			->getCollection()
			->addFieldToFilter('customer_id', $customerId)
			->addFieldToFilter('id', array('in' => $ids));
			
		foreach($invitations as $invitation){
			$invitation->delete();
		}
	}
	
	/* deprecated methods */
	public function getLastCustomerInv($seconds = 86400)
	{
		if (!isset($this->_lastCustomerInvs[$seconds]))
		{
			$this->_lastCustomerInvs[$seconds] = Mage::getModel('invitations/invitations')->getCollection()
				->addFieldToFilter('customer_id', Mage::helper('invitations')->getCurrentCustomerId())
				->addFieldToFilter('updated_at', array('from' => date('Y-m-d H:i:s', time() - $seconds))); 
		}
		return $this->_lastCustomerInvs[$seconds];
	}
	
	public function getLastABookCustomerInvCount($aBookId, $seconds = 86400)
	{
		$invitations = $this->getLastCustomerInv($seconds);
		$result = 0;
		foreach($invitations as $invitation)
			if ($invitation->getAddressBookId() == $aBookId)
				$result++;
		return $result;
	}
	/* end deprecated methods */

	public function getInviter()
	{
		if ($this->getCustomerId()) {
			$inviter = Mage::getModel('customer/customer')->load($this->getCustomerId());
		} else {
			$inviter = Mage::getModel('invitations/guest')->load($this->getGuestId());
		}

		return $inviter;

	}


	public function getEncodedInviteeConnectCode()
	{
		$value = explode('@', $this->getInviteeConnectCode());
		$value[0]{0} = '*';
		if (strlen($value[0]) > 3) {
			$value[0]{3} = '*';
		}

		if (strlen($value[0]) > 6) {
			$value[0]{6} = '*';
		}

		return implode('@', $value);

	}

	public function sendInvitationConfirmationEmail()
    {
    	$config = Mage::getModel('invitations/config');

		$inviter = $this->getInviter();

		if (!$inviter->getId()) {
			return;
		}

		$emailTemplateCode	= $config->sendInvitationConfirmationEmailTemplate();
        if (is_numeric($emailTemplateCode)){
			$emailTemplate  = Mage::getModel('core/email_template')->load($emailTemplateCode);
		} else {
			$emailTemplate  = Mage::getModel('core/email_template')->loadDefault($emailTemplateCode);
		}

		$couponCode = $this->getData('reward_coupon_code');
		if (!$couponCode) {
			$couponCode = Mage::helper('invitations')->getNewCouponCodeByRule($config->getInvitationConfirmationCouponRule());
			$this->setData('reward_coupon_code', $couponCode)->save();
		}

		$emailTemplateVariables = array(
			'inviter' => $inviter,
			'coupon_code' => $couponCode,
			'invitation' => $this
		);

		$emailTemplate
			->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'))
			->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));

		return $emailTemplate->send($inviter->getEmail(), $inviter->getName(), $emailTemplateVariables);

    }

}
