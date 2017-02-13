<?php

/**
 * Mailjet
 */
class Mailjet_Iframes_Model_Email_Queue extends Mage_Core_Model_Email_Queue
{
    public function send()
    {
        if (!Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_ENABLED)) {
            return parent::send();
        }

        /** @var $collection Mage_Core_Model_Resource_Email_Queue_Collection */
        $collection = Mage::getModel('core/email_queue')->getCollection()
            ->addOnlyForSendingFilter()
            ->setPageSize(self::MESSAGES_LIMIT_PER_CRON_RUN)
            ->setCurPage(1)
            ->load();

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $config = array(
            'username' => Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_LOGIN),
            'password' => Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PASSWORD)
        );

        $config['smtp'] = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_HOST);
        $config['port'] = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_PORT);
        $config['auth'] = 'login';

        $ssl = Mage::getStoreConfig(Mailjet_Iframes_Helper_Config::XML_PATH_SMTP_SSL);

        if (!empty($ssl)) {
            $config['ssl'] = $ssl;
        }

        $transport = new Zend_Mail_Transport_Smtp($config['smtp'], $config);

        /** @var $message Mage_Core_Model_Email_Queue */
        foreach ($collection as $message) {
            if ($message->getId()) {
                $parameters = new Varien_Object($message->getMessageParameters());
                if ($parameters->getReturnPathEmail() !== null) {
                    $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $parameters->getReturnPathEmail());
                    Zend_Mail::setDefaultTransport($mailTransport);
                }

                $mailer = new Zend_Mail('utf-8');
                foreach ($message->getRecipients() as $recipient) {
                    list($email, $name, $type) = $recipient;
                    switch ($type) {
                        case self::EMAIL_TYPE_BCC:
                            $mailer->addBcc($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                        case self::EMAIL_TYPE_TO:
                        case self::EMAIL_TYPE_CC:
                        default:
                            $mailer->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                    }
                }

                $templateId = 'no-template-found';
                if ($parameters->getTemplateId() !== null) {
                    $templateId = $parameters->getTemplateId();
                }

                $mailer->addHeader('X-Mailer', 'Mailjet-for-Magento/3.0', TRUE);
                $mailer->addHeader('X-Mailjet-Campaign', $templateId . '_' . date("Y") . '_' . date("W"), TRUE);

                if ($parameters->getIsPlain()) {
                    $mailer->setBodyText($message->getMessageBody());
                } else {
                    $mailer->setBodyHTML($message->getMessageBody());
                }

                $mailer->setSubject('=?utf-8?B?' . base64_encode($parameters->getSubject()) . '?=');
                $mailer->setFrom($parameters->getFromEmail(), $parameters->getFromName());
                if ($parameters->getReplyTo() !== null) {
                    $mailer->setReplyTo($parameters->getReplyTo());
                }
                if ($parameters->getReturnTo() !== null) {
                    $mailer->setReturnPath($parameters->getReturnTo());
                }

                try {
                    $mailer->send($transport);
                    unset($mailer);
                    $message->setProcessedAt(Varien_Date::formatDate(true));
                    $message->save();
                }
                catch (Exception $e) {
                    unset($mailer);
                    $oldDevMode = Mage::getIsDeveloperMode();
                    Mage::setIsDeveloperMode(true);
                    Mage::logException($e);
                    Mage::setIsDeveloperMode($oldDevMode);

                    return false;
                }
            }
        }

        return $this;
    }


}
