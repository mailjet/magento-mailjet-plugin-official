<?php
/**
 * Mailjet
 */
class Mailjet_Iframes_Helper_IframesWrapper extends Mage_Core_Helper_Abstract
{

    const ON    = 'on';
    const OFF   = 'off';

    const PAGE_STATS        = 'stats';
    const PAGE_CAMPAIGNS    = 'campaigns';
    const PAGE_CONTACTS     = 'contacts';

    const SESSION_NAME      = 'IframesWrapperToken';
    const SESSION_SET       = 'IframesWrapperTokenSet';

    /**
     *
     * @var MailjetApi
     */
    private $mailjetApi;

    /**
     *
     * @var array
     */
    private $locales = array(
        'fr_FR', 'en_US', 'en_GB', 'en_EU', 'de_DE', 'es_ES'
    );

    /**
     *
     * @var array
     */
    private $allowedPages = array(
        self::PAGE_STATS,
        self::PAGE_CONTACTS,
        self::PAGE_CAMPAIGNS
    );

    /**
     *
     * @var string
     */
    private $url                = 'https://app.mailjet.com/';

    /**
     *
     * @var string
     */
    private $callback           = '';
    /**
     *
     * @var string
     */
    private $locale             = 'en_US';

    /**
     *
     * @var integer
     */
    private $sessionExpiration = 3600;

    /**
     *
     * @var array
     */
    private $tokenAccessAvailable = array(
        'campaigns',
        'contacts',
        'stats',
        'reports',
        'preferences',
        'property',
        'contact_filter'
    );

    /**
     *
     * @var string
     */
    private $tokenAccess        = '';
    /**
     *
     * @var string
     */
    private $segmentation       = self::ON;

    /**
     *
     * @var string
     */
    private $personalization    = self::ON;

    /**
     *
     * @var string
     */
    private $campaingComparison = self::ON;

    /**
     *
     * @var string
     */
    private $documentationProperties = self::ON;

    /**
     *
     * @var string
     */
    private $newContactListCreation  = self::ON;

    /**
     *
     * @var string
     */
    private $menu               = self::ON;

    /**
     * Flag to mark if to display the black campaign name title bar in the iframe
     *
     * @access  private
     * @var string 'on'/'off'
     */
    private $showBar = self::ON;

    /**
     *
     * @var string
     */
    private $logos              = self::ON;

    /**
     *
     * @var string
     */
    private $initialPage        = self::PAGE_STATS;
    /**
     *
     * @param string $apitKey
     * @param string $secretKey
     */
    public function __construct($apitKey, $secretKey)
    {
        $this->startSession();
        $this->mailjetApi = new Mailjet_Iframes_Helper_ApiWrapper($apitKey, $secretKey);
    }

    /**
     *
     * @param integer $seconds
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     */
    public function setTokenExpiration($seconds = 600)
    {
        if (!is_numeric($seconds)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Token expiration should be a valid number.")
            );
        }

