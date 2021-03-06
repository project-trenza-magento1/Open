<?php
/**
 * Intenso Premium Theme
 * 
 * @category    Itactica
 * @package     Itactica_LayeredNavigation
 * @copyright   Copyright (c) 2014-2016 Itactica (http://www.itactica.com)
 * @license     http://getintenso.com/license
 */

class Itactica_LayeredNavigation_Block_Catalog_Product_List_Pager extends Mage_Page_Block_Html_Pager
{

    /**
     * Return current URL with rewrites and additional parameters
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        if (!Mage::helper('itactica_layerednavigation')->isEnabled() ||
            Mage::getSingleton('core/design_package')->getPackageName() != 'intenso') {
            return parent::getPagerUrl($params);
        }

        if ($this->helper('itactica_layerednavigation')->isCatalogSearch() ||
            $this->helper('itactica_layerednavigation')->isAdvancedSearch()) {
            $params['isLayerAjax'] = null;
            return parent::getPagerUrl($params);
        }

        return $this->helper('itactica_layerednavigation')->getPagerUrl($params);
    }

}