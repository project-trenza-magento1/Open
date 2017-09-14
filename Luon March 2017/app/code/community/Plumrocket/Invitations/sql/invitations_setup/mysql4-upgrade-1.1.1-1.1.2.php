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

$mailSettings	= '{"consumer_key":{"type":"password","label":"Consumer key","class":"required-entry","required":true,"value":""},"consumer_secret":{"type":"password","label":"Consumer secret","class":"required-entry","required":true,"value":""}}';
$mailAction	= 'pInvitations.yahooConnect();';

$installer->run("UPDATE `{$this->getTable('invitations_addressbooks')}` SET `action`='{$mailAction}', settings='{$mailSettings}' WHERE `key` = 'yahoo';");
$installer->run("UPDATE `{$this->getTable('invitations_addressbooks')}` SET step2 = '0' WHERE `key` = 'yahoo' ;");

$installer->endSetup();
