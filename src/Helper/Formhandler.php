<?php

namespace Deepcommerce\Phpshop\Helper;

use \Deepcommerce\Phpshop\Core\Localize;

/**
 * Class FormHandler
 * @package deepcommerce\Helper
 */
class Formhandler {

    private $formElements = [];
    private $formValues = [];
    private $formErrors = [];
    private $formErrorMessages = [];

    /**
     * FormHandler constructor.
     * @param array $formElements
     */
    public function __construct($formElements) {
        $this->formElements = $formElements;
    }

    /**
     * Get Form Elements
     * @return array
     */
    public function getFormElements() {
        foreach ($this->formElements as &$element) {
            if ($element['type'] == 'nop') {
                continue;
            }
            $element['value'] = isset($this->formValues[$element['name']]) ? $this->formValues[$element['name']] : '';
            if (!empty($this->formErrors[$element['name']])) {
                $element['error'] = $this->formErrors[$element['name']];
            }
        }
        return $this->formElements;
    }

    /**
     * Validate Form Data
     * @param array $postData
     * @return array
     */
    public function validateFormData($postData) {
        $this->formValues = $postData;
        foreach ($this->formElements as $element) {
            if ($element['type'] == 'nop') {
                continue;
            }
            $name = $element['name'];
            $value = @$postData[$name];
            if ($element['mandatory'] && !strlen($value)) {
                $this->addError($name, Localize::translate('Mandatory'));
                $this->addErrorMessage(Localize::translate('Mandatory') . ': ' . Localize::translate($element['label']));
            }
            if (!empty($element['validation'])) {
                $validation = $element['validation'];
                if (!preg_match($validation, $value)) {
                    $this->addError($name, Localize::translate('Validation Error'));
                    $this->addErrorMessage(Localize::translate('Validation Error') . ': ' . Localize::translate($element['label']));
                }
            }
        }
        return $this->formErrorMessages;
    }

    /**
     * Get Form Values
     * @param array $postData
     * @return array
     */
    public function getFormValues($postData) {
        $values = [];
        foreach ($this->formElements as $element) {
            if ($element['type'] == 'nop' || !isset($postData[$element['name']])) {
                continue;
            }
            $values[$element['name']] = $postData[$element['name']];
        }
        return $values;
    }

    /**
     * Add Error
     * @param string $name
     * @param string $message
     */
    public function addError($name, $message) {
        $this->formErrors[$name] = $message;
    }

    /**
     * Add Error Message
     * @param string $message
     */
    public function addErrorMessage($message) {
        $this->formErrorMessages[] = $message;
    }

}
