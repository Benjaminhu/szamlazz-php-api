<?php

/**
 * Ez a példa megmutatja, hogy hogyan tudunk elküldeni egy nyugta értesítőt egy vevő részére
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\Buyer;
use \SzamlaAgent\Seller;
use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Document\Receipt\Receipt;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A nyugta sikeres elküldése esetén az Agent által visszadott válasz
     * tartalmazni fogja a létrejött bizonylatot PDF formátumban.
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    // Nyugtaszám beállítása
    $receipt = new Receipt('NYGTA-2021-001');

    // Vevő létrehozása
    $buyer = new Buyer();
    // Vevő e-mail címe (ide megy ki a levél)
    $buyer->setEmail('vevo@example.com');
    // Vevői adatok hozzáadása a nyugtához
    $receipt->setBuyer($buyer);

    // Eladó e-mail értesítő beállítása
    $seller = new Seller();
    // Ha a vevő válaszol, erre a címre érkezik be a válasz
    $seller->setEmailReplyTo('elado@example.com');
    $seller->setEmailSubject('Email tárgya');
    $seller->setEmailContent('Ez az email szövege...');
    // Eladói adatok hozzáadása a nyugtához
    $receipt->setSeller($seller);

    // Nyugta elküldése
    $result = $agent->sendReceipt($receipt);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A nyugta kiküldése sikeresen megtörtént!';
        // Válasz adatai a további feldolgozáshoz
        var_dump($result->getData());
    }
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}