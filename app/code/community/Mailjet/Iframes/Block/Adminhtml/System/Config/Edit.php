<?php


class Mailjet_Iframes_Block_Adminhtml_System_Config_Edit extends Mage_Adminhtml_Block_System_Config_Edit
{

    protected function _prepareLayout()
    {
        $sectionCode = $this->getRequest()->getParam('section');

        if ($sectionCode == 'mailjetiframes_options') {
            $this->setChild('save_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('adminhtml')->__('Save Config'),
                        'onclick' => 'alert(\'Be patient, please! This will configure your Mailjet API settings and all of your newsletter subscribed customers will be exported into your Mailjet acount. This may take few minutes.\');configForm.submit()',
                        'class' => 'save',
                    ))
            );
            return $this;
        } else {
            parent::_prepareLayout();
        }
    }
}
