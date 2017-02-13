<?php
/**
 * Mailjet
 */
class Mailjet_Iframes_Model_Mail extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('iframes/mail');
    }

    public function _beforeSave(){
    	if(!$this->getDate()){
    		$this->setDate(now());
    	}
    	return parent::_beforeSave();
    }
}