<?php

namespace SzamlaAgent\Document\Invoice;

use SzamlaAgent\Document\Document;
use SzamlaAgent\Header\InvoiceHeader;
use SzamlaAgent\Item\InvoiceItem;
use SzamlaAgent\CreditNote\InvoiceCreditNote;
use SzamlaAgent\Log;
use SzamlaAgent\Waybill\Waybill;
use SzamlaAgent\Buyer;
use SzamlaAgent\Seller;
use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Számla
 *
 * @package SzamlaAgent\Document\Invoice
 */
class Invoice extends Document {

    /** Számla típus: papír számla */
    const INVOICE_TYPE_P_INVOICE = 1;

    /** Számla típus: e-számla */
    const INVOICE_TYPE_E_INVOICE = 2;

    /** Számla lekérdezése számlaszám alapján */
    const FROM_INVOICE_NUMBER = 1;

    /** Számla lekérdezése rendelési szám alapján */
    const FROM_ORDER_NUMBER = 2;

    /** Számla lekérdezése külső számlaazonosító alapján */
    const FROM_INVOICE_EXTERNAL_ID = 3;

    /**
     * Jóváírások maximális száma
     * a számla kifizetettségének beállításakor
     */
    const CREDIT_NOTES_LIMIT = 5;

    /** Számlához csatolandó fájlok maximális száma */
    const INVOICE_ATTACHMENTS_LIMIT = 5;

    /** Számlázz.hu ajánlott számlakép */
    const INVOICE_TEMPLATE_DEFAULT = 'SzlaMost';

    /** Tradicionális számlakép */
    const INVOICE_TEMPLATE_TRADITIONAL = 'SzlaNoEnv';

    /** Borítékbarát számlakép */
    const INVOICE_TEMPLATE_ENV_FRIENDLY = 'SzlaAlap';

    /** Hőnyomtatós számlakép (8 cm széles) */
    const INVOICE_TEMPLATE_8CM = 'Szla8cm';

    /** Retró kéziszámla számlakép */
    const INVOICE_TEMPLATE_RETRO = 'SzlaTomb';


    /**
     * A számla fejléce
     *
     * @var InvoiceHeader
     */
    private $header;

    /**
     * A számlán szereplő eladó adatok
     *
     * @var Seller
     */
    protected $seller;

    /**
     * A számlán szereplő vevő adatok
     *
     * @var Buyer
     */
    protected $buyer;

    /**
     * Fuvarlevél
     *
     * @var Waybill
     */
    protected $waybill;

    /**
     * Számla tételek
     *
     * @var InvoiceItem[]
     */
    protected $items = [];

    /**
     * Számlához tartozó jóváírások
     *
     * @var InvoiceCreditNote[]
     */
    protected $creditNotes = [];

    /**
     * Összeadandó-e a jóváírás
     *
     * Ha igaz, akkor nem törli a korábbi jóváírásokat,
     * hanem hozzáadja az összeget az eddigiekhez.
     *
     * @var bool
     */
    protected $additive = true;

    /**
     * Számlához tartozó mellékletek
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Számla létrehozása
     *
     * Átutalással fizetendő magyar nyelvű (Ft) számla kiállítása mai keltezési és
     * teljesítési dátummal, +8 nap fizetési határidővel, üres számlaelőtaggal.
     *
     * @param int $type számla típusa (papír vagy e-számla)
     *
     * @throws SzamlaAgentException
     */
    public function __construct($type = self::INVOICE_TYPE_P_INVOICE) {
        // Alapértelmezett fejléc adatok hozzáadása a számlához
        if (!empty($type)) {
            $this->setHeader(new InvoiceHeader($type));
        }
    }

    /**
     * @return InvoiceHeader
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * @param InvoiceHeader $header
     */
    public function setHeader(InvoiceHeader $header) {
        $this->header = $header;
    }

    /**
     * @return Seller
     */
    public function getSeller() {
        return $this->seller;
    }

    /**
     * @param Seller $seller
     */
    public function setSeller(Seller $seller) {
        $this->seller = $seller;
    }

    /**
     * @return Buyer
     */
    public function getBuyer() {
        return $this->buyer;
    }

    /**
     * @param Buyer $buyer
     */
    public function setBuyer(Buyer $buyer) {
        $this->buyer = $buyer;
    }

    /**
     * @return Waybill
     */
    public function getWaybill() {
        return $this->waybill;
    }

    /**
     * @param Waybill $waybill
     */
    public function setWaybill(Waybill $waybill) {
        $this->waybill = $waybill;
    }

    /**
     * @param InvoiceItem $item
     */
    public function addItem(InvoiceItem $item) {
        array_push($this->items, $item);
    }

    /**
     * @return InvoiceItem[]
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @param InvoiceItem[] $items
     */
    public function setItems($items) {
        $this->items = $items;
    }

    /**
     * Jóváírás hozzáadása a számlához
     *
     * @param InvoiceCreditNote $creditNote
     */
    public function addCreditNote(InvoiceCreditNote $creditNote) {
        if (count($this->creditNotes) < self::CREDIT_NOTES_LIMIT) {
            array_push($this->creditNotes, $creditNote);
        }
    }

