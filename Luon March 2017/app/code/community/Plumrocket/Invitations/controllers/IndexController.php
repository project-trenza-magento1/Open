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


class Plumrocket_Invitations_IndexController extends Mage_Core_Controller_Front_Action
{
	private $_customer = NULL;
	public function preDispatch()
    {
		parent::preDispatch();

		$openActions = array('accept', 'noroute');
		if (Mage::getModel('invitations/config')->getGuestsCanMakeInvites()){
			$openActions[] = 'send';
		}
		
		$_helper = Mage::helper('invitations');
		if (!($_helper->moduleEnabled())){
			$this->_toBaseUrl();
		}
		 
		$customer = $_helper->getCurrentCustomer();

		if ($this->getRequest()->getActionName() == 'noroute' && Mage::helper('invitations')->isReferralPath()) {
			$this->_forward('accept');
			return;
		}
			
		if (in_array($this->getRequest()->getActionName(), $openActions)){
			$this->_customer = $customer;
		} elseif ($customer && $customer->getId()){
			$this->_customer = $customer;
		} else {
			$this->_toBaseUrl();
		}
    }
	
	private function _toBaseUrl()
	{
		 header('Location: '.Mage::getUrl('customer/account/login'));
		 exit();
	}
	
	public function indexAction() {
	 	$this->loadLayout();
		$this->getRequest()->setParam('customer', $this->_customer);  
      	$this->renderLayout(); 
    }

	
    protected function _lastSendCount($customerId, $guestId, $timeLimit)
    {
    	if (!$timeLimit){
    		return 0;
    	}

    	$collection = Mage::getModel('invitations/invitations')->getCollection();
    	if ($customerId){
    		$collection->addFieldToFilter('customer_id', $customerId);
    	} else {
    		$collection->addFieldToFilter('guest_id', $guestId);
    	}

    	$collection->addFieldToFilter('updated_at', array('gteq' => date('Y-m-d H:i:s', time() - $timeLimit * 60)) );

    	return count($collection);
    }

