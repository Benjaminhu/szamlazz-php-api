<?php

namespace SzamlaAgent\Response;

/**
 * Díjbekérő törlés kérésére adott válasz
 *
 * @package SzamlaAgent\Response
 */
class ProformaDeletionResponse {

    /**
     * Díjbekérő száma
     *
     * @var string
     */
    protected $proformaNumber;

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
     * Sikeres-e a válasz
     *
     * @var bool
     */
    protected $success = false;

    /**
     * A válasz fejléc adatai
     *
     * @var array
     */
    protected $headers;

    /**
     * Feldolgozás után visszaadja a díjbekérő törlés válaszát objektumként
     *
     * @param array $data
     *
     * @return ProformaDeletionResponse
     */
    public static function parseData(array $data) {
        $response   = new ProformaDeletionResponse();
        $headers = array_change_key_case($data['headers'], CASE_LOWER);

        if (!empty($headers)) {
            $response->setHeaders($headers);

            if (array_key_exists('szlahu_error', $headers)) {
                $error = urldecode($headers['szlahu_error']);
                $response->setErrorMessage($error);
            }

            if (array_key_exists('szlahu_error_code', $headers)) {
                $response->setErrorCode($headers['szlahu_error_code']);
            }

            if ($response->isNotError()) {
                $response->setSuccess(true);
            }
        }
        return $response;
    }

    /**
     * Visszaadja a bizonylat számát
     *
     * @return string
     */
    public function getDocumentNumber() {
        return $this->getProformaNumber();
    }

    /**
     * Visszaadja a díjbekérő számát
     *
     * @return string
     */
    public function getProformaNumber() {
        return $this->proformaNumber;
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
     * Visszaadja a válasz sikerességét
     *
     * @return bool
     */
    public function isSuccess() {
        return ($this->success && $this->isNotError());
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e hibát
     *
     * @return bool
     */
    public function isError() {
        return (!empty($this->getErrorMessage()) || !empty($this->getErrorCode()));
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
}