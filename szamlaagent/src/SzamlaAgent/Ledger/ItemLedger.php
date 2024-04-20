<?php

namespace SzamlaAgent\Ledger;

/**
 * Tétel főkönyvi adatok
 *
 * @package SzamlaAgent\Ledger
 */
class ItemLedger {

    /**
     * Árbevétel főkönyvi szám
     *
     * @var string
     */
    protected $revenueLedgerNumber;

    /**
     * ÁFA főkönyvi szám
     *
     * @var string
     */
    protected $vatLedgerNumber;

    /**
     * Tétel főkönyvi adatok létrehozása
     *
     * @param string $revenueLedgerNumber Árbevétel főkönyvi szám
     * @param string $vatLedgerNumber     ÁFA főkönyvi szám
     */
    protected function __construct($revenueLedgerNumber = '', $vatLedgerNumber = '') {
        $this->setRevenueLedgerNumber($revenueLedgerNumber);
        $this->setVatLedgerNumber($vatLedgerNumber);
    }

    /**
     * @return string
     */
    public function getRevenueLedgerNumber() {
        return $this->revenueLedgerNumber;
    }

    /**
     * @param string $revenueLedgerNumber
     */
    public function setRevenueLedgerNumber($revenueLedgerNumber) {
        $this->revenueLedgerNumber = $revenueLedgerNumber;
    }

    /**
     * @return string
     */
    public function getVatLedgerNumber() {
        return $this->vatLedgerNumber;
    }

    /**
     * @param string $vatLedgerNumber
     */
    public function setVatLedgerNumber($vatLedgerNumber) {
        $this->vatLedgerNumber = $vatLedgerNumber;
    }
}