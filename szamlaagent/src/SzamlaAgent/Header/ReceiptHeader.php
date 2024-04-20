<?php

namespace SzamlaAgent\Header;

use SzamlaAgent\Document\Document;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugta fejléc
 *
 * @package SzamlaAgent\Header
 */
class ReceiptHeader extends DocumentHeader {

    /**
     * Nyugtaszám
     *
     * @var string
     */
    protected $receiptNumber;

    /**
     * A létrehozás egyedi azonosítója, megakadályozza a nyugta duplikált létrehozását
     *
     * @var string
     */
    protected $callId;

    /**
     * Nyugtaszám előtag
     *
     * @example NYGTA-2017-111
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Nyugta fizetési módja
     *
     * A fizetési mód bármilyen szöveg lehet vagy a felületen használt értékek egyike.
     * (lásd. a bizonylat fizetési módjainál)
     *
     * @see Document
     *
     * @var string
     */
    protected $paymentMethod;

    /**
     * Nyugta pénzneme
     *
     * @example Ft, HUF, EUR, USD stb.
     *
     * @var string
     */
    protected $currency;

    /**
     * Deviza (nem Ft/HUF) pénznem esetén az árfolyamot jegyző bank neve
     *
     * Devizás bizonylat esetén meg kell adni, hogy melyik bank árfolyamával számoltuk a bizonylaton a forintos ÁFA értéket.
     * Ha 'MNB' és nincs megadva az árfolyam ($exchangeRate), akkor az 'MNB' aktuális árfolyamát használjuk a bizonylat elkészítésekor.
     *
     * @var string
     */
    protected $exchangeBank;

    /**
     * Deviza árfolyama
     *
     * Ha nincs megadva vagy 0-t adunk meg az árfolyam ($exchangeRate) értékének és a megadott pénznem ($currency) létezik az MNB adatbázisában,
     * akkor az MNB aktuális árfolyamát használjuk a számlakészítéskor.
     *
     * @var float
     */
    protected $exchangeRate;

    /**
     * Általános szöveges megjegyzés, nyugtán megjelenik
     *
     * @var string
     */
    protected $comment;

    /**
     * Egyedi PDF sablon esetén annak azonosítója
     *
     * @var string
     */
    protected $pdfTemplate;

    /**
     * Vevő főkönyvi azonosítója
     *
     * @var string
     */
    protected $buyerLedgerId;