    /**
     * @return InvoiceCreditNote[]
     */
    public function getCreditNotes() {
        return $this->creditNotes;
    }

    /**
     * @param InvoiceCreditNote[] $creditNotes
     */
    public function setCreditNotes(array $creditNotes) {
        $this->creditNotes = $creditNotes;
    }

    /**
     * @return bool
     */
    public function isAdditive() {
        return $this->additive;
    }

    /**
     * @param bool $additive
     */
    public function setAdditive($additive)
    {
        $this->additive = $additive;
    }

    /**
     * Összeállítja a számla XML adatait
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_INVOICE:
                $data = $this->buildFieldsData($request, ['beallitasok', 'fejlec', 'elado', 'vevo', 'fuvarlevel', 'tetelek']);
                break;
            case $request::XML_SCHEMA_DELETE_PROFORMA:
                $data = $this->buildFieldsData($request, ['beallitasok', 'fejlec']);
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_INVOICE:
                $data = $this->buildFieldsData($request, ['beallitasok', 'fejlec', 'elado', 'vevo']);
                break;
            case $request::XML_SCHEMA_PAY_INVOICE:
                $data = $this->buildFieldsData($request, ['beallitasok']);
                $data = array_merge($data, $this->buildCreditsXmlData());
                break;
            case $request::XML_SCHEMA_REQUEST_INVOICE_XML:
            case $request::XML_SCHEMA_REQUEST_INVOICE_PDF:
                $settings = $this->buildFieldsData($request, ['beallitasok']);
                $data = $settings['beallitasok'];
                break;
            default:
                throw new SzamlaAgentException(SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS . ": {$request->getXmlName()}.");
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

        if (!empty($fields)) {
            foreach ($fields as $key) {
                switch ($key) {
                    case 'beallitasok': $value = $request->getAgent()->getSetting()->buildXmlData($request); break;
                    case 'fejlec':      $value = $this->getHeader()->buildXmlData($request); break;
                    case 'tetelek':     $value = $this->buildXmlItemsData(); break;
                    case 'elado':       $value = (SzamlaAgentUtil::isNotNull($this->getSeller()))  ? $this->getSeller()->buildXmlData($request)  : array(); break;
                    case 'vevo':        $value = (SzamlaAgentUtil::isNotNull($this->getBuyer()))   ? $this->getBuyer()->buildXmlData($request)   : array(); break;
                    case 'fuvarlevel':  $value = (SzamlaAgentUtil::isNotNull($this->getWaybill())) ? $this->getWaybill()->buildXmlData($request) : array(); break;
                    default:
                        throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS . ": {$key}");
                }

                if (isset($value)) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Összeállítja a bizonylathoz tartozó tételek adatait
     *
     * @return array
     * @throws SzamlaAgentException
     */
    protected function buildXmlItemsData() {
        $data = [];

        if (!empty($this->getItems())) {
            foreach ($this->getItems() as $key => $item) {
                $data["item{$key}"] = $item->buildXmlData();
            }
        }
        return $data;
    }

    /**
     * Összeállítja a számlához tartozó jóváírások adatait
     *
     * @return array
     * @throws SzamlaAgentException
     */
    protected function buildCreditsXmlData() {
        $data = [];
        if (!empty($this->getCreditNotes())) {
            foreach ($this->getCreditNotes() as $key => $note) {
                $data["note{$key}"] = $note->buildXmlData();
            }
        }
        return $data;
    }

    /**
     * Visszaadja a számlához tartozó fájl mellékleteket
     *
     * @return array
     */
    public function getAttachments() {
        return $this->attachments;
    }

    /**
     * Fájl csatolása a számlához
     *
     * Összesen 5 db mellékletet tudsz egy számlához csatolni.
     * A beküldött fájlok mérete nem haladhatja meg a 2 MB méretet. Ha valamelyik beküldött fájl csatolása valamilyen okból sikertelen,
     * akkor a nem megfelelő csatolmányokról a rendszer figyelmeztető emailt küld a beküldőnek (minden rossz fájlról külön-külön).
     *
     * Hibás csatolmány esetén is kiküldésre kerül az értesítő email úgy, hogy a megfelelő fájlok csatolva lesznek.
     * Ha nem érkezik kérés értesítő email kiküldésére, akkor a beküldött csatolmányok nem kerülnek feldolgozásra.
     *
     * @param string $filePath
     *
     * @throws SzamlaAgentException
     */
    public function addAttachment($filePath) {
        if (empty($filePath)) {
            Log::writeLog("A csatolandó fájl neve nincs megadva!", Log::LOG_LEVEL_WARN);
        } else {
            if (count($this->attachments) >= self::INVOICE_ATTACHMENTS_LIMIT) {
                throw new SzamlaAgentException('A következő fájl csatolása sikertelen: "' . $filePath. '". Egy számlához maximum ' . self::INVOICE_ATTACHMENTS_LIMIT . ' fájl csatolható!');
            }

            if (!file_exists($filePath)) {
                throw new SzamlaAgentException(SzamlaAgentException::ATTACHMENT_NOT_EXISTS . ': '. $filePath);
            }
            array_push($this->attachments, $filePath);
        }
    }
}