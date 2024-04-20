<?php

namespace SzamlaAgent\Waybill;

use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Sprinter fuvarlevél
 *
 * @package SzamlaAgent\Waybill
 */
class SprinterWaybill extends Waybill {

    /**
     * Fuvarlevél azonosító
     * a Sprinterrel egyeztetett 3 karakteres rövidítés
     *
     * @var string
     */
    protected $id;

    /**
     * Sprintertől kapott feladókód, 10 jegyű szám
     *
     * @var string
     */
    protected $senderId;

    /**
     * Sprinteres iránykód, az a sprinter saját "irányítószáma", pl. "106"
     *
     * @var string
     */
    protected $shipmentZip;

    /**
     * Csomagok száma, ennyi fuvarlevél lesz a számlához összesen
     *
     * @var int
     */
    protected $packetNumber;

    /**
     * Számlánként egyedi vonalkód, 7-13 karakteres azonosító
     *
     * @var string
     */
    protected $barcodePostfix;

    /**
     * Szállítási idő
     * ez az 1 munkanapos szöveg, többnyire
     *
     * @var string
     */
    protected $shippingTime;

    /**
     * Sprinter fuvarlevél létrehozása
     *
     * @param string  $destination  Úti cél
     * @param string  $barcode      Vonalkód
     * @param string  $comment      fuvarlevél megjegyzés
     */
    function __construct($destination = '', $barcode = '', $comment = '') {
        parent::__construct($destination, self::WAYBILL_TYPE_SPRINTER, $barcode, $comment);
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
                case 'senderId':
                case 'shipmentZip':
                case 'barcodePostfix':
                case 'shippingTime':
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

        $data['sprinter'] = [];
        if (SzamlaAgentUtil::isNotBlank($this->getId()))             $data['sprinter']['azonosito'] = $this->getId();
        if (SzamlaAgentUtil::isNotBlank($this->getSenderId()))       $data['sprinter']['feladokod'] = $this->getSenderId();
        if (SzamlaAgentUtil::isNotBlank($this->getShipmentZip()))    $data['sprinter']['iranykod'] = $this->getShipmentZip();
        if (SzamlaAgentUtil::isNotNull($this->getPacketNumber()))    $data['sprinter']['csomagszam'] = $this->getPacketNumber();
        if (SzamlaAgentUtil::isNotBlank($this->getBarcodePostfix())) $data['sprinter']['vonalkodPostfix'] = $this->getBarcodePostfix();
        if (SzamlaAgentUtil::isNotBlank($this->getShippingTime()))   $data['sprinter']['szallitasiIdo'] = $this->getShippingTime();

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
    public function getSenderId() {
        return $this->senderId;
    }

    /**
     * @param string $senderId
     */
    public function setSenderId($senderId) {
        $this->senderId = $senderId;
    }

    /**
     * @return string
     */
    public function getShipmentZip() {
        return $this->shipmentZip;
    }

    /**
     * @param string $shipmentZip
     */
    public function setShipmentZip($shipmentZip) {
        $this->shipmentZip = $shipmentZip;
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
    public function getBarcodePostfix() {
        return $this->barcodePostfix;
    }

    /**
     * @param string $barcodePostfix
     */
    public function setBarcodePostfix($barcodePostfix) {
        $this->barcodePostfix = $barcodePostfix;
    }

    /**
     * @return string
     */
    public function getShippingTime() {
        return $this->shippingTime;
    }

    /**
     * @param string $shippingTime
     */
    public function setShippingTime($shippingTime) {
        $this->shippingTime = $shippingTime;
    }
}