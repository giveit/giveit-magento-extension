<?php

/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Synocom BV (http://www.synocom.nl)
 * @author     Brennon Blokland <info@synocom.nl>
 */
require_once 'lib/giveit/sdk.php';

/**
 * Wrapper class for Give It SDK
 */
class Synocom_GiveIt_Model_GiveIt
    extends \GiveIt\SDK
{

    const XML_PATH_GIVEIT_SETTINGS = 'synocom_giveit/settings';

    protected $_settings = array();

    /**
     * Singleton construct for use
     *
     * @return \GiveIt\SDK
     */
    public function __construct($settings = array())
    {
        if (empty($settings)) {
            $settings = $this->_getSettings();
        }
        parent::__construct($settings);
    }

    /**
     * Get settings from admin configuration for passing trough SDK constructor
     *
     * @return array
     */
    protected function _getSettings()
    {
        $settings = array();
        $configSettings = Mage::getStoreConfig(self::XML_PATH_GIVEIT_SETTINGS);

        foreach ($configSettings as $underscoredSettingName => $value) {
            $camelizedSettingName            = lcfirst(uc_words($underscoredSettingName, ''));
            $settings[$camelizedSettingName] = $value;
        }

        return $settings;
    }

}
