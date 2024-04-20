<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy helyesbítő számlát.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\Currency;
use \SzamlaAgent\Language;
use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\Item\InvoiceItem;
use \SzamlaAgent\Document\Invoice\CorrectiveInvoice;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A számla sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új helyesbítő számla létrehozása
     */
    $invoice = new CorrectiveInvoice();
    // A helyesbített számla száma
    $invoice->getHeader()->setCorrectivedNumber('TESZT-001');
    $invoice->getHeader()->setCurrency(Currency::CURRENCY_FT);
    $invoice->getHeader()->setLanguage(Language::LANGUAGE_HU);
    // Vevő adatainak hozzáadása (kötelezően kitöltendő adatokkal)
    $invoice->setBuyer(new Buyer('Kovács Bt.', '2030', 'Érd', 'Tárnoki út 23.'));

    // A helyesbített számla tétel összeállítása (-1 db tétel 27%-os áfatartalommal)
    $item = new InvoiceItem('Eladó tétel 1', 10000.0, -1.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(-10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(-2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(-12700.0);
    // Tétel hozzáadása a számlához
    $invoice->addItem($item);

    // Helyesbítő számla elkészítése
    $result = $agent->generateCorrectiveInvoice($invoice);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A helyesbítő számla sikeresen elkészült. Számlaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}