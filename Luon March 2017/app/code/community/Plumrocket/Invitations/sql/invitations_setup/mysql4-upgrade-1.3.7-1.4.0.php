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

$installer = $this;
$installer->startSetup();

// Add new network.
$set = array(
	'`name` = "Hotmail, Outlook "',
	'`key` = "live"',
	'`type` = "EMAIL"',
	'`action` = "pInvitations.liveConnect();"',
	'`step2` = 0',
	'`settings` = "'. str_replace('"', '\"', '{"client_id":{"type":"password","label":"Client ID","class":"required-entry","required":true,"value":""},"client_secret":{"type":"password","label":"Client Secret","class":"required-entry","required":true,"value":""},"redirect_uri":{"type":"label","label":"Redirect URLs","class":"","required":false,"value":"{{base_url}}invitations/inviter/liveRedirect/"}}') .'"',
	'`status` = "ENABLED"',
);
$installer->run("INSERT INTO `{$this->getTable('invitations_addressbooks')}` SET ". implode(',', $set));

// Add position column.
$installer->run("ALTER TABLE `{$this->getTable('invitations_addressbooks')}` ADD `position` INT(11) UNSIGNED NOT NULL");

// Set position.
$position = array(
	'facebook',
	'gmail',
	'yahoo',
	'live',
	'lastfm',
	'flickr',
	'badoo',
	'friendfeed',
	'hyves',
	'inet',
	'katamail',
	'kincafe',
	'libero',
	'mail2world',
	'mail_ru',
	'netaddress',
	'netlog',
	'nz11',
	'o2',
	'perfspot',
	'plaxo',
	'rediff',
	'techemail',
);

foreach ($position as $n => $key) {
	$installer->run("UPDATE `{$this->getTable('invitations_addressbooks')}` SET `position` = '". ($n+1) ."' WHERE `key` = '{$key}'");
}


$installer->endSetup();
