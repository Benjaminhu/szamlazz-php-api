<?php

namespace SzamlaAgent;

use SzamlaAgent\Document\Document;
use SzamlaAgent\Document\DeliveryNote;
use SzamlaAgent\Document\Proforma;
use SzamlaAgent\Document\Receipt\Receipt;
use SzamlaAgent\Document\Receipt\ReverseReceipt;
use SzamlaAgent\Document\Invoice\Invoice;
use SzamlaAgent\Document\Invoice\ReverseInvoice;
use SzamlaAgent\Document\Invoice\CorrectiveInvoice;
use SzamlaAgent\Document\Invoice\FinalInvoice;
use SzamlaAgent\Document\Invoice\PrePaymentInvoice;
use SzamlaAgent\Response\SzamlaAgentResponse;
use SzamlaAgent\Header\DocumentHeader;


/**
 * A Számla Agent inicializálását, az adatok küldését és fogadását kezelő osztály
 *
 * @package SzamlaAgent
 */
class SzamlaAgent {

    /**
     * Számla Agent API aktuális verzió
     */
    const API_VERSION = '2.10.18';

    /**
     * Számla Agent API url
     */
    const API_URL = 'https://www.szamlazz.hu/szamla/';

    /**
     * Számla Agent API használatához szükséges minimum PHP verzió
     */
    const PHP_VERSION = '5.6';

    /**
     * Alapértelmezett karakterkódolás
     */
    const CHARSET = 'utf-8';

    /**
     * PDF dokumentumok útvonala
     */
    const PDF_FILE_SAVE_PATH = './pdf';

    /**
     * XML fájlok útvonala
     */
    const XML_FILE_SAVE_PATH = './xmls';

    /**
     * Fájl mellékletek útvonala
     */
    const ATTACHMENTS_SAVE_PATH = './attachments';

    /**
     * Naplózási szint
     *
     * 0: LOG_LEVEL_OFF   - nincs naplózás
     * 1: LOG_LEVEL_ERROR - hibák naplózása
     * 2: LOG_LEVEL_WARN  - hibák és figyelmeztetések naplózása
     * 3: LOG_LEVEL_DEBUG - minden típus naplózása (fejlesztői mód)
     *
     * @var int
     */
    private $logLevel;

    /**
     * Naplózási e-mail cím
     * Erre az e-mail címre küldünk üzenetet, ha hiba esemény történik
     *
     * @var string
     */
    private $logEmail = '';

    /**
     * Számla Agent beállítások
     *
     * @var SzamlaAgentSetting
     */
    private $setting;

    /**
     * Az aktuális Agent kérés
     *
     * @var SzamlaAgentRequest
     */
    private $request;

    /**
     * Agent kéréshez alkalmazott timeout
     *
     * @var int
     */
    private $requestTimeout = SzamlaAgentRequest::REQUEST_TIMEOUT;

    /**
     * Az aktuális Agent válasz
     *
     * @var SzamlaAgentResponse
     */
    private $response;

    /**
     * @var SzamlaAgent[]
     */
    protected static $agents = [];

    /**
     * Egyedi HTTP fejlécek
     *
     * @var array
     */
    protected $customHTTPHeaders = [];

    /**
     * API URL
     *
     * @var string
     */
    protected $apiUrl = self::API_URL;

    /**
     * XML fájlok mentésének engedélyezése
     *
     * @var boolean
     */
    protected $xmlFileSave = true;

    /**
     * Generált (szervernek elküldött) XML fájlok mentésének engedélyezése
     *
     * @var boolean
     */
    protected $requestXmlFileSave = true;

    /**
     * Generált (szervertől visszakapott) válasz XML fájlok mentésének engedélyezése
     *
     * @var boolean
     */
    protected $responseXmlFileSave = true;

    /**
     * Generált PDF fájlok mentésének engedélyezése
     *
     * @var boolean
     */
    protected $pdfFileSave = true;

    /**
     * @var array
     */
    protected $environment = array();

