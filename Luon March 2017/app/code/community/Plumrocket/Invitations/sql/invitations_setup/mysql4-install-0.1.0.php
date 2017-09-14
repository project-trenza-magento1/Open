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
$installer->run("
	CREATE TABLE IF NOT EXISTS {$this->getTable('invitations_addressbooks')} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` char(255) NOT NULL,
	  `key` char(50) NOT NULL,
	  `type` enum('EMAIL','SOCIAL') NOT NULL,
	  `action` char(255) NOT NULL,
	  `settings` text NOT NULL,
	  `status` enum('ENABLED','DISABLED') NOT NULL DEFAULT 'ENABLED',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=23 ;

	--
	-- Dumping data for table `invitations_addressbooks`
	--

	INSERT INTO {$this->getTable('invitations_addressbooks')} (`id`, `name`, `key`, `type`, `action`, `settings`, `status`) VALUES
	(1, 'Gmail', 'gmail', 'EMAIL', '', '', 'ENABLED'),
	(2, 'Yahoo', 'yahoo', 'EMAIL', '', '', 'ENABLED'),
	(6, 'Facebook', 'facebook', 'SOCIAL', 'pInvitations.facebookConnect();', '".'{"application_id":{"type":"password","label":"Application Id","class":"required-entry","required":true,"value":""},"secret_key":{"type":"password","label":"Secret Key","class":"required-entry","required":true,"value":""}}'."', 'ENABLED'),
	(14, 'Mail_ru', 'mail_ru', 'EMAIL', '', '', 'ENABLED');

	CREATE TABLE IF NOT EXISTS {$this->getTable('invitations_entity')} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `customer_id` int(11) NOT NULL,
	  `invitee_name` char(255) NOT NULL,
	  `invitee_connect_code` char(50) NOT NULL,
	  `addressbook_id` int(11) NOT NULL,
	  `sent_count` int(11) NOT NULL DEFAULT '1',
	  `invitee_customer_id` int(11) NOT NULL,
	  `first_order` tinyint(1) NOT NULL DEFAULT '0',
	  `deactivated` tinyint(1) NOT NULL DEFAULT '0',
	  `created_at` datetime NOT NULL,
	  `updated_at` datetime NOT NULL,
	  `confirmed_at` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `customer_id` (`customer_id`)
	) ENGINE=InnoDB;
");
$installer->endSetup();
