<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Mailjet_Iframes_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    public function synchronize()
	{
        /*
         * Sync all subscribed customers from Magento to Mailjet
         */
        $syncManager = new Mailjet_Iframes_Helper_SyncManager();
        return $syncManager->synchronize();
	}
    
    
}
