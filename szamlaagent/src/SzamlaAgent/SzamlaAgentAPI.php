<?php

namespace SzamlaAgent;

use SzamlaAgent\Response\SzamlaAgentResponse;

/**
 * A Számla Agent inicializálását, az adatok küldését és fogadását kezelő osztály
 *
 * @package SzamlaAgent
 */
class SzamlaAgentAPI extends SzamlaAgent {

    /**
     * Számla Agent API létrehozása
     *
     * @param string $apiKey       Számla Agent kulcs
     * @param bool   $downloadPdf  szeretnénk-e letölteni a bizonylatot PDF formátumban
     * @param int    $logLevel     naplózási szint
     * @param int    $responseType válasz típusa (szöveges vagy XML)
     * @param string $aggregator   webáruházat futtató motor neve
     *
     * @return SzamlaAgent
     * @throws SzamlaAgentException
     */
    public static function create($apiKey, $downloadPdf = true, $logLevel = Log::LOG_LEVEL_DEBUG, $responseType = SzamlaAgentResponse::RESULT_AS_TEXT, $aggregator = '', $singleton = true) {

        $agent = null;

        if ($singleton) {
            $index = self::getHash($apiKey);
            if (isset(self::$agents[$index])) {
                $agent = self::$agents[$index];
            } else {
                $agent = new self(null, null, $apiKey, $downloadPdf, $logLevel, $responseType, $aggregator);
                self::$agents[$index] = $agent;
            }
        } else {
            $agent =  new self(null, null, $apiKey, $downloadPdf, $logLevel, $responseType, $aggregator);
            $agent->setSingleton($singleton);
        }
        return $agent;
    }
}