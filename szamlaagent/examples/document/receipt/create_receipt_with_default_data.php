<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy nyugtát.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Document\Receipt\Receipt;
use \SzamlaAgent\Header\ReceiptHeader;
use \SzamlaAgent\Item\ReceiptItem;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A nyugta sikeres kiállítása esetén az Agent által visszadott válasz
     * tartalmazni fogja a létrejött bizonylatot PDF formátumban.
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új nyugta létrehozása
     * (fizetési mód: készpénz, pénznem: Ft)
     */
    $receipt = new Receipt();
    // Nyugta fejléc létrehozása
    $receipt->setHeader(new ReceiptHeader());
    // Nyugta előtag beállítása
    $receipt->getHeader()->setPrefix('NYGTA');
    // Nyugta tétel összeállítása (1 db eladó tétel 27%-os ÁFA tartalommal)
    $item = new ReceiptItem('Eladó tétel', 10000.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(12700.0);
    // Tétel hozzáadása a nyugtához
    $receipt->addItem($item);

    // Nyugta elkészítése
    $result = $agent->generateReceipt($receipt);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A nyugta sikeresen elkészült. Nyugtaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
        var_dump($result->getData());
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}