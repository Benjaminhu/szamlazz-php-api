<?php

namespace SzamlaAgent\Waybill;

use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Transoflex fuvarlevél
 *
 * @package SzamlaAgent\Waybill
 */
class TransoflexWaybill extends Waybill {

    /**
     * Fuvarlevél azonosító
     * a TOF-tól kapott 5 jegyű szám
     *
     * @var string
     */
    protected $id;

    /**
     * Egyedi szállítási azonosító
     *
     * @var string
     */
    protected $shippingId;

    /**
     * Csomagszám
     *
     * @var int
     */
    protected $packetNumber;

    /**
     * Országkód
     *
     * @var string
     */
    protected $countryCode;

    /**
     * Irányítószám
     *
     * @var string
     */
    protected $zip;

    /**
     * Szolgáltatás
     *
     * @var string
     */
    protected $service;

    /**
     * Transoflex fuvarlevél létrehozása
     *
     * @param string  $destination  Úti cél
     * @param string  $barcode      Vonalkód
     * @param string  $comment      fuvarlevél megjegyzés
     */
    function __construct($destination = '', $barcode = '', $comment = '') {
        parent::__construct($destination, self::WAYBILL_TYPE_TRANSOFLEX, $barcode, $comment);
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
                case 'packetNumber':
                    SzamlaAgentUtil::checkIntField($field, $value, false, __CLASS__);
                    break;
                case 'id':
                case 'shippingId':
                case 'countryCode':
                case 'zip':
                case 'service':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
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

        $data['tof'] = [];
        if (SzamlaAgentUtil::isNotBlank($this->getId()))           $data['tof']['azonosito'] = $this->getId();
        if (SzamlaAgentUtil::isNotBlank($this->getShippingId()))   $data['tof']['shippingID'] = $this->getShippingId();
        if (SzamlaAgentUtil::isNotNull($this->getPacketNumber()))  $data['tof']['csomagszam'] = $this->getPacketNumber();
        if (SzamlaAgentUtil::isNotBlank($this->getCountryCode()))  $data['tof']['countryCode'] = $this->getCountryCode();
        if (SzamlaAgentUtil::isNotBlank($this->getZip()))          $data['tof']['zip'] = $this->getZip();
        if (SzamlaAgentUtil::isNotBlank($this->getService()))      $data['tof']['service'] = $this->getService();

        return $data;
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
    public function getShippingId() {
        return $this->shippingId;
    }

    /**
     * @param string $shippingId
     */
    public function setShippingId($shippingId) {
        $this->shippingId = $shippingId;
    }

    /**
     * @return int
     */
    public function getPacketNumber() {
        return $this->packetNumber;
    }

    /**
     * @param int $packetNumber
     */
    public function setPacketNumber($packetNumber) {
        $this->packetNumber = $packetNumber;
    }

    /**
     * @return string
     */
    public function getCountryCode() {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode) {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getZip() {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip($zip) {
        $this->zip = $zip;
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
}