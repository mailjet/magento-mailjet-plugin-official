<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Mailjet_Iframes_IndexController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     *
     * @var string
     */
    protected $_apikey;
    
    /**
     *
     * @var string
     */
    protected $_secretKey;
    
    public function preDispatch() 
    {
        /*
         * Turns off security key check to make it able to open this action from outside magento
         */
        if ($this->getRequest()->getActionName() == 'events') {
            Mage::getSingleton('adminhtml/url')->turnOffSecretKey();
        } else  {
        parent::preDispatch();
        
        $this->_apikey = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_LOGIN);
        $this->_secretKey = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PASSWORD);
        }
    }

    public function indexAction()
    {
        if (!$this->_apikey || !$this->_secretKey) {
            $this->_redirect('adminhtml/system_config/edit/section/mailjetiframes_options');
        } else {
            $this->_forward('iframe');
        }
    }
    
    /**
     * Includes mailjet iFrame
     */
    public function iframeAction()
    {
        $this->checkValidApiCredentials();
        $this->loadLayout();
        
        $iframesHelper = $this->_getIframesWrapperHelper();

        $block = $this->getLayout()
            ->createBlock('core/text', 'example-block')
            ->setText($iframesHelper->getHtml());

        $this->_addContent($block);
        
        $this->_setActiveMenu('mailjet/settings');
        $this->renderLayout();
    }
    /**
     * 
     */
    public function eventsAction()
    {
        $params = $this->getRequest()->getParams();
		$post = $_REQUEST;
        $postinput = trim(file_get_contents('php://input'));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$postinput0'."\r\n".$postinput['event']);
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$postinput01'."\r\n".json_decode($postinput));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$postinput02'."\r\n".json_encode($params));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$postinput1'."\r\n".print_r($postinput, 1));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$postinput2'."\r\n".json_encode(print_r($postinput, 1)));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$post3'."\r\n".json_encode($postinput));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$post4'."\r\n".print_r($post, 1));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$post5'."\r\n".json_decode(print_r($post, 1)));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$post6'."\r\n".json_decode($post));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('eventsAction'."\r\n".print_r($params, 1));
        Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$params[event]'."\r\n".$params['event']);
		Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$params[event]2'."\r\n".json_decode($params));
		Mage::getModel('core/log_adapter', 'iframes_setup.log')->log('$params[event]3'."\r\n".json_decode($params['event']));
        switch ($params['event']) {
            case 'open':
                /* => do action */
                /* If an error occurs, tell Mailjet to retry later: header('HTTP/1.1 400 Error'); */
                /* If it works, tell Mailjet it's OK */
                header('HTTP/1.1 200 Ok');
                break;
            case 'click':
                /* => do action */
                break;
            case 'bounce':
                /* => do action */
                break;
            case 'spam':
                /* => do action */
                break;
            case 'blocked':
                /* => do action */
                break;
            case 'unsub':
                /* => do action */
//                if(isset($params['email']) && !empty($params['email'])) {
//                    $syncManager = new Mailjet_Iframes_Helper_SyncManager();
//                    $syncManager->usubscribeByEmail($params['email']);
//                }
                break;
            case 'typofix':
                /* => do action */
                break;
            /* # No handler */
            default:
                header('HTTP/1.1 423 No handler');
                /* => do action */
                break;
        }
    }
    
    /**
     * 
     * @return Mailjet_Iframes_Helper_IframesWrapper
     */
    protected function _getIframesWrapperHelper()
    {
        return new Mailjet_Iframes_Helper_IframesWrapper(
            $this->_apikey, $this->_secretKey
        );
    }
    protected function checkValidApiCredentials()
    {
        $mailjetApi = new Mailjet_Iframes_Helper_ApiWrapper(
            $this->_apikey, 
            $this->_secretKey
        );
        $response = $mailjetApi->sender(array('limit' => 1))->getResponse();
        if(!isset($response->Data)) {
            Mage::getSingleton('adminhtml/session')->addNotice("Wrong API login and/or password!");
            Mage::getSingleton('core/session')->addNotice("Wrong API login and/or password!");
            Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/mailjetiframes_options'));
            return false;
        }
        return true;
    }
}