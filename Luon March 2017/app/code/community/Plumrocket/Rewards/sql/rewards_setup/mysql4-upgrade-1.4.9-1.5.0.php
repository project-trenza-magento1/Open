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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php
    set_time_limit(0);

	$installer = $this;
	$installer->startSetup();

    $setup = Mage::getModel('eav/entity_setup', 'core_setup');
    $entityTypeId = $setup->getEntityTypeId('catalog_product');
    $attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
    $attributeGroupId = $setup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');

    $setup->addAttribute('catalog_product', 'additional_purchase_points', array(
        'type'              => 'int',
        'label'             => 'Additional Purchase Reward Points',
        'input'             => 'text',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => 1,
        'required'          => 0,
        'user_defined'      => 0,
        'default'           => 0,
        'position'          => 252,
    ));

    $setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'additional_purchase_points', '252');
    $setup->addAttributeToSet($entityTypeId, $attributeSetId, $attributeGroupId, 'additional_purchase_points', '252');


	$installer->endSetup();