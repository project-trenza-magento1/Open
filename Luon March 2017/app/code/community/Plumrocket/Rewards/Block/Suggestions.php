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
 * @package     Plumrocket_Rewards
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Block_Suggestions extends Mage_Catalog_Block_Product_Abstract
{

    protected $_mapRenderer = 'msrp_item';
    protected $_itemCollection;

    protected $_linkTypes = 'Related';


    public function getLinksType()
    {
        if ($data = $this->hasData('links_type')) {
            return $data;
        }
        return 'related';
    }

    public function getItems()
    {
        if (!$this->getCustomer()) {
            return array();
        }

        if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
            if (is_null($this->_itemCollection)) {

                $this->_itemCollection = array();

                $productIds = $this->_getAllProductIds($this->getCustomer());
                $collection = $this->_getCollection();
                $collection->getSelect()->group('e.entity_id');

                if ($productIds) {
                    $collection->addProductFilter($productIds);
                    $collection->getSelect()->where('e.entity_id NOT IN (?)', $productIds);
                }

                foreach ($collection as $product) {
                    if (!((int)$product->getPrice()) && $product->getTypeId() != 'bundle') {
                        continue;
                    }

                    if ($product->getData('is_recurring')) {
                        continue;
                    }

                    $product->setDoNotUseCategoryId(true);
                    $this->_itemCollection[] = $product;
                }

                $this->_randomize();

            }
            return $this->_itemCollection;
        }
    }


    public function getItemsCount()
    {
        if ($data = $this->hasData('items_count')) {
            return $data;
        }
        return 4;
    }


    public function getMaxAmountAvailble()
    {
        $config = Mage::getModel('rewards/config');
        $maxPointsAvailble = min(
            $config->getMaxRedeemableCredit(),
            $this->getAvailablePoints()
        );
        return $maxPointsAvailble / $config->getRedeemPointsRate();
    }


    protected function _getAllProductIds($customer)
    {
        $ids = array();

        $resource = Mage::getSingleton('core/resource');
        $items = Mage::getSingleton('sales/order_item')->getCollection();
        $items->getSelect()->joinLeft(
            array('order' => $resource->getTableName('sales/order')),
            'main_table.order_id = order.entity_id',
            array()
        );

        $items
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('status', array('nin' => array('canceled', 'closed')))
            ->addFieldToFilter('base_grand_total', array('gt' => 0))
            ->addFieldToFilter('order.created_at', array('gt' => date('Y-m-d H:i:s', time() - 86400 * 150)))
            ->setOrder('created_at', 'DESC')
            ->setPageSize(50);


        foreach($items as $_item) {
            $ids[] = $_item->getProductId();
        }

        return $ids;
    }


    protected function _getCollection()
    {
        $type = $this->getLinksType() ? $this->getLinksType() : $this->_linkTypes;

        $method = 'use'.ucfirst($type).'Links';

        $collection = Mage::getModel('catalog/product_link')->$method()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addStoreFilter()
            ->setPageSize(20);
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }


    protected function _randomize()
    {
        shuffle($this->_itemCollection);
        if (count($this->_itemCollection) > $this->getItemsCount()) {
            array_splice($this->_itemCollection, $this->getItemsCount());
        }

        return $this;
    }


    protected function _getSettings($fiels)
    {
        return Mage::getStoreConfig('checkoutspage/suggestions/'.$fiels);
    }

}