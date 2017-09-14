<?php 
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Helper
 */
class Remmote_Facebookpixelremarketing_Helper_Data extends Mage_Core_Helper_Abstract
{

    //Config paths
    const MODULE_ENABLED            = 'remmote_facebookpixelremarketing/general/enabled';
    const PIXEL_ID                  = 'remmote_facebookpixelremarketing/general/pixel_id';
    const VIEW_CONTENT              = 'remmote_facebookpixelremarketing/events/view_content';
    const SEARCH                    = 'remmote_facebookpixelremarketing/events/search';
    const ADD_TO_CART               = 'remmote_facebookpixelremarketing/events/add_to_cart';
    const ADD_TO_WISHLIST           = 'remmote_facebookpixelremarketing/events/add_to_wishlist';
    const ADD_PAYMENT_INFO          = 'remmote_facebookpixelremarketing/events/add_payment_info';
    const INITIATE_CHECKOUT         = 'remmote_facebookpixelremarketing/events/initiate_checkout';
    const PURCHASE                  = 'remmote_facebookpixelremarketing/events/purchase';
    const LEAD                      = 'remmote_facebookpixelremarketing/events/lead';
    const COMPLETE_REGISTRATION     = 'remmote_facebookpixelremarketing/events/complete_registration';
    
    //Product catalogs
    const CRON_EXPRESSION_PATH  = 'crontab/jobs/remmote_facebookpixelremarketing_exportcatalog/schedule/cron_expr';
    const EXTRA_ATTRIBUTES      = 'remmote_facebookpixelremarketing/product_catalogs/extra_attributes';
    const EXPORT_ALL            = 'remmote_facebookpixelremarketing/product_catalogs/export_all';
    const SYNC_ENABLED          = 'remmote_facebookpixelremarketing/product_catalogs/enabled';
    const CRON_FREQUENCY        = 'remmote_facebookpixelremarketing/product_catalogs/frequency';
    const CRON_TIME             = 'remmote_facebookpixelremarketing/product_catalogs/time';
    const TIME_LASTEXPORT       = 'remmote_facebookpixelremarketing/product_catalogs/time_lastexport';
    const INCLUDE_TAX           = 'remmote_facebookpixelremarketing/product_catalogs/include_tax';
    const EXPORT_NOT_VISIBLE    = 'remmote_facebookpixelremarketing/product_catalogs/export_not_visible_individually';

    //Third party checkout extensions
    const ONESTEPCHECKOUT_ENABLED   = 'onestepcheckout/general/rewrite_checkout_links';
    
    /**
     * Check if module is enabled and Pixel ID is set
     * @param  [type]     $store
     * @return boolean
     * @author edudeleon
     * @date   2016-10-10
     */
	public function isEnabled($store = null)
    {
        $pixelId = $this->getPixelId($store);
        return $pixelId && Mage::getStoreConfig(self::MODULE_ENABLED, $store);
    }

    /**
     * Get Pixel ID
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-10
     */
	public function getPixelId($store = null)
    {
        return Mage::getStoreConfig(self::PIXEL_ID, $store);
    }

    /**
     * Check if viewContent event is enabled
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function viewContentEnabled($store = null){
        return Mage::getStoreConfig(self::VIEW_CONTENT, $store);
    }

    /**
     * Check if Search event is enabled
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function searchEnabled($store = null){
        return Mage::getStoreConfig(self::SEARCH, $store);
    }

    /**
     * Check if AddToCart event is enabled
     * @param  [type]     $store
     * @author edudeleon
     * @date   2016-10-11
     */
    public function addToCartEnabled($store = null){
        return Mage::getStoreConfig(self::ADD_TO_CART, $store);
    }

    /**
     * Check if AddToWhislist event is enabled
     * @param  [type]     $store
     * @author edudeleon
     * @date   2016-10-11
     */
    public function addToWhishlistEnabled($store = null){
        return Mage::getStoreConfig(self::ADD_TO_WISHLIST, $store);
    }

    /**
     * Check if AddPaymentInfo event is enabled
     * @param  [type]     $store
     * @author edudeleon
     * @date   2016-10-11
     */
    public function addPaymentInfoEnabled($store = null){
        return Mage::getStoreConfig(self::ADD_PAYMENT_INFO, $store);
    }

