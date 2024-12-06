<?php

namespace SzamlaAgent\Response;

use SzamlaAgent\Document\Document;
use SzamlaAgent\Document\Invoice\Invoice;
use SzamlaAgent\Header\InvoiceHeader;
use SzamlaAgent\Log;
use SzamlaAgent\SimpleXMLExtended;
use SzamlaAgent\SzamlaAgent;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * A Számla Agent választ kezelő osztály
 *
 * @package SzamlaAgent\Response
 */
class SzamlaAgentResponse {

    /**
     * Számla Agent kérésre adott válaszban szöveges válasz érkezik
     */
    const RESULT_AS_TEXT = 1;

    /**
     * Számla Agent kérésre adott válasz XML formátumú lesz
     */
    const RESULT_AS_XML = 2;

    /**
     * Számla Agent kérésre adott válasz a NAV Online Számla Rendszer által visszaadott XML formátumú lesz
     * @see https://onlineszamla.nav.gov.hu/dokumentaciok
     */
    const RESULT_AS_TAXPAYER_XML = 3;

    /**
     * @var SzamlaAgent
     */
    private $agent;

    /**
     * A teljes válasz (fejléc és tartalom)
     *
     * @var array
     */
    private $response;

    /**
     * @var int
     */
    private $httpCode;

    /**
     * Hibaüzenet
     *
     * @var string
     */
    private $errorMsg = '';

    /**
     * Hibakód
     *
     * @var int
     */
    private $errorCode;

    /**
     * Bizonylatszám
     * (számlaszám, díjbekérő szám, stb.)
     *
     * @var string
     */
    private $documentNumber;

    /**
     * Válaszban kapott XML
     *
     * @var \SimpleXMLElement
     */
    private $xmlData;

    /**
     * A válaszban kapott PDF fájl
     *
     * @var string
     */
    private $pdfFile;

    /**
     * A válasz szöveges tartalma, ha nem PDF
     *
     * @var string
     */
    private $content;

    /**
     * A válasz adatait tartalmazó objektum
     *
     * @var object
     */
    private $responseObj;

    /**
     * XML séma típusa (számla, nyugta, adózó)
     *
     * @var string
     */
    private $xmlSchemaType;

    /**
     * Mentett PDF fálj neve
     *
     * @var string
     */
    private $previewFileName;


    /**
     * Számla Agent válasz létrehozása
     *
     * @param SzamlaAgent $agent
     * @param array       $response
     */
    public function __construct(SzamlaAgent $agent, array $response) {
        $this->setAgent($agent);
        $this->setResponse($response);
        $this->setXmlSchemaType($response['headers']['schema-type']);
    }

