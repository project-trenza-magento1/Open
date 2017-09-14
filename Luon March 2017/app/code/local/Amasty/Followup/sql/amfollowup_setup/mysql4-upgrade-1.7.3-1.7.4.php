<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

$this->run("
ALTER TABLE `{$this->getTable('amfollowup/rule')}` CHANGE COLUMN `cancel_event_type` `cancel_event_type` TEXT
");


