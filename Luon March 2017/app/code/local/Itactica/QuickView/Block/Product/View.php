<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_QuickView
 * @copyright   Copyright (c) 2014-2015 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Itactica_QuickView_Block_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Product object
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('itactica_quickview/product/view.phtml');
    }
   
    /**
     * Get product object
     *
     * @return  Mage_Catalog_Model_Product
     */
    public function getProduct() {
        if (!$this->_product) {
            if (Mage::registry('current_product')) {
                $this->_product = Mage::registry('current_product');
            } else {
                $this->_product = Mage::getSingleton('catalog/product');
            }
        }
        Mage::dispatchEvent('catalog_controller_product_view', array('product' => $this->_product));
        return $this->_product;
    }
}