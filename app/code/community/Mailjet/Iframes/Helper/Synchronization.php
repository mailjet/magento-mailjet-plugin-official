<?php
class Mailjet_Iframes_Helper_Synchronization extends Mailjet_Iframes_Helper_SynchronizationAbstract
{
	/**
	 * 
	 * @var array
	 */
	private $_mailjetContacts = array();
    
    public static $_limitPerRequest = 10000;
    
    const FILTER_ID = '1';

    private $_allowedProperties = array("email", "firstname", "lastname");
    private $_allowedPropertiesNames = array("email", "firstname", "lastname");
    
    public static $_existingListId = null;
    
    /**
	 * 
	 * @param array $contacts
	 * @param string $filterId
	 * @param string $fiterName
	 */
	public function synchronize(
        $contacts, 
        $updateOnlyGiven = false, 
        $filterId = self::FILTER_ID, 
        $fiterName = Mailjet_Iframes_Helper_SynchronizationAbstract::LIST_NAME)
	{
		
        /*
         * checks first if exists already created mailjet contact list - if not then checjs if exists currently created but not processed by mailjet contacts list
         * if none of them exists then create new contact list
         */
        if (intval(self::$_existingListId) <= 0) {
            self::$_existingListId = $this->getExistingMailjetListId($filterId);
        }
        
		if (intval(self::$_existingListId) > 0) { 
            return $this->_update($contacts, $updateOnlyGiven, self::$_existingListId);
        }

		return $this->_create($contacts, $filterId, $fiterName);
	}
    
    

	/**
	 * 
	 * @param int $filterId
	 * @param string $newName	 
	 * @return bool
	 */
	public function updateName($mailjetListId, $magentoFilterId, $newName)
	{
		if ($mailjetListId) {

			$params = array(
				'ID'		=> $mailjetListId,
				'method' 	=> 'JSON',
				'Name' 		=> $magentoFilterId.'mag'.preg_replace('`[^a-zA-Z0-9]`iUs', '', strtolower($newName))
			);

			/* # Api call */
            $this->_getApiOverlay()->resetRequest();    	
            $this->_getApiOverlay()->contactslist($params);
            $oldList = $this->_getApiOverlay()->getResponse();    	
			
			if ($oldList) {
				/* $listId = $oldList->Data->ID; */
				return true;
			}
		}

		return false;
	}

    
    
	/**
	 * 
	 * @param int $mailjetListId
	 */
	public function deleteList($mailjetListId)
	{
		if ($mailjetListId) {
            
			$params = array(
				'ID'		=> $mailjetListId,
				'method' 	=> 'DELETE'
			);

			/* # Api call */
			/* $oldList = */
            $this->_getApiOverlay()->resetRequest();    	
            $this->_getApiOverlay()->contactslist($params);
            $res = $this->_getApiOverlay()->getResponse();    	
			
			return true;
		}

		return false;
	}

    
    
