<?php

namespace SzamlaAgent\Ledger;

use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugtatétel főkönyvi adatok
 *
 * @package SzamlaAgent\Ledger
 */
class ReceiptItemLedger extends ItemLedger {

    /**
     * Tétel főkönyvi adatok létrehozása
     *
     * @param string  $revenueLedgerNumber   Árbevétel főkönyvi szám
     * @param string  $vatLedgerNumber       ÁFA főkönyvi szám
     */
    function __construct($revenueLedgerNumber = '', $vatLedgerNumber = '') {
        parent::__construct($revenueLedgerNumber, $vatLedgerNumber);
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
            switch ($field) {
                case 'revenueLedgerNumber':
                case 'vatLedgerNumber':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
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

        if (SzamlaAgentUtil::isNotBlank($this->getRevenueLedgerNumber())) $data['arbevetel'] = $this->getRevenueLedgerNumber();
        if (SzamlaAgentUtil::isNotBlank($this->getVatLedgerNumber()))     $data['afa'] = $this->getVatLedgerNumber();

        return $data;
    }
}