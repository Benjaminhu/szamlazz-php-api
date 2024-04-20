<?php

namespace SzamlaAgent\Item;

use SzamlaAgent\Ledger\InvoiceItemLedger;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Számlatétel
 *
 * @package SzamlaAgent\Item
 */
class InvoiceItem extends Item {

    /**
     * Tételhez tartozó főkönyvi adatok
     *
     * @var InvoiceItemLedger
     */
    protected $ledgerData;

    /**
     * Számlatétel példányosítás
     *
     * @param string $name          tétel név
     * @param double $netUnitPrice  nettó egységár
     * @param double $quantity      mennyiség
     * @param string $quantityUnit  mennyiségi egység
     * @param string $vat           áfatartalom
     */
    public function __construct($name, $netUnitPrice, $quantity = self::DEFAULT_QUANTITY, $quantityUnit = self::DEFAULT_QUANTITY_UNIT, $vat = self::DEFAULT_VAT) {
        parent::__construct($name, $netUnitPrice, $quantity, $quantityUnit, $vat);
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData() {
        $data = [];
        $this->checkFields();

        $data['megnevezes']       = $this->getName();

        if (SzamlaAgentUtil::isNotBlank($this->getId())){
            $data['azonosito']    = $this->getId();
        }

        $data['mennyiseg']        = SzamlaAgentUtil::doubleFormat($this->getQuantity());
        $data['mennyisegiEgyseg'] = $this->getQuantityUnit();
        $data['nettoEgysegar']    = SzamlaAgentUtil::doubleFormat($this->getNetUnitPrice());
        $data['afakulcs']         = $this->getVat();

        if (SzamlaAgentUtil::isNotNull($this->getPriceGapVatBase())) {
            $data['arresAfaAlap'] = SzamlaAgentUtil::doubleFormat($this->getPriceGapVatBase());
        }

        $data['nettoErtek']       = SzamlaAgentUtil::doubleFormat($this->getNetPrice());
        $data['afaErtek']         = SzamlaAgentUtil::doubleFormat($this->getVatAmount());
        $data['bruttoErtek']      = SzamlaAgentUtil::doubleFormat($this->getGrossAmount());

        if (SzamlaAgentUtil::isNotBlank($this->getComment())) {
            $data['megjegyzes']   = $this->getComment();
        }

        if (SzamlaAgentUtil::isNotNull($this->getLedgerData())) {
            $data['tetelFokonyv']     = $this->getLedgerData()->buildXmlData();
        }
        return $data;
    }


    /**
     * @return float
     */
    public function getPriceGapVatBase() {
        return $this->priceGapVatBase;
    }

    /**
     * @param float $priceGapVatBase
     */
    public function setPriceGapVatBase($priceGapVatBase) {
        $this->priceGapVatBase = (float)$priceGapVatBase;
    }

    /**
     * @return InvoiceItemLedger
     */
    public function getLedgerData() {
        return $this->ledgerData;
    }

    /**
     * @param InvoiceItemLedger $ledgerData
     */
    public function setLedgerData(InvoiceItemLedger $ledgerData) {
        $this->ledgerData = $ledgerData;
    }

    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }
}