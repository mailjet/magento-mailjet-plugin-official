![alt text](https://www.mailjet.com/images/email/transac/logo_header.png "Mailjet")

# [API v3] Mailjet for Magento

## Description 

Increase your merchant revenue with the Mailjet all-in-one Magento email plugin! Create, send and analyze your transactional and email marketing campaigns straight from within your Magento admin and boost your bottom line. Mailjet is a powerful all-in-one email service provider used to get maximum insight and deliverability results from both marketing and transactional emails. Our analytics tools and intelligent APIs give senders the best understanding of how to maximize benefits for each individual contact and campaign email after email.

## Plug-in Key Info

* Plug-in languages: EN
* PHP Compatibility: PHP 5.2.0 - 7.2.0
* Magento Compatibility: Magento v1.5.0.0 - v1.9.x. Magento 2.0 is not supported by this plugin!
* Support: https://app.mailjet.com/support/ticket
* Requires Mailjet account

## Merchant Benefits

With Mailjet, optimise your deliverability, get your emails delivered to the inbox and avoid the spam folder. Install the official Mailjet Magento extension and get access to:
 
* Use Mailjet's SMTP relay with enhanced deliverability and tracking. 

* The extension also provides the ability to initially synchronise all of your Magento newsletter subscribed customers to your Mailjet account and send bulk and targeted emails to them with real time statistics including opens, clicks, geography, average time to click, unsubs, etc.

* Trigger customer synchronization based on certain events occurred in Magento like new customer registration, customer profile changes, newsletter subscription/unsubscription, all customer`s profile changes by the site Admin, etc..
  
* Automatically remove unsubscribers from your contact lists and Newsletters to keep your deliverability reputation intact
 
* Personalize your emailings with contact list properties
 
* Create & manage all Mailjet campaigns and contacts directly within Magento

## Features

* Create personalized messages for your client base using our segmentation feature
 
* Compare the sending rates of multiple campaigns to target the best performing newsletters with Mailjet’s campaign comparison tool
 
* Use our drag-and-drop (WYSIWYG) template builder to create beautiful newsletters -- no coding necessary
 
* 24/7 customer support is available in English, French, German and Spanish


## Customer Benefits

Your customers will benefit by receiving personalized and pertinent emails delivered straight into their inbox increasing engagement and repeat buying. 

## Installation:

1. Download the plugin archive Mailjet-2.x.x.tgz
2. Log in as administrator in Magento
3. Go to System > Magento connect manager and upload the archive file using the section "Direct package file upload". (Be sure there is no any previous version of the plugin already installed - GO to section "Manage Existing Extensions" and check the list for mailjet plugin and if exists uninstall it.)
4. Connect your Mailjet Account (Go to Magento Admin > System > Configuration > Mailjet settings).
5. Once you have a valid Mailjet account and entered valid API credentials click on "Save Config" button and all of your Magento newsletter subscribed customers will be synchronized with Mailjet and a new contact list with all of your synchronized Magento contacts will be added to your account.

**Important!** 
* Make sure to apply the settings for each "Configuration scope"!
* The email address used by the Mailjet Plugin for sending emails is the "Magento General Contact" (Can be found in Magento Admin > General > Store Email Addresses > General contact). This address must also be validated in your [Mailjet account](https://app.mailjet.com/account/sender).
* If you are not yet a Mailjet user, please click [Register](https://app.mailjet.com/signup?aff=magento-3.0) to create a new account.

## Screenshots 
* Plugin archive upload -  https://github.com/mailjet/magento-mailjet-plugin-official/blob/development/Magento_Downloader.png
* Plugin uninstallation - https://github.com/mailjet/magento-mailjet-plugin-official/blob/development/Magento_uninstall.png
* Mailjet API authentication - https://github.com/mailjet/magento-mailjet-plugin-official/blob/development/Configuration_System_Magento_Admin.png
* Mailjet account Dashboard -  https://github.com/mailjet/magento-mailjet-plugin-official/blob/development/Magento_Iframe.png


## Changelog

= 2.0.6 =
* Fixed issue with Mailjet tab showing a blank page

= 2.0.5 =
* Improve email events processing - to handle both type 1 and type 2 of event API messages
* Fix issue with clearing API credentials on save of forms in other pages

= 2.0.3 =
* Fix encoding and removed redundant method usage.

= 2.0.1 =
* Fix initial sync.

= 2.0.0 =
* Configure plugin for releas on Magento Connect portal.

= 1.0.0 =
* Initial upload.


## Support
https://www.mailjet.com/support/ticket
