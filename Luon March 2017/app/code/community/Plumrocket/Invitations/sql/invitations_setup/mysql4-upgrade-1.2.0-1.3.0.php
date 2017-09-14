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

$installer->run("ALTER TABLE  `{$this->getTable('invitations_entity')}` ADD  `guest_id` INT NOT NULL AFTER  `customer_id` , ADD INDEX (  `guest_id` );");
$installer->run("
	CREATE TABLE IF NOT EXISTS `{$this->getTable('invitations_guest')}` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `email` char(127) NOT NULL,
	  `code` char(32) NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `email` (`email`)
	) ENGINE=MyISAM;");

$installer->endSetup();
