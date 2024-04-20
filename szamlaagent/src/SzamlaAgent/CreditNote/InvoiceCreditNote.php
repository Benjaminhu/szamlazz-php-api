<?php

namespace SzamlaAgent\CreditNote;

use SzamlaAgent\Document\Document;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Számla jóváírás
 *
 * @package SzamlaAgent\CreditNote
 */
class InvoiceCreditNote extends CreditNote {

    /**
     * Jóváírás dátuma
     *
     * @var string
     */
    protected $date;

    /**
     * Kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = ['date', 'paymentMode', 'amount'];

    /**
     * Jóváírás létrehozása
     *
     * @param string $date        jóváírás dátuma
     * @param string $paymentMode jóváírás jogcíme (fizetési módja)
     * @param double $amount      jóváírás összege
     * @param string $description jóváírás leírása
     */
    function __construct($date, $amount, $paymentMode = Document::PAYMENT_METHOD_TRANSFER, $description = '') {
        parent::__construct($paymentMode, $amount, $description);
        $this->setDate($date);
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
                case 'date':
                    SzamlaAgentUtil::checkDateField($field, $value, $required, __CLASS__);
                    break;
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

        if (SzamlaAgentUtil::isNotBlank($this->getDate()))        $data['datum']  = $this->getDate();
        if (SzamlaAgentUtil::isNotBlank($this->getPaymentMode())) $data['jogcim'] = $this->getPaymentMode();
        if (SzamlaAgentUtil::isNotNull($this->getAmount()))       $data['osszeg'] = SzamlaAgentUtil::doubleFormat($this->getAmount());
        if (SzamlaAgentUtil::isNotBlank($this->getDescription())) $data['leiras'] = $this->getDescription();

        return $data;
    }

    /**
     * @return string
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date) {
        $this->date = $date;
    }
 }