	public function sendAction()
	{
		$request			= $this->getRequest();
		$contacts			= $request->getParam('contacts');
		$helper				= Mage::helper('invitations');
		$modelInvitations	= Mage::getModel('invitations/invitations');
		$modelConfig		= Mage::getModel('invitations/config');
		
		$websiteId			= Mage::app()->getWebsite()->getId();
		$currentCustomerId 	= $helper->getCurrentCustomerId();
		
		$errors = array();
		$notices = array();
		$successes = array();

		list($maxSendCount, $sendTimeLimit) = $modelConfig->getInviteeSendLimit();
		
		if (!$currentCustomerId && $modelConfig->getGuestsCanMakeInvites()){
			$guestEmail = $request->getParam('guest_email');
			if (!$guestEmail || !Zend_Validate::is($guestEmail, 'EmailAddress')){
				$errors[] = $helper->__('Please enter a valid email address. For example johndoe@domain.com.');
				return $this->_setSendResponse($successes, $errors, $notices);
			} else {
				$customer = $helper->getCustomerByEmail($guestEmail);

				if ($customer->getId()) {
					/*
					$errors[] = $helper->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.',
						Mage::helper('customer')->getLoginUrl());
					return $this->_setSendResponse($successes, $errors, $notices);
					*/
					$customerInfo = $customer;
					$lastSendCount = $this->_lastSendCount($customerInfo->getId(), null, $sendTimeLimit);
				} else {
					$customerInfo = Mage::getModel('customer/customer')
						->setFirstName($this->__('Your'))
						->setLastName($this->__('Friend'))
						->setEmail($guestEmail)
						->setGuestId(Mage::getModel('invitations/guest')->getGuestId($guestEmail));

					$lastSendCount = $this->_lastSendCount(null, $customerInfo->getGuestId(), $sendTimeLimit);
				}
			}
		} else {
			$customerInfo = $this->_customer;
			$lastSendCount = $this->_lastSendCount($customerInfo->getId(), null, $sendTimeLimit);
		}

		if ($maxSendCount){
			$leftSendCount = $maxSendCount - $lastSendCount;
		} else {
			$leftSendCount = 9999;
		}

		if ($leftSendCount <= 0){
			$notices[] = $helper->__('You can not sending more than %s emails per %s minutes. Please try again later.', $maxSendCount, $sendTimeLimit);
			return $this->_setSendResponse($successes, $errors, $notices);
		}

		$emails = array();
		foreach($contacts as $key => $contact){
			if (!empty($contact['connectCode'])){
				if ($contact['connectCode'] == $customerInfo->getEmail()){
					unset($contacts[$key]);
					$notices[] = $helper->__('You cannot invite yourself');
				} elseif (Zend_Validate::is($contact['connectCode'], 'EmailAddress')){
					$contacts[$key]['connectCode'] = strtolower($contacts[$key]['connectCode']);
					$emails[] = $contacts[$key]['connectCode'];
				} else {
					unset($contacts[$key]);
					$notices[] = $helper->__('"%s" is invalid email.',$contact['connectCode']);
				}
			} else {
				unset($contacts[$key]);
			}
		}

		$regEmails = array();
		if ($emails)
		{
			$regCustomers = Mage::getModel('customer/customer')
				->getCollection()
				->addFieldToFilter('email', array('in' => $emails))
				->addFieldToFilter('website_id', $websiteId);

			foreach ($regCustomers as $customer){
				$regEmails[$customer->getEmail()] = true;
			}
		}


		foreach($contacts as $key => $contact){
			if (!empty($regEmails[$contact['connectCode']])){
				unset($contacts[$key]);
				$notices[] = $helper->__('"%s" is already a member.',$contact['connectCode']);
			}
		}

		if (empty($contacts)){
			//$errors[] = $helper->__('No one valid contact given.');
		} else {

			$emailTemplateVariables = array(
				'referral_link' => $helper->getRefferalLink(true, $customerInfo->getGuestId(), $customerInfo->getId()),
				'referral_link_href' => $helper->getRefferalLink(false, $customerInfo->getGuestId(), $customerInfo->getId()),
				'customer_name' => trim($customerInfo->getName()) ? $customerInfo->getName() : $customerInfo->getEmail(),
			);

			$emailTemplateCode = $modelConfig->getInviteEmailTemplate();
			if (!$modelConfig->editInviteeText()){
				$emailTemplateVariables['text'] = nl2br($helper->getFilteredInviteeText($emailTemplateVariables, false));
			} else {
				$emailTemplateVariables['text'] = nl2br(htmlspecialchars($request->getParam('message')));
				if ($customerInfo->getGuestId()) {
					$emailTemplateVariables['text'] = str_replace(
						$helper->getRefferalLink(false),
						$emailTemplateVariables['referral_link_href'],
						$emailTemplateVariables['text']
					);
				}
			}

			$emailTemplateVariables['subject'] = $subject = $helper->getFilteredInviteeSubject($emailTemplateVariables);

			$aBooks = Mage::getModel('invitations/addressbooks')->getEnabled();
			$aBooksById = array();

			foreach($aBooks as $aBook)
				$aBooksById[$aBook->getId()] = $aBook;

			$aCounts = array(); //availableCounts
			$sendedEmails = array();

			foreach($contacts as $key => $contact){

				if ($leftSendCount <= 0){
					$notices[] = $helper->__('You can not sending more than %s emails per 5 minutes. Please try again later.', $maxSendCount);
					return $this->_setSendResponse($successes, $errors, $notices);
				}

				$send = false;
				$contact['connectCode'] = trim($contact['connectCode']);

				if (isset($sendedEmails[$contact['connectCode']])){
					continue;
				}

				if (
					   !empty($contact['connectCode'])
					&& !empty($contact['inviteeName'])
					&& isset($contact['addressBookId'])
				) {

					if ($modelConfig->invitationPerOne()
						&& $modelInvitations->alreadyInvited($contact['connectCode'], $customerInfo->getId(), $customerInfo->getGuestId(), $websiteId)
					) {
						$notices[] = $helper->__('"%s" already invited by other person.',$contact['connectCode']);
						continue;
					}

					$invitation = $modelInvitations->loadInvitation($contact['connectCode'], $customerInfo->getId(), $customerInfo->getGuestId(), $websiteId);
					$couponCode = $invitation->getInviteeCouponCode();
					if (!$couponCode) {
						$couponCode = $helper->getNewCouponCodeByRule($modelConfig->getInvitationCouponRule());
					}

					$emailTemplateVariables['coupon_code'] = $couponCode;

					$send = $this->_mail($customerInfo, $contact['connectCode'], $contact['inviteeName'], $subject, $emailTemplateCode, $emailTemplateVariables);
					$sendedEmails[$contact['connectCode']] = true;

					if ($send) {
						$invitation
						   ->setData('invitee_coupon_code', $couponCode)
						   ->saveInvitation( $contact['inviteeName'], $contact['addressBookId']);
						$successes[] = $helper->__('"%s" invited successfully.',$contact['connectCode']);
						$leftSendCount--;
						continue;
					}
				}

				$notices[] = $helper->__('You can not invite '. htmlspecialchars($contact['connectCode']));

			}
		}

		$this->_setSendResponse($successes, $errors, $notices);
	}

