<?php

namespace SzamlaAgent;

use SzamlaAgent\Response\SzamlaAgentResponse;

/**
 * A Számla Agent beállításait kezelő osztály
 *
 * @package SzamlaAgent
 */
class SzamlaAgentSetting {

    /**
     * Alapértelmezett számlamásolatok darabszám
     */
    const DOWNLOAD_COPIES_COUNT = 1;

    /**
     * Számla Agent kulcs hossza
     */
    const API_KEY_LENGTH = 42;

    /**
     * Számla Agent kéréshez használt felhasználónév
     * A felhasználónév a https://www.szamlazz.hu/szamla/login oldalon használt e-mail cím vagy bejelentkezési név.
     *
     * @var string
     */
    private $username = '';

    /**
     * Számla Agent kéréshez használt jelszó
     * A jelszó a https://www.szamlazz.hu/szamla/login/ oldalon használt bejelentkezési jelszó.
     *
     * @var string
     */
    private $password = '';

    /**
     * Számla Agent kéréshez használt kulcs
     *
     * @link https://www.szamlazz.hu/blog/2019/07/szamla_agent_kulcsok/
     */
    private $apiKey;

    /**
     * Szeretnénk-e PDF formátumban is megkapni a bizonylatot?
     *
     * @var bool
     */
    private $downloadPdf = true;

    /**
     * Letöltendő bizonylat másolatainak száma
     *
     * Amennyiben az Agenttel papír alapú számlát készít és kéri a számlaletöltést ($downloadPdf = true),
     * akkor opcionálisan megadható, hogy nem csak a számla eredeti példányát kéri, hanem a másolatot is egyetlen pdf-ben.
     *
     * @var int
     */
    private $downloadCopiesCount;

    /**
     * Számla Agent válaszának (response) típusa
     *
     * 1: RESULT_AS_TEXT - egyszerű szöveges válaszüzenetet vagy pdf-et ad vissza.
     * 2: RESULT_AS_XML - xml válasz, ha kérte a pdf-et az base64 kódolással benne van az xml-ben.
     *
     * @var int
     */
    private $responseType;

    /**
     * Ha bérelhető webáruházat üzemeltetsz, ebben a mezőben jelezheted a webáruházat futtató motor nevét.
     * Ha nem vagy benne biztos, akkor kérd ügyfélszolgálatunk segítségét (info@szamlazz.hu).
     * (pl. WooCommerce, OpenCart, PrestaShop, Shoprenter, Superwebáruház, Drupal invoice Agent, stb.)
     *
     * @var string
     */
    private $aggregator;

    /**
     * @var bool
     */
    private $guardian;

    /**
     * @var bool
     */
    private $invoiceItemIdentifier;

    /**
     * A számlát a külső rendszer (Számla Agentet használó rendszer) ezzel az adattal azonosítja. Az adatot trimmelve tároljuk.
     * (a számla adatai később ezzel az adattal is lekérdezhetők lesznek)
     *
     * @var string
     */
    private $invoiceExternalId;

    /**
     * @var string
     */
    private $taxNumber;


    /**
     * Számla Agent beállítás létrehozása
     *
     * @param string $username     szamlazz.hu fiók felhasználónév vagy e-mail cím
     * @param string $password     szamlazz.hu fiók jelszava
     * @param string $apiKey       SzámlaAgent kulcs
     * @param bool   $downloadPdf  szeretnénk-e PDF formátumban is megkapni a bizonylatot
     * @param int    $copiesCount  bizonylat másolatok száma, ha PDF letöltést választottuk
     * @param int    $responseType válasz típusa (szöveges vagy XML)
     * @param string $aggregator   webáruházat futtató motor neve
     */
    function __construct($username, $password, $apiKey, $downloadPdf = true, $copiesCount = self::DOWNLOAD_COPIES_COUNT, $responseType = SzamlaAgentResponse::RESULT_AS_TEXT, $aggregator = '') {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setApiKey($apiKey);
        $this->setDownloadPdf($downloadPdf);
        $this->setDownloadCopiesCount($copiesCount);
        $this->setResponseType($responseType);
        $this->setAggregator($aggregator);
    }

