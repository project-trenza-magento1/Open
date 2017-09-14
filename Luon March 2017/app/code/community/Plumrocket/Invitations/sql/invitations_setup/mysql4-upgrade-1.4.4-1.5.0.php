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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('invitations_entity')}` ADD `reward_coupon_code` CHAR(32) NOT NULL AFTER `invitee_connect_code`");
$installer->run("ALTER TABLE `{$this->getTable('invitations_entity')}` ADD `invitee_coupon_code` CHAR(32) NOT NULL AFTER `invitee_connect_code`");

$installer->endSetup();
