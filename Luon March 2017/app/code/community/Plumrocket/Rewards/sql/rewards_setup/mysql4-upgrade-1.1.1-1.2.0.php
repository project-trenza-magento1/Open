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
 * @copyright   Copyright (c) 2013 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php  
	set_time_limit(0);
	
	/* update db */
	$this->run("
		ALTER TABLE  `{$this->getTable('rewards/history')}` CHANGE  `obj_type`  `obj_type` ENUM( 'INVITEE','ORDER','ORDER_CREDITS','ORDER_ITEM_REFUND','ADMIN_CHANGE','CUSTOMER_REGISTRATION','REVIEW' )  NOT NULL;
		ALTER TABLE  `{$this->getTable('rewards/history')}` ADD  `store_group_id` INT NOT NULL AFTER  `customer_id` , ADD INDEX (  `store_group_id` );
		UPDATE  `{$this->getTable('rewards/history')}` SET  `store_group_id` = 1;
	");
	
	$this->run("
		CREATE TABLE IF NOT EXISTS `{$this->getTable('rewards/points')}` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `customer_id` int(11) NOT NULL,
		  `store_group_id` int(11) NOT NULL,
		  `available` int(11) NOT NULL DEFAULT '0',
		  `spent` int(11) NOT NULL DEFAULT '0',
		  `activated` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `customer_id` (`customer_id`),
		  KEY `store_group_id` (`store_group_id`)
		) ENGINE=InnoDB;
	");
	
	
	/* migrate points for old versions */
	$_oldAttributes = array('rewards_available_points', 'rewards_spent_points');
	$attrIds = array();
	foreach($_oldAttributes as $name){
		$attribute = Mage::getModel('customer/customer')->getResource()->getAttribute($name);
		if ($attribute && $attribute->getId()){
			$attrIds[$name] = $attribute->getId();
		}
	}
	
	if (count($attrIds)){
		$this->run("
			INSERT INTO `{$this->getTable('rewards_points')}` (customer_id,  store_group_id, available, spent, activated) 
			SELECT `e`.entity_id as customer_id, 1 as store_group_id  , `at_rewards_available_points`.`value` AS `available`, `at_rewards_spent_points`.`value` AS `spent`, 0 as activated  FROM `{$this->getTable('customer_entity')}` AS `e` INNER JOIN `{$this->getTable('customer_entity_int')}` AS `at_rewards_available_points` ON (`at_rewards_available_points`.`entity_id` = `e`.`entity_id`) AND (`at_rewards_available_points`.`attribute_id` = '".$attrIds['rewards_available_points']."') INNER JOIN `{$this->getTable('customer_entity_int')}` AS `at_rewards_spent_points` ON (`at_rewards_spent_points`.`entity_id` = `e`.`entity_id`) AND (`at_rewards_spent_points`.`attribute_id` = '".$attrIds['rewards_spent_points']."') WHERE (`e`.`entity_type_id` = '1') AND ((at_rewards_available_points.value > 0) OR (at_rewards_spent_points.value > 0))
		");
	}
