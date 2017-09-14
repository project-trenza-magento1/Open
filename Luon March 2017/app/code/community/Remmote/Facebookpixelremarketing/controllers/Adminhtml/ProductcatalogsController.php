<?php 
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Main controller
 */
class Remmote_Facebookpixelremarketing_Adminhtml_ProductcatalogsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Export product catalog to media/facebook_productcatalog folder
     * Fields exported are the required fields to create standard dynamic ads in Facebook
     * @return [type]
     * @author remmote
     * @date   2016-02-09
     */
	public function exportAction()
	{
        //Getting website code
        $websiteCode  = Mage::app()->getRequest()->getParam('websiteCode');
        
        //Intiantate product catalog model
        $product_catalog_model = Mage::getModel('remmote_facebookpixelremarketing/productcatalog');

        //Call method that exports the product catalog
        $product_catalog_model->exportCatalog($websiteCode);

        Mage::getSingleton('adminhtml/session')->addSuccess('Product catalog was succesfully generated.');
	}
}