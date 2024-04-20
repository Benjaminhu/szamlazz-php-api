<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy nyugtát egyedi adatokkal.
 */
require __DIR__ . '/../../autoload.php';

use \SzamlaAgent\Header\ReceiptHeader;
use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\Document\Receipt\Receipt;
use \SzamlaAgent\Item\ReceiptItem;
use \SzamlaAgent\Ledger\ReceiptItemLedger;
use \SzamlaAgent\CreditNote\ReceiptCreditNote;
use \SzamlaAgent\Response\SzamlaAgentResponse;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A nyugta sikeres kiállítása esetén az Agent által visszadott válasz
     * tartalmazni fogja a létrejött bizonylatot PDF formátumban.
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');
    // Átállítjuk a válasz típusát szövegesről XML-re
    $agent->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);

    /**
     * Új nyugta létrehozása egyedi adatokkal (NYGTA előtaggal)
     */
    $receipt = new Receipt();
    // Új nyugta fejléc létrehozása
    $header = new ReceiptHeader();
    // Nyugta előtag beállítása
    $header->setPrefix('NYGTA');
    // Nyugta fizetőeszközének beállítása
    $header->setPaymentMethod('bankcard');
    // Nyugta pénznemének beállítása
    $header->setCurrency('EUR');
    // Nyugta megjegyzésének beállítása
    $header->setComment('Teszt');
    // Árfolyamot jegyző bank nevének beállítása
    $header->setExchangeBank('MNB');
    // Árfolyam értéke (ha nincs megadva, a MNB aktuális napi árfolyamával számol a rendszer)
    $header->setExchangeRate(300.0);
    // Nyugta fejléc adatok hozzáadása
    $receipt->setHeader($header);

    // Nyugta tétel összeállítása (3 db eladó szék 27%-os áfatartalommal)
    $item = new ReceiptItem('chair', 100.0);
    // Tétel mennyiség beállítása
    $item->setQuantity(3.0);
    // Tétel mennyiségi egység beállítása
    $item->setQuantityUnit('unit');
    // Tétel mennyiségi egység beállítása
    $item->setVat('27');
    // Tétel nettó értékének beállítása
    $item->setNetPrice(300.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(81.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(381.0);
    // Tétel főkönyvi adatok hozzáadása
    $item->setLedgerData(new ReceiptItemLedger('123456789', '123456789'));
    // Tétel megjegyzés hozzáadása
    $item->setComment('Example comment');
    // Tétel hozzáadása a nyugtához
    $receipt->addItem($item);

    // Kifizetettség összegének (jóváírás) hozzáadása
    $receipt->addCreditNote(new ReceiptCreditNote('cash', 300.0, 'description'));
    $receipt->addCreditNote(new ReceiptCreditNote('transfer', 81.0, 'decription'));

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