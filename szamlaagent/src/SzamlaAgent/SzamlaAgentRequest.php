<?php

namespace SzamlaAgent;

use SzamlaAgent\Document\Document;

/**
 * A Számla Agent kéréseket kezelő osztály
 *
 * @package SzamlaAgent
 */
class SzamlaAgentRequest {

    /**
     * Sikesességet jelző válaszkód
     */
    const HTTP_OK = 200;

    const CRLF = "\r\n";

    /**
     * Számla Agent XML séma alapértelmezett URL
     * (az XML generálásához használjuk, ne változtasd meg)
     */
    const XML_BASE_URL = 'http://www.szamlazz.hu/';

    /**
     * Számla Agent kérés maximális idő másodpercben
     */
    const REQUEST_TIMEOUT = 30;

    /**
     * Számlakészítéshez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agent/xmlszamla.xsd
     */
    const XML_SCHEMA_CREATE_INVOICE = 'xmlszamla';

    /**
     * Számla sztornózásához használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentst/xmlszamlast.xsd
     */
    const XML_SCHEMA_CREATE_REVERSE_INVOICE = 'xmlszamlast';

    /**
     * Jóváírás rögzítéséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentkifiz/xmlszamlakifiz.xsd
     */
    const XML_SCHEMA_PAY_INVOICE = 'xmlszamlakifiz';

    /**
     * Számla adatok lekéréséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentxml/xmlszamlaxml.xsd
     */
    const XML_SCHEMA_REQUEST_INVOICE_XML = 'xmlszamlaxml';

    /**
     * Számla PDF lekéréséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/agentpdf/xmlszamlapdf.xsd
     */
    const XML_SCHEMA_REQUEST_INVOICE_PDF = 'xmlszamlapdf';

    /**
     * Nyugta készítéséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtacreate/xmlnyugtacreate.xsd
     */
    const XML_SCHEMA_CREATE_RECEIPT = 'xmlnyugtacreate';

    /**
     * Nyugta sztornóhoz használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtast/xmlnyugtast.xsd
     */
    const XML_SCHEMA_CREATE_REVERSE_RECEIPT = 'xmlnyugtast';

    /**
     * Nyugta kiküldéséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtasend/xmlnyugtasend.xsd
     */
    const XML_SCHEMA_SEND_RECEIPT = 'xmlnyugtasend';

    /**
     * Nyugta megjelenítéséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/nyugtaget/xmlnyugtaget.xsd
     */
    const XML_SCHEMA_GET_RECEIPT = 'xmlnyugtaget';

    /**
     * Adózó adatainak lekérdezéséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/taxpayer/xmltaxpayer.xsd
     */
    const XML_SCHEMA_TAXPAYER = 'xmltaxpayer';

    /**
     * Díjbekérő törléséhez használt XML séma
     * @see https://www.szamlazz.hu/szamla/docs/xsds/dijbekerodel/xmlszamladbkdel.xsd
     */
    const XML_SCHEMA_DELETE_PROFORMA = 'xmlszamladbkdel';

    // Kérés engedélyezési módok
    const REQUEST_AUTHORIZATION_BASIC_AUTH = 1;


    /**
     * @var SzamlaAgent
     */
    private $agent;

    /**
     * A Számla Agent kérés típusa
     *
     * @see SzamlaAgentRequest::getActionName()
     * @var string
     */
    private $type;

    /**
     * Az az entitás, amelynek adatait XML formátumban továbbítani fogjuk
     * (számla, díjbekérő, szállítólevél, adózó, stb.)
     *
     * @var object
     */
    private $entity;

    /**
     * Az Agent kéréshez összeállított XML adatok
     *
     * @var string
     */
    private $xmlData;

    /**
     * XML gyökérelem neve
     *
     * @var string
     */
    private $xmlName;

    /**
     * XML fájl elérési útvonala
     *
     * @var string
     */
    private $xmlFilePath;

    /**
     * XSD könyvtárának neve
     *
     * @var string
     */
    private $xsdDir;

    /**
     * Számla Agent kérés XML fájlneve
     *
     * @var string
     */
    private $fileName;

