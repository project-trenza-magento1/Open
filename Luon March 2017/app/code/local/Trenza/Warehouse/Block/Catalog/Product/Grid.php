<?php
class Trenza_Warehouse_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{   
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
         
        // Append new mass action option 
        $this->getMassactionBlock()->addItem(
            'warehouse',
            array('label' => $this->__('Process Article'), 
                  'url'   => $this->getUrl('warehouse/adminhtml_warehouse/processArticle') //this should be the url where there will be mass operation
            )
        );
    }
}