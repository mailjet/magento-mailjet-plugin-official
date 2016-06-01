<?php

class Mailjet_Iframes_Helper_SyncManager extends Mage_Core_Helper_Abstract
{
    
    public static function getApiInstance()
    {
        return new Mailjet_Iframes_Helper_ApiWrapper(
            Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_LOGIN),
            Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PASSWORD)
        );
    }

    
    /**
     * 
     * @param type $filterEmails
     * @return boolean
     */
    public function synchronize($filterEmails = array())
	{
        $apiOverlay = self::getApiInstance();
        $sync = new Mailjet_Iframes_Helper_Synchronization($apiOverlay);
                    
        // updates only given array of customers contact - don`t touch all magento customers
        $updateOnlyGiven = !empty($filterEmails) ? true : false;
        
        if (is_array($filterEmails) && !empty($filterEmails)) { 
            $pageSize = count($filterEmails);
        } else {
            $pageSize = Mailjet_Iframes_Helper_Synchronization::$_limitPerRequest;
        }

        $customerCollectionCount = $this->_getAllCustomersCount($filterEmails);
        $pages = ceil($customerCollectionCount/$pageSize);    
        
        Mage::register('startSynchronization', true);
/*
        $existingMailjetList = $sync->getExistingMailjetListId();
        if (intval($existingMailjetList) > 0) {
            $sync->deleteList($existingMailjetList);
        }
*/
        for ($i = 0; $i <= $pages; $i++) {
             
            $subscribersData = array();
             
            $customerCollection = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('id')
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->setPageSize($pageSize)
                ->setCurPage($i);
        
            if (is_array($filterEmails) && !empty($filterEmails)) { 
                $customerCollection->addAttributeToFilter('email',  array('in' => $filterEmails));
            } 
//                
            foreach($customerCollection as $customer) {
                // process only active customers
                if ($customer->getData('is_active') != 1) { 
                    continue;                
                }

                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getData('email'));  
    //            $orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $customer->getId());                        
    //            $orderCnt = $orders->count(); //orders count

                if($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                   //$subscribers[] = array_merge($customer->getData(), array('orderCnt' => $orderCnt));
                   $subscribersData[] = $customer->getData();
                }
            }   
            
            if (count($subscribersData) === 0) {
                //throw new Exception('You don\'t have any users in the database.');
            } else {
                $sync->synchronize($subscribersData, $updateOnlyGiven);
            }
        }
		
        Mage::unregister('startSynchronization');
        
		return true;
	}
    
    
    
    
    
    public function removeContactsFromMailjetList($contacts = array(), $listId = null)
	{
        $apiOverlay = self::getApiInstance();
        $sync = new Mailjet_Iframes_Helper_Synchronization($apiOverlay);
        $existingListId = $sync->getExistingMailjetListId();
        if(is_array($contacts) && !empty($contacts) && (int) $existingListId > 0) {
            $success = $sync->removeContactsFromMailjetList($existingListId, $contacts);
            if ($success !== true) {
                throw new Exception(Mage::helper('iframes')->__('Remove contacts from Mailjet list failed'));
            }
        }   
         
    }
    
       
    
    
    public function usubscribeByEmail($email)
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
        if($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED) {
            $subscriber->unsubscribe($email);
            return $subscriber;
        }
        
        return false;
    }
    
    
    
    
    private function _getAllCustomersCount($filterEmails = array())
	{
        /** @var $coreResource Mage_Core_Model_Resource */
        $coreResource = Mage::getSingleton('core/resource');

        /** @var $conn Varien_Db_Adapter_Pdo_Mysql */
        $conn = $coreResource->getConnection('core_read');

        /** @var $select Varien_Db_Select */
        $select = $conn->select()
            ->from(array(Mage::getSingleton('core/resource')->getTableName('customer_entity')), 
                new Zend_Db_Expr('COUNT(*)'));
            

        if (is_array($filterEmails) && !empty($filterEmails)) { 
            $select->where('email IN (?)', implode("','", $filterEmails));
        } 
        
        $customerCollectionCount = $conn->fetchOne($select);
        
        return (int) $customerCollectionCount;
         
    }
    
    public function getEventsTrackingUrl()
    {
        $currentUrl = Mage::getBaseUrl();

        $currentUrlArr = parse_url($currentUrl);
        $pluginUser = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_LOGIN);
        $pluginPass = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PASSWORD);
        
        $url = $currentUrlArr['scheme'].'://' . $pluginUser. ':' . $pluginPass . '@' . $currentUrlArr['host'] . $currentUrlArr['path'];
       
        return $url . 'iframes/index/events/';
        
    }
    
}
