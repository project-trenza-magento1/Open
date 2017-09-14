<?php 
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote    
 * @copyright   2016 - Remmote.com
 * @descripion  Save cronjob expression for the extension
 */
class Remmote_Facebookpixelremarketing_Model_System_Config_Backend_Cron extends Mage_Core_Model_Config_Data
{
 
    /**
     * Save the cron expression in core_config table
     * @return [type]
     * @author edudeleon
     * @date   2015-05-12
     */
    protected function _afterSave()
    {
        // Get values from backend
        $frequency_days = $this->getData('groups/product_catalogs/fields/frequency/value');
        $time_value     = $this->getValue();

        $time           = explode(",", $time_value);
        $hour           = (int)$time[0];
        $minute         = (int)$time[1];

        //Prepare cron expression (Run every N days at X time)
        $cron_expression = $minute." ".$hour. " */".$frequency_days." * *";

        //Save to cron expression
        Mage::getModel('core/config')->saveConfig(Remmote_Facebookpixelremarketing_Helper_Data::CRON_EXPRESSION_PATH, $cron_expression);
    }
}