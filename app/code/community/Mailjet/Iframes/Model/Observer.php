<?php

/**
 * Mailjet
 */
class Mailjet_Iframes_Model_Observer
{
    protected static $fields = array ();

    public function sendTestMail(Varien_Event_Observer $observer)
    {
        $data = $observer->getEvent()->getData('data_object')->getData();

        switch ($data['field'])
        {
            case 'login':
            {
                self::$fields['username'] = $data['value'];

                break;
            }
            case 'password':
            case 'test':
            case 'test_address':
            {
                self::$fields[$data['field']] = $data['value'];

                break;
            }
        }

        if (isset(self::$fields['test']) && 4 == count(self::$fields))
        {
            $configs = array(array('ssl://', 465),
                              array('tls://', 587),
                              array('', 587),
                              array('', 588),
                              array('tls://', 25),
                              array('', 25));

            $host = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_HOST);
            $connected = FALSE;

            for ($i = 0; $i < count($configs); ++$i) {
                
                $soc = @fSockOpen($configs [$i] [0].$host, $configs [$i] [1], $errno, $errstr, 5);

                if ($soc) {
                    
                    fClose ($soc);

                    $connected = TRUE;

                    break;
                }
            }

            if ($connected) {
                
                if ('ssl://' == $configs [$i] [0])
                {
                    Mage::getConfig()->saveConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_SSL, 'SSL');
                }
                elseif ('tls://' == $configs [$i] [0])
                {
                    Mage::getConfig()->saveConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_SSL, 'TLS');
                }
                else
                {
                    Mage::getConfig()->saveConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_SSL, '');
                }

                Mage::getConfig()->saveConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PORT, $configs [$i] [1]);

                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();

                $to = self::$fields ['test_address'];
                $from = Mage::getStoreConfig('trans_email/ident_general/email');

                $Mail = Mage::getModel('iframes/mail');

                $Mail->setBody(Mage::helper('iframes')->__('Your Mailjet configuration is ok!'));
                $Mail->setIsPlain(TRUE);
                $Mail->setSubject(Mage::helper('iframes')->__('Your test mail from Mailjet'));

                $Mail
                    ->setFromName('Mailjet')
                    ->setFromEmail($from)
                    ->setReplyTo($from)
                    ->setToName($to)
                    ->setToEmail($to);

                $sender = Mage::getModel('iframes/email_template')->load (Mage::getStoreConfig(Mage::app()->getStore()->getId()));

                $sender->sendMail($Mail, self::$fields);
            } else {
                throw new Exception(sPrintF(Mage::helper('iframes')->__('Please contact Mailjet support to sort this out.').'<br /><br />%d - %s', $errno, $errstr));
            }
        }
    }
    
    
    /**
     * Create or Update Mailjet contact list and add all newsletter subscribed Magento customers to that list
     * This event is triggered ot System Configuration save
     * 
     * @param Varien_Event_Observer $observer
     * @return boolean
     */
    public function initialSync(Varien_Event_Observer $observer)
    { 
        $data = $observer->getEvent()->getData('data_object')->getData();
     
        if(isset($data['field']) && $data['field'] == 'login') {
            if(!Mage::registry('failedApiKeyValidation')) {
            $credentialsOk = Mailjet_Iframes_Helper_Config::checkApiCredentials();
            if($credentialsOk) {
                $syncManager = new Mailjet_Iframes_Helper_SyncManager();
                $syncManager->synchronize();
                }
            }
        }
        return true;        
    }
    
    
    
    public function checkValidApiKey(Varien_Event_Observer $observer)
    {         
        try {
			
            $data = $observer->getEvent()->getData('data_object')->getData();
            if(isset($data['field']) && $data['field'] == 'login') {
                $mailjetApi = new Mailjet_Iframes_Helper_ApiWrapper(
                    Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_LOGIN), 
                    Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PASSWORD)
                );
                $response = $mailjetApi->sender(array('limit' => 1))->getResponse();
                // Check if the list exists
                if(!isset($response->Data)) {
                    Mage::register('failedApiKeyValidation', true);
                    Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('iframes')->__("Please verify that you have entered your API and secret key correctly. <br />If this is the case and you have still this error message, please go to Account API keys (<a href='https://www.mailjet.com/account/api_keys'>https://www.mailjet.com/account/api_keys</a>) to regenerate a new Secret Key for the plug-in."));
                    Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/mailjetiframes_options'));
                    return false;
                }
            }

		} catch (Exception $e) {
            Mage::register('failedApiKeyValidation', true);
			return false;
		}

        Mage::unregister('failedApiKeyValidation');
        return true;        
    }
    
    
    /**
     * Synchronize customer on registration
     * @param type $observer
     * @return boolean
     */
    public function customerRegisterSync(Varien_Event_Observer $observer)
    { 
        $customer = $observer->getCustomer();
        $email = $customer->getData('email');
        
        if ($this->_isSubscribed($email)) {
            $credentialsOk = Mailjet_Iframes_Helper_Config::checkApiCredentials();
            if($credentialsOk) {
                $syncManager = new Mailjet_Iframes_Helper_SyncManager();
                $syncManager->synchronize(array($email));
            }
        }
        
        return true;        
    }
    
    
    /**
     * Synchronize customer on edit/save
     * @param type $observer
     * @return boolean
     */
    public function customerSaveSync(Varien_Event_Observer $observer)
    {
        $credentialsOk = Mailjet_Iframes_Helper_Config::checkApiCredentials();
        if($credentialsOk) {
            $syncManager = new Mailjet_Iframes_Helper_SyncManager();
        }
        
        $customer = $observer->getCustomer();
        $orig_email = $customer->getOrigData('email');
        $new_email = $customer->getData('email');
        
        if($orig_email != $new_email) {
            // remove contact with the OLD email from mailjet list
            $syncManager->removeContactsFromMailjetList(array($orig_email));
        } 

        /*
         * Check if customer is subscribet and if is - add it to mailjet list,
         * otherwise remove it from mailjet list
         */
        if ($this->_isSubscribed($new_email)) {
            $syncManager->synchronize(array($new_email));
        } else {
            $syncManager->removeContactsFromMailjetList(array($new_email));
        }
        
        return true;

    }
    
    
    
    public function customerDeleteSync(Varien_Event_Observer $observer)
    {
        $customer = $observer->getCustomer();
        $email = $customer->getData('email');

        $credentialsOk = Mailjet_Iframes_Helper_Config::checkApiCredentials();
        if($credentialsOk) {
            $syncManager = new Mailjet_Iframes_Helper_SyncManager();
            $syncManager->removeContactsFromMailjetList(array($email));
        }

        return true;        
    }
    
    
    
    public function customerNewOrderSync(Varien_Event_Observer $observer)
    {
        $credentialsOk = Mailjet_Iframes_Helper_Config::checkApiCredentials();
        if($credentialsOk) {
            $syncManager = new Mailjet_Iframes_Helper_SyncManager();
        }
        $customer = $observer->getCustomer();
        $email = $customer->getData('email');
        //Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('customerNewOrderSync'."\r\n".print_r($customer->getData(), 1));
        $syncManager->removeContactsFromMailjetList(array($email));
        /*
         * Check if customer is subscribet and if is - add it to mailjet list,
         * otherwise remove it from mailjet list
         */
        if ($this->_isSubscribed($email)) {
            $syncManager->synchronize(array($email));
        } 
        return true;
    }
    
    
    private function _subscribeByEmail($email)
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
        if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            $subscriber->setImportMode(true)->subscribe($email);
        }
        
        return $subscriber;
    }
    
    
    private function _isSubscribed($email)
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
        return ($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
    }
    
}

?>