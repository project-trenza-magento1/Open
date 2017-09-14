<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Code Block
 */
class Remmote_Facebookpixelremarketing_Block_Code extends Mage_Core_Block_Template {

	/**
	 * Renders pixel code if module is enabled
	 * @return [type]
	 * @author edudeleon
	 * @date   2016-10-10
	 */
	public function _toHtml()
    {
        if (Mage::helper('remmote_facebookpixelremarketing')->isEnabled()){
            return parent::_toHtml();
        }
    }

    /**
     * Return Facebook Pixel Id
     * @return [type]
     * @author edudeleon
     * @date   2016-10-10
     */
    public function getPixelId(){
    	return Mage::helper('remmote_facebookpixelremarketing')->getPixelId();
    }

    /**
     * Get store current section
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    private function _getSection(){
        $pageSection  = Mage::app()->getFrontController()->getAction()->getFullActionName(); 
        return  $pageSection; 
    }

    /**
     * Get current store currency
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    private function _getStoreCurrency(){
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Return View Content event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getViewContentEvent(){
        $pageSection        = $this->_getSection();
        $currentCurrency    = $this->_getStoreCurrency();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->viewContentEnabled()){ 
            if($pageSection == 'catalog_product_view'){
                $product = Mage::registry('current_product');
                if(!empty($product)){ 
                    //Defining product type
                    $content_type = 'product';

                    //Getting category name
                    $categoryIds   = $product->getCategoryIds();
                    $categoryName  = '';
                    if(count($categoryIds) >= 1){
                        $category_id            = end($categoryIds); //(lowest category level)
                        $category               = Mage::getModel('catalog/category')->load($category_id);
                        $categoryName           = $this->replace_special_chars($category->getName());
                    }

                    return "fbq('track', 'ViewContent', {
                            content_name: '".$this->replace_special_chars($product->getName())."',
                            content_ids: ['".$product->getSku()."'],
                            content_type: '".$content_type."',
                            content_category: '".$categoryName."',
                            value: '".number_format($product->getFinalPrice(),2)."',
                            currency: '".$currentCurrency."'
                    });";
                }
            }
        }
    }

    /**
     * Return Search event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getSearchEvent(){
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->searchEnabled()){
            if($pageSection == 'catalogsearch_result_index' || $pageSection == 'catalogsearch_advanced_result'){
                // Getting search string
                $searchString = $this->replace_special_chars(Mage::app()->getRequest()->getParam('q'));

                return "fbq('track', 'Search', {
                    'search_string' : '". $searchString ."'
                });";
            }
        }
    }

    /**
     * Return AddToCart event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getAddToCartEvent(){
        $pageSection        = $this->_getSection();
        $currentCurrency    = $this->_getStoreCurrency();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->addToCartEnabled()){

            $pixelEvent     = Mage::getModel('core/session')->getPixelAddToCart();
            $productId      = Mage::getModel('core/session')->getPixelAddToCartProductId();
            if($pixelEvent && $productId){
                //Unset events
                Mage::getModel('core/session')->unsPixelAddToCart();
                Mage::getModel('core/session')->unsPixelAddToCartProductId();

                //Loading Product
                $product = Mage::getModel('catalog/product')->load($productId);

                //Checking type of product
                if($product->getTypeId() == 'grouped') {
                    $product = Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection()
                        ->getLastItem()
                        ->getProduct();
                }

                return "fbq('track', 'AddToCart', {
                        content_name:   '".$this->replace_special_chars($product->getName())."',
                        content_ids:    ['".$product->getSku()."'],
                        content_type:   'product',
                        value:          '". number_format($product->getFinalPrice(),2). "',
                        currency:       '". $currentCurrency. "'
                });";   
            }            
        }
    }

    /**
     * Return AddToWishlist event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getAddToWishlistEvent(){
        $pageSection        = $this->_getSection();
        $currentCurrency    = $this->_getStoreCurrency();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->addToWhishlistEnabled()){            
            $pixelEvent     = Mage::getModel('core/session')->getPixelAddToWishlist();
            $productId      = Mage::getModel('core/session')->getPixelAddToWishlistProductId();
            if($pixelEvent && $productId){
                //Unset event
                Mage::getModel('core/session')->unsPixelAddToWishlist();
                Mage::getModel('core/session')->unsPixelAddToWishlistProductId();

                //Loading Product
                $product = Mage::getModel('catalog/product')->load($productId);

                //Checking type of product
                if($product->getTypeId() == 'grouped') {
                    $product = Mage::getSingleton('checkout/session')->getQuote()->getItemsCollection()
                        ->getLastItem()
                        ->getProduct();
                }

                //Getting category name
                $categoryIds   = $product->getCategoryIds();
                $categoryName  = '';
                if(count($categoryIds) >= 1){
                    $category_id            = end($categoryIds); //(lowest category level)
                    $category               = Mage::getModel('catalog/category')->load($category_id);
                    $categoryName           = $this->replace_special_chars($category->getName());
                }

                return "fbq('track', 'AddToWishlist',{
                        content_name:   '".$this->replace_special_chars($product->getName())."',
                        content_ids:    ['".$product->getSku()."'],
                        content_type:   'product',
                        content_category: '".$categoryName."',
                        value:          '". number_format($product->getFinalPrice(),2). "',
                        currency:       '". $currentCurrency. "'
                });";   
            }
        }
    }

    /**
     * Return InitiateCheckout event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getInitiateCheckoutEvent(){
        $pageSection        = $this->_getSection();
        $currentCurrency    = $this->_getStoreCurrency();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->initiateCheckoutEnabled()){
            if ($pageSection == 'checkout_onepage_index' || $pageSection == 'onestepcheckout_index_index' || $pageSection == 'opc_index_index'){

                // Get cart details
                $session            = Mage::getSingleton('checkout/session');
                $productSkuArray    = array();
                $quote              = $session->getQuote();
                foreach($quote->getAllVisibleItems() as $item) {
                    $productSkuArray[]  = $item->getSku();
                }
                $num_items = $this->helper('checkout/cart')->getSummaryCount();

                return "fbq('track', 'InitiateCheckout',{
                        content_ids:    ['".implode(",", $productSkuArray)."'],
                        value:          '". number_format($quote->getGrandTotal(), 2). "',
                        num_items:      '" .$num_items. "',
                        content_type:   'product',
                        currency:       '". $currentCurrency. "'
                });";
            }
        }
    }

    /**
     * Return AddPaymentInfo event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getAddPaymentInfoEvent(){
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->addPaymentInfoEnabled()){
            $pixelEvent = Mage::getModel('core/session')->getPixelPaymentInfo();
            if($pixelEvent){
                //Unset event
                Mage::getModel('core/session')->unsPixelPaymentInfo();

                return "fbq('track', 'AddPaymentInfo');";
            }
        }
    }

    /**
     * Return Purchase event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getPurchaseEvent(){
        $pageSection        = $this->_getSection();
        $currentCurrency    = $this->_getStoreCurrency();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->purchaseEnabled()){
        
            $pixelEvent = Mage::getModel('core/session')->getPixelPurchase();
            if($pixelEvent){
                // Mage::log('RemmotePixel - pageSection: '. $pageSection);

                // Check if standard checkout...
                if(Mage::helper('remmote_facebookpixelremarketing')->onestepcheckoutEnabled()){ //One Step Checkout
                    if($pageSection != 'checkout_onepage_success'){
                        return;
                    }
                }

                //Unset event
                Mage::getModel('core/session')->unsPixelPurchase();

                $orderId            = Mage::getSingleton('checkout/session')->getLastRealOrderId();
                $order              = Mage::getModel('sales/order')->loadByIncrementId($orderId);
                $orderGrandTotal    = number_format($order->getGrandTotal(),2);

                $items = $order->getAllVisibleItems();
                foreach($items as $item){
                    $sku[] = $item->getSku();
                } 
                $num_items = number_format($order->getTotalQtyOrdered(),0);
                
                return "fbq('track', 'Purchase', {
                    content_ids:    ['" . implode("','",$sku) . "'],
                    content_type:   'product',
                    value:          '".$orderGrandTotal."',
                    currency:       '".$currentCurrency."',
                    num_items:      '" .$num_items. "'
                });";
            }
        }
    }  

    /**
     * Return Lead event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getLeadEvent(){
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->leadEnabled()){            
            $pixelEvent = Mage::getModel('core/session')->getPixelLead();
            if($pixelEvent){
                //Unset event
                Mage::getModel('core/session')->unsPixelLead();
                
                return "fbq('track', 'Lead');";
            }
        }
    }

    /**
     * Return CompleteRegistration event track
     * @return [type]
     * @author edudeleon
     * @date   2016-10-11
     */
    public function getCompleteRegistrationEvent(){
        $pageSection = $this->_getSection();

        //Check if event is enabled
        if(Mage::helper('remmote_facebookpixelremarketing')->completeRegistrationEnabled()){
            $pixelEvent = Mage::getModel('core/session')->getPixelCompleteRegistration();
            if($pixelEvent){
                //Unset event
                Mage::getModel('core/session')->unsPixelCompleteRegistration();
                
                return "fbq('track', 'CompleteRegistration');";
            }
        }
    }

    /**
     * Replace special chars
     * @param  [type]     $string
     * @return [type]
     * @author edudeleon
     * @date   2016-12-19
     */
    private function replace_special_chars($string){
        return str_replace("'", "\'", $string);
    }
}