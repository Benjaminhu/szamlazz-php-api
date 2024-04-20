<?php

namespace SzamlaAgent\Response;

/**
 * Egy adózó adatainak lekérésére adott választ reprezentáló osztály
 *
 * FONTOS! Az adatok a NAV-tól érkeznek. A NAV bármikor változtathat az interface-en,
 * illetve nem minden esetben adnak vissza címadatokat, így erre is fel kell készítened a kódot.
 *
 * Ha üzleti logikát építesz erre az interface-re, akkor javasoljuk saját XML feldolgozóval kezelni
 * a NAV-tól érkező adatokat, felkészítve arra, hogy a NAV bármikor megváltoztathatja annak szerkezetét!
 *
 * A NAV-tól érkező nyers adatokat az alábbi példafájlban található módon kérdezheted le:
 * @see examples/get_taxpayer.php
 *
 * @package SzamlaAgent\Response
 * @deprecated 2.9.10, use your own XML processor
 */
class TaxPayerResponse {

    /**
     * Kérés azonosító
     *
     * @var string
     */
    protected $requestId;

    /**
     * Kérés időbélyege
     *
     * @var string
     */
    protected $timestamp;

    /**
     * Kérés verziója
     *
     * @var string
     */
    protected $requestVersion;

    /**
     * Kérés sikeres volt-e
     *
     * @var string
     */
    protected $funcCode;

    /**
     * Adószám érvényes-e?
     *
     * @var bool
     */
    protected $taxpayerValidity;

    /**
     * A válaszban visszakapott adózói adatok
     *
     * @var array
     */
    private $taxPayerData;

    /**
     * Hibakód
     *
     * @var string
     */
    protected $errorCode;

    /**
     * Hibaüzenet
     *
     * @var string
     */
    protected $errorMessage;


    /**
     * Adózó lekérdezésének adatai
     */
    function __construct() {}

    /**
     * Feldolgozás után visszaadja az adózó válaszát objektumként
     *
     * @param array $data
     *
     * @return TaxPayerResponse
     */
    public static function parseData(array $data) {
        $payer = new TaxPayerResponse();

        if (isset($data['result']['funcCode']))  $payer->setFuncCode($data['result']['funcCode']);
        if (isset($data['result']['errorCode'])) $payer->setErrorCode($data['result']['errorCode']);
        if (isset($data['result']['message']))   $payer->setErrorMessage($data['result']['message']);
        if (isset($data['taxpayerValidity']))    $payer->setTaxpayerValidity(($data['taxpayerValidity'] === 'true'));

        if (isset($data['header'])) {
            $header = $data['header'];
            $payer->setRequestId($header['requestId']);
            $payer->setTimestamp($header['timestamp']);
            $payer->setRequestVersion($header['requestVersion']);
        }

        if (isset($data['taxpayerData'])) {
            $payer->setTaxPayerData($data['taxpayerData']);
        }
        return $payer;
    }

    /**
     * Visszaadja a válasz azonosítóját
     *
     * @return string
     */
    public function getRequestId() {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    protected function setRequestId($requestId) {
        $this->requestId = $requestId;
    }

    /**
     * Visszaadja a válasz időbélyegét
     *
     * @return string
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     */
    protected function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    /**
     * Visszaadja a kérés verzióját
     *
     * @return string
     */
    public function getRequestVersion() {
        return $this->requestVersion;
    }

    /**
     * @param string $requestVersion
     */
    protected function setRequestVersion($requestVersion) {
        $this->requestVersion = $requestVersion;
    }

    /**
     * Visszaadja a kérés sikerességét
     *
     * @return string
     */
    public function getFuncCode() {
        return $this->funcCode;
    }

    /**
     * @param string $funcCode
     */
    protected function setFuncCode($funcCode) {
        $this->funcCode = $funcCode;
    }

    /**
     * Visszaadja, hogy az adószám érvényes-e
     *
     * @return string
     */
    public function isTaxpayerValidity() {
        return $this->taxpayerValidity;
    }

    /**
     * @param string $taxpayerValidity
     */
    protected function setTaxpayerValidity($taxpayerValidity) {
        $this->taxpayerValidity = $taxpayerValidity;
    }

    /**
     * Visszaadja, hogy a válaszban vannak-e adózói adatok
     *
     * @return bool
     */
    public function hasTaxPayerData() {
        return (!empty($this->taxPayerData));
    }

    /**
     * Visszaadja az adózó adatait
     *
     * @return array
     */
    public function getTaxPayerData() {
        return $this->taxPayerData;
    }

    /**
     * @param array
     */
    protected function setTaxPayerData(array $data) {
        $this->taxPayerData = $data;
    }

    /**
     * Visszaadja az adózó lekérdezésének sikerességét
     *
     * @return bool
     */
    public function isSuccess() {
        return ($this->getFuncCode() == 'OK');
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e hibát
     *
     * @return bool
     */
    public function isError() {
        return !$this->isSuccess();
    }

    /**
     * Visszaadja a hibakódot
     *
     * @return string
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     * @param string $errorCode
     */
    protected function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    /**
     * Visszaadja a hibaüzenetet
     *
     * @return string
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    protected function setErrorMessage($errorMessage) {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function toString() {
        $str = "Adószám érvényessége: " . (($this->isTaxpayerValidity()) ? 'érvényes' : "érvénytelen");
        if (empty($this->getTaxPayerData()) && $this->getFuncCode()) {
            $str.= ", az adószámhoz nem található adat!";
        }
        return $str;
    }
 }