<?php
/**
 * GiveIt extension
 *
 * @category   Synocom
 * @package    Synocom_GiveIt
 * @copyright  Copyright (c) 2013 Light4website (http://www.light4website.com)
 * @author     Szymon Niedziela <office@light4website.com>
 *
 */
class Synocom_GiveIt_Model_Giveit_Sale_Address extends Mage_Core_Model_Abstract {

    protected $_firstName;
    protected $_lastName;

    public function setData($key, $value = null) {
        parent::setData($key, $value);
        $this->_initData();

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet() {
        $street = array(
            $this->getData('line_1'),
            $this->getData('line_2')
        );

        return join(', ', $street);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName() {
        return $this->_firstName;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName() {
        return $this->_lastName;
    }

    /**
     * Init data
     */
    protected function _initData() {
        $firstLastName = explode(' ', $this->getName(), 1);

        $this->_firstName = array_pop($firstLastName);
        $this->_lastName = array_pop($firstLastName);
    }
}