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

class Plumrocket_Invitations_Adminhtml_Invitations_IndexController extends Mage_Adminhtml_Controller_Action
{
	public function preDispatch()
    {
		parent::preDispatch();
		Mage::helper('invitations')->checkAdminAccess('invitations/invitations');
    }
	
    public function indexAction()
    {
		$this->loadLayout();
		$this->renderLayout();	
	}
	
	public function DeleteAction()
	{
		$helper = Mage::helper('invitations');
		$helper->admCheckAccess('invitations');
		if($ids = $this->getRequest()->getParam('id'))
		{
			$modelInvitations = Mage::getModel('invitations/invitations');
				foreach($ids as $id)
			{
				if ( ($invitations = $modelInvitations->load($id)) && $invitations->getId() )
					$invitations->setId($id)->delete();
			}
			$helper->admResultSuccess('Address book(s) have been deleted.');
		}
		else
			$helper->admResultError('Address book(s) have not been deleted.');
	}

}
