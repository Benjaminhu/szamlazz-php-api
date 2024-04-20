<?php

/**
 * Ez a példa megmutatja, hogy hogyan töröljünk egy díjbekérőt számlaszám vagy rendelésszám alapján
 */
require __DIR__ . '/../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;

try {
    // Számla Agent létrehozása alapértelmezett adatokkal
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Díjbekérő törlése számlaszám vagy rendelésszám alapján
     *
     * Rendelésszám alapján több díjbekérőt is törölhetünk.
     * Ha valamelyik díjbekérő törlése nem sikerül, akkor rollbackelünk.
     *
     * @example $agent->deleteProforma('D-TESZT-001', Proforma::FROM_ORDER_NUMBER);
     */
    $result = $agent->getDeleteProforma('D-TESZT-001');

    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A díjbekérő törlése sikeresen megtörtént!';
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}