    /**
     * @var CookieHandler
     */
    private $cookieHandler;

    /**
     * Számla Agent létrehozása
     *
     * @param string $username     e-mail cím vagy bejelentkezési név
     * @param string $password     jelszó
     * @param string $apiKey       Számla Agent kulcs
     * @param bool   $downloadPdf  szeretnénk-e letölteni a bizonylatot PDF formátumban
     * @param int    $logLevel     naplózási szint
     * @param int    $responseType válasz típusa (szöveges vagy XML)
     * @param string $aggregator   webáruházat futtató motor neve
     *
     */
    protected function __construct($username, $password, $apiKey, $downloadPdf, $logLevel = Log::LOG_LEVEL_DEBUG,  $responseType = SzamlaAgentResponse::RESULT_AS_TEXT, $aggregator = '') {
        $this->setSetting(new SzamlaAgentSetting($username, $password, $apiKey, $downloadPdf, SzamlaAgentSetting::DOWNLOAD_COPIES_COUNT, $responseType, $aggregator));
        $this->setLogLevel($logLevel);
        $this->setCookieHandler(new CookieHandler($this));
        $this->writeLog("Számla Agent inicializálása kész (" . (!empty($username) ? 'username: ' . $username : 'apiKey: ' . $apiKey) . ").", Log::LOG_LEVEL_DEBUG);
    }

    /**
     * Számla Agent létrehozása (felhasználónév és jelszóval)
     *
     * @param string $username    e-mail cím vagy bejelentkezési név
     * @param string $password    jelszó
     * @param bool   $downloadPdf szeretnénk-e letölteni a bizonylatot PDF formátumban
     * @param int    $logLevel    naplózási szint
     *
     * @return SzamlaAgent
     *
     * @deprecated 2.5 Nem ajánlott a használata, helyette SzamlaAgentAPI::create($apiKey);
     */
    public static function create($username, $password, $downloadPdf = true, $logLevel = Log::LOG_LEVEL_DEBUG) {
        $index = self::getHash($username);

        $agent = null;
        if (isset(self::$agents[$index])) {
            $agent = self::$agents[$index];
        }

        if ($agent === null) {
            return self::$agents[$index] = new self($username, $password, null, $downloadPdf, $logLevel);
        } else {
            return $agent;
        }
    }

    function __destruct() {
        $this->writeLog("Számla Agent műveletek befejezve." . PHP_EOL . str_repeat("_",80) . PHP_EOL, Log::LOG_LEVEL_DEBUG);
    }

    /**
     * Létrehozott Számla Agent példány visszaadása
     *
     * @param  string $instanceId  e-mail cím, bejelentkezési név vagy kulcs
     *
     * @return SzamlaAgent
     * @throws SzamlaAgentException
     */
    public static function get($instanceId) {
        $index = self::getHash($instanceId);
        $agent = self::$agents[$index];

        if ($agent === null) {
            if (strpos($instanceId, '@') === false && strlen($instanceId) == SzamlaAgentSetting::API_KEY_LENGTH) {
                throw new SzamlaAgentException(SzamlaAgentException::NO_AGENT_INSTANCE_WITH_APIKEY);
            } else {
                throw new SzamlaAgentException(SzamlaAgentException::NO_AGENT_INSTANCE_WITH_USERNAME);
            }
        }
        return $agent;
    }

    /**
     * @param $username
     *
     * @return string
     */
    protected static function getHash($username) {
        return hash('sha1', $username);
    }

