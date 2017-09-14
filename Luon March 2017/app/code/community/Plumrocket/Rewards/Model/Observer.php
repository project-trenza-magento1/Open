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

class Plumrocket_Rewards_Model_Observer
{
	public function getEncodedModel()
	{
		return Mage::getSingleton('rewards/observerEncoded');
	}

	public function salesQuoteAddressCollectTotalsAfter($observer)
	{
		if (Mage::getSingleton('rewards/config')->modulEnabled()){

			$quote 	= $observer->getEvent()->getQuote();

			foreach($quote->getAllAddresses() as $address) {
				$items = $address->getAllNonNominalItems();

		        if (!count($items)) {
		            continue;
		        }

		        $address->setBaseRpdiscountAmount(
		        	Mage::getSingleton('rewards/points')->getBaseRpdiscountAmount(false)
		        );

		    }
		}

		return $this;
	}

	public function salesOrderPlaceBefore($observer)
	{
		$order		= $observer->getEvent()->getOrder();
		$_config	= Mage::getModel('rewards/config')->setStoreId($order->getStoreId());

		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->salesOrderPlaceBefore($observer);
		}
	}
	
	public function orderCancelAfter($observer)
	{
		$order			= $observer->getEvent()->getOrder();
		$_config		= Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->orderCancelAfter($observer);
		}
	}
	
	
	public function salesOrderInvoiceRegister($observer)
    {
		$order		= $observer->getEvent()->getOrder();
		$_config	= Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->salesOrderInvoiceRegister($observer);
		}
    }
	
	public function salesOrderCreditmemoRefund($observer)
	{
		$creditmemo	= $observer->getEvent()->getCreditmemo();
		$order		= $creditmemo->getOrder();
		$_config	= Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->salesOrderCreditmemoRefund($observer);
		}
	}
	
	
	
	public function invitationsInviteeCustomerFirstOrder($observer)
	{
		$order		= $observer->getEvent()->getOrder();
		$_config	= Mage::getModel('rewards/config')->setStoreId($order->getStoreId());
		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->invitationsInviteeCustomerFirstOrder($observer);
		}
	}

	public function invitationsInviteeAccept($observer)
	{
		$_config = Mage::getModel('rewards/config');
		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->invitationsInviteeAccept($observer);
		}
	}


	public function recalculateActivated($observer)
	{
		if (Mage::getModel('rewards/config')->modulEnabled()){
			Mage::getModel('rewards/points')->recalculateActivated();
		}
		
	}
	
	
	public function customerRegisterSuccess($observer)
	{
		return $this->_customerRegisterSuccess($observer->getEvent()->getCustomer());
	}
	
	public function salesOrderPlaceAfter($observer)
	{
		$order = $observer->getOrder();
		$quote = $order->getQuote();
		if ($quote->getCheckoutMethod(true) == 'register'){
			$this->_customerRegisterSuccess($order->getCustomer());
		}
		
		return $this;
	}


	protected function _customerRegisterSuccess($customer)
	{
		if ( Mage::getModel('rewards/config')->modulEnabled()){
			if ($landingpageId = Mage::app()->getRequest()->getParam('landingpage_id')) {
				return Mage::getModel('rewards/observerLandingpage')->customerRegisterSuccess($landingpageId, $customer);
			} else {
				return $this->getEncodedModel()->customerRegisterSuccess($customer);
			}
			
		}
	}

	public function reviewSaveAfter($observer)
	{
		$review		= $observer->getEvent()->getObject();
		$_config	= Mage::getModel('rewards/config')->setStoreId($review->getStoreId());
		if ($_config->modulEnabled()) {
			$this->getEncodedModel()->reviewSaveAfter($observer);
		}
	}


	public function adminOrderCreateProcess($observer)
	{

		$quote = $observer->getEvent()->getData('order_create_model')->getQuote();
		$config = Mage::getModel('rewards/config')->setStoreId($quote->getStoreId());

		if (!$config->modulEnabled()) {
			return;
		}

		$_session = Mage::getSingleton('adminhtml/session_quote');
		$helper = Mage::helper('rewards');
		$request = $observer->getEvent()->getData('request_model');
		$data = $request->getPost();

		Mage::register('adminhtml_rewards_point_quote', $quote, true);
		
		if (!isset($data['order']['rewards_point_count']) || !$quote->getCustomerId()) {
			return;
		}

		$pointsCount = (int)$data['order']['rewards_point_count'];


		$modelPoints = Mage::getModel('rewards/points')
			->setQuote($quote)
			->getByCustomer($quote->getCustomerId(), $quote->getStoreId(), true);


		if ($pointsCount <= 0) {
			$modelPoints->deactivate();
			$_session->addSuccess($helper->__('You have successfully canceled points.'));
			return;
		}

		if ($quote && $quote->getCouponCode() && !$config->getRedeemPointsWithCoupon()) {
			$_session->addNotice($helper->__('You cannot use  Reward Points and Coupon Codes at the same time.'));
		} else {
			if ($modelPoints->canActivate($pointsCount)) {
				$modelPoints->activate($pointsCount);
				$_session->addSuccess($helper->__('You have successfully activated %s Point', $pointsCount));
			} else {
				$_session->addNotice($helper->__('You cannot use this amount of points.'));
			}
		}


	}


	public function adminhtmlCustomerSave($observer)
	{
		$controller = $observer->getData('controller_action');

		$_request	= $controller->getRequest();
		$addPoints	= $_request->getParam('add_points');

		if (
			($customerId = $_request->getParam('customer_id'))
			&& $_request->getParam('store')
			&& (!is_null($addPoints))
			&& ($description = $_request->getParam('description'))
		) {
			$customerPoints = Mage::getModel('rewards/points')->getByCustomer($customerId, $_request->getParam('store'));

			$desc = array(
				'objId' 		=> Mage::getSingleton('admin/session')->getUser()->getId(),
				'objType' 		=> Plumrocket_Rewards_Model_History::OBJ_TYPE_ADMIN,
				'text'			=> $description,
				//'expiration' 	=> (($addPoints > 0) ? $_request->getParam('expiration') : 0),
			);

			if ($addPoints){
				if ($addPoints > 0) {
					$customerPoints->add($addPoints, $desc);
				} else {
					$customerPoints->take(-$addPoints, $desc, false);
				}
			}

			Mage::getSingleton('adminhtml/session')->addSuccess($controller->__('Successfully changed customer points.'));
			return $this;
		}

		if ($addPoints) {
			Mage::getSingleton('adminhtml/session')->addError($controller->__('Having trouble during saving reward points.'));
		}
	}

}