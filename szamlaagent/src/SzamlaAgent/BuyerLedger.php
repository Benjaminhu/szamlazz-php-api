<?php

namespace SzamlaAgent;

/**
 * A vevő főkönyvi adatai
 *
 * @package SzamlaAgent
 */
class BuyerLedger {

    /**
     * vevő gazdasági esemény azonosító
     *
     * @var string
     */
    protected $buyerId;

    /**
     * Könyvelés dátum
     *
     * @var string
     */
    protected $bookingDate;

    /**
     * Vevő főkönyvi szám
     *
     * @var string
     */
    protected $buyerLedgerNumber;

    /**
     * Folyamatos teljesítés
     *
     * @var boolean
     */
    protected $continuedFulfillment;

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
     * Vevő főkönyvi adatok példányosítása
     *
     * @param string    $buyerId              vevő gazdasági esemény azonosító
     * @param string    $bookingDate          könyvelés dátum
     * @param string    $buyerLedgerNumber    vevő főkönyvi szám
     * @param boolean   $continuedFulfillment folyamatos teljesítés
     */
    public function __construct($buyerId = '', $bookingDate = '', $buyerLedgerNumber = '', $continuedFulfillment = false) {
        $this->setBuyerId($buyerId);
        $this->setBookingDate($bookingDate);
        $this->setBuyerLedgerNumber($buyerLedgerNumber);
        $this->setContinuedFulfillment($continuedFulfillment);
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
                case 'bookingDate':
                case 'settlementPeriodStart':
                case 'settlementPeriodEnd':
                    SzamlaAgentUtil::checkDateField($field, $value, false, __CLASS__);
                    break;
                case 'continuedFulfillment':
                    SzamlaAgentUtil::checkBoolField($field, $value, false, __CLASS__);
                    break;
                case 'buyerId':
                case 'buyerLedgerNumber':
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
    public function getXmlData() {
        $data = [];
        $this->checkFields();

        if (SzamlaAgentUtil::isNotBlank($this->getBookingDate()))           $data['konyvelesDatum'] = $this->getBookingDate();
        if (SzamlaAgentUtil::isNotBlank($this->getBuyerId()))               $data['vevoAzonosito'] = $this->getBuyerId();
        if (SzamlaAgentUtil::isNotBlank($this->getBuyerLedgerNumber()))     $data['vevoFokonyviSzam'] = $this->getBuyerLedgerNumber();
        if ($this->isContinuedFulfillment())                                $data['folyamatosTelj'] = $this->isContinuedFulfillment();
        if (SzamlaAgentUtil::isNotBlank($this->getSettlementPeriodStart())) $data['elszDatumTol'] = $this->getSettlementPeriodStart();
        if (SzamlaAgentUtil::isNotBlank($this->getSettlementPeriodEnd()))   $data['elszDatumIg'] = $this->getSettlementPeriodEnd();

        return $data;
    }

    /**
     * Visszaadja a vevő gazdasági esemény azonosítót
     *
     * @return string
     */
    public function getBuyerId() {
        return $this->buyerId;
    }

    /**
     * Beállítja a vevő gazdasági esemény azonosítót
     *
     * @param string $buyerId
     */
    public function setBuyerId($buyerId) {
        $this->buyerId = $buyerId;
    }

    /**
     * Visszaadja a könyvelési dátumot
     *
     * @return string
     */
    public function getBookingDate() {
        return $this->bookingDate;
    }

    /**
     * Beállítja a könyvelési dátumot
     *
     * @param string $bookingDate
     */
    public function setBookingDate($bookingDate) {
        $this->bookingDate = $bookingDate;
    }

    /**
     * Visszaadja a vevő főkönyvi számát
     *
     * @return string
     */
    public function getBuyerLedgerNumber() {
        return $this->buyerLedgerNumber;
    }

    /**
     * Beállítja a vevő főkönyvi számát
     *
     * @param string $buyerLedgerNumber
     */
    public function setBuyerLedgerNumber($buyerLedgerNumber) {
        $this->buyerLedgerNumber = $buyerLedgerNumber;
    }

    /**
     * Visszaadja a vevő folyamatos teljesítésének állapotát
     *
     * @return bool
     */
    public function isContinuedFulfillment() {
        return $this->continuedFulfillment;
    }

    /**
     * Beállítja a vevő folyamatos teljesítésének állapotát
     *
     * @param bool $continuedFulfillment
     */
    public function setContinuedFulfillment($continuedFulfillment) {
        $this->continuedFulfillment = $continuedFulfillment;
    }

    /**
     * Visszaadja a folyamatos teljesítéséhez tartozó elszámolási időszak kezdetét
     *
     * @return string
     */
    public function getSettlementPeriodStart() {
        return $this->settlementPeriodStart;
    }

    /**
     * Beállítja a folyamatos teljesítéséhez tartozó elszámolási időszak kezdetét
     *
     * @param string $settlementPeriodStart
     */
    public function setSettlementPeriodStart($settlementPeriodStart) {
        $this->settlementPeriodStart = $settlementPeriodStart;
    }

    /**
     * Visszaadja a folyamatos teljesítéséhez tartozó elszámolási időszak végét
     *
     * @return string
     */
    public function getSettlementPeriodEnd() {
        return $this->settlementPeriodEnd;
    }

    /**
     * Beállítja a folyamatos teljesítéséhez tartozó elszámolási időszak végét
     *
     * @param string $settlementPeriodEnd
     */
    public function setSettlementPeriodEnd($settlementPeriodEnd) {
        $this->settlementPeriodEnd = $settlementPeriodEnd;
    }
}