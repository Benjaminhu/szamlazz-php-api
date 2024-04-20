<?php

/**
 * Ez a példa megmutatja, hogy hogyan kérdezzük le egy nyugta adatait
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;

try {
    // Számla Agent létrehozása alapértelmezett adatokkal
    $agent = SzamlaAgentAPI::create('agentApiKey', false);

    // Nyugta adatok lekérdezése nyugtaszám alapján
    $result = $agent->getReceiptData('NYGTA-2021-001');

    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        var_dump($result->getDataObj());
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}