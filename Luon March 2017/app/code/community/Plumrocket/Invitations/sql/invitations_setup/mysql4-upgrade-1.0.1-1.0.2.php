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

$installer = $this;
$installer->startSetup();

$gMailSettings	= '{"client_id":{"type":"password","label":"Client ID","class":"required-entry","required":true,"value":""},"client_secret":{"type":"password","label":"Client secret","class":"required-entry","required":true,"value":""},"redirect_uri":{"type":"label","label":"Redirect Uri","class":"","required":false,"value":"{{base_url}}invitations\\/inviter\\/googleRedirect\\/"}}';
$gMailAction	= 'pInvitations.gmailConnect();';


$installer->run("UPDATE `{$this->getTable('invitations_addressbooks')}` SET `action`='{$gMailAction}', settings='{$gMailSettings}' WHERE `key` = 'gmail'");
$installer->run("ALTER TABLE  `{$this->getTable('invitations_addressbooks')}` ADD  `step2` TINYINT( 4 ) NOT NULL AFTER  `action`");
$installer->run("UPDATE `{$this->getTable('invitations_addressbooks')}` SET step2 = '1' WHERE `key` NOT IN ('gmail', 'facebook')");

/*$fbAddressBook = Mage::getModel('invitations/addressbooks')->getByKey('facebook');
if ($settings = $fbAddressBook->getSettings())
{
	$settings = str_replace('"type":"text"', '"type":"password"', $settings);
	$fbAddressBook->setSettings($settings)->save();
}*/
$installer->endSetup();