    /**
     * Számla Agent kérés elküldése és a válasz visszaadása
     *
     * @param SzamlaAgentRequest $request
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    private function sendRequest(SzamlaAgentRequest $request) {
        try {
            $this->setRequest($request);
            $response = new SzamlaAgentResponse($this, $request->send());
            return $response->handleResponse();
        } catch (SzamlaAgentException $sze) {
            throw $sze;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Bizonylat elkészítése
     *
     * @param string   $type
     * @param Document $document
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateDocument($type, Document $document) {
        $request = new SzamlaAgentRequest($this, $type, $document);
        return $this->sendRequest($request);
    }

    /**
     * Számla elkészítése
     *
     * @param Invoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateInvoice(Invoice $invoice) {
        return $this->generateDocument('generateInvoice', $invoice);
    }

    /**
     * Előlegszámla elkészítése
     *
     * @param PrePaymentInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generatePrePaymentInvoice(PrePaymentInvoice $invoice) {
        return $this->generateInvoice($invoice);
    }

    /**
     * Végszámla elkészítése
     *
     * @param FinalInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateFinalInvoice(FinalInvoice $invoice) {
        return $this->generateInvoice($invoice);
    }

    /**
     * Helyesbítő számla elkészítése
     *
     * @param CorrectiveInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateCorrectiveInvoice(CorrectiveInvoice $invoice) {
        return $this->generateInvoice($invoice);
    }

    /**
     * Nyugta elkészítése
     *
     * @param Receipt $receipt
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateReceipt(Receipt $receipt) {
        return $this->generateDocument('generateReceipt', $receipt);
    }

    /**
     * Számla jóváírás rögzítése
     *
     * @param Invoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function payInvoice(Invoice $invoice) {
        if ($this->getResponseType() != SzamlaAgentResponse::RESULT_AS_TEXT) {
            $msg = 'Helytelen beállítási kísérlet a számla kifizetettségi adatok elküldésénél: a kérésre adott válaszverziónak TEXT formátumúnak kell lennie!';
            $this->writeLog($msg, Log::LOG_LEVEL_WARN);
        }
        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_TEXT);
        return $this->generateDocument('payInvoice', $invoice);
    }

    /**
     * Nyugta elküldése
     *
     * @param Receipt $receipt
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function sendReceipt(Receipt $receipt) {
        return $this->generateDocument('sendReceipt', $receipt);
    }

    /**
     * Számla adatok lekérdezése számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int    $type
     * @param bool   $downloadPdf
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getInvoiceData($data, $type = Invoice::FROM_INVOICE_NUMBER, $downloadPdf = false) {
        $invoice = new Invoice();

        if ($type == Invoice::FROM_INVOICE_NUMBER) {
            $invoice->getHeader()->setInvoiceNumber($data);
        } else {
            $invoice->getHeader()->setOrderNumber($data);
        }

        if ($this->getResponseType() !== SzamlaAgentResponse::RESULT_AS_XML) {
            $msg = 'Helytelen beállítási kísérlet a számla adatok lekérdezésénél: Számla adatok letöltéséhez a kérésre adott válasznak xml formátumúnak kell lennie!';
            $this->writeLog($msg, Log::LOG_LEVEL_WARN);
        }

        $this->setDownloadPdf($downloadPdf);
        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);

        return $this->generateDocument('requestInvoiceData', $invoice);
    }

    /**
     * Számla PDF lekérdezés számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int    $type
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getInvoicePdf($data, $type = Invoice::FROM_INVOICE_NUMBER) {
        $invoice = new Invoice();

        if ($type == Invoice::FROM_INVOICE_NUMBER) {
            $invoice->getHeader()->setInvoiceNumber($data);
        } elseif ($type == Invoice::FROM_INVOICE_EXTERNAL_ID) {
            if (SzamlaAgentUtil::isBlank($data)) {
                throw new SzamlaAgentException(SzamlaAgentException::INVOICE_EXTERNAL_ID_IS_EMPTY);
            }
            $this->getSetting()->setInvoiceExternalId($data);
        } else {
            $invoice->getHeader()->setOrderNumber($data);
        }

        if (!$this->isDownloadPdf()) {
            $msg = 'Helytelen beállítási kísérlet a számla PDF lekérdezésénél: Számla letöltéshez a "downloadPdf" paraméternek "true"-nak kell lennie!';
            $this->writeLog($msg, Log::LOG_LEVEL_WARN);
        }
        $this->setDownloadPdf(true);
        return $this->generateDocument('requestInvoicePDF', $invoice);
    }


    /**
     * Visszaadja külső számlaazonosító alapján, hogy létezik-e a számla a számlázz.hu rendszerében
     *
     * @param $invoiceExternalId
     * @return bool
     */
    public function isExistsInvoiceByExternalId($invoiceExternalId) {
        try {
            $result = $this->getInvoicePdf($invoiceExternalId, Invoice::FROM_INVOICE_EXTERNAL_ID);
            if ($result->isSuccess() && SzamlaAgentUtil::isNotBlank($result->getDocumentNumber())) {
                return true;
            }
        } catch (\Exception $e) {}

        return false;
    }

