<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Block_Catalog_Product_List_Related extends Amasty_Mostviewed_Block_Catalog_Product_List_Related_Pure
{
    protected function _prepareData()
    {
        if (!Mage::getStoreConfig('ammostviewed/related_products/enabled')) {
            return parent::_prepareData();
        }
        
        $id = Mage::registry('product')->getId();
        
        if (Mage::getStoreConfig('ammostviewed/related_products/manually')) {
            $this->_itemCollection = Mage::helper('ammostviewed')->getViewedWith($id, 'related_products');
        } else {
            parent::_prepareData();
            if (!count($this->_itemCollection)) {
                $this->_itemCollection = Mage::helper('ammostviewed')->getViewedWith($id, 'related_products');
            }
        }
        
        return $this;
    }
}