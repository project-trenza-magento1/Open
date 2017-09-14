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


class Plumrocket_Rewards_Adminhtml_Rewards_PointsController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
		$this->_title($this->__('Manage Customer Points'));
		$this->loadLayout();
		$this->renderLayout();
	}


	public function editAction()
    {
		$_request = $this->getRequest();
		if ($customerId = $_request->getParam('customer_id')){
			$this->_title($this->__('Points Information'));
			$customerPoints = Mage::getModel('rewards/points')->getByCustomer($customerId, $_request->getParam('store'));

			Mage::register('customerPoints', $customerPoints);
			$this->loadLayout();
		    $this->renderLayout();
		} else {
			$this->_redirect('*/*');
		}
	}

	public function saveAction()
	{
		$_request	= $this->getRequest();
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

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Successfully changed customer points.'));
			$this->_redirect('*/*');
			return $this;
		}

		Mage::getSingleton('adminhtml/session')->addError($this->__('Having trouble during saving customer points.'));
		$this->_redirectReferer();
	}


	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('plumrocket/rewards/clients_points');
    }
}