        if ($seconds <= 0) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Token expiration should be greater than 0")
            );
        }

        $this->sessionExpiration = $seconds;
        return $this;
    }

    /**
     *
     * @param string $callback
     * @param boolean $isEncoded
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     */
    public function setCallback($callback = '', $isEncoded = false)
    {
        if ($isEncoded) {
            $this->callback = $callback;
        } else {
            $this->callback = urldecode($callback);
        }

        return $this;
    }

    /**
     *
     * @param string $locale
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function setLocale($locale = 'en_US')
    {
        if (!in_array($locale, $this->locales)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                "{$locale}" . Mage::helper('iframes')->__('is not supported.')
            );
        }

        $this->locale = $locale;
        return $this;
    }

    /**
     *
     * @param array $access
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     */
    public function setTokenAccess(array $access = array())
    {
        foreach ($access as $value) {
            if (!in_array($value, $this->tokenAccessAvailable)) {
                throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                    "{$value}" . Mage::helper('iframes')->__('is not a valid token access.')
                );
            }
        }

        $this->tokenAccess = implode(', ', $access);
        return $this;
    }
    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnSegmentation($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Segmentation requires a valid on/off parameter.")
            );
        }

        $this->segmentation = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnPersonalization($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Personalization requires a valid on/off parameter.")
            );
        }

        $this->personalization = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnCampaignComparison($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Campaign comparison requires a valid on/off parameter.")
            );
        }

        $this->campaingComparison = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnDocumentationProperties($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Documentation properties requires a valid on/off parameter.")
            );
        }

        $this->documentationProperties = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnNewContactListCreation($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("New contact list creation requires a valid on/off parameter.")
            );
        }

        $this->newContactListCreation = $flag;
        return $this;
    }
    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnMenu($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Menu requires a valid on/off parameter.")
            );
        }

        $this->menu = $flag;
        return $this;
    }

    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnBar($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Bar requires a valid on/off parameter.")
            );
        }

        $this->showBar = $flag;
        return $this;
    }


    /**
     *
     * @param string $flag
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function turnMailjetLogos($flag = self::ON)
    {
        if (!$this->isAllowedOnOffParameter($flag)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("Mailjet logos require a valid on/off parameter.")
            );
        }

        $this->logos = $flag;
        return $this;
    }

    /**
     *
     * @param string $page
     * @return \Mailjet_Iframes_Helper_IframesWrapper
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    public function setInitialPage($page = self::PAGE_STATS)
    {
        if (!in_array($page, $this->allowedPages)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                "{$page}" . Mage::helper('iframes')->__('is uknown.')
            );
        }

        $this->initialPage = $page;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getHtml()
    {
        $iframeUrl = $this->getIframeUrl();

        $html = <<<HTML

<iframe
  width="100%s"
  height="1500%s"
  frameborder="0" style="border:0"
  src="%s">
</iframe>

HTML;

        return sprintf($html, '%', 'px', $iframeUrl);
    }

    /**
     *
     * @param mixed $parameter
     * @return boolean
     */
    private function isAllowedOnOffParameter($parameter)
    {
        if ($parameter !== self::ON && $parameter !== self::OFF) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    private function getToken()
    {
        if (!isset($_SESSION[self::SESSION_NAME])) {

            $_SESSION[self::SESSION_NAME]       = $this->generateToken();
            $_SESSION[self::SESSION_SET]        = time();

        } else {

            if (time() - $_SESSION[self::SESSION_SET] >= $this->sessionExpiration) {
                $_SESSION[self::SESSION_NAME]       = $this->generateToken();
                $_SESSION[self::SESSION_SET]        = time();
            }

        }

        return $_SESSION[self::SESSION_NAME];
    }

    /**
     *
     * @return string
     * @throws Mailjet_Iframes_Helper_IframesWrapper_Exception
     */
    private function generateToken()
    {
        $params = array(
            'method'        => 'JSON',
            'AllowedAccess' => $this->tokenAccess,
            'IsActive'      => 'true',
            'TokenType'     => 'iframe',
            'APIKeyALT'     => $this->mailjetApi->getAPIKey(),
            'ValidFor'      => $this->sessionExpiration,
        );
        // get the response
        $response = $this->mailjetApi->apitoken($params)->getResponse();

        if (!$response) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("The Mailjet API does not respond.")
            );
        }

        if ($response->Count <= 0) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("The Mailjet API object not found.")
            );
        }

        if (!isset($response->Data[0]->Token)) {
            throw new Mailjet_Iframes_Helper_IframesWrapper_Exception(
                Mage::helper('iframes')->__("The Mailjet API returned invalid response.")
            );
        }

        return $response->Data[0]->Token;
    }
    /**
     *
     * @return string
     */
    private function getIframeUrl()
    {
        $url = $this->url . $this->initialPage . '?t=' . $this->getToken();

        $url .= '&locale=' . $this->locale;

        if ($this->callback !== '') {
            $url .= '&cb=' . $this->callback;
        }

        $featuresDisabled = array();

        if ($this->segmentation === self::OFF) {
            $featuresDisabled[] = 's';
        }

        if ($this->personalization === self::OFF) {
            $featuresDisabled[] = 'p';
        }
        if ($this->campaingComparison === self::OFF) {
            $featuresDisabled[] = 'c';
        }

        if ($this->newContactListCreation === self::OFF) {
            $featuresDisabled[] = 'l';
        }

        if (!empty($featuresDisabled)) {
            $url .= '&f=' . implode('', $featuresDisabled);
        }

        if ($this->menu === self::OFF) {
            $url .= '&show_menu=none';
        }

        if ($this->showBar === self::ON) {
            $url .= '&show_bar=yes';
        }

        if ($this->logos === self::OFF) {
            $url .= '&mj=hidden';
        }

        return $url;
    }

    /**
     *
     * @return null
     */
    private function startSession()
    {
        if(session_id() == '') {
            session_start();
        }
    }
}