	protected function _setSendResponse($successes, $errors, $notices)
	{
		$result = array('success' => empty($errors), 'messages' => array(
			'errors' => (empty($errors)) ? false : implode('<br/>', $errors), //false,
			'successes' => (empty($successes)) ? false : implode('<br/>', $successes),
			'notices' => (empty($notices)) ? false : implode('<br/>', $notices),
		));
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		
		return $this;
	}
	

	public function acceptedInvitationsAction()
	{
		$data = array(
			'success'	=> true,
			'result'	=> $this->getLayout()->createBlock('invitations/accepted')->setData('customerId', $this->_customer->getId())->toHtml(),
		);
		$this->getResponse()->setBody(json_encode($data));
	}
	
	public function openInvitationsAction()
	{
		$data = array(
			'success'	=> true,
			'result'	=> $this->getLayout()->createBlock('invitations/open')->setData('customerId', $this->_customer->getId())->toHtml(),
		);
		$this->getResponse()->setBody(json_encode($data));
	}
	
	public function deleteOpenInvitationsAction()
	{
		$invitationIds = $this->getRequest()->getParam('invitation');
		$modelInvitations = Mage::getModel('invitations/invitations');
		$modelInvitations->deleteInvites($invitationIds, $this->_customer->getId());
		
		$this->getResponse()->setBody(
			$this->getLayout()->createBlock('invitations/open')->setData('customerId', $this->_customer->getId())->toHtml()
		);
	}
	
	public function acceptAction()
	{
		Mage::register( 'turpentine_nocache_flag', true, true );

		$key = $this->_getAcceptKey();
		if (!empty($key) && !($this->_customer && $this->_customer->getId()))
		{
			//Mage::getSingleton('core/session')->setData('invitationAcceptId', $customerId);
			Mage::getModel('core/cookie')->set('invitationAcceptId', $key, 61516800);
			$this->loadLayout();
      		$this->renderLayout(); 
			return;
		}
		//$this->_redirect('customer/account/login');
		$this->_redirect('/');
	}
	
	protected function _getAcceptKey()
	{
		$cUrl = Mage::helper('core/url')->getCurrentUrl();
		$items = explode('/',$cUrl);
		$count = count($items);
		if ($items[$count - 1])
			return $items[$count - 1];
		elseif ($count > 2)
			return $items[$count - 2];
		else
			return false;
	}

	
	private function _mail($customerInfo, $toEmail, $toName, $subject, $emailTemplateCode, $emailTemplateVariables)
	{
		if (is_numeric($emailTemplateCode))
			$emailTemplate  = Mage::getModel('core/email_template')->load($emailTemplateCode); 
		else
			$emailTemplate  = Mage::getModel('core/email_template')->loadDefault($emailTemplateCode); 
									
        $emailTemplate->getProcessedTemplate($emailTemplateVariables);
        $emailTemplate->setTemplateSubject($subject);
		
		if (Mage::getModel('invitations/config')->getEmailFromStore())
		{
			$emailTemplate
				->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'))
				->setSenderName($customerInfo->getName())
				->setReplyTo($customerInfo->getEmail());
		}
		else
		{
			$emailTemplate
				->setSenderEmail($customerInfo->getEmail())
				->setSenderName($customerInfo->getName());
		}
            
		return $emailTemplate->send($toEmail, $toName, $emailTemplateVariables);   
	}
    
/*
    private function _getStoreName(){
        if (empty($this->_data['store_name'])) {
            $this->_data['store_name'] = Mage::app()->getStore()->getName();
        }
        return $this->_data['store_name'];
    }

    private function _getStoreLogoUrl(){
        if (empty($this->_data['store_logo_url'])) {
            $this->_data['store_logo_url'] = $this->getLayout()->createBlock('invitations/default')->getSkinUrl(Mage::getStoreConfig('design/header/logo_src'));
        }
        return $this->_data['store_logo_url'];
    }
    
    private function _getStoreDescription(){
        if (empty($this->_data['store_description'])) {
            $this->_data['store_description'] = Mage::getStoreConfig('design/head/default_description');
        }
        return $this->_data['store_description'];
    }
*/
}
