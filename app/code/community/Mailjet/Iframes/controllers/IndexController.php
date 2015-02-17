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
        parent::preDispatch();
        
        $this->_apikey = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_LOGIN);
        $this->_secretKey = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PASSWORD);
    }

    public function indexAction()
    {
        if (!$this->_apikey || !$this->_secretKey) {
            $this->_redirect('adminhtml/system_config/edit/section/mailjetiframes_options');
        } else {
            $this->_forward('iframe');
        }
    }
    
    public function iframeAction()
    {
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
     * @return Mailjet_Iframes_Helper_IframesWrapper
     */
    protected function _getIframesWrapperHelper()
    {
        return new Mailjet_Iframes_Helper_IframesWrapper(
            $this->_apikey, $this->_secretKey
        );
    }
}