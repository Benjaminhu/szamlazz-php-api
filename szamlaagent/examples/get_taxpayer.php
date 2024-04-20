<?php
    /**
     * Ez a példa megmutatja, hogy hogyan kérdezzük le egy adózó adatait törzsszám alapján.
     *
     * FONTOS! Az adatok a NAV-tól érkeznek. A NAV bármikor változtathat az interface-en,
     * illetve nem minden esetben adnak vissza címadatokat, így erre is fel kell készíteni a kódot.
     *
     * Ha üzleti logikát építesz erre az interface-re, akkor javasoljuk saját XML feldolgozóval kezelni
     * a NAV-tól érkező adatokat, felkészítve arra, hogy a NAV bármikor megváltoztathatja annak szerkezetét!
     *
     * A válaszban érkező adatok feldolgozását a TaxPayerResponse objektumon keresztül 2.9.10 verzióig támogattuk.
     * @see TaxPayerResponse
     */
    require __DIR__ . '/autoload.php';

    use \SzamlaAgent\SzamlaAgentAPI;

    try {
        // Számla Agent létrehozása alapértelmezett adatokkal
        $agent = SzamlaAgentAPI::create('agentApiKey');
        // Adózó adatainak lekérdezése törzsszám (adószám első 8 számjegye) alapján
        $result = $agent->getTaxPayer('12345678');
        // A NAV-tól kapott nyers XML adatok további feldolgozáshoz (az XML adatokat javasolt egy saját XML feldolgozóval kezelni)
        var_dump($result->getTaxPayerData());

    } catch (\Exception $e) {
        $agent->logError($e->getMessage());
    }