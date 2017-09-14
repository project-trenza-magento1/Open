<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Facebook Product Catalog export button block
 */
class Remmote_Facebookpixelremarketing_Block_Adminhtml_System_Config_Fields_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('remmote/facebookpixelremarketing/system/config/fields/button.phtml');
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
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxExportcatalogUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/productcatalogs/export');
    }

    /**
     * Get website code from URL
     * @return [type]
     * @author edudeleon
     * @date   2016-11-30
     */
    public function getWebsiteCode(){
        return Mage::app()->getRequest()->getParam('website');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    { 
        //Get current website code from URL
        $websiteCode  = $this->getWebsiteCode();

        //Get Magento websites
        $websites           = Mage::app()->getWebsites();
        $restrict_export    = false;
        if(count($websites) > 1){
           if(!$websiteCode) {//Check if website code is present in URL
                $restrict_export = true;
           }
        }

        //Prepare button properties
        $button_properties = array(
            'id'        => 'check',
            'label'     => $this->helper('adminhtml')->__('Export Now'),
            'style'     => 'width: 280px;',
            'onclick'   => 'javascript:export_catalog(); return false;',
        );

        if($restrict_export){
            $button_properties['disabled'] = 'disabled'; 
        }

        $button     = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($button_properties);
        $buttonHTML =  $button->toHtml();

        //Get product catalog file URL
        $filename = $websiteCode ? 'products_'.$websiteCode.'.csv' : 'products_default.csv';
        $product_catalog_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'facebook_productcatalog'.$filename;
        
        //Message below Export Now button
        $websites_msg = '<p class="note">Click here to export your product catalog and generate the link to access your product catalog file. <br>You might need to reindex your products if your CSV file is empty. </p>';

        //Include notice message
        if($restrict_export){
            $websites_msg = '<p class="note">It seems that you have more than one website configured on your Magento installation. Please select the website from which you need to export the product catalog (dropdown menu in the left site of this screen).</p>';
        }

        $buttonHTML .= $websites_msg;
    
        return $buttonHTML;
    }
}