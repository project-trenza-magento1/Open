<?php

class Raveinfosys_Exporter_Adminhtml_ImporterController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
                ->_setActiveMenu('exporter/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders Import'), Mage::helper('adminhtml')->__('Orders Import'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
                ->renderLayout();
    }

    public function farsAction()
    {
        
        #
        $x =  Mage::getSingleton("sales/order")->getCollection()->addAttributeToSelect('entity_id')->getLastItem()->getId();
        
        echo $x;
        
        $coll = Mage::getModel('sales/order_status_history')->getCollection()->addAttributeToSelect('entity_id')->addFieldToFilter('parent_id', 21444);
        
        
        foreach ($coll as $history):
            echo '<pre>';
            print_r($history);
            echo '</pre>';
        
        endforeach;
        
        
        
    }

    public function importOrdersAction()
    {
                
        
        
		ignore_user_abort(true);
        if ($_FILES['order_csv']['name'] != '') {
            $data = $this->getRequest()->getPost();
            try {
                $uploader = new Varien_File_Uploader('order_csv');
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                
                $path = Mage::getBaseDir('media') . DS . 'raveinfosys/exporter/import/';
                $uploader->save($path, $_FILES['order_csv']['name']);
                
                $csv = Mage::getModel('exporter/importorders')->readCSV($path . $_FILES['order_csv']['name'], $data);
                #mail('faroque.golam@gmail.com' , 'test 7' , 'hello');
            
                #echo 'Success';
                #exit;
            
                $this->_redirect('*/*/');
            } 
            catch (Exception $e) 
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                echo $e->getMessage();exit;
            }

            
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('exporter')->__('Unable to find the import file'));
            $this->_redirect('*/*/');
            
            #echo 'File Name: ' . $_FILES['order_csv']['name'];
            #exit;
        }
    }

}
