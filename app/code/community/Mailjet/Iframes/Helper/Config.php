<?php
/**
 * Mailjet
 */
class Mailjet_Iframes_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'mailjetiframes_options/general/enabled';
    const XML_PATH_TEST = 'mailjetiframes_options/general/test';
    const XML_PATH_TEST_ADDRESS = 'mailjetiframes_options/general/test_address';
    const XML_PATH_SMTP_HOST = 'mailjetiframes_options/smtp/host';
    const XML_PATH_SMTP_PORT = 'mailjetiframes_options/smtp/port';
    const XML_PATH_SMTP_LOGIN = 'mailjetiframes_options/smtp/login';
    const XML_PATH_SMTP_PASSWORD = 'mailjetiframes_options/smtp/password';
    const XML_PATH_SMTP_SSL = 'mailjetiframes_options/smtp/ssl';
    
    
    
    
    public static function checkApiCredentials()
    {
        $login = Mage::getStoreConfig(self::XML_PATH_SMTP_LOGIN);
        $password = Mage::getStoreConfig(self::XML_PATH_SMTP_PASSWORD);
        return (!empty($login) && !empty($password));
    }
}