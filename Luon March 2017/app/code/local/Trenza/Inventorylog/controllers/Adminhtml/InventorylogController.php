<?php
class Trenza_Inventorylog_Adminhtml_InventorylogController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
			$this->loadLayout()->_setActiveMenu("catalog/inventorylog")->_addBreadcrumb(Mage::helper("adminhtml")->__("Inventory Log"),Mage::helper("adminhtml")->__("Inventory Log"));
			return $this;
	}
	public function indexAction() 
	{     
        $this->_initAction();        
        $this->renderLayout();
	}
    
    protected function _isAllowed()
    {
        return true;
    }        
    
    public function newAction() 
	{     
        $this->_forward('view');
	}
    public function viewAction() 
	{
        $id = $this->getRequest()->getParam('id');
        $log_data  = Mage::getModel('inventorylog/inventorylog')->load($id);
        
        Mage::register('log_data', $log_data);	
               
        $this->_initAction();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
	} 
    
    public function saveAction()
    {   
        $data = $this->getRequest()->getPost();
        
        
        
        
        $model = Mage::getModel('inventorylog/inventorylog');		
        
        $model->setData($data)->setId($this->getRequest()->getParam('id'))->save();
        
        $message = "Save Successfully!";
                     
        Mage::getSingleton('core/session')->addSuccess($message);
     
        $this->_redirect('*/*/');
        
    }
    
    public function deleteAction()
	{
		if( $this->getRequest()->getParam("id") > 0 ) {
			try {
				$model = Mage::getModel("inventorylog/inventorylog");
				$model->setId($this->getRequest()->getParam("id"))->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
				$this->_redirect("*/*/");
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				$this->_redirect("*/*/view", array("id" => $this->getRequest()->getParam("id")));
			}
		}
		$this->_redirect("*/*/");
	} 
                   
}