    /**
     * Egyedi elválasztó azonosító az XML kéréshez
     *
     * @var string
     */
    private $delim;

    /**
     * Az Agent kérésnél továbbított POST adatok
     *
     * @var string
     */
    private $postFields;

    /**
     * Az Agent kéréshez tartozó adatok CDATA-ként lesznek átadva
     *
     * @var boolean
     */
    private $cData = true;

    /**
     * Agent kéréshez alkalmazott timeout
     *
     * @var int
     */
    private $requestTimeout;

    /**
     * @var CookieHandler
     */
    private $cookieHandler;

    /**
     * Számla Agent kérés létrehozása
     *
     * @param SzamlaAgent $agent
     * @param string      $type
     * @param object      $entity
     */
    public function __construct(SzamlaAgent $agent, $type, $entity) {
        $this->setAgent($agent);
        $this->setType($type);
        $this->setEntity($entity);
        $this->setCData(true);
        $this->setRequestTimeout($agent->getRequestTimeout());
    }

    /**
     * Összeállítja a kérés elküldéséhez szükséges XML adatokat
     *
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    private function buildXmlData() {
        $this->setXmlFileData($this->getType());
        $agent = $this->getAgent();
        $agent->writeLog("XML adatok összeállítása elkezdődött.", Log::LOG_LEVEL_DEBUG);
        $xmlData = $this->getEntity()->buildXmlData($this);

        $xml = new SimpleXMLExtended($this->getXmlBase());
        $this->arrayToXML($xmlData, $xml);
        try {
            $result = SzamlaAgentUtil::checkValidXml($xml->saveXML());
            if (!empty($result)) {
                throw new SzamlaAgentException(SzamlaAgentException::XML_NOT_VALID . " a {$result[0]->line}. sorban: {$result[0]->message}. ");
            }
            $formatXml = SzamlaAgentUtil::formatXml($xml);
            $this->setXmlData($formatXml->saveXML());
            // Ha nincs hiba az XML-ben, elmentjük
            $agent->writeLog("XML adatok létrehozása kész.", Log::LOG_LEVEL_DEBUG);
            if (($agent->isXmlFileSave() && $agent->isRequestXmlFileSave()) || version_compare(PHP_VERSION, '7.4.1') <= 0) {
                $this->createXmlFile($formatXml);
            }
        } catch (\Exception $e) {
            try {
                $formatXml = SzamlaAgentUtil::formatXml($xml);
                $this->setXmlData($formatXml->saveXML());
                if (!empty($this->getXmlData())) {
                    $xmlData = $this->getXmlData();
                }
            } catch (\Exception $ex) {
                // ha az adatok alapján nem állítható össze az XML, továbblépünk és naplózzuk az eredetileg beállított XML adatokat
            }
            $agent->writeLog(print_r($xmlData, true), Log::LOG_LEVEL_DEBUG);
            throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_BUILD_FAILED . ":  {$e->getMessage()} ");
        }
    }

    /**
     * @param array             $xmlData
     * @param SimpleXMLExtended $xmlFields
     */
    private function arrayToXML(array $xmlData, SimpleXMLExtended &$xmlFields) {
        foreach ($xmlData as $key => $value) {
            if (is_array($value)) {
                $fieldKey = $key;
                if (strpos($key, "item") !== false) $fieldKey = 'tetel';
                if (strpos($key, "note") !== false) $fieldKey = 'kifizetes';
                $subNode = $xmlFields->addChild("$fieldKey");
                $this->arrayToXML($value, $subNode);
            } else {
                if (is_bool($value)) {
                    $value = ($value) ? 'true' : 'false';
                } else if (!$this->isCData()) {
                    $value = htmlspecialchars("$value");
                }

                if ($this->isCData()) {
                    $xmlFields->addChildWithCData("$key", $value);
                } else {
                    $xmlFields->addChild("$key", $value);
                }
            }
        }
    }

