<?php

/**
 * Ez a példa megmutatja, hogy hogyan tudunk hozzáadni egy jóváírást egy számlához
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\CreditNote\InvoiceCreditNote;
use \SzamlaAgent\Document\Invoice\Invoice;
use \SzamlaAgent\Document\Document;
use \SzamlaAgent\SzamlaAgentUtil;

try {
    // Számla Agent létrehozása alapértelmezett adatokkal
    $agent = SzamlaAgentAPI::create('agentApiKey');

    // Új számla létrehozása
    $invoice = new Invoice(Invoice::INVOICE_TYPE_E_INVOICE);
    // Számla fejléce
    $header = $invoice->getHeader();
    // Annak a számlának a számlaszáma, amelyikhez a jóváírást szeretnénk rögzíteni
    $header->setInvoiceNumber('TESZT-2021-001');
    // Fejléc hozzáadása a számlához
    $invoice->setHeader($header);

    // Hozzáadjuk a jóváírás összegét (false esetén felülírjuk a teljes összeget)
    $invoice->setAdditive(true);

    // Új jóváírás létrehozása (az összeget a számla devizanemében kell megadni)
    $creditNote = new InvoiceCreditNote(SzamlaAgentUtil::getTodayStr(), 10000.0, Document::PAYMENT_METHOD_BANKCARD, 'TESZT');
    // Jóváírás hozzáadása a számlához
    $invoice->addCreditNote($creditNote);

    // Számla jóváírás elküldése
    $result = $agent->payInvoice($invoice);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A jóváírás rögzítése sikerült. Számlaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}