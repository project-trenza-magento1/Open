<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_LayeredNavigation
 * @copyright   Copyright (c) 2014 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Itactica_LayeredNavigation_Model_Resource_Indexer_Attribute extends Mage_Index_Model_Resource_Abstract
{

    protected $_storesIds;
    protected $_helper;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('itactica_layerednavigation/attribute_url_key', 'id');
    }

    /**
     * Reindex all
     *
     * @return Itactica_LayeredNavigation_Model_Resource_Indexer_Attribute
     */
    public function reindexAll()
    {
        $this->reindexSeoUrlKeys();
        return $this;
    }

    /**
     * Generate SEO values for catalog product attributes options
     * 
     * @param int $attributeId - transmit this to limit processing to one specific attribute
     * @return Itactica_LayeredNavigation_Model_Resource_Indexer_Attribute
     */
    public function reindexSeoUrlKeys($attributeId = null)
    {
        $attributes = $this->_getAttributes($attributeId);
        $stores = $this->_getAllStoresIds();

        $data = array();
        foreach ($attributes as $attribute) {
            if ($attribute->usesSource()) {
                foreach ($stores as $storeId) {
                    $result = $this->_getInsertValues($attribute, $storeId);
                    $data = array_merge($data, $result);
                }
            }
        }

        if (!empty($attributeId)) {
            $this->_saveData($data, array("`attribute_id` = ?" => $attributeId));
        } else {
            $this->_saveData($data);
        }

        return $this;
    }

    /**
     * Save data into database
     * 
     * @param array $data
     * @param array $deleteWhere
     */
    protected function _saveData(array $data, array $deleteWhere = array())
    {
        // Continue only if we have something to insert
        if (empty($data)) {
            return $this;
        }

        // Do it in one transaction
        $this->beginTransaction();

        try {
            $writeAdapter = $this->_getWriteAdapter();
            $writeAdapter->delete($this->getMainTable(), $deleteWhere);
            $writeAdapter->insertMultiple($this->getMainTable(), $data);

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieve product attributes with frontend input type 'select' and 'multiselect'
     *
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    protected function _getAttributes($attributeId = null)
    {
        $collection = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
            ->getAttributeCollection()
            ->addFieldToFilter('frontend_input', array('in' => array('select', 'multiselect')));
        if (!empty($attributeId)) {
            $collection->addFieldToFilter('attribute_id', $attributeId);
        }

        return $collection;
    }

    /**
     * Retrieve data to be insterted after processing attribute
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param int $storeId
     * @return array
     */
    protected function _getInsertValues($attribute, $storeId)
    {

        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setStoreFilter($storeId)
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->load();
        $options = $collection->toOptionArray();

        $data = array();
        foreach ($options as $option) {
            // Generate url key
            $urlKey = $this->_getHelper()->transliterate($option['label']);

            // Check if this url key is taken and add -{count}
            $count = 0;
            $origUrlKey = $urlKey;
            do {
                $found = false;
                foreach ($data as $line) {
                    if ($line['url_key'] == $urlKey) {
                        $found = true;
                    }
                }
                if ($found) {
                    $urlKey = $origUrlKey . '-' . ++$count;
                }
            } while ($found);

            $data[] = array(
                'attribute_code' => $attribute->getAttributeCode(),
                'attribute_id' => $attribute->getId(),
                'store_id' => $storeId,
                'option_id' => $option['value'],
                'url_key' => $urlKey
            );
        }

        return $data;
    }

    /**
     * Retrieve all stores ids
     *
     * @return array
     */
    protected function _getAllStoresIds()
    {
        if ($this->_storesIds === null) {
            $this->_storesIds = array();
            $stores = Mage::app()->getStores();
            foreach ($stores as $storeId => $store) {
                $this->_storesIds[] = $storeId;
            }
        }

        return $this->_storesIds;
    }

    /**
     * Retrieve helper object
     *
     * @return Itactica_LayeredNavigation_Helper_Data
     */
    protected function _getHelper()
    {
        if ($this->_helper === null) {
            $this->_helper = Mage::helper('itactica_layerednavigation');
        }

        return $this->_helper;
    }

    /**
     * Reindex attribute options on attribute save event
     * 
     * @param Mage_Index_Model_Event $event
     * @return Itactica_LayeredNavigation_Model_Resource_Indexer_Attribute
     */
    public function catalogEavAttributeSave(Mage_Index_Model_Event $event)
    {
        $attribute = $event->getDataObject();
        $this->reindexSeoUrlKeys($attribute->getId());

        return $this;
    }

}