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

class Plumrocket_Invitations_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{

    public function initControllerRouters($observer)
    {
        $front = $observer->getEvent()->getFront();
        $front->addRouter('invitations', $this);
    }

    public function match(Zend_Controller_Request_Http $request)
    {

		if (!Mage::helper('invitations')->isReferralPath()){
			return false;
		}

        $request
			->setModuleName('invitations')
			->setControllerName('index')
			->setActionName('accept');
		
		$identifier = trim($request->getPathInfo(), '/');

		$request->setAlias(
			Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
			$identifier
		);
		
		return true;
    }

}
