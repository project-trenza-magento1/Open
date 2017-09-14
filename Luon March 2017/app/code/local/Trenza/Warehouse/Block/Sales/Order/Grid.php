<?php
class Trenza_Warehouse_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{   
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
         
        // Append new mass action option 
        $this->getMassactionBlock()->addItem(
            'warehouse',
            array('label' => $this->__('Send order to ongoing'), 
                  'url'   => $this->getUrl('warehouse/adminhtml_warehouse/processOrder') //this should be the url where there will be mass operation
            )
        );

        $this->getMassactionBlock()->addItem(
            'ongoingstatus',
            array('label' => $this->__('Ongoing Status'),
                'url'   => $this->getUrl('warehouse/adminhtml_warehouse/ongoingStatus') //this should be the url where there will be mass operation
            )
        );

    }
}