	/**
	 * 
	 * @param array $contacts
	 * @param string $filterId
	 * @param string $fiterName
	 * @return mixed
	 */
	private function _create($contacts, $filterId, $fiterName)
	{        
		self::$_existingListId = $this->_createNewMailjetList($filterId, $fiterName);
        
		if (!isset($contacts) || !is_array($contacts) || !self::$_existingListId) {
            return false;
        }

        $contactsToAdd = array();
        foreach ($contacts as $contact) {
            if (!empty($contact['email'])) {
                $propertiesToProcess = array();
                foreach ($this->_allowedProperties as $allowedProperty) {
                    if (isset($contact[$allowedProperty])) {
                        $propertiesToProcess[] = $contact[$allowedProperty];
                    }                    
                }
                if (!empty($propertiesToProcess)) {
                    $contactsToAdd[$contact['email']] = $propertiesToProcess;
                }
            }
        }

        # Call
        try {

            if (!empty($contactsToAdd)) {
                $success = $this->addContactsToMailjetList(self::$_existingListId, $contactsToAdd);
                if ($success !== true) {
                    throw new Exception(Mage::helper('iframes')->__('Add contacts to Mailjet list failed'));
                }
            }

            $response = Mage::helper('iframes')->__('OK');
        } catch (Exception $e) {
            $response = Mage::helper('iframes')->__('Try again later');
        }

		return $response;
	}

    
    
    
	/**
	 * 
	 * @param array $contacts
	 * @param int $existingListId
	 * @return string
	 */
	private function _update($contacts, $updateOnlyGiven = false, $existingListId)
	{
        
        if (!isset($contacts) || !is_array($contacts) || !$existingListId) {
            return false;
        }
        
        /*
         * Gather existing Contacts in a given Mailjet list
         * We pass this step if we already have any $_mailjetContacts array - empty or not
         */
        if (!isset($this->_mailjetContacts) || !is_array($this->_mailjetContacts)) {
            $this->_mailjetContacts = array();            
            $this->_gatherCurrentContacts($existingListId);
        }
        
		$magentoContacts = array();
        $contactsToCsv = array();
		foreach ($contacts as $contact) {
            $magentoContacts[] = $contact['email'];
            if (!empty($contact['email'])) {
                
                $propertiesToProcess = array();
                foreach ($this->_allowedProperties as $allowedProperty) {
                    if (isset($contact[$allowedProperty])) {
                        $propertiesToProcess[] = $contact[$allowedProperty];
                    }                    
                }
                if (!empty($propertiesToProcess)) {
                    $contactsToCsv[$contact['email']] = $propertiesToProcess;
                }
            }
        }
		$contactsToAdd = array();
		$contactsToRemove = array();

        foreach ($magentoContacts as $email) {
			if (!in_array($email, $this->_mailjetContacts)) {
                $contactsToAdd[] = $contactsToCsv[$email];
            }
		}
		
        /*
         * When proccessing one contacts (for example on 'edit' event)
         * we skip removing any other contacts from mailjets list
         */
        if (!$updateOnlyGiven) {
            foreach ($this->_mailjetContacts as $email) { 
                if (!in_array($email, $magentoContacts)) {
                    $contactsToRemove[] = $email;
                }
            }
        }
        
		  
		try {
            
			if (!empty($contactsToAdd)) {
                $success = $this->addContactsToMailjetList($existingListId, $contactsToAdd, true);
				if ($success !== true) {
                    throw new Exception(Mage::helper('iframes')->__('Add contacts to Mailjet list failed'));
                }
			}

			if (!empty($contactsToRemove)) {
                $success = $this->removeContactsFromMailjetList($existingListId, $contactsToRemove);
				if ($success !== true) {
                    throw new Exception(Mage::helper('iframes')->__('Remove contacts from Mailjet list failed'));
                }
			}

			$response = 'OK';
		} catch (Exception $e) {
			$response = $e;
		}

		return $response;
	}
    
    
    public function addContactsToMailjetList($listId = null, $contactsToAdd = array(), $updateExistingContacts = false)
    {
       
        if (isset($listId) && !empty($contactsToAdd)) {

            if(!isset($contactEmails) || !is_array($contactEmails)) {
                $contactEmails = array();
            }
            
            //$contactsToAdd = array_unique($contactsToAdd);
		
            $contactsFileFolder = Mage::getBaseDir('log');

            if (!is_writable($contactsFileFolder)) {
                chmod($contactsFileFolder, 0777);
            } 
            
            $file = fopen($contactsFileFolder . '/contacts.csv', 'w');
            $headers = $this->_allowedPropertiesNames;
            fputcsv($file, $headers);
            foreach ($contactsToAdd as $contact) {
                if (!in_array($contact[0], $contactEmails)) {
                    fputcsv($file, $contact);
                    $contactEmails[] = $contact[0];
                }
            }
            fclose($file);
         
            $contactsStringCsv = file_get_contents($contactsFileFolder . '/contacts.csv');
            @unlink($contactsFileFolder . '/contacts.csv');
            
            $this->_getApiOverlay()->resetRequest();    	
            $this->_getApiOverlay()->data('contactslist', $listId, 'CSVData', 'text/plain', $contactsStringCsv, 'POST', null);
            $res = json_decode($this->_getApiOverlay()->getResponse());    	

            if (!isset($res->ID)) {
                throw new Exception(Mage::helper('iframes')->__('Create contacts problem'));
            }
          
            if ($updateExistingContacts) {
                $batchJobResponse = $this->_getApiOverlay()->batchJobContacts($listId, $res->ID, 'addforce');
            } else {
                $batchJobResponse = $this->_getApiOverlay()->batchJobContacts($listId, $res->ID);
            }

            if ($batchJobResponse == false) {
                throw new Exception(Mage::helper('iframes')->__('Batchjob problem'));
            }
        }
		
        return true;
    }
    
    
    public function removeContactsFromMailjetList($listId = null, $contactsToRemove = array())
    {
        if (isset($listId) && !empty($contactsToRemove)) {
                
            $contactsToRemoveCsv = implode(' ', $contactsToRemove);

            $this->_getApiOverlay()->resetRequest();    	
            $this->_getApiOverlay()->data('contactslist', $listId, 'CSVData', 'text/plain', $contactsToRemoveCsv, 'POST', null);
            $res = json_decode($this->_getApiOverlay()->getResponse());    	

            if (!isset($res->ID)) {
                throw new Exception(Mage::helper('iframes')->__('Create contacts problem'));
            }

            $batchJobResponse = $this->_getApiOverlay()->batchJobContacts($listId, $res->ID, 'remove');

            if ($batchJobResponse == false) {
                throw new Exception(Mage::helper('iframes')->__('Batchjob problem'));
            }
        }
        
        return true;
    }
    

