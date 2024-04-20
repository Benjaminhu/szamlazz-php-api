<?php

namespace SzamlaAgent\Header;

use SzamlaAgent\Document\Document;
use SzamlaAgent\Document\Invoice\Invoice;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Számla fejléc
 *
 * @package SzamlaAgent\Header
 */
class InvoiceHeader extends DocumentHeader {

    /**
     * Számlaszám
     *
     * @var string
     */
    protected $invoiceNumber;

    /**
     * Számla típusa
     *
     * INVOICE_TYPE_P_INVOICE : papírszámla
     * INVOICE_TYPE_E_INVOICE : e-számla
     *
     * @var int
     */
    protected $invoiceType;


    /**
     * Bizonylat kelte
     * (a bizonylat kiadásának dátuma)
     *
     * @var string
     */
    protected $issueDate;

    /**
     * Bizonylat fizetési módja
     *
     * PAYMENT_METHOD_TRANSFER         : 'átutalás';
     * PAYMENT_METHOD_CASH             : 'készpénz';
     * PAYMENT_METHOD_BANKCARD         : 'bankkártya';
     * PAYMENT_METHOD_CHEQUE           : 'csekk';
     * PAYMENT_METHOD_CASH_ON_DELIVERY : 'utánvét';
     * PAYMENT_METHOD_PAYPAL           : 'PayPal';
     * PAYMENT_METHOD_SZEP_CARD        : 'SZÉP kártya';
     * PAYMENT_METHOD_OTP_SIMPLE       : 'OTP Simple';
     *
     * @var int
     */
    protected $paymentMethod;

    /**
     * Bizonylat pénzneme
     *
     * @var string
     */
    protected $currency;

    /**
     * Bizonylat nyelve
     *
     * @var string
     */
    protected $language;

    /**
     * Bizonylat teljesítési dátuma
     *
     * @var string
     */
    protected $fulfillment;

    /**
     * Bizonylat fizetési határideje
     *
     * @var string
     */
    protected $paymentDue;

    /**
     * A bizonylat előtagja
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * A bizonylaton másodikként megjelenő logó (fájl) neve.
     *
     * @var string
     */
    protected $extraLogo;

    /**
     * Bizonylat végösszegét korrigáló tétel.
     * Nem befolyásolja a bruttó értéket, csak mint fizetendőt kell feltüntetni.
     *
     * @var double
     */
    protected $correctionToPay;

    /**
     * Helyesbített számlaszám
     *
     * @var string
     */
    protected $correctivedNumber = '';

    /**
     * Bizonylat megjegyzés
     *
     * @var string
     */
    protected $comment;

    /**
     * Deviza árfolyamot jegyző bank neve
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
     * Rendelésszám
     *
     * @var string
     */
    protected $orderNumber = '';

    /**
     * Hivatkozás a díjbekérőre
     * A számla kibocsátásakor explicit megadhatjuk annak a díjbekérőnek a számát, amire hivatkozva történik a számlakibocsátás.
     *
     * @var string
     */
    protected $proformaNumber = '';

    /**
     * A bizonylat kifizetettsége
     *
     * @var bool
     */
    protected $paid = false;

    /**
     * Ez a bizonylat árrés alapján áfázik-e?
     *
     * @var bool
     */
    protected $profitVat = false;

    /**
     * Számlasablon
     * Ez a számlakép sablon lesz használva a számla kibocsátásánál.
     *
     * INVOICE_TEMPLATE_DEFAULT      : 'SzlaMost';
     * INVOICE_TEMPLATE_TRADITIONAL  : 'SzlaAlap';
     * INVOICE_TEMPLATE_ENV_FRIENDLY : 'SzlaNoEnv';
     * INVOICE_TEMPLATE_8CM          : 'Szla8cm';
     * INVOICE_TEMPLATE_RETRO        : 'SzlaTomb';
     *
     * @var string
     */
    protected $invoiceTemplate = Invoice::INVOICE_TEMPLATE_DEFAULT;

    /**
     * Előlegszámla számlaszám
     * (ha a végszámlázandó előlegszámla nem azonosítható a rendelésszámmal, akkor itt megadhatod az előlegszámla számlaszámát)
     *
     * @var string
     */
    protected $prePaymentInvoiceNumber;

    /**
     * Ez a bizonylat előnézeti PDF-e?
     * Ebben az esetben bizonylat nem készül!
     *
     * @var bool
     */
    protected $previewPdf = false;

    /**
     * A bizonylat nem magyar áfát tartalmaz-e.
     * Ha tartalmaz, akkor a bizonylat adatai nem lesznek továbbítva a NAV Online Számla rendszere felé.
     *
     * @var bool
     */
    protected $euVat = false;

    /**
     * XML-ben kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = [];


    /**
     * InvoiceHeader constructor.
     *
     * @param int $type
     *
     * @throws SzamlaAgentException
     */
    function __construct($type = Invoice::INVOICE_TYPE_P_INVOICE) {
        if (!empty($type)) {
            $this->setDefaultData($type);
        }
    }

