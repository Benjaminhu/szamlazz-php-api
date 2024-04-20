<?php

namespace SzamlaAgent\Response;

use SzamlaAgent\SzamlaAgentUtil;

/**
 * Egy nyugta típusú bizonylat kérésére adott választ reprezentáló osztály
 *
 * @package SzamlaAgent\Response
 */
class ReceiptResponse {

    /**
     * Nyugta azonosítója
     *
     * @var int
     */
    protected $id;

    /**
     * Nyugtaszám
     *
     * @var string
     */
    protected $receiptNumber;

    /**
     * A nyugta típusa
     *
     * @var string
     */
    protected $type;

    /**
     * A nyugta sztornózott-e
     *
     * @var false
     */
    protected $reserved;

    /**
     * Sztornózott nyugtaszám
     *
     * @var string
     */
    protected $reservedReceiptNumber;

    /**
     * A nyugta kelte
     *
     * @var string
     */
    protected $created;

    /**
     * A nyugta fizetési módja
     *
     * @var string
     */
    protected $paymentMethod;

    /**
     * A nyugta pénzneme
     *
     * @var string
     */
    protected $currency;

    /**
     * Teszt vagy valós céggel lett létrehozva a nyugta
     *
     * @var boolean
     */
    protected $test;

    /**
     * A nyugta tételei
     *
     * @var array
     */
    protected $items;

    /**
     * A nyugta összegei
     *
     * @var array
     */
    protected $amounts;

    /**
     * A válasz hibakódja
     *
     * @var string
     */
    protected $errorCode;

    /**
     * A válasz hibaüzenete
     *
     * @var string
     */
    protected $errorMessage;

    /**
     * A válaszban kapott PDF adatai
     *
     * @var string
     */
    protected $pdfData;

    /**
     * Sikeres-e a válasz
     *
     * @var bool
     */
    protected $success;

    /**
     * Jóváírások
     *
     * @var array
     */
    protected $creditNotes;


    /**
     * Nyugta létrehozása nyugtaszám alapján
     *
     * @param string $receiptNumber
     */
    function __construct($receiptNumber = '') {
        $this->setReceiptNumber($receiptNumber);
    }

    /**
     * Feldolgozás után visszaadja a nyugta válaszát objektumként
     *
     * @param array $data
     * @param int   $type
     *
     * @return ReceiptResponse
     */
    public static function parseData(array $data, $type = SzamlaAgentResponse::RESULT_AS_TEXT) {
        $response = new ReceiptResponse();

        if ($type == SzamlaAgentResponse::RESULT_AS_TEXT) {
            $params = $xmlData = new \SimpleXMLElement(base64_decode($data['body']));
            $data = SzamlaAgentUtil::toArray($params);
        }

        $base = [];
        if (isset($data['nyugta']['alap']))        $base = $data['nyugta']['alap'];

        if (isset($base['id']))                    $response->setId($base['id']);
        if (isset($base['nyugtaszam']))            $response->setReceiptNumber($base['nyugtaszam']);
        if (isset($base['tipus']))                 $response->setType($base['tipus']);
        if (isset($base['stornozott']))            $response->setReserved(($base['stornozott'] === 'true'));
        if (isset($base['stornozottNyugtaszam']))  $response->setReservedReceiptNumber($base['stornozottNyugtaszam']);
        if (isset($base['kelt']))                  $response->setCreated($base['kelt']);
        if (isset($base['fizmod']))                $response->setPaymentMethod($base['fizmod']);
        if (isset($base['penznem']))               $response->setCurrency($base['penznem']);
        if (isset($base['teszt']))                 $response->setTest(($base['teszt'] === 'true'));
        if (isset($data['nyugta']['tetelek']))     $response->setItems($data['nyugta']['tetelek']);
        if (isset($data['nyugta']['osszegek']))    $response->setAmounts($data['nyugta']['osszegek']);
        if (isset($data['nyugta']['kifizetesek'])) $response->setCreditNotes($data['nyugta']['kifizetesek']);
        if (isset($data['sikeres']))               $response->setSuccess(($data['sikeres'] === 'true'));

        if (isset($data['nyugtaPdf']))             $response->setPdfData($data['nyugtaPdf']);
        if (isset($data['hibakod']))               $response->setErrorCode($data['hibakod']);
        if (isset($data['hibauzenet']))            $response->setErrorMessage($data['hibauzenet']);

        return $response;
    }

