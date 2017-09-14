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
 
class Plumrocket_Invitations_Block_Inviter_Step1 extends Plumrocket_Invitations_Block_Inviter_Abstract
{   
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('invitations/inviter/step1.phtml');
	}
	
	public function getItemHtml($aBook)
	{
		$customAbooks = array('facebook', 'gmail', 'yahoo', 'live', 'mailru');
		if (in_array($aBook->getKey(), $customAbooks)){
			$template = $aBook->getKey();
		} else {
			$template = 'default';
		}
		
		return $this->getLayout()->createBlock('invitations/inviter_step1') 
			->setTemplate('invitations/inviter/step1/items/'.$template.'.phtml')
			->setAddressBook($aBook)
			->toHtml();
	}
	
}
