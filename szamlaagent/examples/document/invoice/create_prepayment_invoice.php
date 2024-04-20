<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy előlegszámlát díjbekérőből.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\Item\InvoiceItem;
use \SzamlaAgent\Document\Invoice\PrePaymentInvoice;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * Az előlegszámla sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új előlegszámla létrehozása
     *
     * Átutalással fizetendő magyar nyelvű (Ft) előlegszámla kiállítása mai keltezési és
     * teljesítési dátummal, +8 nap fizetési határidővel.
     */
    $invoice = new PrePaymentInvoice(PrePaymentInvoice::INVOICE_TYPE_P_INVOICE);
    // Rendelésszám beállítása (díjbekérő alapján)
    $header = $invoice->getHeader()->setOrderNumber('TESZT-001');

    // Vevő adatainak hozzáadása (kötelezően kitöltendő adatokkal)
    $invoice->setBuyer(new Buyer('Kovács Bt.', '2030', 'Érd', 'Tárnoki út 23.'));

    // Számla tétel összeállítása alapértelmezett adatokkal (1 db tétel 27%-os áfatartalommal)
    $item = new InvoiceItem('Eladó tétel 1', 10000.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(12700.0);
    // Tétel hozzáadása a számlához
    $invoice->addItem($item);

    // Számla elkészítése
    $result = $agent->generatePrePaymentInvoice($invoice);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'Az előlegszámla sikeresen elkészült. Számlaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}