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
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
    	if (!$this->_haveAccess()) {
    		$this->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
    		return;
    	}

	 	$this->loadLayout();
      	$this->renderLayout();
    }


	public function activatePointsAction()
	{
		$helper = Mage::helper('rewards');
		if (!$this->_haveAccess()) {
			Mage::getSingleton('checkout/session')->addNotice($helper->__('You cannot use points.'));
		} else {
			$modelPoints = Mage::getModel('rewards/points')->getByCustomer();
			$pointsCount = (int)($this->getRequest()->getParam('rewards_point_count'));


			$quote = Mage::getSingleton('checkout/cart')->getQuote();
			if ($quote && $quote->getCouponCode() && !Mage::getModel('rewards/config')->getRedeemPointsWithCoupon()) {
				Mage::getSingleton('checkout/session')->addNotice($helper->__('You cannot use  Reward Points and Coupon Codes at the same time.'));
			} else {
				if ($modelPoints->canActivate($pointsCount)) {
					$modelPoints->activate($pointsCount);
					Mage::getSingleton('checkout/session')->addSuccess($helper->__('You have successfully activated %s Point(s)', $pointsCount));
				} else {
					Mage::getSingleton('checkout/session')->addNotice($helper->__('You cannot use this amount of points.'));
				}
			}
		}

		$this->_redirectReferer();
	}


	public function deactivatePointsAction()
	{
		$helper = Mage::helper('rewards');
		if (!$this->_haveAccess()) {
			Mage::getSingleton('checkout/session')->addNotice($helper->__('You cannot do this.'));
		} else {
			$modelPoints = Mage::getModel('rewards/points')->getByCustomer()->deactivate();
			Mage::getSingleton('checkout/session')->addSuccess($helper->__('You have successfully canceled points.'));
		}

		$this->_redirectReferer();
	}


	public function historyAction()
	{
		if (!$this->_haveAccess()) {
			$data = array();
		} else {
			$data = array(
				'success'	=> true,
				'result'	=> $this->getLayout()->createBlock('rewards/history')->toHtml(),
			);
		}
		$this->getResponse()->setBody(json_encode($data));
	}


	protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }


    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _haveAccess()
    {
    	return Mage::getModel('rewards/config')->modulEnabled() && Mage::helper('rewards')->getCurrentCustomerId();
    }

}
