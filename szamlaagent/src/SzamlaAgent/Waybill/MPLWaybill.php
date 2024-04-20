<?php

namespace SzamlaAgent\Waybill;

use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * MPL fuvarlevél
 *
 * @package SzamlaAgent\Waybill
 */
class MPLWaybill extends Waybill {

    /**
     * MPL vevőkód
     *
     * @var string
     */
    protected $buyerCode;

    /**
     * A vonalkód ezen string alapján készül
     *
     * @var string
     */
    protected $barcode;

    /**
     * A csomag tömege, tartalmazhat tizedes pontot, ha szükséges
     *
     * @var string
     */
    protected $weight;

    /**
     * A különszolgáltatásokhoz megadható ikonok konfigurációja, ha nincs megadva, akkor egy ikon sem jelenik meg
     *
     * @var string
     */
    protected $service;

    /**
     * A fuvarlevélen az értéknyilvánítás mező értéke
     *
     * @var double
     */
    protected $insuredValue;

    /**
     * Kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = ['buyerCode', 'barcode', 'weight'];

    /**
     * MPL fuvarlevél létrehozása
     *
     * @param string  $destination  Úti cél
     * @param string  $barcode      Vonalkód
     * @param string  $comment      fuvarlevél megjegyzés
     */
    function __construct($destination = '', $barcode = '', $comment = '') {
        parent::__construct($destination, self::WAYBILL_TYPE_MPL, $barcode, $comment);
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
                case 'insuredValue':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'buyerCode':
                case 'weight':
                case 'service':
                case 'shippingTime':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }
        return $value;
    }

    /**
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        $this->checkFields(get_class());
        $data = parent::buildXmlData($request);

        $data['mpl'] = [];
        $data['mpl']['vevokod'] = $this->getBuyerCode();
        $data['mpl']['vonalkod'] = $this->getBarcode();
        $data['mpl']['tomeg'] = $this->getWeight();

        if (SzamlaAgentUtil::isNotBlank($this->getService())) {
            $data['mpl']['kulonszolgaltatasok'] = $this->getService();
        }

        if (SzamlaAgentUtil::isNotNull($this->getInsuredValue())) {
            $data['mpl']['erteknyilvanitas'] = SzamlaAgentUtil::doubleFormat($this->getInsuredValue());
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getBuyerCode() {
        return $this->buyerCode;
    }

    /**
     * @param string $buyerCode
     */
    public function setBuyerCode($buyerCode) {
        $this->buyerCode = $buyerCode;
    }

    /**
     * @return string
     */
    public function getWeight() {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight($weight) {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @param string $service
     */
    public function setService($service) {
        $this->service = $service;
    }

    /**
     * @return float
     */
    public function getInsuredValue() {
        return $this->insuredValue;
    }

    /**
     * @param float $insuredValue
     */
    public function setInsuredValue($insuredValue) {
        $this->insuredValue = (float)$insuredValue;
    }
}