    /**
     * Beállítja a bizonylat alapértelmezett adatait
     *
     * @param $type
     *
     * @throws SzamlaAgentException
     * @throws \Exception
     */
    function setDefaultData($type) {
        // A bizonylat számla típusú
        $this->setInvoice(true);
        // Számla típusa (papír vagy e-számla)
        $this->setInvoiceType($type);
        // Számla kiállítás dátuma
        $this->setIssueDate(SzamlaAgentUtil::getTodayStr());
        // Számla fizetési módja (átutalás)
        $this->setPaymentMethod(Document::PAYMENT_METHOD_TRANSFER);
        // Számla pénzneme
        $this->setCurrency(Document::getDefaultCurrency());
        // Számla nyelve
        $this->setLanguage(Document::getDefaultLanguage());
        // Számla teljesítés dátuma
        $this->setFulfillment(SzamlaAgentUtil::getTodayStr());
        // Számla fizetési határideje
        $this->setPaymentDue(SzamlaAgentUtil::addDaysToDate(SzamlaAgentUtil::DEFAULT_ADDED_DAYS));
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
                case 'issueDate':
                case 'fulfillment':
                case 'paymentDue':
                    SzamlaAgentUtil::checkDateField($field, $value, $required, __CLASS__);
                    break;
                case 'exchangeRate':
                case 'correctionToPay':
                    SzamlaAgentUtil::checkDoubleField($field, $value, $required, __CLASS__);
                    break;
                case 'proforma':
                case 'deliveryNote':
                case 'prePayment':
                case 'final':
                case 'reverse':
                case 'paid':
                case 'profitVat':
                case 'corrective':
                case 'previewPdf':
                case 'euVat':
                    SzamlaAgentUtil::checkBoolField($field, $value, $required, __CLASS__);
                    break;
                case 'paymentMethod':
                case 'currency':
                case 'comment':
                case 'exchangeBank':
                case 'orderNumber':
                case 'correctivedNumber':
                case 'extraLogo':
                case 'prefix':
                case 'invoiceNumber':
                case 'invoiceTemplate':
                case 'prePaymentInvoiceNumber':
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
     * Csak azokat az XML mezőket adjuk hozzá, amelyek kötelezőek,
     * illetve amelyek opcionálisak, de ki vannak töltve.
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

            $this->setRequiredFields([
                'invoiceDate', 'fulfillment', 'paymentDue', 'paymentMethod', 'currency', 'language', 'buyer', 'items'
            ]);

            $data = [
                "keltDatum"             => $this->getIssueDate(),
                "teljesitesDatum"       => $this->getFulfillment(),
                "fizetesiHataridoDatum" => $this->getPaymentDue(),
                "fizmod"                => $this->getPaymentMethod(),
                "penznem"               => $this->getCurrency(),
                "szamlaNyelve"          => $this->getLanguage()
            ];

            if (SzamlaAgentUtil::isNotBlank($this->getComment()))                 $data['megjegyzes'] = $this->getComment();
            if (SzamlaAgentUtil::isNotBlank($this->getExchangeBank()))            $data['arfolyamBank'] = $this->getExchangeBank();

            if (SzamlaAgentUtil::isNotNull($this->getExchangeRate())) {
                $data['arfolyam'] = SzamlaAgentUtil::doubleFormat($this->getExchangeRate());
            }

            if (SzamlaAgentUtil::isNotBlank($this->getOrderNumber()))             $data['rendelesSzam'] = $this->getOrderNumber();
            if (SzamlaAgentUtil::isNotBlank($this->getProformaNumber()))          $data['dijbekeroSzamlaszam'] = $this->getProformaNumber();
            if ($this->isPrePayment())                                            $data['elolegszamla']  = $this->isPrePayment();
            if ($this->isFinal())                                                 $data['vegszamla']  = $this->isFinal();
            if (SzamlaAgentUtil::isNotBlank($this->getPrePaymentInvoiceNumber())) $data['elolegSzamlaszam'] = $this->getPrePaymentInvoiceNumber();
            if ($this->isCorrective())                                            $data['helyesbitoszamla']  = $this->isCorrective();
            if (SzamlaAgentUtil::isNotBlank($this->getCorrectivedNumber()))       $data['helyesbitettSzamlaszam']  = $this->getCorrectivedNumber();
            if ($this->isProforma())                                              $data['dijbekero']  = $this->isProforma();
            if ($this->isDeliveryNote())                                          $data['szallitolevel']  = $this->isDeliveryNote();
            if (SzamlaAgentUtil::isNotBlank($this->getExtraLogo()))               $data['logoExtra']  = $this->getExtraLogo();
            if (SzamlaAgentUtil::isNotBlank($this->getPrefix()))                  $data['szamlaszamElotag']  = $this->getPrefix();

            if (SzamlaAgentUtil::isNotNull($this->getCorrectionToPay()) && $this->getCorrectionToPay() !== 0) {
                $data['fizetendoKorrekcio'] = SzamlaAgentUtil::doubleFormat($this->getCorrectionToPay());
            }

            if ($this->isPaid())                                                  $data['fizetve']  = $this->isPaid();
            if ($this->isProfitVat())                                             $data['arresAfa'] = $this->isProfitVat();

            $data['eusAfa'] = ($this->isEuVat() ? true : false);

            if (SzamlaAgentUtil::isNotBlank($this->getInvoiceTemplate()))         $data['szamlaSablon'] = $this->getInvoiceTemplate();

            if ($this->isPreviewPdf())                                            $data['elonezetpdf']  = $this->isPreviewPdf();

            $this->checkFields();

            return $data;
        } catch (SzamlaAgentException $e) {
            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getIssueDate() {
        return $this->issueDate;
    }

    /**
     * @param string $issueDate
     */
    public function setIssueDate($issueDate) {
        $this->issueDate = $issueDate;
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
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language) {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getFulfillment() {
        return $this->fulfillment;
    }

    /**
     * @param string $fulfillment
     */
    public function setFulfillment($fulfillment) {
        $this->fulfillment = $fulfillment;
    }

    /**
     * @return string
     */
    public function getPaymentDue() {
        return $this->paymentDue;
    }

    /**
     * @param string $paymentDue
     */
    public function setPaymentDue($paymentDue) {
        $this->paymentDue = $paymentDue;
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
    public function getExtraLogo() {
        return $this->extraLogo;
    }

    /**
     * @param string $extraLogo
     */
    public function setExtraLogo($extraLogo) {
        $this->extraLogo = $extraLogo;
    }

    /**
     * @return float
     */
    public function getCorrectionToPay() {
        return $this->correctionToPay;
    }

    /**
     * @param float $correctionToPay
     */
    public function setCorrectionToPay($correctionToPay) {
        $this->correctionToPay = (float)$correctionToPay;
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
    public function getOrderNumber() {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getPrePaymentInvoiceNumber() {
        return $this->prePaymentInvoiceNumber;
    }

    /**
     * @param string $prePaymentInvoiceNumber
     */
    public function setPrePaymentInvoiceNumber($prePaymentInvoiceNumber) {
        $this->prePaymentInvoiceNumber = $prePaymentInvoiceNumber;
    }

    /**
     * @return string
     */
    public function getProformaNumber() {
        return $this->proformaNumber;
    }

    /**
     * Beállítja annak a díjbekérőnek a számát, amire hivatkozva történik a számlakibocsátás
     *
     * @param string $proformaNumber
     */
    public function setProformaNumber($proformaNumber) {
        $this->proformaNumber = $proformaNumber;
    }

    /**
     * @return bool
     */
    public function isPaid() {
        return $this->paid;
    }

    /**
     * @param bool $paid
     */
    public function setPaid($paid) {
        $this->paid = $paid;
    }

    /**
     * @return bool
     */
    public function isProfitVat() {
        return $this->profitVat;
    }

    /**
     * @param bool $profitVat
     */
    public function setProfitVat($profitVat) {
        $this->profitVat = $profitVat;
    }

    /**
     * @return string
     */
    public function getCorrectivedNumber() {
        return $this->correctivedNumber;
    }

    /**
     * @param string $correctivedNumber
     */
    public function setCorrectivedNumber($correctivedNumber) {
        $this->correctivedNumber = $correctivedNumber;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber) {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return int
     */
    public function getInvoiceTemplate() {
        return $this->invoiceTemplate;
    }

    /**
     * Számlakép sablon beállítása
     *
     * INVOICE_TEMPLATE_DEFAULT      (számlázz.hu ajánlott számlakép)
     * INVOICE_TEMPLATE_TRADITIONAL  (tradicionális számlakép)
     * INVOICE_TEMPLATE_ENV_FRIENDLY (borítékbarát számlakép)
     * INVOICE_TEMPLATE_8CM          (hőnyomtatós számlakép - 8 cm széles)
     * INVOICE_TEMPLATE_RETRO        (retró kéziszámla számlakép)
     *
     * @param string $invoiceTemplate
     */
    public function setInvoiceTemplate($invoiceTemplate) {
        $this->invoiceTemplate = $invoiceTemplate;
    }

    /**
     * @return int
     */
    public function getInvoiceType() {
        return $this->invoiceType;
    }

    /**
     * @param $type
     */
    public function setInvoiceType($type) {
        $this->invoiceType = $type;
    }

    /**
     * @return bool
     */
    public function isEInvoice() {
        return ($this->getInvoiceType() == Invoice::INVOICE_TYPE_E_INVOICE);
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
     * @return bool
     */
    public function isPreviewPdf() {
        return $this->previewPdf;
    }

    /**
     * Beállítja a bizonylatot előnézeti PDF-re.
     * Ebben az esetben bizonylat nem készül.
     *
     * @param bool $previewPdf
     */
    public function setPreviewPdf($previewPdf) {
        $this->previewPdf = $previewPdf;
    }

    /**
     * @return bool
     */
    public function isEuVat() {
        return $this->euVat;
    }

    /**
     * Beállítja a bizonylathoz, hogy nem magyar áfát tartalmaz-e.
     * Ha tartalmaz, akkor a bizonylat adatai nem lesznek továbbítva a NAV Online Számla rendszere felé.
     *
     * @param bool $euVat
     */
    public function setEuVat($euVat) {
        $this->euVat = $euVat;
    }

}