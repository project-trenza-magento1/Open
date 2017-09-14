<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Product Catalog URL block
 */
class Remmote_Facebookpixelremarketing_Block_Adminhtml_System_Config_Fields_Productcatalogurl extends Mage_Adminhtml_Block_System_Config_Form_Field{
	/*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('remmote/facebookpixelremarketing/system/config/fields/productcatalog_url.phtml');
    }
 
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
 
    /**
     * Get product catalog URL based on website selected in the backend
     *
     * @return string
     */
    public function getProductCatalogUrl()
    {
        //Get current website code from URL
        $websiteCode  = Mage::app()->getRequest()->getParam('website');

        //Get Magento websites
        $websites       = Mage::app()->getWebsites();
        $multi_store    = false;
        if(count($websites) > 1){
           if(!$websiteCode) {//Check if website code is present in URL
                $multi_store = true;
           }
        }

        //Getting website
        if ($websiteCode) {
            $website    = Mage::getModel('core/website')->load($websiteCode);
        } else {
            //Getting default website
            $website    = Mage::helper('remmote_facebookpixelremarketing')->getDefaultWebsite();
        }

        //Getting catalog file name...
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            foreach ($stores as $store) {
                if($store->getIsActive()) {
                    $filename   = 'products_'.$website->getCode().'_'.$store->getCode().'.csv';
                }
            }
        }

        //Check if file exist
        $catalog_path   = Mage::getBaseDir('media') . DS . 'facebook_productcatalog'. DS . $filename;
        
        //Check if store is multi-store
        if($multi_store && !$websiteCode){
            $product_catalog_url    = "You have more one than website configured on your Magento installation. Please select the website from which you want to export your product catalog.";
        
        }elseif(!file_exists($catalog_path)) {
                $product_catalog_url    = "No product catalog found. Please configure the extension according to your preferred settings and click the button 'Export Now' to generate your Product Catalog URL.";
        } else {
           
            //Preparing catalog URLs
            $product_catalog_url = '';
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    if($store->getIsActive()){
                        $store_name = '<strong>'.$store->getName().'</strong>';
                        $file_url   =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'facebook_productcatalog'.DS.'products_'.$website->getCode().'_'.$store->getCode().'.csv';

                        $product_catalog_url .= $store_name.'<br>'.$file_url.'<br>'.'<a href="'.$file_url.'" style="color:#888;">Download the .csv file</a><br><br>';
                    }
                }
            }
        }

        return  $product_catalog_url;
    }
}