    /**
     * Nyugta adatok lekérdezése nyugtaszám alapján
     *
     * @param string $receiptNumber nyugtaszám
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getReceiptData($receiptNumber) {
        return $this->generateDocument('requestReceiptData', new Receipt($receiptNumber));
    }

    /**
     * Nyugta PDF lekérdezése nyugtaszám alapján
     *
     * @param string $receiptNumber nyugtaszám
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getReceiptPdf($receiptNumber) {
        return $this->generateDocument('requestReceiptPDF', new Receipt($receiptNumber));
    }

    /**
     * Adózó adatainak lekérdezése törzsszám alapján
     * A választ a NAV Online Számla XML formátumában kapjuk vissza
     *
     * @param string $taxPayerId
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getTaxPayer($taxPayerId) {
        $request  = new SzamlaAgentRequest($this, 'getTaxPayer', new TaxPayer($taxPayerId));
        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_TAXPAYER_XML);
        return $this->sendRequest($request);
    }

    /**
     * Sztornó számla elkészítése
     *
     * @param ReverseInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateReverseInvoice(ReverseInvoice $invoice) {
        return $this->generateDocument('generateReverseInvoice', $invoice);
    }

    /**
     * Sztornó nyugta elkészítése
     *
     * @param ReverseReceipt $receipt
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateReverseReceipt(ReverseReceipt $receipt) {
        return $this->generateDocument('generateReverseReceipt', $receipt);
    }

    /**
     * Díjbekérő elkészítése
     *
     * @param Proforma $proforma
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateProforma(Proforma $proforma) {
        return $this->generateDocument('generateProforma', $proforma);
    }

    /**
     * Díjbekérő törlése számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int    $type
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    public function getDeleteProforma($data, $type = Proforma::FROM_INVOICE_NUMBER) {
        $proforma = new Proforma();

        if ($type == Proforma::FROM_INVOICE_NUMBER) {
            $proforma->getHeader()->setInvoiceNumber($data);
        } else {
            $proforma->getHeader()->setOrderNumber($data);
        }

        $this->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);
        $this->setDownloadPdf(false);

        return $this->generateDocument('deleteProforma', $proforma);
    }

    /**
     * Szállítólevél elkészítése
     *
     * @param DeliveryNote $deliveryNote
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateDeliveryNote(DeliveryNote $deliveryNote) {
        return $this->generateDocument('generateDeliveryNote', $deliveryNote);
    }

    /**
     * @param string $message
     * @param int    $type
     *
     * @return bool
     */
    public function writeLog($message, $type = Log::LOG_LEVEL_DEBUG) {
        if ($this->logLevel < $type) {
            return false;
        }

        if ($this->logLevel != Log::LOG_LEVEL_OFF) {
            Log::writeLog($message, $type, $this->logEmail);
        }
        return true;
    }

    /**
     * @param $message
     */
    public function logError($message) {
        $this->writeLog($message, Log::LOG_LEVEL_ERROR);
    }

    /**
     * @return string
     */
    public function getApiVersion() {
        return self::API_VERSION;
    }

    /**
     * Visszaadja a naplózási szintet
     *
     * @return int
     */
    public function getLogLevel() {
        return $this->logLevel;
    }

