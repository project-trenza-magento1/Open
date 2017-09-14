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


class Plumrocket_Rewards_Block_Adminhtml_Customer_Edit_Tab_Rewards_History extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        $this->setId('manage_rewards_history');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }


    protected function _prepareCollection()
    {
        $_tp = (string) Mage::getConfig()->getTablePrefix();
        $collection = Mage::getModel('rewards/history')->getCollection()
            ->addFilterToMap('created_at', 'main_table.created_at')
            ->addFieldToFilter('customer_id', Mage::registry('current_customer')->getId());

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

        if (!Mage::app()->isSingleStoreMode()) {

            $storeGroups = array();
            foreach(Mage::app()->getWebsites() as $website){
                foreach($website->getGroups() as $group){
                    $storeGroups[$group->getId()] = $group->getName().' ('.$website->getName().')';
                }
            }

            $this->addColumn('store_group_id', array(
                'header'        => $this->__('Store View'),
                'index'         => 'store_group_id',
                'type'          => 'options',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => true,
                'options'        => $storeGroups,
            ));
        }

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


    public function getGridUrl()
     {
       return $this->getUrl('adminhtml/rewards_history/customergrid', array('_current'=>true, 'customer_id' => Mage::registry('current_customer')->getId()));
     }

}
