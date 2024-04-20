<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy díjbekérőt.
 */
require __DIR__ . '/../autoload.php';

use \SzamlaAgent\Buyer;
use \SzamlaAgent\Document\Proforma;
use \SzamlaAgent\Item\ProformaItem;
use \SzamlaAgent\SzamlaAgentAPI;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A díjbekérő sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új díjbekérő létrehozása
     *
     * Átutalással fizetendő magyar nyelvű (Ft) díjbekérő kiállítása mai keltezési és
     * teljesítési dátummal, +8 nap fizetési határidővel
     */
    $proforma = new Proforma();
    // Rendelésszám hozzáadása
    $proforma->getHeader()->setOrderNumber('TESZT-001');

    // Vevő adatainak hozzáadása (kötelezően kitöltendő adatokkal)
    $proforma->setBuyer(new Buyer('Kovács Bt.', '2030', 'Érd', 'Tárnoki út 23.'));

    // A bizonylat (díjbekérő) tétel összeállítása alapértelmezett adatokkal (1 db tétel 27%-os áfatartalommal)
    $item = new ProformaItem('Eladó tétel 1', 10000.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(12700.0);
    // Tétel hozzáadása a díjbekérőhöz
    $proforma->addItem($item);

    // Díjbekérő elkészítése
    $result = $agent->generateProforma($proforma);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A díjbekérő sikeresen elkészült. Díjbekérő száma: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}