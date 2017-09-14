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
 * @copyright   Copyright (c) 2014 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php  
    set_time_limit(0);

	$installer = $this;
	$installer->startSetup();
    
    $installer->run("ALTER TABLE `{$this->getTable('rewards_points')}` ADD `expire_at` DATE NOT NULL;");
    $installer->run("ALTER TABLE `{$this->getTable('rewards_points')}` ADD `notify` TINYINT NOT NULL AFTER `activated`, ADD INDEX (  `notify` )");

    $installer->run("ALTER TABLE `{$this->getTable('rewards_history')}` ADD `expire_at` DATE NOT NULL");
    $installer->run("ALTER TABLE `{$this->getTable('rewards_history')}` CHANGE `obj_type` `obj_type` ENUM( 'INVITEE', 'ORDER', 'ORDER_CREDITS', 'ORDER_ITEM_REFUND', 'ADMIN_CHANGE', 'CUSTOMER_REGISTRATION', 'REVIEW', 'EXPIRED' ) NOT NULL");


    $setup = Mage::getModel('eav/entity_setup', 'core_setup');
    $entityTypeId = $setup->getEntityTypeId('catalog_product');
    $attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
    $attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

    $setup->addAttribute('catalog_product', 'disallow_earning_points', array(
        'type'              => 'int',
        'label'             => 'Allow Earning Reward Points',
        'input'             => 'select',
        'source'           => 'rewards/entity_attribute_source_disallowEarningPoints',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => 1,
        'required'          => 0,
        'user_defined'      => 0,
        'default'           => 0,
        'position'          => 250,
    ));

    $setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'disallow_earning_points', '250');
    $setup->addAttributeToSet($entityTypeId, $attributeSetId, $attributeGroupId, 'disallow_earning_points', '250');


	$installer->endSetup();