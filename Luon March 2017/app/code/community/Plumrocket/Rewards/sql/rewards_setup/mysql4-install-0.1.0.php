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
 * @package     Plumrocket_Reward_Points
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php  

$installer = $this;
$installer->startSetup();
$installer->run("	
	CREATE TABLE IF NOT EXISTS {$this->getTable('rewards_history')} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `customer_id` int(11) NOT NULL,
	  `obj_id` int(11) NOT NULL,
	  `obj_type` enum('ORDER','INVITEE','ADMIN_CHANGE','CUSTOMER_REGISTRATION') NOT NULL,
	  `points` int(11) NOT NULL,
	  `description` char(255) NOT NULL,
	  `created_at` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `customer_id` (`customer_id`)
	) ENGINE=InnoDB;
");
$installer->endSetup();


/*
* Add atribute rewards_available_points

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$setup->addAttribute('customer', 'rewards_available_points', array(
	'input'         => 'text',
	'type'          => 'int',
	'label'         => 'Rewards Available Points',
	'visible'       => true,
	'required'      => false,
	'user_defined' => 0,
));
$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'rewards_available_points',
	'999'
);

/*
* Add atribute rewards_spent_points

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$setup->addAttribute('customer', 'rewards_spent_points', array(
	'input'         => 'text',
	'type'          => 'int',
	'label'         => 'Rewards Available Points',
	'visible'       => true,
	'required'      => false,
	'user_defined' => 0,
));
$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'rewards_spent_points',
	'999'
);
*/
