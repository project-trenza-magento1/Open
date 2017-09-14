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
 * @package     Plumrocket_Reward_Points
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Rewards_Block_Adminhtml_History_List_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{

    public function __construct()
    {
        parent::__construct();

        $this->setId('manage_rewards_history');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $_request = $this->getRequest();
        if (! $_request->getParam('store') && ($store = $this->getSession()->getPlumrocketRewardsGridStor())){
             $_request->setParam('store', $store);
        }

        $this->_storeId = $_request->getParam('store');
        if ($this->_storeId){
            $this->getSession()->setPlumrocketRewardsGridStor($this->_storeId);
        }

        foreach (Mage::app()->getWebsites() as $website){
            foreach ($website->getGroups() as $store){
                if (!$this->_storeId){
                    $this->_storeId = $store->getId();
                }

                if ($this->_storeId == $store->getId()){
                    $this->_websiteId = $store->getWebsiteId();
                    break;
                }
            }
        }
    }


    protected function _prepareCollection()
    {
        $_tp = (string) Mage::getConfig()->getTablePrefix();
        $collection = Mage::getModel('rewards/history')->getCollection()
            ->addFieldToFilter('store_group_id', $this->getStoreId())
            ->addFilterToMap('created_at', 'main_table.created_at');
        $collection->getSelect()
            ->joinLeft( array('c' => $_tp.'customer_entity'), 'main_table.customer_id = c.entity_id', array('c.email'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('rewards')->__('ID'),
            'align'     =>'left',
            'index'     => 'id',
            'type' => 'number',

        ));

        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('rewards')->__('Customer ID'),
            'align'     =>'left',
            'index'     => 'customer_id',
            'type' => 'number',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('rewards')->__('Customer Email'),
            'align'     =>'left',
            'index'     => 'email',
            //'escape'    => true,
            //'sortable'  => false,
            //'filter'    => false,
            //'renderer'  => new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_CustomerEmail,
            'type' => 'text',

        ));

        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('rewards')->__('Customer Name'),
            'align'     =>'left',
            'index'     => 'customer_id',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_CustomerName,
            'type' => 'text',
        ));

        $this->addColumn('obj_type', array(
            'header'    => Mage::helper('rewards')->__('Type'),
            'align'     =>'left',
            'index'     => 'obj_type',
            'type'      => 'options',
            'options'   => Mage::getSingleton('rewards/history')->getTypes(),
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('rewards')->__('Description'),
            'align'     =>'left',
            'index'     => 'description',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_HistoryDescription,
            'type' => 'text',
        ));

        $this->addColumn('points', array(
            'header'    => Mage::helper('rewards')->__('Points'),
            'align'     =>'left',
            'index'     => 'points',
            'type' => 'number',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('rewards')->__('Created'),
            'align'     =>'left',
            'index'     => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('expire_at', array(
            'header'    => Mage::helper('rewards')->__('Expiry Date'),
            'align'     =>'left',
            'index'     => 'expire_at',
            'type' => 'date',
            'renderer'  => new Plumrocket_Rewards_Block_Adminhtml_Grid_Renderer_ExpireAt,
        ));

        return parent::_prepareColumns();
    }


    public function getStoreId()
    {
        return $this->_storeId;
    }


    public function getWebsiteId()
    {
        return $this->_websiteId;
    }


    public function getSession()
    {
        return Mage::getSingleton('admin/session');
    }

}
