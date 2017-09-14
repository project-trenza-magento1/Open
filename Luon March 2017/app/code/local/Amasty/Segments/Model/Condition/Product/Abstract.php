<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


$baseHelper = Mage::helper("ambase");

if (!$baseHelper || $baseHelper->isVersionLessThan(7)) {
    $autoloader = Varien_Autoload::instance();
    $autoloader->autoload('Amasty_Segments_Model_Condition_Product_Legacy');
} else {
    abstract class Amasty_Segments_Model_Condition_Product_Abstract extends Mage_Rule_Model_Condition_Product_Abstract
    {
    }
}
