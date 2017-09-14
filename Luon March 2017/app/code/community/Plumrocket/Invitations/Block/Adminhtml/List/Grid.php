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
 * @package     Plumrocket_Invitations
 * @copyright   Copyright (c) 2012 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */
?>
<?php 

class Plumrocket_Invitations_Block_Adminhtml_List_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    private $_modelInvitations;

    public function __construct()
    {
        parent::__construct();

        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);

        $this->_modelInvitations = Mage::getModel('invitations/invitations');
    }

    
    protected function _prepareCollection()
    {
        $_tp = (string) Mage::getConfig()->getTablePrefix();
        $collection = $this->_modelInvitations->getCollection()
            ->addFilterToMap('website_id', 'main_table.website_id')
            ->addFilterToMap('created_at', 'main_table.created_at');
         
        $collection->getSelect()
            ->joinLeft( array('c' => $_tp.'customer_entity'), 'main_table.customer_id = c.entity_id', array('c.email'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header'    => Mage::helper('invitations')->__('ID'),
            'align'     =>'left',
            'index'     => 'id',
            'type' => 'text',  
        ));
        
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('invitations')->__('Customer ID'),
            'align'     =>'left',
            'index'     => 'customer_id',
            'type' => 'text',
        
        ));
        
        
        $this->addColumn('email', array(
            'header'    => Mage::helper('invitations')->__('Customer Email'),
            'align'     =>'left',
            'index'     => 'email',
            'renderer'  => 'Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_CustomerEmail',
            'type' => 'text',
        )); 
        
        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('invitations')->__('Customer Name'),
            'align'     =>'left',
            'index'     => 'customer_id',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => new Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_CustomerName,
            'type' => 'text',
        )); 
        
        $this->addColumn('invitee_connect_code', array(
            'header'    => Mage::helper('invitations')->__('Invitee Email'),
            'align'     =>'left',
            'index'     => 'invitee_connect_code',
            'type'      => 'text',
        )); 
        
        $this->addColumn('invitee_customer_id', array(
            'header'    => Mage::helper('invitations')->__('Invitee Customer ID'),
            'align'     =>'left',
            'index'     => 'invitee_customer_id',
            'type' => 'text',
        ));

        $this->addColumn('invitee_coupon_code', array(
            'header'    => Mage::helper('invitations')->__('Invitee Coupon Code'),
            'align'     =>'left',
            'index'     => 'invitee_coupon_code',
            'type' => 'text',
        ));
        
        $this->addColumn('invitee_customer_name', array(
            'header'    => Mage::helper('invitations')->__('Invitee Customer Name'),
            'align'     =>'left',
            'index'     => 'invitee_customer_id',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => new Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_CustomerInviteeName,
            'type' => 'text',
        ));
        
        /*$this->addColumn('sent_count', array(
            'header'    => Mage::helper('invitations')->__('Number of Times Sent'),
            'align'     =>'left',
            'index'     => 'sent_count',
            'type' => 'text',
        ));*/
    
        $this->addColumn('deactivated', array(
            'header'    => Mage::helper('invitations')->__('Deactivated'),
            'align'     =>'left',
            'index'     => 'deactivated',
            'type'      => 'options', 
            'options'   => array($this->__('No'),$this->__('Yes')), 
        )); 
    
        $this->addColumn('first_order', array(
            'header'    => Mage::helper('invitations')->__('First Order'),
            'align'     =>'left',
            'index'     => 'first_order',
            'type'      => 'options', 
            'options'   => array($this->__('No'),$this->__('Yes')), 
        ));
        
        if( !Mage::app()->isSingleStoreMode() ){
            $websites = array();
            foreach(Mage::app()->getWebsites() as $website){
                $websites[$website->getId()] = $website->getName();
            }
            
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('invitations')->__('Website'),
                'align'     =>'left',
                'index'     => 'website_id',
                'type'      => 'options', 
                'options'   => $websites, 
            )); 
        }
        
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('invitations')->__('Created'),
            'align'     =>'left',
            'index'     => 'created_at',
            'type' => 'date',
        ));
    
        $confirmedAtRenderer = new Plumrocket_Invitations_Block_Adminhtml_Grid_Renderer_Date();
        $confirmedAtRenderer->setField('confirmed_at');
    
        $this->addColumn('confirmed_at', array(
            'header'    => Mage::helper('invitations')->__('Confirmed'),
            'align'     =>'left',
            'index'     => 'confirmed_at',
            'renderer'  => $confirmedAtRenderer,
            'type'      => 'date',
        ));

        $this->addColumn('reward_coupon_code', array(
            'header'    => Mage::helper('invitations')->__('Award Coupon Code'),
            'align'     =>'left',
            'index'     => 'reward_coupon_code',
            'type' => 'text',
        ));
        
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('invitations')->__('Delete'),
             'url'      => $this->getUrl('*/*/delete', array('_current'=>true)),
             'confirm'  => Mage::helper('invitations')->__('Are you sure?'),
        ));
        
        return $this;
    }
}