    /**
     * XML-ben kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields;


    /**
     * Nyugta fejléc létrehozása
     * Beállítja a nyugta fejlécének alapértelmezett adatait
     *
     * @param string $receiptNumber nyugtaszám
     */
    function __construct($receiptNumber = '') {
        $this->setReceipt(true);
        $this->setReceiptNumber($receiptNumber);
        $this->setPaymentMethod(Document::PAYMENT_METHOD_CASH);
        $this->setCurrency(Document::getDefaultCurrency());
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
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'exchangeRate':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'receiptNumber':
                case 'callId':
                case 'prefix':
                case 'paymentMethod':
                case 'currency':
                case 'exchangeBank':
                case 'comment':
                case 'pdfTemplate':
                case 'buyerLedgerId':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
                    break;
            }
        }
        return $value;
    }

    /**
     * Ellenőrizzük a mezőket
     *
     * @throws SzamlaAgentException
     */
    protected function checkFields() {
        $fields = get_object_vars($this);
        foreach ($fields as $field => $value) {
            $this->checkField($field, $value);
        }
    }

    /**
     * Összeállítja a bizonylat elkészítéséhez szükséges XML fejléc adatokat
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        try {
            if (empty($request)) {
                throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_NOT_AVAILABLE);
            }
            $requireFields = ['receiptNumber'];
            switch ($request->getXmlName()) {
                case $request::XML_SCHEMA_CREATE_RECEIPT:
                    $requireFields = ['prefix', 'paymentMethod', 'currency'];
                    $data = $this->buildFieldsData($request, [
                        'hivasAzonosito', 'elotag', 'fizmod', 'penznem', 'devizabank', 'devizaarf', 'megjegyzes', 'pdfSablon', 'fokonyvVevo'
                    ]);
                    break;
                case $request::XML_SCHEMA_CREATE_REVERSE_RECEIPT:
                    $data = $this->buildFieldsData($request, ['nyugtaszam', 'pdfSablon', 'hivasAzonosito']);
                    break;
                case $request::XML_SCHEMA_GET_RECEIPT:
                    $data = $this->buildFieldsData($request, ['nyugtaszam', 'pdfSablon']);
                    break;
                case $request::XML_SCHEMA_SEND_RECEIPT:
                    $data = $this->buildFieldsData($request, ['nyugtaszam']);
                    break;
                default:
                    throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS . ": {$request->getXmlName()}");
            }
            $this->setRequiredFields($requireFields);
            $this->checkFields();

            return $data;
        } catch (SzamlaAgentException $e) {
            throw $e;
        }
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

        if (empty($request) || !empty($field)) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_NOT_AVAILABLE);
        }

        foreach ($fields as $key) {
            switch ($key) {
                case 'hivasAzonosito': $value = (SzamlaAgentUtil::isNotBlank($this->getCallId())) ? $this->getCallId() : null; break;
                case 'elotag':         $value = $this->getPrefix(); break;
                case 'fizmod':         $value = $this->getPaymentMethod(); break;
                case 'penznem':        $value = $this->getCurrency(); break;
                case 'devizabank':     $value = (SzamlaAgentUtil::isNotBlank($this->getExchangeBank())) ? $this->getExchangeBank() : null; break;
                case 'devizaarf':      $value = (SzamlaAgentUtil::isNotNull($this->getExchangeRate())) ? SzamlaAgentUtil::doubleFormat($this->getExchangeRate()) : null; break;
                case 'megjegyzes':     $value = (SzamlaAgentUtil::isNotBlank($this->getComment())) ? $this->getComment() : null; break;
                case 'pdfSablon':      $value = (SzamlaAgentUtil::isNotBlank($this->getPdfTemplate())) ? $this->getPdfTemplate() : null; break;
                case 'fokonyvVevo':    $value = (SzamlaAgentUtil::isNotBlank($this->getBuyerLedgerId())) ? $this->getBuyerLedgerId() : null; break;
                case 'nyugtaszam':     $value = $this->getReceiptNumber(); break;
                default:
                    throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS . ": {$key}");
            }

            if (isset($value)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * A bizonylat előtagjának beállítása
     * Üres előtag esetén az alapértelmezett előtagot fogja használni a rendszer.
     *
     * @param string $prefix
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getExchangeBank() {
        return $this->exchangeBank;
    }

    /**
     * @param string $exchangeBank
     */
    public function setExchangeBank($exchangeBank) {
        $this->exchangeBank = $exchangeBank;
    }

    /**
     * @return float
     */
    public function getExchangeRate() {
        return $this->exchangeRate;
    }

    /**
     * @param float $exchangeRate
     */
    public function setExchangeRate($exchangeRate) {
        $this->exchangeRate = (float)$exchangeRate;
    }

    /**
     * @return string
     */
    public function getReceiptNumber() {
        return $this->receiptNumber;
    }

    /**
     * Nyugta sorszám beállítása
     *
     * A nyugta létrehozásánál ne használd, mert a kiállított nyugták számait a Számlázz.hu
     * a jogszabálynak megfelelően automatikusan osztja ki: 1-től indulva, kihagyásmentesen.
     * @see https://tudastar.szamlazz.hu/gyik/szamlaszam-formatumok-mikor-kell-megadni
     *
     * @param string $receiptNumber
     */
    public function setReceiptNumber($receiptNumber) {
        $this->receiptNumber = $receiptNumber;
    }

    /**
     * @return array
     */
    protected function getRequiredFields() {
        return $this->requiredFields;
    }

    /**
     * @param array $requiredFields
     */
    protected function setRequiredFields(array $requiredFields) {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @return string
     */
    public function getCallId() {
        return $this->callId;
    }

    /**
     * @param string $callId
     */
    public function setCallId($callId) {
        $this->callId = $callId;
    }

    /**
     * @return string
     */
    public function getPdfTemplate() {
        return $this->pdfTemplate;
    }

    /**
     * @param string $pdfTemplate
     */
    public function setPdfTemplate($pdfTemplate) {
        $this->pdfTemplate = $pdfTemplate;
    }

    /**
     * @return string
     */
    public function getBuyerLedgerId() {
        return $this->buyerLedgerId;
    }

    /**
     * @param string $buyerLedgerId
     */
    public function setBuyerLedgerId($buyerLedgerId) {
        $this->buyerLedgerId = $buyerLedgerId;
    }
}