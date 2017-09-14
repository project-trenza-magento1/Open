<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Facebook Pixel Events observer
 */
class Remmote_Facebookpixelremarketing_Model_Observer
{
    /**
     * Set a flag in session when a product is added to the cart
     * @param  [type]     $observer
     * @return [type]
     * @author edudeleon
     * @date   2016-10-12
     */
    public function logPixelAddToCart($observer) {
        //Logging event
        Mage::getModel('core/session')->setPixelAddToCart(true);

        //Logging product ID
        $product = $observer->getEvent()->getProduct();
        Mage::getModel('core/session')->setPixelAddToCartProductId($product->getId());
    }

    /**
     * Set a flag in session when a product is added to the wishlist
     * @param  [type]     $observer
     * @return [type]
     * @author edudeleon
     * @date   2016-10-12
     */
    public function logPixelAddToWishlist($observer) {
        //Logging event
        Mage::getModel('core/session')->setPixelAddToWishlist(true);

        //Logging product ID
        $product = $observer->getEvent()->getProduct();
        Mage::getModel('core/session')->setPixelAddToWishlistProductId($product->getId());
    }

    /**
     * Set a flag in session when a customer selects the payment method
     * @param  [type]     $observer
     * @return [type]
     * @author edudeleon
     * @date   2016-10-12
     */
    public function logPixelPaymentInfo($observer){
        //Logging event
        Mage::getModel('core/session')->setPixelPaymentInfo(true);
    }

    /**
     * Set a flag in session when a purchase is made
     * @param  [type]     $observer
     * @return [type]
     * @author edudeleon
     * @date   2016-10-12
     */
    public function logPixelPurchase($observer){
        //Logging event
        Mage::getModel('core/session')->setPixelPurchase(true);
    }

    /**
     * Set a flag in session when a customer creates an account
     * @param  [type]     $observer
     * @return [type]
     * @author edudeleon
     * @date   2016-10-12
     */
    public function logPixelCompleteRegistration($observer){
        //Logging event
        Mage::getModel('core/session')->setPixelCompleteRegistration(true);
    }

    /**
     * Set a flag in session when a customer signup to the newsletter
     * @param  [type]     $observer
     * @return [type]
     * @author edudeleon
     * @date   2016-10-12
     */
    public function logPixelCompleteRegistrationNewsletter($observer) {
        $subscriber     = $observer->getEvent()->getSubscriber();
        $statusChanged  = $subscriber->getIsStatusChanged();
        if($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED && $statusChanged) {
            Mage::getModel('core/session')->setPixelCompleteRegistration(true);

            //Set flag for Lead Event
            Mage::getModel('core/session')->setPixelLead(true);
        }
    }

    /**
    * Method called by Magento Crontab
    * Export product catalog to media/facebook_productcatalog folder
    * @return [type]
    * @author edudeleon
    * @date   2016-11-29
    */
    public function export_catalog(){
        //Intiantate product catalog model
        $product_catalog_model = Mage::getModel('remmote_facebookpixelremarketing/productcatalog');

        //Load websites
        $websites = Mage::app()->getWebsites();
        if(count($websites) > 1){
            foreach ($websites as $website) {
                //Checks if extension is enable for webiste
                if (Mage::helper('remmote_facebookpixelremarketing')->isSyncEnabled($website->getId(), TRUE)){
                    //Call method that exports the product catalog
                    $product_catalog_model->exportCatalog($website->getCode());          
                }
            }
        } else {
            //Call method that exports the product catalog
            if(Mage::helper('remmote_facebookpixelremarketing')->isSyncEnabled()) {
                $product_catalog_model->exportCatalog();
            }
        }
    }
}