    /**
     * Beállítja a naplózási szintet
     *
     * 0: LOG_LEVEL_OFF   - nincs naplózás
     * 1: LOG_LEVEL_ERROR - hibák naplózása
     * 2: LOG_LEVEL_WARN  - hibák és figyelmeztetések naplózása
     * 3: LOG_LEVEL_DEBUG - minden típus naplózása (fejlesztői mód)
     *
     * @var int
     */
    public function setLogLevel($logLevel) {
        if (Log::isNotValidLogLevel($logLevel)) {
            $logLevel = Log::LOG_LEVEL_DEBUG;
        }
        $this->logLevel = $logLevel;
    }

    /**
     * @return string
     */
    public function getLogEmail() {
        return $this->logEmail;
    }

    /**
     * @param string $logEmail
     */
    public function setLogEmail($logEmail) {
        $this->logEmail = $logEmail;
    }

    /**
     * @return string
     */
    public function getCookieFileName() {
        return $this->cookieHandler->getCookieFileName();
    }

    /**
     * Beállítja a kérés elküldéséhez csatolt cookie fájl nevét
     *
     * Erre akkor van szükség, ha több számlázási fiókhoz használod az Agent API-t.
     * Ebben az esetben számlázási fiókonként beállíthatod a session-hoz tartozó sütit
     *
     * @param $cookieFile
     */
    public function setCookieFileName($cookieFile) {
        $this->cookieHandler->setCookieFileName($cookieFile);
    }

    /**
     * @return SzamlaAgentSetting
     */
    public function getSetting() {
        return $this->setting;
    }

    /**
     * @param SzamlaAgentSetting $setting
     */
    public function setSetting($setting) {
        $this->setting = $setting;
    }

    /**
     * Visszaadja a már létrehozott Számla Agent példányokat
     *
     * @return SzamlaAgent[]
     */
    public static function getAgents() {
        return self::$agents;
    }

    /**
     * Visszaadja a Számla Agent kéréshez használt felhasználónevet
     *
     * @return string
     */
    public function getUsername() {
        return $this->getSetting()->getUsername();
    }

    /**
     * Beállítja a Számla Agent kéréshez használt felhasználónevet
     * A felhasználónév a https://www.szamlazz.hu/szamla/login oldalon használt e-mail cím vagy bejelentkezési név.
     *
     * @param $username
     */
    public function setUsername($username) {
        $this->getSetting()->setUsername($username);
    }

    /**
     * Visszaadja a Számla Agent kéréshez használt jelszót
     *
     * @return string
     */
    public function getPassword() {
        return $this->getSetting()->getPassword();
    }

    /**
     * Beállítja a Számla Agent kéréshez használt jelszót
     * A jelszó a https://www.szamlazz.hu/szamla/login/ oldalon használt bejelentkezési jelszó.
     *
     * @param $password
     */
    public function setPassword($password) {
        $this->getSetting()->setPassword($password);
    }

    /**
     * Visszaadja a Számla Agent kéréshez használt kulcsot
     *
     * @return string
     */
    public function getApiKey() {
        return $this->getSetting()->getApiKey();
    }

    /**
     * Beállítja a Számla Agent kéréshez használt kulcsot
     *
     * @link  https://www.szamlazz.hu/blog/2019/07/szamla_agent_kulcsok/
     * @param string $apiKey
     */
    public function setApiKey($apiKey) {
        $this->getSetting()->setApiKey($apiKey);
    }

