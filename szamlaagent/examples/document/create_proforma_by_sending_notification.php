<?php

/**
 * Ez a példa megmutatja, hogy hogyan hozzunk létre egy díjbekérőt e-mail értesítő küldésével.
 */
require __DIR__ . '/../autoload.php';

use \SzamlaAgent\Buyer;
use \SzamlaAgent\BuyerLedger;
use \SzamlaAgent\Document\Proforma;
use \SzamlaAgent\Item\ProformaItem;
use \SzamlaAgent\Seller;
use \SzamlaAgent\SzamlaAgentAPI;
use \SzamlaAgent\TaxPayer;

try {
    /**
     * Számla Agent létrehozása alapértelmezett adatokkal
     *
     * A díjbekérő sikeres kiállítása esetén a válasz (response) tartalmazni fogja
     * a létrejött bizonylatot PDF formátumban (1 példányban)
     */
    $agent = SzamlaAgentAPI::create('agentApiKey');

    /**
     * Új díjbekérő létrehozása
     */
    $proforma = new Proforma();
    // Rendelésszám hozzáadása
    $proforma->getHeader()->setOrderNumber('TESZT-001');

    // A bizonylat (díjbekérő) tétel összeállítása alapértelmezett adatokkal (1 db tétel 27%-os áfatartalommal)
    $item = new ProformaItem('Eladó tétel 1', 10000.0);
    // Tétel nettó értékének beállítása
    $item->setNetPrice(10000.0);
    // Tétel ÁFA értékének beállítása
    $item->setVatAmount(2700.0);
    // Tétel bruttó értékének beállítása
    $item->setGrossAmount(12700.0);
    // Tétel hozzáadása a díjbekérőhöz
    $proforma->addItem($item);

    // Eladó létrehozása
    $seller = new Seller('OBER', '11111111-22222222-33333333');
    // Eladó válasz e-mail címe
    $seller->setEmailReplyTo('seller@example.org');
    // Eladó aláírója
    $seller->setSignatoryName('Seller signatory');
    // Eladó e-mail tárgya
    $seller->setEmailSubject('Invoice notification');
    // Eladó e-mail tartalma
    $seller->setEmailContent('Pay the bill, otherwise the bank interest will be...');
    $proforma->setSeller($seller);

    // Vevő létrehozása (név, irányítószám, település, cím)
    $buyer = new Buyer('Kovacs Bt.', '2030', 'Érd', 'Tarnoki street 23.');
    // Vevő telefonszáma
    $buyer->setPhone('+36301234567');
    // Vevő adószáma
    $buyer->setTaxNumber('11111111-1-11');
    // Vevő adóalanyisága (van magyar adószáma)
    $buyer->setTaxPayer(TaxPayer::TAXPAYER_HAS_TAXNUMBER);
    // Vevő főkönyvi adatok létrehozása (vevő azonosító, könyvelési dátum, vevő főkönyvi szám, folyamatos teljesítés)
    $buyerLedger = new BuyerLedger('123456', '2022-01-01', '123456', true);
    // Számla elszámolási időszak kezdete (folyamatos teljesítés esetén)
    $buyerLedger->setSettlementPeriodStart('2022-01-01');
    // Számla elszámolási időszak vége (folyamatos teljesítés esetén)
    $buyerLedger->setSettlementPeriodEnd('2022-01-31');
    // Főkönyvi adatok hozzáadása a vevőhöz
    $buyer->setLedgerData($buyerLedger);
    // Ha egyedi e-mail üzenetet állítunk be a vevő számára (lásd fentebb az Eladónál), akkor az e-mail kiküldéséhez az alábbi 2 mező beállítása is szükséges:
    $buyer->setEmail('buyer@example.org');
    $buyer->setSendEmail(true);
    // Vevő hozzáadása a számlához
    $proforma->setBuyer($buyer);

    // Díjbekérő elkészítése
    $result = $agent->generateProforma($proforma);
    // Agent válasz sikerességének ellenőrzése
    if ($result->isSuccess()) {
        echo 'A díjbekérő sikeresen elkészült. Díjbekérő száma: ' . $result->getDocumentNumber();
        // Válasz adatai a további feldolgozáshoz
    }
    var_dump($result->getData());
} catch (\Exception $e) {
    $agent->logError($e->getMessage());
}