    /**
     * Check if InitiateCheckout event is enabled
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function initiateCheckoutEnabled($store = null){
        return Mage::getStoreConfig(self::INITIATE_CHECKOUT, $store);
    }

    /**
     * Check if Purchase event is enabled
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function purchaseEnabled($store = null){
        return Mage::getStoreConfig(self::PURCHASE, $store);
    }

    /**
     * Check if OneStepCheckout is enabled in the store
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2017-05-10
     */
    public function onestepcheckoutEnabled($store = null) {
        return Mage::getStoreConfig(self::ONESTEPCHECKOUT_ENABLED, $store);
    }

    /**
     * check if Lead event is enabled
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function leadEnabled($store = null){
        return Mage::getStoreConfig(self::LEAD, $store);
    }

    /**
     * check if CompleteRegistration event is enabled
     * @param  [type]     $store
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function completeRegistrationEnabled($store = null){
        return Mage::getStoreConfig(self::COMPLETE_REGISTRATION, $store);
    }

    /**
     * Check if module is enabled
     * @param  [type]     $store            Store ID. If website_level is set, the value correspond to the Website ID
     * @param  boolean    $website_level    Define if value is retrieve by website level
     * @return boolean
     * @author remmote
     * @date   2016-11-29
     */
    public function isSyncEnabled($store = null, $website_level = false){
        if($website_level){
            return Mage::app()->getWebsite($store)->getConfig(self::SYNC_ENABLED);
        } else {
            return Mage::getStoreConfig(self::SYNC_ENABLED, $store);
        }
    }   

    /**
     * Return array of extra attributes
     * @param  [type]     $website
     * @param  boolean    $array_format
     * @return [type]
     * @author edudeleon
     * @date   2017-03-29
     */
    public function getExtraAttributes($website = null, $array_format=true){
        if($website){
            $extra_attributes = Mage::app()->getWebsite($website)->getConfig(self::EXTRA_ATTRIBUTES);
        } else {
            $extra_attributes = Mage::getStoreConfig(self::EXTRA_ATTRIBUTES, $website);
        }

        if($array_format){
            if($extra_attributes){
                $extra_attributes = explode(',', $extra_attributes);
                if(is_array($extra_attributes)){
                    foreach ($extra_attributes as $key => $value) {
                        $extra_attributes[$key] = trim($value);
                    }
                    return $extra_attributes;
                } else {
                    return array();
                }
            }
        }

        return $extra_attributes;
    }

    /**
     * Check if option to export all products is selected
     * @param  [type]     $website  Website ID
     * @return [type]
     * @author edudeleon
     * @date   2016-12-03
     */
    public function exportAll($website = null){
        if($website){
            return Mage::app()->getWebsite($website)->getConfig(self::EXPORT_ALL);
        } else {
            return Mage::getStoreConfig(self::EXPORT_ALL, $website);
        }
    }

    /**
     * Export products not visible individually
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2017-05-31
     */
    public function exportProductsNotVisibleIndividually($website = null){
        if($website){
            return Mage::app()->getWebsite($website)->getConfig(self::EXPORT_NOT_VISIBLE);
        } else {
            return Mage::getStoreConfig(self::EXPORT_NOT_VISIBLE, $website);
        }
    }

    /**
     * Check if include tax in product price
     * @param  [type]     $website
     * @return [type]
     * @author edudeleon
     * @date   2017-05-31
     */
    public function includeTax($website = null){
        if($website){
            return Mage::app()->getWebsite($website)->getConfig(self::INCLUDE_TAX);
        } else {
            return Mage::getStoreConfig(self::INCLUDE_TAX, $website);
        }
    }

    /**
     * Gets the cron frequency cofiguration value
     * @param  [type]     $store
     * @return [type]
     * @author remmote
     * @date   2016-11-29
     */
    public function getCronFrequency($store = null){
        return Mage::getStoreConfig(self::CRON_FREQUENCY, $store);
    } 

    /**
     * Gets the cron time configuration value
     * @param  [type]     $store
     * @return [type]
     * @author remmote
     * @date   2016-11-29
     */
    public function getCronTime($store = null){
        return Mage::getStoreConfig(self::MODULE_TIME, $store);
    }

    /**
     * Get store default website
     * @return [type]
     * @author edudeleon
     * @date   2017-06-01
     */
    public function getDefaultWebsite() {
        $websites       = Mage::getModel('core/website')->getCollection()->addFieldToFilter('is_default', 1);
        $website        = $websites->getFirstItem();

        return $website;
    }
}