	/**
	 * 
	 * @param string $filterId
	 * @param string $fiterName
	 * @return int
	 */
	public function getExistingMailjetListId($filterId = self::FILTER_ID)
	{
        $params = array(
    		'method'	 	=> 'GET',
    		'limit'			=> 0
    	);
        
        $this->_getApiOverlay()->resetRequest();    	
		$this->_getApiOverlay()->contactslist($params);
        $lists = $this->_getApiOverlay()->getResponse();    	
		
		$listId = 0;
			
		if ($lists->Count > 0) {
            $listsInfo = $lists->Data;
			foreach($listsInfo as $l) {
				$n = explode('_magento_', $l->Name);
				if ((string) $n[0] == (string) $filterId) {
					$listId = (int) $l->ID;
					break;
				}
			}
		}

		return $listId;
	}
    
    

	/**
	 * 
	 * @param string $filterId
	 * @param string $fiterName
	 * @return number
	 */
	private function _createNewMailjetList($filterId, $fiterName)
	{
		$listId = 0;

		$params = array(
			'method' 	=> 'JSON',
			'Name' 		=> $filterId.'_magento_'.preg_replace('`[^a-zA-Z0-9_]`iUs', '', strtolower($fiterName))
		);

		/* # Api call */
        $this->_getApiOverlay()->resetRequest();    	
		$this->_getApiOverlay()->contactslist($params);
        $newList = $this->_getApiOverlay()->getResponse();    	
		
        if ($newList) {
            $listId = $newList->Data[0]->ID;
        }

		return $listId;
	}


    private function _gatherCurrentContacts($mailjetListId, $offset = 0)
	{
        if (!isset($this->_mailjetContacts) || !is_array($this->_mailjetContacts)) {
            $this->_mailjetContacts = array();            
        }
        
		$params = array(
			'method'			=> 'GET',
			'ContactsList'		=> $mailjetListId,
			'style'				=> 'full',
			'CountRecords'		=> 1,
			'offset'			=> $offset,
			'limit'				=> 100000,
		);

		$this->_getApiOverlay()->resetRequest();
        $this->_getApiOverlay()->listrecipient($params);
        $response = $this->_getApiOverlay()->getResponse();    	

		$totalCount = isset($response->Total) ? $response->Total : 0;
		$current 	= isset($response->Count) ? $response->Count : 0;

        if (isset($response->Data) && is_array($response->Data)) {
            foreach ($response->Data as $contact) {
                $this->_mailjetContacts[] = $contact->Contact->Email->Email;
            }
        }


		if ($offset + $current < $totalCount) {
            $offset = ($offset + $current);
            $this->_gatherCurrentContacts($mailjetListId, $offset + self::$_limitPerRequest);
        }
	}
    
    
}