    /**
     * Visszaadja a nyugta azonosítót
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    protected function setId($id) {
        $this->id = $id;
    }

    /**
     * Visszaadja a nyugta számát
     *
     * @return string
     */
    public function getReceiptNumber() {
        return $this->receiptNumber;
    }

    /**
     * Visszaadja a bizonylat számát
     *
     * @return string
     */
    public function getDocumentNumber() {
        return $this->getReceiptNumber();
    }

    /**
     * @param string $receiptNumber
     */
    protected function setReceiptNumber($receiptNumber) {
        $this->receiptNumber = $receiptNumber;
    }

    /**
     * Visszaadja a nyugta típusát
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     */
    protected function setType($type) {
        $this->type = $type;
    }

    /**
     * Visszaadja, hogy a nyugta sztornózott-e
     *
     * @return false
     */
    public function getReserved() {
        return $this->reserved;
    }

    /**
     * @param false $reserved
     */
    protected function setReserved($reserved) {
        $this->reserved = $reserved;
    }

    /**
     * Visszaadja a nyugta keltét
     *
     * @return string
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param string $created
     */
    protected function setCreated($created) {
        $this->created = $created;
    }

    /**
     * Visszaadja a nyugta fizetési módját
     *
     * @return string
     */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    protected function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Visszaadja a nyugta valutáját
     *
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    protected function setCurrency($currency) {
        $this->currency = $currency;
    }

    /**
     * Visszaadja, hogy a nyugtát teszt cég hozta-e létre
     *
     * @return bool
     */
    public function isTest() {
        return $this->test;
    }

    /**
     * @param bool $test
     */
    protected function setTest($test) {
        $this->test = $test;
    }

    /**
     * Visszaadja a nyugta tételeit
     *
     * @return array
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @param array $items
     */
    protected function setItems($items) {
        $this->items = $items;
    }

    /**
     * Visszaadja a nyugta összegeit
     *
     * @return array
     */
    public function getAmounts() {
        return $this->amounts;
    }

    /**
     * @param array $amounts
     */
    protected function setAmounts($amounts) {
        $this->amounts = $amounts;
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
     * Visszaadja a nyugta PDF fájlt
     *
     * @return bool|string
     */
    public function getPdfFile() {
        $pdfData = SzamlaAgentUtil::isNotNull($this->getPdfData()) ? $this->getPdfData() : '';
        return base64_decode($pdfData);
    }

    /**
     * Visszaadja a nyugta PDF adatokat
     *
     * @return string
     */
    public function getPdfData() {
        return $this->pdfData;
    }

    /**
     * @param string $pdfData
     */
    protected function setPdfData($pdfData) {
        $this->pdfData = $pdfData;
    }

    /**
     * Visszaadja a nyugta kiállításának sikerességét
     *
     * @return bool
     */
    public function isSuccess() {
        return $this->success;
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
     * @param bool $success
     */
    protected function setSuccess($success) {
        $this->success = $success;
    }

    /**
     * Visszaadja a nyugta jóváírásait
     *
     * @return array
     */
    public function getCreditNotes() {
        return $this->creditNotes;
    }

    /**
     * @param array $creditNotes
     */
    protected function setCreditNotes($creditNotes) {
        $this->creditNotes = $creditNotes;
    }

    /**
     * Visszaadja a sztornózott nyugta számát
     *
     * @return string
     */
    public function getReservedReceiptNumber() {
        return $this->reservedReceiptNumber;
    }

    /**
     * @param string $reservedReceiptNumber
     */
    protected function setReservedReceiptNumber($reservedReceiptNumber) {
        $this->reservedReceiptNumber = $reservedReceiptNumber;
    }
}