    /**
     * Visszaadja a Számla Agent kéréshez használt felhasználónevet
     *
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Beállítja a Számla Agent kéréshez használt felhasználónevet
     * A felhasználónév a https://www.szamlazz.hu/szamla/login oldalon használt e-mail cím vagy bejelentkezési név.
     *
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Visszaadja a Számla Agent kéréshez használt jelszót
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Beállítja a Számla Agent kéréshez használt jelszót
     * A jelszó a https://www.szamlazz.hu/szamla/login/ oldalon használt bejelentkezési jelszó.
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Visszaadja a Számla Agent kéréshez használt kulcsot
     *
     * @return string
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * Beállítja a Számla Agent kéréshez használt kulcsot
     *
     * @link  https://www.szamlazz.hu/blog/2019/07/szamla_agent_kulcsok/
     * @param string $apiKey
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Visszaadja, hogy a Agent válaszában megkapjuk-e a számlát PDF-ként
     *
     * @return bool
     */
    public function isDownloadPdf() {
        return $this->downloadPdf;
    }

    /**
     * Beállítja, hogy a Agent válaszában megkapjuk-e a számlát PDF-ként
     *
     * @param bool $downloadPdf
     */
    public function setDownloadPdf($downloadPdf) {
        $this->downloadPdf = $downloadPdf;
    }


    /**
     * Visszaadja a letöltendő PDF-ben szereplő bizonylat másolatainak számát
     *
     * @return int
     */
    public function getDownloadCopiesCount() {
        return $this->downloadCopiesCount;
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
        $this->downloadCopiesCount = $downloadCopiesCount;
    }

    /**
     * Visszaadja a Számla Agent válaszának típusát
     *
     * @return int
     */
    public function getResponseType() {
        return $this->responseType;
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
        $this->responseType = $responseType;
    }

    /**
     * Visszaadja a bérelhető webáruházat futtató motor nevét
     *
     * @return string
     */
    public function getAggregator() {
        return $this->aggregator;
    }

    /**
     * Ha bérelhető webáruházat üzemeltetsz, beállítja a webáruházat futtató motor nevét.
     * Ha nem vagy benne biztos, akkor kérd ügyfélszolgálatunk segítségét (info@szamlazz.hu).
     * (pl. WooCommerce, OpenCart, PrestaShop, Shoprenter, Superwebáruház, Drupal invoice Agent, stb.)
     *
     * @param string $aggregator
     */
    public function setAggregator($aggregator) {
        $this->aggregator = $aggregator;
    }

    /**
     * @return bool
     */
    public function getGuardian() {
        return $this->guardian;
    }

    /**
     * @param bool $guardian
     */
    public function setGuardian($guardian) {
        $this->guardian = $guardian;
    }

    /**
     * @return bool
     */
    public function isInvoiceItemIdentifier() {
        return $this->invoiceItemIdentifier;
    }

    /**
     * @param bool $invoiceItemIdentifier
     */
    public function setInvoiceItemIdentifier($invoiceItemIdentifier) {
        $this->invoiceItemIdentifier = $invoiceItemIdentifier;
    }

    /**
     * @return string
     */
    public function getInvoiceExternalId() {
        return $this->invoiceExternalId;
    }

    /**
     * Beállítja a külső számlaazonosítót
     *
     * A számlát a külső rendszer (Számla Agentet használó rendszer) ezzel az adattal azonosítja. Az adatot trimmelve tároljuk.
     * (a számla adatai később ezzel az adattal is lekérdezhetők lesznek)
     *
     * @param string $invoiceExternalId
     */
    public function setInvoiceExternalId($invoiceExternalId) {
        $this->invoiceExternalId = $invoiceExternalId;
    }

    /**
     * @return string
     */
    public function getTaxNumber() {
        return $this->taxNumber;
    }

    /**
     * @param string $taxNumber
     */
    public function setTaxNumber($taxNumber) {
        $this->taxNumber = $taxNumber;
    }