    /**
     * Számla Agent válasz feldolgozása
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function handleResponse() {
        $response = $this->getResponse();
        $agent    = $this->getAgent();

        if (empty($response) || $response === null) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_IS_EMPTY);
        }

        if (isset($response['headers']) && !empty($response['headers'])) {
            $headers = array_change_key_case($response['headers'], CASE_LOWER);

            if (isset($headers['szlahu_down']) && SzamlaAgentUtil::isNotBlank($headers['szlahu_down'])) {
                throw new SzamlaAgentException(SzamlaAgentException::SYSTEM_DOWN, 500);
            }
        } else {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_NO_HEADER);
        }

        if (!isset($response['body']) || empty($response['body'])) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_NO_CONTENT);
        }

        if (array_key_exists('http_code', $headers)) {
            $this->setHttpCode($headers['http_code']);
        }

        // XML adatok beállítása és a fájl létrehozása
        if ($this->isXmlResponse()) {
            $this->buildResponseXmlData();
        } else {
            $this->buildResponseTextData();
        }

        $this->buildResponseObjData();
        if ($agent->isXmlFileSave() && $agent->isResponseXmlFileSave()) {
            $this->createXmlFile($this->getXmlData());
        }
        $this->checkFields();

        if ($this->hasInvoiceNotificationSendError()) {
            $agent->writeLog(SzamlaAgentException::INVOICE_NOTIFICATION_SEND_FAILED, Log::LOG_LEVEL_DEBUG);
        }

        if ($this->isFailed()) {
            throw new SzamlaAgentException( SzamlaAgentException::AGENT_ERROR . ": [{$this->getErrorCode()}], {$this->getErrorMsg()}");
        } else if ($this->isSuccess()) {
            $agent->writeLog("Agent hívás sikeresen befejeződött.", Log::LOG_LEVEL_DEBUG);

            if ($this->isNotTaxPayerXmlResponse()) {
                try {
                    $responseObj = $this->getResponseObj();
                    $this->setDocumentNumber($responseObj->getDocumentNumber());
                    if ($agent->isDownloadPdf()) {
                        $pdfData = $responseObj->getPdfFile();
                        $xmlName = $agent->getRequest()->getXmlName();
                        if (empty($pdfData) && !in_array($xmlName, [SzamlaAgentRequest::XML_SCHEMA_SEND_RECEIPT, SzamlaAgentRequest::XML_SCHEMA_PAY_INVOICE])) {
                            throw new SzamlaAgentException(SzamlaAgentException::DOCUMENT_DATA_IS_MISSING);
                        } else if (!empty($pdfData)) {
                            $this->setPdfFile($pdfData);

                            if ($agent->isPdfFileSave()) {
                                $file = file_put_contents($this->getPdfFileName(), $pdfData);

                                if ($file !== false) {
                                    $agent->writeLog(SzamlaAgentException::PDF_FILE_SAVE_SUCCESS . ': ' . $this->getPdfFileName(), Log::LOG_LEVEL_DEBUG);
                                } else {
                                    $errorMsg = SzamlaAgentException::PDF_FILE_SAVE_FAILED . ': ' . SzamlaAgentException::FILE_CREATION_FAILED;
                                    $agent->writeLog($errorMsg, Log::LOG_LEVEL_DEBUG);
                                    throw new SzamlaAgentException($errorMsg);
                                }
                            }
                        }
                    } else {
                        $this->setContent($response['body']);
                    }
                } catch (\Exception $e) {
                    $agent->writeLog(SzamlaAgentException::PDF_FILE_SAVE_FAILED . ': ' . $e->getMessage(), Log::LOG_LEVEL_DEBUG);
                    throw $e;
                }
            }
        }
        return $this;
    }

    /**
     * Ellenőrzi a válasz mezőit
     *
     * @throws SzamlaAgentException
     */
    private function checkFields() {
        $response = $this->getResponse();

        if ($this->isAgentInvoiceResponse()) {
            $keys = implode(",", array_keys($response['headers']));
            if (!preg_match('/(szlahu_)/', $keys, $matches)) {
                throw new SzamlaAgentException(SzamlaAgentException::NO_SZLAHU_KEY_IN_HEADER);
            }
        }
    }

    /**
     * Létrehozza a válasz adatait tartalmazó XML fájlt
     *
     * @param  \SimpleXMLElement $xml
     *
     * @throws SzamlaAgentException
     * @throws \ReflectionException
     */
    private function createXmlFile(\SimpleXMLElement $xml) {
        $agent  = $this->getAgent();

        if ($this->isTaxPayerXmlResponse()) {
            $response = $this->getResponse();
            $xml = SzamlaAgentUtil::formatResponseXml($response['body']);
        } else {
            $xml = SzamlaAgentUtil::formatXml($xml);
        }

        $type   = $agent->getResponseType();

        $name = '';
        if ($this->isFailed()) {
            $name = 'error-';
        }
        $name .= strtolower($agent->getRequest()->getXmlName());

        switch ($type) {
            case self::RESULT_AS_XML:
            case self::RESULT_AS_TAXPAYER_XML: $postfix = "-xml"; break;
            case self::RESULT_AS_TEXT:         $postfix = "-text"; break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::RESPONSE_TYPE_NOT_EXISTS . " ($type)");
        }

        $fileName = SzamlaAgentUtil::getXmlFileName('response', $name . $postfix, $agent->getRequest()->getEntity());
        $xmlSaved = $xml->save($fileName);

