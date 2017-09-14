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

// Update Mail_ru.
$set = array(
	'`key` = "mailru"',
	'`action` = "pInvitations.mailruConnect();"',
	'`step2` = 0',
	'`settings` = "'. str_replace('"', '\"', '{"client_id":{"type":"text","label":"Application ID","class":"required-entry","required":true,"value":""},"client_secret":{"type":"password","label":"Secret Key","class":"required-entry","required":true,"value":""}}') .'"',
	'`position` = "7"'
);

$installer->run("UPDATE `{$this->getTable('invitations_addressbooks')}` SET ". implode(',', $set) ." WHERE `key` = 'mail_ru'");

$installer->run("ALTER TABLE `{$this->getTable('invitations_addressbooks')}` CHANGE  `key`  `ad_key` CHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");


$installer->endSetup();
