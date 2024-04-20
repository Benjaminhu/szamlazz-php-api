<?php

namespace SzamlaAgent\CreditNote;

use SzamlaAgent\Document\Document;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugta jóváírás
 *
 * @package SzamlaAgent\CreditNote
 */
class ReceiptCreditNote extends CreditNote {

    /**
     * Fizetőeszköz megnevezése
     *
     * @var string
     */
    protected $paymentMode;

    /**
     * A fizetőeszközzel kiegyenlített összeg
     *
     * @var double
     */
    protected $amount;

    /**
     * A fizetőeszköz egyedi leírása
     *
     * @var string
     */
    protected $description = '';

    /**
     * Nyugta kifizetés létrehozása
     *
     * @param string $paymentMode fizetőeszköz megnevezése
     * @param double $amount      fizetőeszköz összege
     * @param string $description fizetőeszköz egyedi leírása
     */
    function __construct($paymentMode = Document::PAYMENT_METHOD_CASH, $amount = 0.0, $description = '') {
        parent::__construct($paymentMode, $amount, $description);
    }

    /**
     * @return array
     */
    protected function getRequiredFields() {
        return $this->requiredFields;
    }

    /**
     * Ellenőrizzük a mező típusát
     *
     * @param $field
     * @param $value
     *
     * @return string
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value) {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'amount':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'paymentMode':
                case 'description':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }
        return $value;
    }

    /**
     * Ellenőrizzük a tulajdonságokat
     *
     * @throws SzamlaAgentException
     */
    protected function checkFields() {
        $fields = get_object_vars($this);
        foreach ($fields as $field => $value) {
            $this->checkField($field, $value);
        }
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData() {
        $data = [];
        $this->checkFields();

        if (SzamlaAgentUtil::isNotBlank($this->getPaymentMode())) $data['fizetoeszkoz'] = $this->getPaymentMode();
        if (SzamlaAgentUtil::isNotNull($this->getAmount()))       $data['osszeg'] = SzamlaAgentUtil::doubleFormat($this->getAmount());
        if (SzamlaAgentUtil::isNotBlank($this->getDescription())) $data['leiras'] = $this->getDescription();

        return $data;
    }
 }