    /**
     * Összeállítja a Számla Agent beállítás XML adatait
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        $settings = ['felhasznalo', 'jelszo', 'szamlaagentkulcs'];

        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_INVOICE:
                $data = $this->buildFieldsData($request, array_merge($settings, ['eszamla', 'szamlaLetoltes', 'szamlaLetoltesPld', 'valaszVerzio', 'aggregator', 'guardian', 'cikkazoninvoice', 'szamlaKulsoAzon']));
                break;
            case $request::XML_SCHEMA_DELETE_PROFORMA:
                $data = $this->buildFieldsData($request, $settings);
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_INVOICE:
                $data = $this->buildFieldsData($request, array_merge($settings, ['eszamla', 'szamlaLetoltes', 'szamlaLetoltesPld', 'aggregator', 'guardian', 'valaszVerzio', 'szamlaKulsoAzon']));
                break;
            case $request::XML_SCHEMA_PAY_INVOICE:
                $data = $this->buildFieldsData($request, array_merge($settings, ['szamlaszam', 'adoszam', 'additiv', 'aggregator', 'valaszVerzio']));
                break;
            case $request::XML_SCHEMA_REQUEST_INVOICE_XML:
                $data = $this->buildFieldsData($request, array_merge($settings, ['szamlaszam', 'rendelesSzam', 'pdf']));
                break;
            case $request::XML_SCHEMA_REQUEST_INVOICE_PDF:
                $data = $this->buildFieldsData($request, array_merge($settings, ['szamlaszam', 'rendelesSzam', 'valaszVerzio', 'szamlaKulsoAzon']));
                break;
            case $request::XML_SCHEMA_CREATE_RECEIPT:
            case $request::XML_SCHEMA_CREATE_REVERSE_RECEIPT:
            case $request::XML_SCHEMA_GET_RECEIPT:
                $data = $this->buildFieldsData($request, array_merge($settings, ['pdfLetoltes']));
                break;
            case $request::XML_SCHEMA_SEND_RECEIPT:
            case $request::XML_SCHEMA_TAXPAYER:
                $data = $this->buildFieldsData($request, $settings);
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS . ": {$request->getXmlName()}");
        }
        return $data;
    }

    /**
     * Összeállítja és visszaadja az adott mezőkhöz tartozó adatokat
     *
     * @param SzamlaAgentRequest $request
     * @param array              $fields
     *
     * @return array
     * @throws SzamlaAgentException
     */
    private function buildFieldsData(SzamlaAgentRequest $request, array $fields) {
        $data = [];

        foreach ($fields as $key) {
            switch ($key) {
                case 'felhasznalo':       $value = $this->getUsername(); break;
                case 'jelszo':            $value = $this->getPassword(); break;
                case 'szamlaagentkulcs':  $value = $this->getApiKey();   break;
                case 'szamlaLetoltes':
                case 'pdf':
                case 'pdfLetoltes':       $value = $this->isDownloadPdf(); break;
                case 'szamlaLetoltesPld': $value = $this->getDownloadCopiesCount(); break;
                case 'valaszVerzio':      $value = $this->getResponseType(); break;
                case 'aggregator':        $value = $this->getAggregator(); break;
                case 'guardian':          $value = $this->getGuardian(); break;
                case 'cikkazoninvoice':   $value = $this->isInvoiceItemIdentifier(); break;
                case 'szamlaKulsoAzon':   $value = $this->getInvoiceExternalId(); break;
                case 'eszamla':           $value = $request->getEntity()->getHeader()->isEInvoice(); break;
                case 'additiv':           $value = $request->getEntity()->isAdditive(); break;
                case 'szamlaszam':        $value = $request->getEntity()->getHeader()->getInvoiceNumber(); break;
                case 'rendelesSzam':      $value = $request->getEntity()->getHeader()->getOrderNumber(); break;
                case 'adoszam':           $value = $this->getTaxNumber(); break;
                default:
                    throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS . ": {$key}");
            }

            if (isset($value)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}