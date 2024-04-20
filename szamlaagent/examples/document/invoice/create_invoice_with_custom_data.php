<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy számlát egyedi adatokkal.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\BuyerLedger;
use \SzamlaAgent\Seller;
use \SzamlaAgent\TaxPayer;
use \SzamlaAgent\Currency;
use \SzamlaAgent\Language;
use \SzamlaAgent\Document\Invoice\Invoice;
use \SzamlaAgent\Item\InvoiceItem;
use \SzamlaAgent\Ledger\InvoiceItemLedger;
use \SzamlaAgent\Response\SzamlaAgentResponse;
use \SzamlaAgent\Log;

try {
    // Számla Agent létrehozása egyedi beállításokkal
    $agent = SzamlaAgentAPI::create('agentApiKey', true, Log::LOG_LEVEL_DEBUG);
    // Az Agent választ XML formátumban kapjuk meg
    $agent->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);
    // A bérelhető webáruházat futtató motor neve
    $agent->setAggregator('WooCommerce');
    // Generált XML fájlok mentésének engedélyezése
    $agent->setXmlFileSave(true);

    // Új e-számla létrehozása alapértelmezett adatokkal
    $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);
    // Számla fejléce
    $header = $invoice->getHeader();
    // Számla fizetési módja (bankkártya)
    $header->setPaymentMethod(Invoice::PAYMENT_METHOD_BANKCARD);
    // Számla pénzneme
    $header->setCurrency(Currency::CURRENCY_EUR);
    // Számla nyelve
    $header->setLanguage(Language::LANGUAGE_EN);
    // Számla kifizetettség (fizetve)
    $header->setPaid(true);
    // Számla teljesítés dátuma
    $header->setFulfillment('2022-05-23');
    // Számla fizetési határideje
    $header->setPaymentDue('2022-05-23');
    // Egyedi számlaelőtag használata
    $header->setPrefix('');
    // Egyedi számlasablon használata
    $header->setInvoiceTemplate(Invoice::INVOICE_TEMPLATE_DEFAULT);
    // Előnézeti PDF beállítása
    $header->setPreviewPdf(false);
    // A számla tartalmaz-e nem magyar áfát (ha tartalmaz, akkor a bizonylat adatai nem lesznek továbbítva a NAV Online Számla rendszere felé)
    $header->setEuVat(false);

    // Eladó létrehozása
    $seller = new Seller('OBER', '11111111-22222222-33333333');
    // Eladó válasz e-mail címe
    $seller->setEmailReplyTo('seller@example.org');
    // Eladó aláírója
    $seller->setSignatoryName('Seller signatory');
    // Eladó e-mail tárgya
    $seller->setEmailSubject('Invoice notification');
    // Eladó e-mail tartalma
    $seller->setEmailContent('Pay the bill, otherwise the bank interest will be...');
    $invoice->setSeller($seller);

    // Vevő létrehozása (név, irányítószám, település, cím)
    $buyer = new Buyer('Kovacs Bt.', '2030', 'Érd', 'Tarnoki street 23.');
    // Vevő telefonszáma
    $buyer->setPhone('+36301234567');
    // Vevő adóalanyisága (van magyar adószáma)
    $buyer->setTaxPayer(TaxPayer::TAXPAYER_NO_TAXNUMBER);

    // Vevő főkönyvi adatok létrehozása (vevő azonosító, könyvelési dátum, vevő főkönyvi szám, folyamatos teljesítés)
    $buyerLedger = new BuyerLedger('123456', '2022-05-01', '123456', true);
    // Számla elszámolási időszak kezdete (folyamatos teljesítés esetén)
    $buyerLedger->setSettlementPeriodStart('2022-04-01');
    // Számla elszámolási időszak vége (folyamatos teljesítés esetén)
    $buyerLedger->setSettlementPeriodEnd('2022-04-30');
    // Főkönyvi adatok hozzáadása a vevőhöz
    $buyer->setLedgerData($buyerLedger);
    // Ha egyedi e-mail üzenetet állítunk be a vevő számára (lásd fentebb az Eladónál), akkor az e-mail kiküldéséhez az alábbi 2 mező beállítása is szükséges:
    $buyer->setEmail('buyer@example.org');
    $buyer->setSendEmail(true);
    // Vevő hozzáadása a számlához
    $invoice->setBuyer($buyer);

    // Számla tétel összeállítása egyedi adatokkal
    $item = new InvoiceItem("Test item 1", 100.0, 2.0, 'unit', '20');
    // Tétel nettó értéke
    $item->setNetPrice(200.0);
    // Tétel ÁFA értéke
    $item->setVatAmount(40.0);
    // Tétel bruttó értéke
    $item->setGrossAmount(240.0);
    // Tétel főkönyvi adatok létrehozása
    $itemLedger = new InvoiceItemLedger('economic event type', 'vat economic event type', 'revenue ledger number', 'vat ledger number');
    // Tétel elszámolási időszak kezdete
    $itemLedger->setSettlementPeriodStart('2022-04-01');
    // Tétel elszámolási időszak vége
    $itemLedger->setSettlementPeriodEnd('2022-04-30');
    // Tétel főkönyvi adatok hozzáadása
    $item->setLedgerData($itemLedger);
    // Tétel hozzáadása a számlához
    $invoice->addItem($item);

    // Számla elkészítése
    $result = $agent->generateInvoice($invoice);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo "A számla sikeresen elkészült. Számlaszám: {$result->getDocumentNumber()}, Kintlévőség: {$result->getDataObj()->getAssetAmount()}, Bruttó összeg: {$result->getDataObj()->getGrossAmount()} ";
        // A válasz PDF tartalma
        $pdf  = $result->toPdf();
        // A válasz XML formátumban
        $xml  = $result->toXML();
        // A válasz JSON formátumban
        $json = $result->toJson();
    }
    // ha sikertelen az számlaértesítő kézbesítése
    if ($result->hasInvoiceNotificationSendError()) {
    }
    var_dump($result->getDataObj());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}