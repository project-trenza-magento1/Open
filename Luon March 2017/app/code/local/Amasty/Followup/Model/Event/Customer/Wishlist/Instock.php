<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


class Amasty_Followup_Model_Event_Customer_Wishlist_Instock extends Amasty_Followup_Model_Event_Customer_Wishlist
{
    function validate($customer){
        $wishlist = Mage::getModel("wishlist/wishlist")->loadByCustomer($customer);

        return $wishlist->getItemsCount() > 0 &&
        $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
    }
}