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

/*
* Add atribute rewards_activated_points

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$setup->addAttribute('customer', 'rewards_activated_points', array(
	'input'         => 'text',
	'type'          => 'int',
	'label'         => 'Rewards Activated Points',
	'visible'       => true,
	'required'      => false,
	'user_defined' => 0,
));
$setup->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'rewards_activated_points',
	'999'
);
*/

$this->run("
	ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `rpdiscount_amount` DECIMAL( 10, 2 ) NOT NULL;
	ALTER TABLE  `".$this->getTable('sales/quote_address')."` ADD  `base_rpdiscount_amount` DECIMAL( 10, 2 ) NOT NULL;

	ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `rpdiscount_amount` DECIMAL( 10, 2 ) NOT NULL;
	ALTER TABLE  `".$this->getTable('sales/order')."` ADD  `base_rpdiscount_amount` DECIMAL( 10, 2 ) NOT NULL;
");
