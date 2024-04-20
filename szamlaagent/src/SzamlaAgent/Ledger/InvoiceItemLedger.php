<?php

namespace SzamlaAgent\Ledger;

use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Számlatétel főkönyvi adatok
 *
 * @package SzamlaAgent\Ledger
 */
class InvoiceItemLedger extends ItemLedger {

    /**
     * Gazdasági esemény típus
     *
     * @var string
     */
    protected $economicEventType;

    /**
     * ÁFA gazdasági esemény típus
     *
     * @var string
     */
    protected $vatEconomicEventType;

    /**
     * Elszámolási időszak kezdete
     *
     * @var string
     */
    protected $settlementPeriodStart;

    /**
     * Elszámolási időszak vége
     *
     * @var string
     */
    protected $settlementPeriodEnd;

    /**
     * Tétel főkönyvi adatok létrehozása
     *
     * @param string  $economicEventType     Gazdasági esemény típus
     * @param string  $vatEconomicEventType  ÁFA gazdasági esemény típus
     * @param string  $revenueLedgerNumber   Árbevétel főkönyvi szám
     * @param string  $vatLedgerNumber       ÁFA főkönyvi szám
     */
    function __construct($economicEventType = '', $vatEconomicEventType = '', $revenueLedgerNumber = '', $vatLedgerNumber = '') {
        parent::__construct((string)$revenueLedgerNumber, (string)$vatLedgerNumber);
        $this->setEconomicEventType($economicEventType);
        $this->setVatEconomicEventType($vatEconomicEventType);
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
                case 'settlementPeriodStart':
                case 'settlementPeriodEnd':
                    SzamlaAgentUtil::checkDateField($field, $value, false, __CLASS__);
                    break;
                case 'economicEventType':
                case 'vatEconomicEventType':
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

        if (SzamlaAgentUtil::isNotBlank($this->getEconomicEventType()))     $data['gazdasagiEsem'] = $this->getEconomicEventType();
        if (SzamlaAgentUtil::isNotBlank($this->getVatEconomicEventType()))  $data['gazdasagiEsemAfa'] = $this->getVatEconomicEventType();
        if (SzamlaAgentUtil::isNotBlank($this->getRevenueLedgerNumber()))   $data['arbevetelFokonyviSzam'] = $this->getRevenueLedgerNumber();
        if (SzamlaAgentUtil::isNotBlank($this->getVatLedgerNumber()))       $data['afaFokonyviSzam'] = $this->getVatLedgerNumber();
        if (SzamlaAgentUtil::isNotBlank($this->getSettlementPeriodStart())) $data['elszDatumTol'] = $this->getSettlementPeriodStart();
        if (SzamlaAgentUtil::isNotBlank($this->getSettlementPeriodEnd()))   $data['elszDatumIg'] = $this->getSettlementPeriodEnd();

        return $data;
    }

    /**
     * @return string
     */
    public function getEconomicEventType() {
        return $this->economicEventType;
    }

    /**
     * @param string $economicEventType
     */
    public function setEconomicEventType($economicEventType) {
        $this->economicEventType = $economicEventType;
    }

    /**
     * @return string
     */
    public function getVatEconomicEventType() {
        return $this->vatEconomicEventType;
    }

    /**
     * @param string $vatEconomicEventType
     */
    public function setVatEconomicEventType($vatEconomicEventType) {
        $this->vatEconomicEventType = $vatEconomicEventType;
    }

    /**
     * @return string
     */
    public function getSettlementPeriodStart() {
        return $this->settlementPeriodStart;
    }

    /**
     * @param string $settlementPeriodStart
     */
    public function setSettlementPeriodStart($settlementPeriodStart) {
        $this->settlementPeriodStart = $settlementPeriodStart;
    }

    /**
     * @return string
     */
    public function getSettlementPeriodEnd() {
        return $this->settlementPeriodEnd;
    }

    /**
     * @param string $settlementPeriodEnd
     */
    public function setSettlementPeriodEnd($settlementPeriodEnd) {
        $this->settlementPeriodEnd = $settlementPeriodEnd;
    }
}