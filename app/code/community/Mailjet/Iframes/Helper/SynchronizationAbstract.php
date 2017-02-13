<?php

abstract class Mailjet_Iframes_Helper_SynchronizationAbstract extends Mage_Core_Helper_Abstract 
{

	/**
	 *
	 * @var string
	 */
	const LIST_NAME = 'Customers_Master_List';

	/**
	 * 
	 * @var int
	 */
	protected $_masterListId;

	/**
	 * 
	 * @var ApiOverlay
	 */
	protected $_apiOverlay;

	/**
	 * 
	 * @param Mailjet_ApiOverlay $apiOverlay
	 */
	public function __construct(Mailjet_Iframes_Helper_ApiWrapper $apiOverlay)
	{
		$this->_apiOverlay = $apiOverlay;
	}

	/**
	 * 
	 * @return Mailjet_ApiOverlay
	 */
	protected function _getApiOverlay()
	{
		return $this->_apiOverlay;
	}
	/**
	 *
	 * @return number|boolean
	 */
	protected function _getAlreadyCteatedMasterListId()
	{
		if (!$this->_masterListId) {
            
            $params = array(
                'method'	 	=> 'GET',
                'limit'			=> 0
            );

            $this->_getApiOverlay()->resetRequest();    	
            $this->_getApiOverlay()->contactslist($params);
            $lists = $this->_getApiOverlay()->getResponse();    	
			
			if ($lists->Count > 0) {
				foreach ($lists->Data as $list) {
					if ($list->Name === self::LIST_NAME) {
                        $this->_masterListId = (int)$list->ID;
                    }
				}
			}
		}
		return $this->_masterListId;
	}

}

