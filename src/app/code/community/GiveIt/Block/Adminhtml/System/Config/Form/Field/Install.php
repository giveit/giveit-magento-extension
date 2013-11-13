<?php
/**
 * GiveIt extension
 *

 * @package    GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class GiveIt_Block_Adminhtml_System_Config_Form_Field_Install
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string|GiveIt_Helper_Data
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $key  =  Mage::getStoreConfig('giveit/settings/data_key');

        if ($key != '') {
            return "You have already performed an installation with the Give.it shops website";
        }

        $selectedStoreId    = Mage::app()->getRequest()->getParam('store');
        $user               = Mage::getSingleton('admin/session')->getUser();
        $append             = http_build_query(array(
                                    'website'           => urlencode(Mage::getStoreConfig('web/unsecure/base_url', $selectedStoreId)),
                                    'name'              => urlencode(Mage::getStoreConfig('general/store_information/name', $selectedStoreId)),
                                    'currency'          => Mage::app()->getStore($selectedStoreID)->getCurrentCurrencyCode(),
                                    'user_first_name'   => $user->getFirstname(),
                                    'user_last_name'    => $user->getLastname(),
                                    'user_email'        => $user->getEmail(),
                              ));

        return "\n<iframe src='https://shops.give.it/magento/install?$append' width='100%' height='600' frameborder='0' scrolling='auto' marginwidth='5' marginheight='5'></iframe>\n";
    }

}