        if (!$xmlSaved) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_FILE_SAVE_FAILED);
        }
        $agent->writeLog("XML fájl mentése sikeres: " . SzamlaAgentUtil::getRealPath($fileName), Log::LOG_LEVEL_DEBUG);
    }

    /**
     * Visszaadja a PDF fájl nevét, amennyiben a PDF file-ok mentése be van kapcsolva
     *
     * @param bool $withPath
     *
     * @return string
     */
    public function getPdfFileName($withPath = true) {
        $header = $this->getAgent()->getRequestEntityHeader();

        if ($header instanceof InvoiceHeader && $header->isPreviewPdf()) {

            if (SzamlaAgentUtil::isBlank($this->getPreviewFileName())) {
                $entity = $this->getAgent()->getRequestEntity();
                $name = '';
                if ($entity != null && $entity instanceof Invoice) {
                    try {
                        $name .= (new \ReflectionClass($entity))->getShortName() . '-';
                    } catch (\ReflectionException $e) {}
                }
                $this->setPreviewFileName(strtolower($name) . 'preview-' . SzamlaAgentUtil::getDateTimeWithMilliseconds());
            }

            $documentNumber = $this->getPreviewFileName();
        } else {
            $documentNumber = $this->getDocumentNumber();
        }

        if ($withPath) {
            return $this->getPdfFileAbsPath($documentNumber . '.pdf');
        } else {
            return $documentNumber . '.pdf';
        }
    }

    /**
     * Visszaadja a PDF fájl teljes elérési útvonalát
     *
     * @param $pdfFileName
     *
     * @return bool|string
     */
    protected function getPdfFileAbsPath($pdfFileName) {
        return SzamlaAgentUtil::getAbsPath(SzamlaAgent::PDF_FILE_SAVE_PATH, $pdfFileName);
    }

    /**
     * Letölti a válaszban kapott PDF fájlt (ha létezik)
     *
     * @return bool
     */
    public function downloadPdf($fileName = null) {
        $pdfFileName = $this->getPdfFileName(false);

        if (SzamlaAgentUtil::isNotBlank($pdfFileName)) {
            header("Content-type:application/pdf");
            header("Content-Disposition:attachment;filename=" . (is_null($fileName) ? $pdfFileName : $fileName . '.pdf'));
            readfile($this->getPdfFileAbsPath($pdfFileName));
            return true;
        }
        return false;
    }

    /**
     * Visszaadja a válasz sikerességét
     *
     * @return bool
     */
    public function isSuccess() {
        return !$this->isFailed();
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e hibát
     *
     * @return bool
     */
    public function isFailed() {
        $result = true;
        $obj = $this->getResponseObj();
        if ($obj != null) {
            $result = $obj->isError();
        }
        return $result;
    }

    /**
     * Visszaadja a válaszhoz tartozó Agent objektumot
     *
     * @return SzamlaAgent
     */
    private function getAgent() {
        return $this->agent;
    }

    /**
     * @param SzamlaAgent $agent
     */
    private function setAgent($agent) {
        $this->agent = $agent;
    }

    /**
     * Visszaadja a kapott választ
     *
     * @return array
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param array $response
     */
    private function setResponse(array $response) {
        $this->response = $response;
    }

    /**
     * @return int
     */
    public function getHttpCode() {
        return $this->httpCode;
    }

    /**
     * @param int $httpCode
     */
    private function setHttpCode($httpCode) {
        $this->httpCode = $httpCode;
    }

    /**
     * Visszaadja a hibaüzenetet
     *
     * @return string
     */
    public function getErrorMsg() {
        return $this->errorMsg;
    }

    /**
     * @param string $errorMsg
     */
    private function setErrorMsg($errorMsg) {
        $this->errorMsg = $errorMsg;
    }

    /**
     * Visszaadja a hibakódot
     *
     * @return int
     */
    public function getErrorCode() {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    private function setErrorCode($errorCode) {
        $this->errorCode = $errorCode;
    }

    /**
     * Visszaadja a bizonylatszámot
     *
     * @return string
     */
    public function getDocumentNumber() {
        return $this->documentNumber;
    }

    /**
     * @param string $documentNumber
     */
    private function setDocumentNumber($documentNumber) {
        $this->documentNumber = $documentNumber;
    }

    /**
     * @param string $pdfFile
     */
    private function setPdfFile($pdfFile) {
        $this->pdfFile = $pdfFile;
    }

    /**
     * @return \SimpleXMLElement
     */
    protected function getXmlData() {
        return $this->xmlData;
    }

    /**
     * @param \SimpleXMLElement $xmlData
     */
    protected function setXmlData(\SimpleXMLElement $xmlData) {
        $this->xmlData = $xmlData;
    }

    /**
     * Visszaadja a válasz szöveges tartalmát
     *
     * @return string
     */
    protected function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     */
    protected function setContent($content) {
        $this->content = $content;
    }

    /**
     * Visszaadja az adózó adatait formázott szövegként
     *
     * @return string
     *
     * @deprecated 2.9.10
     */
    public function getTaxPayerStr() {
        $result = '';
        if ($this->isTaxPayerXmlResponse()) {
            $result = $this->getResponseObj()->getTaxPayerStr();
        }
        return $result;
    }

    /**
     * Visszaadja a válasz XML séma típusát
     *
     * @return string
     */
    public function getXmlSchemaType() {
        return $this->xmlSchemaType;
    }

    /**
     * @param string $xmlSchemaType
     */
    protected function setXmlSchemaType($xmlSchemaType) {
        $this->xmlSchemaType = $xmlSchemaType;
    }

    /**
     * Visszaadja a választ tartalmazó objektumot
     *
     * @return object
     */
    public function getResponseObj() {
        return $this->responseObj;
    }

    /**
     * @param object $responseObj
     */
    public function setResponseObj($responseObj) {
        $this->responseObj = $responseObj;
    }

    protected function isAgentInvoiceTextResponse() {
        return ($this->isAgentInvoiceResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_TEXT);
    }

    protected function isAgentInvoiceXmlResponse() {
        return ($this->isAgentInvoiceResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_XML);
    }

    protected function isAgentReceiptTextResponse() {
        return ($this->isAgentReceiptResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_TEXT);
    }

    protected function isAgentReceiptXmlResponse() {
        return ($this->isAgentReceiptResponse() && $this->getAgent()->getResponseType() == self::RESULT_AS_XML);
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'adózó' típusú volt-e
     *
     * @return bool
     */
    public function isTaxPayerXmlResponse() {
        $result = true;

        if ($this->getXmlSchemaType() != 'taxpayer') {
            return false;
        }

        if ($this->getAgent()->getResponseType() != self::RESULT_AS_TAXPAYER_XML) {
            $result = false;
        }
        return $result;
    }

    /**
     * Visszaadja, hogy a válasz XML séma nem 'adózó' típusú volt-e
     *
     * @return bool
     */
    public function isNotTaxPayerXmlResponse() {
        return !$this->isTaxPayerXmlResponse();
    }

    protected function isXmlResponse() {
        return ($this->isAgentInvoiceXmlResponse() || $this->isAgentReceiptXmlResponse() || $this->isTaxPayerXmlResponse());
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'számla' típusú volt-e
     *
     * @return bool
     */
    public function isAgentInvoiceResponse() {
        return ($this->getXmlSchemaType() == Document::DOCUMENT_TYPE_INVOICE);
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'díjbekérő' típusú volt-e
     *
     * @return bool
     */
    public function isAgentProformaResponse() {
        return ($this->getXmlSchemaType() == Document::DOCUMENT_TYPE_PROFORMA);
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'nyugta' típusú volt-e
     *
     * @return bool
     */
    public function isAgentReceiptResponse() {
        return ($this->getXmlSchemaType() == Document::DOCUMENT_TYPE_RECEIPT);
    }

    /**
     * Visszaadja, hogy a válasz XML séma típusa 'adózó' volt-e
     *
     * @return bool
     */
    public function isTaxPayerResponse() {
        return ($this->getXmlSchemaType() == 'taxpayer');
    }

    private function buildResponseTextData() {
        $response = $this->getResponse();
        $xmlData = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response></response>');
        $headers = $xmlData->addChild('headers');

        foreach ($response['headers'] as $key => $value) {
            $headers->addChild($key, $value);
        }

        if ($this->isAgentReceiptResponse()) {
            $content = base64_encode($response['body']);
        } else {
            $content = ($this->getAgent()->isDownloadPdf()) ? base64_encode($response['body']) : $response['body'];
        }

        $xmlData->addChild('body', $content);

        $this->setXmlData($xmlData);
    }

    private function buildResponseXmlData() {
        $response = $this->getResponse();
        if ($this->isTaxPayerXmlResponse()) {
            $xmlData = new SimpleXMLExtended($response['body']);
            $xmlData = SzamlaAgentUtil::removeNamespaces($xmlData);
        } else {
            $xmlData = new \SimpleXMLElement($response['body']);
            // Fejléc adatok hozzáadása
            $headers = $xmlData->addChild('headers');
            foreach ($response['headers'] as $key => $header) {
                $headers->addChild($key, $header);
            }
        }
        $this->setXmlData($xmlData);
    }

    /**
     * Visszaadja a válaszban kapott PDF fájlt
     *
     * @return string
     */
    public function toPdf() {
        return $this->getPdfFile();
    }

    /**
     * Visszaadja a válaszban kapott PDF fájlt
     *
     * @return string
     */
    public function getPdfFile() {
        return $this->pdfFile;
    }

    /**
     * Visszaadja a válasz adatait XML formátumban
     *
     * @return string
     */
    public function toXML() {
        if (!empty($this->getXmlData())) {
            $data = $this->getXmlData();
            return $data->asXML();
        }
        return null;
    }

    /**
     * Visszaadja a válasz adatait JSON formátumban
     *
     * @return string
     * @throws SzamlaAgentException
     */
    public function toJson() {
        $result = json_encode($this->getResponseData());
        if ($result === false || is_null($result) || !SzamlaAgentUtil::isValidJSON($result)) {
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_JSON);
        }
        return $result;
    }

    /**
     * @return mixed
     * @throws SzamlaAgentException
     */
    protected function toArray() {
        return json_decode($this->toJson(),TRUE);
    }

    /**
     * Visszaadja a válasz adatait
     *
     * @return mixed
     * @throws SzamlaAgentException
     */
    public function getData() {
        return $this->toArray();
    }

    /**
     * Visszaadja a választ tartalmazó objektumot
     *
     * @return object
     */
    public function getDataObj() {
        return $this->getResponseObj();
    }

    /**
     * @return mixed
     */
    public function getResponseData() {
        if ($this->isNotTaxPayerXmlResponse()) {
            $result['documentNumber'] = $this->getDocumentNumber();
        }

        if (!empty($this->getXmlData())) {
            $result['result'] = $this->getXmlData();
        } else {
            $result['result'] = $this->getContent();
        }
        return $result;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function buildResponseObjData() {
        $obj    = null;
        $type   = $this->getAgent()->getResponseType();
        $result = $this->getData()['result'];

        if ($this->isAgentInvoiceResponse()) {
            $obj = InvoiceResponse::parseData($result, $type);
        } elseif ($this->isAgentProformaResponse()) {
            $obj = ProformaDeletionResponse::parseData($result);
        } else if ($this->isAgentReceiptResponse()) {
            $obj = ReceiptResponse::parseData($result, $type);
        } else if ($this->isTaxPayerXmlResponse()) {
            $obj = TaxPayerResponse::parseData($result);
        }

        $this->setResponseObj($obj);

        if ($obj->isError() || $this->hasInvoiceNotificationSendError()) {
            $this->setErrorCode($obj->getErrorCode());
            $this->setErrorMsg($obj->getErrorMessage());
        }
    }

    /**
     * Visszaadja, hogy a számlaértesítő kézbesítése sikertelen volt-e
     *
     * @return boolean
     */
    public function hasInvoiceNotificationSendError() {
        if ($this->isAgentInvoiceResponse() && $this->getResponseObj()->hasInvoiceNotificationSendError()) {
            return true;
        }
        return false;
    }

    /**
     * Visszaadja az NAV-tól érkező nyers adatokat további feldolgozáshoz.
     * A kapott adatokat javasolt egy saját XML feldolgozóval kezelni.
     *
     * @return string|null
     */
    public function getTaxPayerData() {
        $data = null;
        if ($this->isTaxPayerResponse()) {
            $response = $this->getResponse();
            $data = $response['body'];
        }
        return $data;
    }

    /**
     * Visszaadja az aktuális számlázz.hu session id-t.
     * Ha a korábban beállított sessionId-hoz tartozó számlázz.hu session lejárt, új session ID-t ad vissza.
     * @return string
     */
    public function getCookieSessionId() {
        return $this->agent->getCookieSessionId();
    }

    public function getPreviewFileName() {
        return $this->previewFileName;
    }

    public function setPreviewFileName(string $previewFileName) {
        $this->previewFileName = $previewFileName;
    }

}