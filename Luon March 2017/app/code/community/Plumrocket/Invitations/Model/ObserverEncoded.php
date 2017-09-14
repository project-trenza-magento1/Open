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
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php

class Plumrocket_Invitations_Model_ObserverEncoded
{

	public function customerRegisterSuccess($customer)
	{
		if (!$customer || !$customer->getId()){
			return $this;
		}

		//accept invite if nead
		$cookie = Mage::getModel('core/cookie');
		if ($invitationAcceptId = $cookie->get('invitationAcceptId')){
			$cookie->delete('invitationAcceptId');
			$invitation = Mage::getModel('invitations/invitations')->acceptInvite($customer, $invitationAcceptId, Mage::app()->getWebsite()->getId());
			if ($invitation && $invitation->getId()) {

				if (Mage::getSingleton('invitations/config')->sendInvitationConfirmationEmailOnRegistration()) {
					$invitation->sendInvitationConfirmationEmail();
				}
				Mage::dispatchEvent('invitations_invitee_accept', array( 'invitation' => $invitation));
			}
		}


		//migrate guest invitations to customer
		$guest = Mage::getModel('invitations/guest')->loadByEmail($customer->getEmail());
		if ($guestId = $guest->getId()){
			$guest->setCustomer($customer);
			if ($guest->neadMigration()){
				if ($guest->neadValidateGuestOwnership()){
					$guest->sendVerificationEmail();
				} else {
					$guest->migration();
				}
			}
		}

		return $this;
	}

	public function salesOrderPlaceAfter($observer)
	{
		$order = $observer->getOrder();

		if (!$order->getCustomerId() && $order->getCustomerEmail()) {
			$cookie = Mage::getModel('core/cookie');
			if ($invitationAcceptId = $cookie->get('invitationAcceptId')){
				$cookie->delete('invitationAcceptId');

				$email = $order->getCustomerEmail();
				$customer = new Varien_Object(array(
					'email' => $email,
					'name' => $email,
				));

				$invitation = Mage::getModel('invitations/invitations')->acceptInvite($customer, $invitationAcceptId, Mage::app()->getWebsite()->getId());
			}
		}
	}


	public function salesOrderInvoiceRegister($observer)
    {
		if (!(Mage::helper('invitations')->moduleEnabled())){
			return $observer;
		}

		$order = $observer->getOrder();

		//if ( $order && ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) && (count($order->getAllItems()) > 0))
		//{
			$orderCriteria		= Mage::getModel('invitations/config')->getFirstOrderCriteria();
			$useOrderCriteria	= Mage::helper('invitations')->modulePlumrocketRewardpointsEnabled();

			// validate order by grand total
			//if ($useOrderCriteria && $order->getSubtotal() < $orderCriteria['min_sub_total']){
			if ($useOrderCriteria && ($orderCriteria['min_sub_total'] > 0) && ($order->getSubtotal() < $orderCriteria['min_sub_total']) ){
				return $observer;
			}

			$modelInvitations	= Mage::getModel('invitations/invitations');
			//$modelInvitationsClassName = get_class($modelInvitations);

			$invitation = $modelInvitations->getCollection();
			if ($order->getCustomerId()) {
				$invitation->addFieldToFilter('invitee_customer_id', $order->getCustomerId());
			} else {
				$invitation->addFieldToFilter('invitee_connect_code', $order->getCustomerEmail());
			}

			$invitation = $invitation
				//->addFieldToFilter('customer_id', array('neq' =>  0))
				->addFieldToFilter('first_order', 0)
				->addFieldToFilter('deactivated', 0)
				->setPageSize(1)
				->getFirstItem();

			if (!$invitation->getId()) {
				return;
			}

			if ($invitation->getCustomerId())
			{
				// validate order by days after customer registration
				if ($useOrderCriteria && ($orderCriteria['max_days'] > 0) )
				{
					$confirmedAt = $invitation->getConfirmedAt();
					if ($invitation->getGuestId()){
						$inviter = Mage::getModel('customer/customer')->load($invitation->getCustomerId());
						if ($inviter->getCreatedAt() > $confirmedAt){
							$confirmedAt = $inviter->getCreatedAt();
						}
					}

					$orderCreatedAt = strtotime($order->getCreatedAt());
					$confirmedTime = strtotime($confirmedAt);
					if ( (($orderCreatedAt - $confirmedTime) / 86400)  > $orderCriteria['max_days']){
						return $observer;
					}
				}

				if (Mage::getSingleton('invitations/config')->sendInvitationConfirmationEmailOnPlaceOrder()) {
					$invitation->sendInvitationConfirmationEmail();
				}

				$invitation->addData(array('first_order'  => 1))->setId($invitation->getId())->save();

				Mage::dispatchEvent('invitations_invitee_customer_first_order',
					array(
						'customer_id'			=> $invitation->getCustomerId(),
						'invitee_customer_id'	=> $invitation->getInviteeCustomerId(),
						'order'					=> $order,
					)
				);
			} else {
				if (Mage::getSingleton('invitations/config')->sendInvitationConfirmationEmailOnPlaceOrder()) {
					$invitation->addData(array('first_order'  => 1))->setId($invitation->getId())->save();
					$invitation->sendInvitationConfirmationEmail();
				}
			}

    }
}

