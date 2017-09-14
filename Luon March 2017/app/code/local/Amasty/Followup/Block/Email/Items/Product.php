<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


/**
 * Class Product
 *
 * @author Artem Brunevski
 */
class Amasty_Followup_Block_Email_Items_Product  extends Amasty_Followup_Block_Email_Items
{

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    protected function _getProductCollection(Mage_Catalog_Model_Product $product)
    {
        return $product->getRelatedProductCollection();
    }

    public function getItems()
    {
        $itemsPrices = array();
        if ($this->getQuote()){
            foreach($quoteItems = $this->getQuote()->getAllVisibleItems() as $item){
                $itemsPrices[$item->getPrice()] = $item->getProductId();

            }
        }
        ksort($itemsPrices);

        if (count($itemsPrices) > 0) {
            $product = Mage::getModel('catalog/product')->load(end($itemsPrices));

            /* @var $product Mage_Catalog_Model_Product */
            $collection = $this->_getProductCollection($product)
                ->addAttributeToSelect('required_options')
                ->setPositionOrder()
                ->addStoreFilter()
            ;

            if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
                Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($collection,
                    $this->getQuote()->getQuoteId()
                );
                $this->_addProductAttributesAndPrices($collection);
            }

            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

            $collection->load();

            foreach ($collection as $product) {
                $product->setDoNotUseCategoryId(true);
            }

            return $collection->getItems();
        }

        return array();
    }
}
