<?php

namespace SzamlaAgent\Response;

use SzamlaAgent\SzamlaAgentUtil;

/**
 * Egy számla típusú bizonylat kérésére adott választ reprezentáló osztály
 *
 * @package SzamlaAgent\Response
 */
class InvoiceResponse {

    /**
     * Számlaértesítő kézbesítése sikertelen
     */
    const INVOICE_NOTIFICATION_SEND_FAILED = 56;

    /**
     * Vevői fiók URL
     *
     * @var string
     */
    protected $userAccountUrl;

    /**
     * Kintlévőség
     *
     * @var int
     */
    protected $assetAmount;

    /**
     * Nettó végösszeg
     *
     * @var int
     */
    protected $netPrice;

    /**
     * Bruttó végösszeg
     *
     * @var int
     */
    protected $grossAmount;

    /**
     * Számlaszám
     *
     * @var string
     */
    protected $invoiceNumber;

    /**
     * Számla azonosító
     *
     * @var int
     */
    protected $invoiceIdentifier;

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
     * A válasz fejléc adatai
     *
     * @var array
     */
    protected $headers;

    /**
     * Számla válasz létrehozása
     *
     * @param string $invoiceNumber
     */
    public function __construct($invoiceNumber = '') {
        $this->setInvoiceNumber($invoiceNumber);
    }

