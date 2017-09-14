<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Block_Checkout_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell
{
    public function getItems()
    {
        $items = $this->getData('items');
        if (!is_null($items)) {
            return $items;
        }
            
        $alreadyInCartIds = $this->_getCartProductIds();
        if (!$alreadyInCartIds) {
            return parent::getItems();
        }    
        
        if (!Mage::getStoreConfig('ammostviewed/cross_sells/enabled')) {
             return parent::getItems();
        }

        $id = (int)$this->_getLastAddedProductId();
        if (!$id) {
            $id = current($alreadyInCartIds);
        }
        
        $items = array();
        if (Mage::getStoreConfig('ammostviewed/cross_sells/manually')) {
            $items = Mage::helper('ammostviewed')->getViewedWith($id, 'cross_sells', $alreadyInCartIds);
        } else {
            $items = parent::getItems();
            if (!count($items)) {
                $items = Mage::helper('ammostviewed')->getViewedWith($id, 'cross_sells', $alreadyInCartIds);
            }
        }
        
        $this->setData('items', $items);
        
        return $items;
    }
}