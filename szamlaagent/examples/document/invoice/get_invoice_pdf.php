<?php

/**
 * Ez a példa megmutatja, hogy hogyan töltsünk le egy számlát PDF-ben
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;

try {
    // Számla Agent létrehozása alapértelmezett adatokkal
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Számla PDF lekérdezése számlaszám, rendelésszám vagy külső számlaazonosító alapján
     *
     * Rendelésszám alapján való lekérdezés esetén a legutolsó bizonylatot adjuk vissza, amelyiknek ez a rendelésszáma.
     * @example $agent->getInvoicePdf('TESZT-001', Invoice::FROM_ORDER_NUMBER);
     *
     * A számla lekérdezhető a külső (Számla Agentet használó) rendszer által megadott azonosító alapján is:
     *
     * @example $agent->getInvoicePdf('TESZT-001', Invoice::FROM_INVOICE_EXTERNAL_ID);
     */
    $result = $agent->getInvoicePdf('TESZT-001');

    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        $result->downloadPdf();
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}