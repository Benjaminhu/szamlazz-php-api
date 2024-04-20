<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy számlát fuvarlevéllel.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\Item\InvoiceItem;
use \SzamlaAgent\Document\Invoice\Invoice;
use \SzamlaAgent\Waybill\TransoflexWaybill;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A számla sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új papír alapú számla létrehozása fuvarlevéllel
     *
     * Átutalással fizetendő magyar nyelvű (Ft) számla kiállítása mai keltezési és
     * teljesítési dátummal, +8 nap fizetési határidővel.
     */
    $invoice = new Invoice(Invoice::INVOICE_TYPE_P_INVOICE);
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

    /**
     * Fuvarlevél létrehozása
     *
     * Támogatott fuvarlevél típusok: TOF, PPP, SPRINTER, MPL, FOXPOST, GLS, EMPTY
     * Fuvarlevél kiállításához kérjük vegye fel a kapcsolatot ügyfélszolgálatunkkal.
     *
     * GLS, FOXPOST, EMPTY esetén: new Waybill('001', 'fuvarlevél típus', '10000001');
     */
    $waybill = new TransoflexWaybill('106', '10000001', 'megjegyzés');
    // Fuvarlevél országa
    $waybill->setCountryCode('hu');
    // Fuvarlevél irányítószáma
    $waybill->setZip('2030');
    // Fuvarlevél hozzáadása a számlához
    $invoice->setWaybill($waybill);

    // Számla elkészítése
    $result = $agent->generateInvoice($invoice);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A számla sikeresen elkészült. Számlaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}