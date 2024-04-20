<?php

/**
 * Ez a példa megmutatja, hogy hogyan kérdezzük le egy számla adatait
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Response\SzamlaAgentResponse;

try {
    // Számla Agent létrehozása alapértelmezett adatokkal
    $agent = SzamlaAgentAPI::create('agentApiKey');
    // Az Agent választ XML formátumban kapjuk meg
    $agent->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);

    /**
     * Számla adatainak lekérdezése számlaszám vagy rendelésszám alapján
     *
     * Rendelésszám alapján való lekérdezés esetén a legutolsó bizonylat adatait adjuk vissza, amelyiknek ez a rendelésszáma.
     * Utolsó paraméterként megadható, ha szeretnénk letölteni a számla PDF-et is.
     *
     * @example $agent->getInvoiceData('TESZT-001', Invoice::FROM_ORDER_NUMBER, true);
     */
    $result = $agent->getInvoiceData('TESZT-001');

    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}