    /**
     * Feldolgozás után visszaadja a számla válaszát objektumként
     *
     * @param array $data
     * @param int   $type
     *
     * @return InvoiceResponse
     */
    public static function parseData(array $data, $type = SzamlaAgentResponse::RESULT_AS_TEXT) {
        $response   = new InvoiceResponse();
        $headers = array_change_key_case($data['headers'], CASE_LOWER);
        $isPdf   = self::isPdfResponse($data);
        $pdfFile = '';

        if (isset($data['body'])) {
            $pdfFile = $data['body'];
        } else if ($type == SzamlaAgentResponse::RESULT_AS_XML && isset($data['pdf'])) {
            $pdfFile = $data['pdf'];
        }

        if (!empty($headers)) {
            $response->setHeaders($headers);

            if (array_key_exists('szlahu_szamlaszam', $headers)) {
                $response->setInvoiceNumber($headers['szlahu_szamlaszam']);
            }

            if (array_key_exists('szlahu_id', $headers)) {
                $response->setInvoiceIdentifier($headers['szlahu_id']);
            }

            if (array_key_exists('szlahu_vevoifiokurl', $headers)) {
                $response->setUserAccountUrl(rawurldecode($headers['szlahu_vevoifiokurl']));
            }

            if (array_key_exists('szlahu_kintlevoseg', $headers)) {
                $response->setAssetAmount($headers['szlahu_kintlevoseg']);
            }

            if (array_key_exists('szlahu_nettovegosszeg', $headers)) {
                $response->setNetPrice($headers['szlahu_nettovegosszeg']);
            }

            if (array_key_exists('szlahu_bruttovegosszeg', $headers)) {
                $response->setGrossAmount($headers['szlahu_bruttovegosszeg']);
            }

            if (array_key_exists('szlahu_error', $headers)) {
                $error = urldecode($headers['szlahu_error']);
                $response->setErrorMessage($error);
            }

            if (array_key_exists('szlahu_error_code', $headers)) {
                $response->setErrorCode($headers['szlahu_error_code']);
            }

            if ($isPdf && !empty($pdfFile)) {
                $response->setPdfData($pdfFile);
            }

            if ($response->isNotError()) {
                $response->setSuccess(true);
            }
        }
        return $response;
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e PDF-et
     *
     * @param $result
     *
     * @return bool
     */
    protected static function isPdfResponse($result) {
        if (isset($result['pdf'])) {
            return true;
        }

        if (isset($result['headers']['content-type']) && $result['headers']['content-type'] == 'application/pdf') {
            return true;
        }

        if (isset($result['headers']['content-disposition']) && stripos($result['headers']['content-disposition'],'pdf') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e számlaszámot
     *
     * @return boolean
     */
    public function hasInvoiceNumber() {
        return (SzamlaAgentUtil::isNotBlank($this->invoiceNumber));
    }

    /**
     * Visszaadja a számlaszámot
     *
     * @return string
     */
    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    /**
     * Visszaadja a bizonylat (számla) számát
     *
     * @return string
     */
    public function getDocumentNumber() {
        return $this->getInvoiceNumber();
    }

    /**
     * @param string $invoiceNumber
     */
    protected function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * Visszaadja a számla azonosítót
     *
     * @return int
     */
    public function getInvoiceIdentifier() {
        return $this->invoiceIdentifier;
    }

    /**
     * @param int $invoiceIdentifier
     */
    protected function setInvoiceIdentifier($invoiceIdentifier) {
        $this->invoiceIdentifier = $invoiceIdentifier;
    }

    /**
     * Visszaadja a válasz hibakódját
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
     * Visszaadja a válasz hibaüzenetét
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
     * @return false|string
     */
    public function getPdfFile() {
        $pdfData = SzamlaAgentUtil::isNotNull($this->getPdfData()) ? $this->getPdfData() : '';
        return base64_decode($pdfData);
    }

    /**
     * Visszaadja a számlához tartozó PDF adatait
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
     * Visszaadja a válasz sikerességét
     *
     * @return bool
     */
    public function isSuccess() {
        return ($this->success && $this->isNotError());
    }

    /**
     * Visszaadja, hogy a számla kiállítása sikertelen volt-e
     *
     * @return bool
     */
    public function isError() {
        $result = false;
        if (!empty($this->getErrorMessage()) || !empty($this->getErrorCode())) {
            $result = true;
        }
        // Ha a számlaértesítő kézbesítése sikertelen volt, de a válasz tartalmaz számlaszámot, akkor a számla kiállítása sikeres.
        if ($this->hasInvoiceNumber() && $this->hasInvoiceNotificationSendError()) {
            $result = false;
        }
        return $result;
    }

    /**
     * Visszaadja, hogy nem történt-e hiba
     *
     * @return bool
     */
    public function isNotError() {
        return !$this->isError();
    }

    /**
     * @param bool $success
     */
    protected function setSuccess($success) {
        $this->success = $success;
    }

    /**
     * Visszaadja a vevői fiók URL-jét
     *
     * @return string
     */
    public function getUserAccountUrl() {
        return urldecode($this->userAccountUrl);
    }

    /**
     * @param string $userAccountUrl
     */
    protected function setUserAccountUrl($userAccountUrl) {
        $this->userAccountUrl = $userAccountUrl;
    }

    /**
     * Visszaadja a kintlévőség összegét
     *
     * @return int
     */
    public function getAssetAmount() {
        return $this->assetAmount;
    }

    /**
     * @param int $assetAmount
     */
    protected function setAssetAmount($assetAmount) {
        $this->assetAmount = $assetAmount;
    }

    /**
     * Visszaadja a nettó összeget
     *
     * @return int
     */
    public function getNetPrice() {
        return $this->netPrice;
    }

    /**
     * @param int $netPrice
     */
    protected function setNetPrice($netPrice) {
        $this->netPrice = $netPrice;
    }

    /**
     * Visszaadja a bruttó összeget
     *
     * @return int
     */
    public function getGrossAmount() {
        return $this->grossAmount;
    }

    /**
     * @param $grossAmount
     */
    protected function setGrossAmount($grossAmount) {
        $this->grossAmount = $grossAmount;
    }

    /**
     * Visszaadja a válasz fejléc adatait
     *
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    protected function setHeaders($headers) {
        $this->headers = $headers;
    }

    /**
     * Visszaadja, hogy a számlaértesítő kézbesítése sikertelen volt-e
     *
     * @return boolean
     */
    public function hasInvoiceNotificationSendError() {
        if ($this->getErrorCode() == self::INVOICE_NOTIFICATION_SEND_FAILED) {
            return true;
        }
        return false;
    }
}