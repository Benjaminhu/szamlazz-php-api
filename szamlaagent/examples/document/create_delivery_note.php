<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy szállítólevelet.
 */
require __DIR__ . '/../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Buyer;
use \SzamlaAgent\Document\DeliveryNote;
use \SzamlaAgent\Item\DeliveryNoteItem;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A szállítólevél sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új szállítólevél létrehozása
     *
     * Átutalásra megjelölt magyar nyelvű (Ft) szállítólevél kiállítása mai keltezési és
     * teljesítési dátummal, +8 nap fizetési határidővel
     */
    $deliveryNote = new DeliveryNote();
    // Vevő adatainak hozzáadása (kötelezően kitöltendő adatokkal)
    $deliveryNote->setBuyer(new Buyer('Kovács Bt.', '2030', 'Érd', 'Tárnoki út 23.'));

    // Szállítólevél tétel összeállítása alapértelmezett adatokkal (1 db tétel 27%-os áfatartalommal)
    $item = new DeliveryNoteItem('Eladó tétel 1', 10000.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(12700.0);
    // Tétel hozzáadása a szállítólevélhez
    $deliveryNote->addItem($item);

    // Számla elkészítése
    $result = $agent->generateDeliveryNote($deliveryNote);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A szállítólevél sikeresen elkészült. Szállítólevél száma: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}