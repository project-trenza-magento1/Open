<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Block_Catalog_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell
{
    protected function _prepareData()
    {
        if (!Mage::getStoreConfig('ammostviewed/up_sells/enabled')) {
            return parent::_prepareData();
        }

        $id = Mage::registry('product')->getId();
        
        if (Mage::getStoreConfig('ammostviewed/up_sells/manually')) {
            $this->_itemCollection = Mage::helper('ammostviewed')->getViewedWith($id, 'up_sells');
        } else {
            parent::_prepareData();
            if (!count($this->_itemCollection)) {
                $this->_itemCollection = Mage::helper('ammostviewed')->getViewedWith($id, 'up_sells');
            }
        }
        
        return $this;
    }
}