    /**
     * @return string
     */
    public function getApiUrl() {
        if (SzamlaAgentUtil::isNotBlank($this->getEnvironmentUrl())) {
            $this->setApiUrl($this->getEnvironmentUrl());
        } else if (SzamlaAgentUtil::isBlank($this->apiUrl)) {
            $this->setApiUrl(self::API_URL);
        }
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    /**
     * Visszaadja, hogy a Agent válaszában megkapjuk-e a számlát PDF-ként
     *
     * @return bool
     */
    public function isDownloadPdf() {
        return $this->getSetting()->isDownloadPdf();
    }

    /**
     * Beállítja, hogy a Agent válaszában megkapjuk-e a számlát PDF-ként
     *
     * @param bool $downloadPdf
     */
    public function setDownloadPdf($downloadPdf) {
        $this->getSetting()->setDownloadPdf($downloadPdf);
    }

    /**
     * Visszaadja a letöltendő PDF-ben szereplő bizonylat másolatainak számát
     *
     * @return int
     */
    public function getDownloadCopiesCount() {
        return $this->getSetting()->getDownloadCopiesCount();
    }

    /**
     * Letöltendő bizonylat másolat számának beállítása
     *
     * Amennyiben az Agenttel papír alapú számlát készítesz és kéred a számlaletöltést ($downloadPdf = true),
     * akkor opcionálisan megadható, hogy nem csak a számla eredeti példányát kéred, hanem a másolatot is egyetlen pdf-ben.
     *
     * @param int $downloadCopiesCount
     */
    public function setDownloadCopiesCount($downloadCopiesCount) {
        $this->getSetting()->setDownloadCopiesCount($downloadCopiesCount);
    }

    /**
     * Visszaadja a Számla Agent válaszának típusát
     *
     * @return int
     */
    public function getResponseType() {
        return $this->getSetting()->getResponseType();
    }

    /**
     * Számla Agent válasz típusának beállítása
     *
     * 1: RESULT_AS_TEXT - egyszerű szöveges válaszüzenetet vagy pdf-et ad vissza.
     * 2: RESULT_AS_XML  - xml válasz, ha kérted a pdf-et az base64 kódolással benne van az xml-ben.
     *
     * @param int $responseType
     */
    public function setResponseType($responseType) {
        $this->getSetting()->setResponseType($responseType);
    }

    /**
     * Visszaadja a bérelhető webáruházat futtató motor nevét
     *
     * @return string
     */
    public function getAggregator() {
        return $this->getSetting()->getAggregator();
    }

    /**
     * Ha bérelhető webáruházat üzemeltetsz, beállítja a webáruházat futtató motor nevét.
     * Ha nem vagy benne biztos, akkor kérd ügyfélszolgálatunk segítségét (info@szamlazz.hu).
     * (pl. WooCommerce, OpenCart, PrestaShop, Shoprenter, Superwebáruház, Drupal invoice Agent, stb.)
     *
     * @param string $aggregator
     */
    public function setAggregator($aggregator) {
        $this->getSetting()->setAggregator($aggregator);
    }

    /**
     * @return bool
     */
    public function getGuardian() {
        return $this->getSetting()->getGuardian();
    }

    /**
     * Ne használd ezt az adattagot
     *
     * @param bool $guardian
     */
    public function setGuardian($guardian) {
        $this->getSetting()->setGuardian($guardian);
    }

    /**
     * @return string
     */
    public function getInvoiceExternalId() {
        return $this->getSetting()->getInvoiceExternalId();
    }

    /**
     * Beállítja a külső számlaazonosítót
     *
     * A számlát a külső rendszer (Számla Agentet használó rendszer) ezzel az adattal azonosítja.
     * (a számla adatai később ezzel az adattal is lekérdezhetők lesznek)
     *
     * @param string $invoiceExternalId
     */
    public function setInvoiceExternalId($invoiceExternalId) {
        $this->getSetting()->setInvoiceExternalId($invoiceExternalId);
    }

    /**
     * @return SzamlaAgentRequest
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @param SzamlaAgentRequest $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * @return SzamlaAgentResponse
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param SzamlaAgentResponse $response
     */
    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return Log
     */
    public function getLog() {
        return Log::get();
    }

    /**
     * @return array
     */
    public function getCustomHTTPHeaders() {
       return $this->customHTTPHeaders;
    }

    /**
     * Egyedi HTTP fejléc hozzáadása
     *
     * @param $key
     * @param $value
     */
    public function addCustomHTTPHeader($key, $value) {
        if (SzamlaAgentUtil::isNotBlank($key)) {
            $this->customHTTPHeaders[$key] = $value;
        } else {
            $this->writeLog('Egyedi HTTP fejléchez megadott kulcs nem lehet üres', Log::LOG_LEVEL_WARN);
        }
    }

    /**
     * Egyedi HTTP fejléc eltávolítása
     *
     * @param $key
     */
    public function removeCustomHTTPHeader($key) {
        if (SzamlaAgentUtil::isNotBlank($key)) {
            unset($this->customHTTPHeaders[$key]);
        }
    }

    /**
     * Visszaadja, hogy engedélyezve van-e a PDF mentés az alapértelmezetten beállított helyre
     *
     * @return bool
     */
    public function isPdfFileSave() {
        return $this->pdfFileSave;
    }

    /**
     * Beállítja, hogy a válaszban kapott PDF-ek el legyenek-e mentve az alapértelmezetten beállított helyre.
     * Ez a beállítás akkor hasznos, ha a válaszban kapott adatokból generált PDF-et saját magad szeretnéd előállítani
     *
     * @param bool $pdfFileSave
     */
    public function setPdfFileSave($pdfFileSave) {
        $this->pdfFileSave = $pdfFileSave;
    }

    /**
     * Visszaadja, hogy engedélyezve van-e az XML mentés az alapértelmezetten beállított helyre
     *
     * @return bool
     */
    public function isXmlFileSave() {
        return $this->xmlFileSave;
    }

    /**
     * @return bool
     */
    public function isNotXmlFileSave() {
        return !$this->isXmlFileSave();
    }

    /**
     * Beállítja, hogy engedélyezve van-e az XML mentés az alapértelmezetten beállított helyre.
     *
     * @param bool $xmlFileSave
     */
    public function setXmlFileSave($xmlFileSave) {
        $this->xmlFileSave = $xmlFileSave;
    }

    /**
     * Visszaadja, hogy engedélyezve van-e a generált (szervernek elküldött) XML fájlok mentése az alapértelmezetten beállított helyre
     *
     * @return bool
     */
    public function isRequestXmlFileSave() {
        return $this->requestXmlFileSave;
    }

    /**
     * @return bool
     */
    public function isNotRequestXmlFileSave() {
        return !$this->isRequestXmlFileSave();
    }

    /**
     * Beállítja, hogy engedélyezve van-e a generált (szervernek elküldött) XML fájlok mentése az alapértelmezetten beállított helyre.
     *
     * @param bool $requestXmlFileSave
     */
    public function setRequestXmlFileSave($requestXmlFileSave) {
        $this->requestXmlFileSave = $requestXmlFileSave;
    }

    /**
     * Visszaadja, hogy engedélyezve van-e a generált (szervertől visszakapott) válasz XML fájlok mentése az alapértelmezetten beállított helyre.
     *
     * @return bool
     */
    public function isResponseXmlFileSave() {
        return $this->responseXmlFileSave;
    }

    /**
     * Beállítja, hogy engedélyezve van-e a generált (szervertől visszakapott) válasz XML fájlok mentése az alapértelmezetten beállított helyre.
     *
     * @param bool $responseXmlFileSave
     */
    public function setResponseXmlFileSave($responseXmlFileSave) {
        $this->responseXmlFileSave = $responseXmlFileSave;
    }

    /**
     * @return Document|object
     */
    public function getRequestEntity() {
        return $this->getRequest()->getEntity();
    }

    /**
     * @return DocumentHeader|null
     */
    public function getRequestEntityHeader() {
        $header = null;

        $request = $this->getRequest();
        $entity = $request->getEntity();

        if ($entity != null && $entity instanceof Invoice) {
            $header = $entity->getHeader();
        }
        return $header;
    }

    /**
     * @return int
     */
    public function getRequestTimeout() {
        return $this->requestTimeout;
    }

    /**
     * Agent kérés timeout beállítása (másodpercben)
     *
     * @param int $timeout
     */
    public function setRequestTimeout($timeout) {
        $this->requestTimeout = $timeout;
    }

    /**
     * @return bool
     */
    public function isInvoiceItemIdentifier() {
        return $this->getSetting()->isInvoiceItemIdentifier();
    }

    /**
     * @param bool $invoiceItemIdentifier
     */
    public function setInvoiceItemIdentifier($invoiceItemIdentifier) {
        $this->getSetting()->setInvoiceItemIdentifier($invoiceItemIdentifier);
    }

    /**
     * @return array
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * @return boolean
     */
    public function hasEnvironment() {
        return ($this->environment != null && is_array($this->environment) && !empty($this->environment));
    }

    /**
     * @return string|null
     */
    public function getEnvironmentName() {
        return ($this->hasEnvironment() && array_key_exists('name', $this->environment) ? $this->environment['name'] : null);
    }

    /**
     * @return string|null
     */
    public function getEnvironmentUrl() {
        return ($this->hasEnvironment() && array_key_exists('url', $this->environment) ? $this->environment['url'] : null);
    }

    /**
     * @param string  $name
     * @param string  $url
     * @param array   $authorization
     */
    public function setEnvironment($name, $url, $authorization = array()) {
        $this->environment = array(
           'name' => $name,
           'url'  => $url,
           'auth' => $authorization
        );
    }

    /**
     * @return bool
     */
    public function hasEnvironmentAuth() {
        return $this->hasEnvironment() && array_key_exists('auth', $this->environment) && is_array($this->environment['auth']);
    }

    /**
     * @return int
     */
    public function getEnvironmentAuthType() {
        return ($this->hasEnvironmentAuth() && array_key_exists('type', $this->environment['auth']) ? $this->environment['auth']['type'] : 0);
    }

    /**
     * @return string
     */
    public function getEnvironmentAuthUser() {
        return ($this->hasEnvironmentAuth() && array_key_exists('user', $this->environment['auth']) ? $this->environment['auth']['user'] : null);
    }

    /**
     * @return string
     */
    public function getEnvironmentAuthPassword() {
        return ($this->hasEnvironmentAuth() && array_key_exists('password', $this->environment['auth']) ? $this->environment['auth']['password'] : null);
    }

    /**
     * Aktuális sütikezelési mód lekérdezése
     * @return int
     */
    public function getCookieHandleMode() {
        return $this->cookieHandler->getCookieHandleMode();
    }

    /**
     * Sütikezelési mód beállítása
     *
     * 1. Alapértelmezett mód esetén a főkönyvtárban lesznek tárolva a sütik (CookieHandler::COOKIE_HANDLE_MODE_DEFAULT)
     * 2. JSON mód használata esetén a cookie mappában lesznek tárolva a sütik (CookieHandler::COOKIE_HANDLE_MODE_JSON)
     * 3. Adatbázis mód használata esetén a tárolást magadnak kell megvalósítanod (CookieHandler::COOKIE_HANDLE_MODE_DATABASE)
     *
     * Fontos! Több számlázási fiókba való számlázás esetén erősen ajánlott az adatbázis mód használata!
     * Párhuzamos futtatás esetén (pl. cronjob) a JSON módot ne használd - használd helyette az adatbázis módot!
     *
     * @param int $cookieHandleMode
     */
    public function setCookieHandleMode($cookieHandleMode) {
        $this->cookieHandler->setCookieHandleMode($cookieHandleMode);
    }

    /**
     * A kéréshez tartozó sessionId-t adja vissza, sütikezelési módtól függetlenül.
     * @return string
     */
    public function getCookieSessionId() {
        return $this->cookieHandler->getCookieSessionId();
    }

    /**
     * Session ID beállítása
     * @param $cookieSessionId
     * @return void
     */
    public function setCookieSessionId($cookieSessionId) {
        $this->cookieHandler->setCookieSessionId($cookieSessionId);
    }

    /**
     * @return CookieHandler
     */
    public function getCookieHandler() {
        return $this->cookieHandler;
    }

    /**
     * @param CookieHandler $cookieHandler
     */
    protected function setCookieHandler($cookieHandler) {
        $this->cookieHandler = $cookieHandler;
    }
}