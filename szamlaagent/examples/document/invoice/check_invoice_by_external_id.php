<?php

/**
 * Ez a példa megmutatja, hogy hogyan ellenőrizheted le egy számla külső számlaazonosítója alapján, hogy létezik-e már a szamlazz.hu rendszerében.
 *
 * Tipp: Egy számlához tartozó külső számlaazonosítót a következőképpen tudsz beállítani egy számla létrehozásakor:
 * @example $agent->setInvoiceExternalId('TESZT-001');
 *
 * @see \SzamlaAgent\SzamlaAgent::setInvoiceExternalId()
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\SzamlaAgentAPI;

try {
    // Számla Agent létrehozása alapértelmezett adatokkal
    $agent = SzamlaAgentAPI::create('agentApiKey');
    // Ellenőrizzük, hogy létezik-e a számla a számlázz.hu rendszerében (külső számlaazonosító alapján)
    if ($agent->isExistsInvoiceByExternalId('TESZT-001')) {
        echo 'a számla létezik';
    } else {
        echo 'a számla nem létezik';
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}