<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre előlegszámlából végszámlát az előlegszámla számlaszáma alapján.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\Item\InvoiceItem;
use \SzamlaAgent\Document\Invoice\FinalInvoice;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A számla sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új végszámla létrehozása előlegszámlából számlaszám alapján
     */
    $invoice = new FinalInvoice(FinalInvoice::INVOICE_TYPE_P_INVOICE);
    // Előlegszámla fejléce
    $header = $invoice->getHeader();
    // Végszámla kiállításának dátuma
    $header->setIssueDate('2021-08-25');
    // Végszámla teljesítés dátuma
    $header->setFulfillment('2021-08-25');
    // Végszámla fizetési határideje
    $header->setPaymentDue('2021-09-01');
    // Előlegszámla számlaszám beállítása
    $header->setPrePaymentInvoiceNumber('TESZT-001');
    // Fejléc módosítása az új adattal
    $invoice->setHeader($header);

    // Vevő adatainak hozzáadása (kötelezően kitöltendő adatokkal)
    $invoice->setBuyer(new Buyer('Kovács Bt.', '2030', 'Érd', 'Tárnoki út 23.'));

    // Negatív számla tétel összeállítása alapértelmezett adatokkal (-1 db tétel 27%-os áfatartalommal)
    $item = new InvoiceItem('Eladó tétel 1', 10000.0, -1.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(-10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(-2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(-12700.0);
    // Tétel hozzáadása a számlához
    $invoice->addItem($item);

    // Számla tétel összeállítása alapértelmezett adatokkal (1 db tétel 27%-os áfatartalommal)
    $item = new InvoiceItem('Eladó tétel 1', 10000.0, 1.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(12700.0);
    // Tétel hozzáadása a számlához
    $invoice->addItem($item);

    // Végszámla elkészítése
    $result = $agent->generateFinalInvoice($invoice);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A végszámla sikeresen elkészült. Számlaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}