<?php

namespace SzamlaAgent\Item;

use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Tétel
 *
 * @package SzamlaAgent\Item
 */
class Item {

    /**
     * Áfakulcs: tárgyi adómentes
     */
    const VAT_TAM = 'TAM';

    /**
     * Áfakulcs: alanyi adómentes
     */
    const VAT_AAM = 'AAM';

    /**
     * Áfakulcs: EU-n belül
     */
    const VAT_EU = 'EU';

    /**
     * Áfakulcs: EU-n kívül
     */
    const VAT_EUK = 'EUK';

    /**
     * Áfakulcs: mentes az adó alól
     */
    const VAT_MAA = 'MAA';

    /**
     * Áfakulcs: fordított áfa
     */
    const VAT_F_AFA = 'F.AFA';

    /**
     * Áfakulcs: különbözeti áfa
     */
    const VAT_K_AFA = 'K.AFA';

    /**
     * Áfakulcs: áfakörön kívüli
     */
    const VAT_AKK = 'ÁKK';

    /**
     * Áfakulcs: áfakörön kívüli
     */
    const VAT_TAHK = 'TAHK';

    /**
     * Áfakulcs: áfakörön kívüli
     */
    const VAT_TEHK = 'TEHK';

    /**
     * Áfakulcs: EU-n belüli termék értékesítés
     */
    const VAT_EUT = 'EUT';

    /**
     * Áfakulcs: EU-n kívüli termék értékesítés
     */
    const VAT_EUKT = 'EUKT';

    /**
     * Áfakulcs: EU-n belüli
     */
    const VAT_KBAET = 'KBAET';

    /**
     * Áfakulcs: EU-n belüli
     */
    const VAT_KBAUK = 'KBAUK';

    /**
     * Áfakulcs: EU-n kívüli
     */
    const VAT_EAM = 'EAM';

    /**
     * Áfakulcs: Mentes az adó alól
     */
    const VAT_NAM = 'KBAUK';

    /**
     * Áfakulcs: áfa tárgyi hatályán kívül
     */
    const VAT_ATK = 'ATK';

    /**
     * Áfakulcs: EU-n belüli
     */
    const VAT_EUFAD37 = 'EUFAD37';

    /**
     * Áfakulcs: EU-n belüli
     */
    const VAT_EUFADE = 'EUFADE';

    /**
     * Áfakulcs: EU-n belüli
     */
    const VAT_EUE = 'EUE';

    /**
     * Áfakulcs: EU-n kívüli
     */
    const VAT_HO = 'HO';

    /**
     * Alapértelmezett ÁFA érték
     */
    const DEFAULT_VAT = '27';

    /**
     * Alapértelmezett mennyiség
     */
    const DEFAULT_QUANTITY = 1.0;

    /**
     * Alapértelmezett mennyiségi egység
     */
    const DEFAULT_QUANTITY_UNIT = 'db';

    /**
     * Tétel azonosító
     *
     * @var string
     */
    protected $id;

    /**
     * Tétel neve
     *
     * @var string
     */
    protected $name;

    /**
     * Tétel mennyisége
     * Az értékesített mennyiség, pl. '10' vagy '2,5'
     *
     * @var double
     */
    protected $quantity;

    /**
     * Tétel mennyiségi egysége
     * (pl. darab, óra, stb.)
     *
     * @var string
     */
    protected $quantityUnit;

    /**
     * Nettó egységár
     * A számla tétel 1 darabra (vagy más mértékegységre) vetített nettó ára
     *
     * @var double
     */
    protected $netUnitPrice;

    /**
     * Áfa kulcs
     *
     * Ugyanaz adható meg, mint a számlakészítés oldalon:
     * https://www.szamlazz.hu/szamla/szamlaszerkeszto
     *
     * Példa konkrét ÁFA értékre:
     * 0,5,7,18,19,20,25,27
     *
     * @var string
     */
    protected $vat;

    /**
     * A tétel árrés ÁFA alapja
     *
     * @var double
     */
    protected $priceGapVatBase;

    /**
     * Tétel nettó értéke
     * (nettó egységár szorozva az értékesített mennyiséggel)
     *
     * @var double
     */
    protected $netPrice;

    /**
     * Tétel ÁFA értéke
     * (a nettó érték alapján az áfakulccsal kalkulált áfa érték)
     *
     * @var double
     */
    protected $vatAmount;

    /**
     * Tétel bruttó értéke
     * (a nettó érték és az áfa érték összege)
     *
     * @var double
     */
    protected $grossAmount;

    /**
     * Tétel megjegyzése
     *
     * @var string
     */
    protected $comment;

    /**
     * Kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = ['name', 'quantity', 'quantityUnit', 'netUnitPrice', 'vat', 'netPrice', 'vatAmount', 'grossAmount'];

    /**
     * Tétel példányosítás
     *
     * @param string $name          tétel név
     * @param double $netUnitPrice  nettó egységár
     * @param double $quantity      mennyiség
     * @param string $quantityUnit  mennyiségi egység
     * @param string $vat           áfatartalom
     */
    protected function __construct($name, $netUnitPrice, $quantity = self::DEFAULT_QUANTITY, $quantityUnit = self::DEFAULT_QUANTITY_UNIT, $vat = self::DEFAULT_VAT) {
        $this->setName($name);
        $this->setNetUnitPrice($netUnitPrice);
        $this->setQuantity($quantity);
        $this->setQuantityUnit($quantityUnit);
        $this->setVat($vat);
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
                case 'quantity':
                case 'netUnitPrice':
                case 'priceGapVatBase':
                case 'netPrice':
                case 'vatAmount':
                case 'grossAmount':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'name':
                case 'id':
                case 'quantityUnit':
                case 'vat':
                case 'comment':
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
            $this::checkField($field, $value);
        }
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getQuantity() {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity) {
        $this->quantity = (float)$quantity;
    }

    /**
     * @return string
     */
    public function getQuantityUnit() {
        return $this->quantityUnit;
    }

    /**
     * @param string $quantityUnit
     */
    public function setQuantityUnit($quantityUnit) {
        $this->quantityUnit = $quantityUnit;
    }

    /**
     * @return float
     */
    public function getNetUnitPrice() {
        return $this->netUnitPrice;
    }

    /**
     * @param float $netUnitPrice
     */
    public function setNetUnitPrice($netUnitPrice) {
        $this->netUnitPrice = (float)$netUnitPrice;
    }

    /**
     * @return string
     */
    public function getVat() {
        return $this->vat;
    }

    /**
     * @param string $vat
     */
    public function setVat($vat) {
        $this->vat = $vat;
    }

    /**
     * @return float
     */
    public function getNetPrice() {
        return $this->netPrice;
    }

    /**
     * @param float $netPrice
     */
    public function setNetPrice($netPrice) {
        $this->netPrice = (float)$netPrice;
    }

    /**
     * @return float
     */
    public function getVatAmount() {
        return $this->vatAmount;
    }

    /**
     * @param float $vatAmount
     */
    public function setVatAmount($vatAmount) {
        $this->vatAmount = (float)$vatAmount;
    }

    /**
     * @return float
     */
    public function getGrossAmount() {
        return $this->grossAmount;
    }

    /**
     * @param float $grossAmount
     */
    public function setGrossAmount($grossAmount) {
        $this->grossAmount = (float)$grossAmount;
    }
 }