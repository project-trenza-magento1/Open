<?php
/**
 * MageWorx
 * MageWorx SeoReports Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoReports
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */



class MageWorx_SeoReports_Block_Adminhtml_Product_Duplicate_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('entity_id');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASK');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', Mage::app()->getDefaultStoreView()->getStoreId());
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $store      = $this->_getStore();
        $collection = Mage::getResourceModel('seoreports/product_collection')->addFieldToFilter('store_id',
                $store->getId());

        $preparedName = $this->getRequest()->getParam('prepared_name', '');
        if ($preparedName) $collection->addFieldToFilter('prepared_name', $preparedName);

        $preparedMetaTitle = $this->getRequest()->getParam('prepared_meta_title', '');
        if ($preparedMetaTitle) $collection->addFieldToFilter('prepared_meta_title', $preparedMetaTitle);

        $url = $this->getRequest()->getParam('url', '');
        if ($url) $collection->addFieldToFilter('url', $url);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id',
                array(
            'header' => Mage::helper('seoreports')->__('ID'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'entity_id',
            'align'  => 'center',
        ));


        $this->addColumn('name',
                array(
            'header' => Mage::helper('seoreports')->__('Product Name'),
            'type'   => 'text',
            'index'  => 'name',
            'align'  => 'left',
        ));


        $this->addColumn('meta_title',
                array(
            'header' => Mage::helper('seoreports')->__('Meta Title'),
            'type'   => 'text',
            'index'  => 'meta_title',
            'align'  => 'left',
        ));

        $this->addColumn('sku',
                array(
            'header' => Mage::helper('seoreports')->__('SKU'),
            'type'   => 'text',
            'index'  => 'sku'
        ));


        $this->addColumn('url',
                array(
            'header'   => Mage::helper('seoreports')->__('Url'),
            'renderer' => 'seoreports/adminhtml_grid_renderer_url',
            'type'     => 'text',
            'index'    => 'url_path',
            'align'    => 'left',
        ));

        $this->addColumn('type',
                array(
            'header'  => Mage::helper('seoreports')->__('Type'),
            'width'   => '125px',
            'index'   => 'type_id',
            'type'    => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            'align'   => 'center'
        ));

        $this->addColumn('action',
                array(
            'header'    => Mage::helper('seoreports')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'    => 'getProductId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('seoreports')->__('Edit'),
                    'url'     => array('base'   => 'adminhtml/catalog_product/edit/', 'params' => array('store' => $this->getRequest()->getParam('store'))),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
            'align'     => 'center',
            'is_system' => true,
        ));


        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit',
                        array('id' => $row->getProductId(), 'store' => $this->getRequest()->getParam('store')));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/duplicateViewGrid', array('_current' => true));
    }

}
