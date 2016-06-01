<?php

class Mailjet_Iframes_Model_Resource_Iframes extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('iframes/iframes_test', 'website_id');
    }
}