<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */ 
class Amasty_Segments_Model_Mysql4_Customer extends Amasty_Segments_Model_Mysql4_Abstract
{
    
//SELECT salesOrder.entity_id, amCustomer.entity_id
//FROM sales_flat_order AS salesOrder
//INNER JOIN amasty_amsegments_customer amCustomer ON amCustomer.customer_email = salesOrder.customer_email
//WHERE salesOrder.customer_id IS NULL
    
    public function _construct()
    {    
        $this->_init('amsegments/customer', 'entity_id');
    }
        
    function bulkUpdate($clearIndex = false){
        if($clearIndex) {
            $this->_clear();
        }
        $this->_fromOrders($clearIndex);
        $this->_fromCustomers($clearIndex);
    }

    protected function _clear(){
        $this->_getWriteAdapter()->delete(
            $this->getTable('amsegments/customer')
        );
        return $this;
    }
    
    protected function _fromCustomers($allTime = false){
        $adapter = $this->_getWriteAdapter();
        
        $segmentsCustomer   = $this->getTable('amsegments/customer');
        
        $columns = array(
            'customer_id',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'website_id',
        );
        
        $select = Mage::getResourceModel('customer/customer_collection')
                ->addNameToSelect()
                ->getSelect();
        if(!$allTime) {
            $this->_addTimeRange($select, 'updated_at');
        }

        $beforeColumns = $select->getPart(Zend_Db_Select::COLUMNS);
        
        $select->reset(Zend_Db_Select::COLUMNS);
        
        $select->columns("entity_id");
        $select->columns("email");
        
        foreach($beforeColumns as $column){
            if ($column[2] == 'firstname'){
                $select->columns($column[0] . '.' . $column[1]);
            }
        }
        
        foreach($beforeColumns as $column){
            if ($column[2] == 'lastname'){
                $select->columns($column[0] . '.' . $column[1]);
            }
        }
        
        $select->columns("website_id");
        
        $sql = $select->insertFromSelect(array('e' => $segmentsCustomer), $columns, TRUE);

        return $adapter->query($sql);
    }


    protected function _fromOrders($allTime = false)
    {
        $adapter = $this->_getWriteAdapter();
        $salesOrder   = $this->getTable('sales/order');
        $customer   = $this->getTable('amsegments/customer');
        
        $select = $adapter->select()->from($salesOrder . ' as main_table', array(
            'customer_id',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'store.website_id',
        ));
        
        $select->joinInner(
                array('store' => $this->getTable('core/store')),
                'main_table.store_id = store.store_id',
                array());
        
//        $select->where('state = ?', Mage_Sales_Model_Order::STATE_COMPLETE);
        if(!$allTime) {
            $this->_addTimeRange($select, 'main_table.created_at');
        }
        
        $sql = $select->insertFromSelect(array('e' => $customer), array(
            'customer_id',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'website_id',
        ), TRUE);
        
        return $adapter->query($sql);
    }
}
