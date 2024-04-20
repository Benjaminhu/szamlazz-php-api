<?php

/**
 * Ez a példa megmutatja, hogy hogyan tudunk sztornózni egy nyugtát.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Document\Receipt\ReverseReceipt;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A sztornó nyugta sikeres kiállítása esetén az Agent által visszadott válasz
     * tartalmazni fogja a létrejött bizonylatot PDF formátumban.
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    // Új sztornó nyugta létrehozása
    $receipt = new ReverseReceipt('NYGTA-2021-001');
    // Sztornó nyugta elkészítése
    $result = $agent->generateReverseReceipt($receipt);

    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A sztornó nyugta sikeresen elkészült. Nyugtaszám: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
        var_dump($result->getData());
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}