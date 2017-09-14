<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


class Amasty_Followup_Block_Email_Items_Related  extends Amasty_Followup_Block_Email_Items_Product
{

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    protected function _getProductCollection(Mage_Catalog_Model_Product $product)
    {
        return $product->getRelatedProductCollection();
    }
}