    /**
     * Létrehozza a kérés adatait tartalmazó XML fájlt
     *
     * @param  \DOMDocument $xml
     *
     * @throws SzamlaAgentException
     * @throws \ReflectionException
     */
    private function createXmlFile(\DOMDocument $xml) {
        $fileName = SzamlaAgentUtil::getXmlFileName('request', $this->getXmlName(), $this->getEntity());
        $xmlSaved = $xml->save($fileName);

        if (!$xmlSaved) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_FILE_SAVE_FAILED);
        }

        $this->setXmlFilePath(SzamlaAgentUtil::getRealPath($fileName));
        $this->getAgent()->writeLog("XML fájl mentése sikeres: " . SzamlaAgentUtil::getRealPath($fileName), Log::LOG_LEVEL_DEBUG);
    }

    /**
     * Visszaadja az alapértelmezett XML fejlécet
     *
     * @return string
     */
    private function getXmlBase() {
        $xmlName = $this->getXmlName();

        $queryData = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $queryData .= '<' . $xmlName . ' xmlns="' . $this->getXmlNs($xmlName) . '" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="' . $this->getSchemaLocation($xmlName) . '">' . PHP_EOL;
        $queryData .= '</' . $xmlName . '>' . self::CRLF;

        return $queryData;
    }

    /**
     * @param $xmlName
     *
     * @return string
     */
    private function getSchemaLocation($xmlName) {
        return self::XML_BASE_URL . "szamla/{$xmlName} http://www.szamlazz.hu/szamla/docs/xsds/{$this->getXsdDir()}/{$xmlName}.xsd";
    }

    /**
     * Visszaadja az XML séma névterét
     *
     * @param $xmlName
     *
     * @return string
     */
    private function getXmlNs($xmlName) {
        return self::XML_BASE_URL . "{$xmlName}";
    }

    /**
     * Összeállítja az elküldendő POST adatokat
     */
    private function buildQuery() {
        $this->setDelim(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 16));

        $queryData  = '--' . $this->getDelim() . self::CRLF;
        $queryData .= 'Content-Disposition: form-data; name="' . $this->getFileName() . '"; filename="' . $this->getFileName() . '"' . self::CRLF;
        $queryData .= 'Content-Type: text/xml' . self::CRLF . self::CRLF;
        $queryData .= $this->getXmlData() . self::CRLF;
        $queryData .= "--" . $this->getDelim() . "--" . self::CRLF;

        $this->setPostFields($queryData);
    }

    /**
     * Beállítja a Számla Agent XML fájl adatait
     * (xml gyökérelem neve, xml fájlnév)
     *
     * @param $type
     *
     * @throws SzamlaAgentException
     */
    private function setXmlFileData($type) {
        switch ($type) {
            // Számlakészítés (normál, előleg, végszámla)
            case 'generateProforma':
            case 'generateInvoice':
            case 'generatePrePaymentInvoice':
            case 'generateFinalInvoice':
            case 'generateCorrectiveInvoice':
            case 'generateDeliveryNote':
                $fileName = 'action-xmlagentxmlfile';
                $xmlName  = self::XML_SCHEMA_CREATE_INVOICE;
                $xsdDir   = 'agent';
                break;
            // Számla sztornó
            case 'generateReverseInvoice':
                $fileName = 'action-szamla_agent_st';
                $xmlName  = self::XML_SCHEMA_CREATE_REVERSE_INVOICE;
                $xsdDir   = 'agentst';
                break;
            // Jóváírás rögzítése
            case 'payInvoice':
                $fileName = 'action-szamla_agent_kifiz';
                $xmlName  = self::XML_SCHEMA_PAY_INVOICE;
                $xsdDir   = 'agentkifiz';
                break;
            // Számla adatok lekérése
            case 'requestInvoiceData':
                $fileName = 'action-szamla_agent_xml';
                $xmlName  = self::XML_SCHEMA_REQUEST_INVOICE_XML;
                $xsdDir   = 'agentxml';
                break;
            // Számla PDF lekérése
            case 'requestInvoicePDF':
                $fileName = 'action-szamla_agent_pdf';
                $xmlName  = self::XML_SCHEMA_REQUEST_INVOICE_PDF;
                $xsdDir   = 'agentpdf';
                break;
            // Nyugta készítés
            case 'generateReceipt':
                $fileName = 'action-szamla_agent_nyugta_create';
                $xmlName  = self::XML_SCHEMA_CREATE_RECEIPT;
                $xsdDir   = 'nyugtacreate';
                break;
            // Nyugta sztornó
            case 'generateReverseReceipt':
                $fileName = 'action-szamla_agent_nyugta_storno';
                $xmlName  = self::XML_SCHEMA_CREATE_REVERSE_RECEIPT;
                $xsdDir   = 'nyugtast';
                break;
            // Nyugta kiküldés
            case 'sendReceipt':
                $fileName = 'action-szamla_agent_nyugta_send';
                $xmlName  = self::XML_SCHEMA_SEND_RECEIPT;
                $xsdDir   = 'nyugtasend';
                break;
            // Nyugta adatok lekérése
            case 'requestReceiptData':
            case 'requestReceiptPDF':
                $fileName = 'action-szamla_agent_nyugta_get';
                $xmlName = self::XML_SCHEMA_GET_RECEIPT;
                $xsdDir = 'nyugtaget';
                break;
            // Adózó adatainak lekérdezése
            case 'getTaxPayer':
                $fileName = 'action-szamla_agent_taxpayer';
                $xmlName = self::XML_SCHEMA_TAXPAYER;
                $xsdDir = 'taxpayer';
                break;
            // Díjbekérő törlése
            case 'deleteProforma':
                $fileName = 'action-szamla_agent_dijbekero_torlese';
                $xmlName = self::XML_SCHEMA_DELETE_PROFORMA;
                $xsdDir = 'dijbekerodel';
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::REQUEST_TYPE_NOT_EXISTS . ": {$type}");
        }

        $this->setFileName($fileName);
        $this->setXmlName($xmlName);
        $this->setXsdDir($xsdDir);
    }

    /**
     * Számla Agent kérés küldése a szamlazz.hu felé
     *
     * @return array
     *
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function send() {
        $this->buildXmlData();
        $this->buildQuery();
        $response = $this->makeCurlCall();


        $this->checkXmlFileSave();
        return $response;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function makeCurlCall() {
        try {
            $agent = $this->getAgent();
            $cookieHandler = $agent->getCookieHandler();

            $ch = curl_init($agent->getApiUrl());

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            if ($this->isBasicAuthRequest()) {
                curl_setopt($ch, CURLOPT_USERPWD, $this->getBasicAuthUserPwd());
            }

            $mimeType = 'text/xml';
            if (($agent->isXmlFileSave() && $agent->isRequestXmlFileSave()) || version_compare(PHP_VERSION, '7.4.1') <= 0) {
                $xmlFile = new \CURLFile($this->getXmlFilePath(), $mimeType, basename($this->getXmlFilePath()));
            } else {
                $xmlContent = 'data://application/octet-stream;base64,' . base64_encode($this->getXmlData());
                $fileName = SzamlaAgentUtil::getXmlFileName('request', $this->getXmlName(), $this->getEntity());
                $xmlFile = new \CURLFile($xmlContent, $mimeType, basename($fileName));
            }

            $postFields = array($this->getFileName() => $xmlFile);

            $httpHeaders = array(
                'charset: ' . SzamlaAgent::CHARSET,
                'PHP: ' . PHP_VERSION,
                'API: ' . SzamlaAgent::API_VERSION
            );

            if ($cookieHandler->isNotHandleModeDefault()) {
                $cookieHandler->addCookieToHeader();
            } else {
                $cookieFile = $cookieHandler->getDefaultCookieFile();
                $cookieHandler->checkCookieFile($cookieFile);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
                if ($cookieHandler->isUsableCookieFile($cookieFile)) {
                    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
                }
            }

            $customHttpHeaders = $agent->getCustomHTTPHeaders();
            if (!empty($customHttpHeaders)) {
                foreach ($customHttpHeaders as $key => $value) {
                    $httpHeaders[] = $key . ': ' . $value;
                }
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);

            if ($this->isAttachments()) {
                $attachments = $this->getEntity()->getAttachments();
                if (!empty($attachments)) {
                    for ($i = 0; $i < count($attachments); $i++) {
                        $attachCount = ($i + 1);
                        if (file_exists($attachments[$i])) {
                            $isAttachable = true;
                            foreach ($postFields as $field) {
                                if ($field->name === $attachments[$i]) {
                                    $isAttachable = false;
                                    $agent->writeLog($attachCount . ". számlamelléklet már csatolva van: " . $attachments[$i], Log::LOG_LEVEL_WARN);
                                }
                            }

                            if ($isAttachable) {
                                $attachment = new \CURLFile($attachments[$i]);
                                $attachment->setPostFilename(basename($attachments[$i]));
                                $postFields["attachfile" . $attachCount] = $attachment;
                                $agent->writeLog($attachCount . ". számlamelléklet csatolva: " . $attachments[$i], Log::LOG_LEVEL_DEBUG);
                            }
                        }
                    }
                }
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->getRequestTimeout());

            $agent->writeLog("CURL adatok elküldése elkezdődött: " . $this->getPostFields(), Log::LOG_LEVEL_DEBUG);
            $result = curl_exec($ch);

            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header     = substr($result, 0, $headerSize);
            $headers    = preg_split('/\n|\r\n?/', $header);
            $body       = substr($result, $headerSize);

            // Beállítjuk a session id-t ha kapunk újat
            $cookieHandler->handleSessionId($header);

            $response = array(
                'headers' => $this->getHeadersFromResponse($headers),
                'body'    => $body
            );

            $error = curl_error($ch);
            if (!empty($error)) {
                $agent->logError(SzamlaAgentException::CONNECTION_ERROR . ' - ' . $error);
                throw new SzamlaAgentException($error);
            } else {
                $keys = implode(",", array_keys($headers));
                if ($response['headers']['content-type'] == 'application/pdf' || (!preg_match('/(szlahu_)/', $keys, $matches))) {
                    $msg = $response['headers'];
                } else {
                    $msg = $response;
                }

                $response['headers']['schema-type'] = $this->getXmlSchemaType();
                $agent->writeLog("CURL adatok elküldése sikeresen befejeződött: " . print_r($msg, TRUE), Log::LOG_LEVEL_DEBUG);
            }
            curl_close($ch);

            // JSON mód esetén mentjük a session-höz tartozó cookie adatokat
            if ($cookieHandler->isHandleModeJson()) {
                $cookieHandler->saveSessions();
            }
            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Visszaadja a válasz fejléc adatait
     *
     * @param $headerContent
     *
     * @return array
     */
    private function getHeadersFromResponse($headerContent) {
        $headers = array();
        foreach ($headerContent as $index => $content) {
            if (SzamlaAgentUtil::isNotBlank($content)) {
                if ($index === 0) {
                    $headers['http_code'] = $content;
                } else {
                    $pos = strpos($content, ":");
                    if ($pos !== false) {
                        list ($key, $value) = explode(': ', $content);
                        $headers[strtolower($key)] = $value;
                    }
                }
            }
        }
        return $headers;
    }

    /**
     * @return SzamlaAgent
     */
    public function getAgent() {
        return $this->agent;
    }

    /**
     * @param SzamlaAgent $agent
     */
    private function setAgent($agent) {
        $this->agent = $agent;
    }

    /**
     * @return string
     */
    private function getType() {
        return $this->type;
    }

    /**
     * Beállítja a kérés típusát
     *
     * @param string $type
     * @see   SzamlaAgentRequest::getActionName()
     */
    private function setType($type) {
        $this->type = $type;
    }

    /**
     * @return object
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * @param object $entity
     */
    private function setEntity($entity) {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    private function getXmlData() {
        return $this->xmlData;
    }

    /**
     * @param string $xmlData
     */
    private function setXmlData($xmlData) {
        $this->xmlData = $xmlData;
    }

    /**
     * @return string
     */
    private function getDelim() {
        return $this->delim;
    }

    /**
     * @param string $delim
     */
    private function setDelim($delim) {
        $this->delim = $delim;
    }

    /**
     * @return string
     */
    private function getPostFields() {
        return $this->postFields;
    }

    /**
     * @param string $postFields
     */
    private function setPostFields($postFields) {
        $this->postFields = $postFields;
    }

    /**
     * @return bool
     */
    private function isCData() {
        return $this->cData;
    }

    /**
     * @param bool $cData
     */
    private function setCData($cData) {
        $this->cData = $cData;
    }

    /**
     * @return string
     */
    public function getXmlName() {
        return $this->xmlName;
    }

    /**
     * @param string $xmlName
     */
    private function setXmlName($xmlName) {
        $this->xmlName = $xmlName;
    }

    /**
     * @return string
     */
    private function getFileName() {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    private function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getXmlFilePath() {
        return $this->xmlFilePath;
    }

    /**
     * @param string $xmlFilePath
     */
    private function setXmlFilePath($xmlFilePath) {
        $this->xmlFilePath = $xmlFilePath;
    }

    /**
     * @return string
     */
    private function getXsdDir() {
        return $this->xsdDir;
    }

    /**
     * @param string $xsdDir
     */
    private function setXsdDir($xsdDir) {
        $this->xsdDir = $xsdDir;
    }

    /**
     * Visszaadja az XML séma típusát
     * (számla, nyugta, adózó)
     *
     * @return string
     * @throws SzamlaAgentException
     */
    private function getXmlSchemaType() {
        switch ($this->getXmlName()) {
            case self::XML_SCHEMA_CREATE_INVOICE:
            case self::XML_SCHEMA_CREATE_REVERSE_INVOICE:
            case self::XML_SCHEMA_PAY_INVOICE:
            case self::XML_SCHEMA_REQUEST_INVOICE_XML:
            case self::XML_SCHEMA_REQUEST_INVOICE_PDF:
                $type = Document::DOCUMENT_TYPE_INVOICE;
                break;
            case self::XML_SCHEMA_DELETE_PROFORMA:
                $type = Document::DOCUMENT_TYPE_PROFORMA;
                break;
            case self::XML_SCHEMA_CREATE_RECEIPT:
            case self::XML_SCHEMA_CREATE_REVERSE_RECEIPT:
            case self::XML_SCHEMA_SEND_RECEIPT:
            case self::XML_SCHEMA_GET_RECEIPT:
                $type = Document::DOCUMENT_TYPE_RECEIPT;
                break;
            case self::XML_SCHEMA_TAXPAYER:
                $type = 'taxpayer';
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS . ": {$this->getXmlName()}");
        }
        return $type;
    }

    private function isAttachments() {
        $entity = $this->getEntity();
        if (is_a($entity, '\SzamlaAgent\Document\Invoice\Invoice')) {
            return (count($entity->getAttachments()) > 0);
        }
        return false;
    }

    /**
     * @return bool
     */
    private function isBasicAuthRequest() {
        $agent = $this->getAgent();
        return ($agent->hasEnvironment() && $agent->getEnvironmentAuthType() == self::REQUEST_AUTHORIZATION_BASIC_AUTH);
    }

    /**
     * @return string
     */
    private function getBasicAuthUserPwd() {
        return $this->getAgent()->getEnvironmentAuthUser() . ":" . $this->getAgent()->getEnvironmentAuthPassword();
    }

    /**
     * @return int
     */
    private function getRequestTimeout() {
        if ($this->requestTimeout == 0) {
            return self::REQUEST_TIMEOUT;
        } else {
            return $this->requestTimeout;
        }
    }

    /**
     * Agent kérés timeout beállítása (másodpercben)
     *
     * @param int $timeout
     */
    private function setRequestTimeout($timeout) {
        $this->requestTimeout = $timeout;
    }

    /**
     * Ellenőrzi, hogy az XML fájl menthető-e, ha nem, akkor törli.
     *
     * @throws SzamlaAgentException
     */
    private function checkXmlFileSave() {
        if ($this->agent != null && ($this->agent->isNotXmlFileSave() || $this->agent->isNotRequestXmlFileSave())) {
            try {
                $xmlData = SzamlaAgentUtil::isNotNull($this->getXmlFilePath()) ? $this->getXmlFilePath() : '';
                if (is_file($xmlData)) {
                    unlink($this->getXmlFilePath());
                }
            } catch (\Exception $e) {
                $this->agent->writeLog('XML fájl törlése sikertelen. ' . $e->getMessage(), Log::LOG_LEVEL_WARN);
            }
        }
    }
}