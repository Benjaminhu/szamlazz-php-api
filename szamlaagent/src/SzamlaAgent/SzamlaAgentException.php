<?php

namespace SzamlaAgent;

/**
 * Számla Agent egyedi kivételeket kezelő osztály
 *
 * @package SzamlaAgent
 */
class SzamlaAgentException extends \Exception {

    const SYSTEM_DOWN                            = 'Az oldal jelenleg karbantartás alatt áll. Kérjük, látogass vissza pár perc múlva.';
    const REQUEST_TYPE_NOT_EXISTS                = 'A kérés típusa nem létezik';
    const RESPONSE_TYPE_NOT_EXISTS               = 'A válasz típusa nem létezik';
    const XML_SCHEMA_TYPE_NOT_EXISTS             = 'Az XML séma típusa nem létezik';
    const XML_KEY_NOT_EXISTS                     = 'XML kulcs nem létezik';
    const XML_NOT_VALID                          = 'Az összeállított XML nem érvényes';
    const XML_DATA_NOT_AVAILABLE                 = 'Hiba történt az XML adatok összeállításánál: nincs adat.';
    const XML_DATA_BUILD_FAILED                  = 'Az XML adatok összeállítása sikertelen';
    const FIELDS_CHECK_ERROR                     = 'Hiba a mezők ellenőrzése közben';
    const DATE_FORMAT_NOT_EXISTS                 = 'Nincs ilyen dátum formátum';
    const NO_AGENT_INSTANCE_WITH_USERNAME        = 'Nincs ilyen felhasználónévvel Számla Agent példányosítva!';
    const NO_AGENT_INSTANCE_WITH_APIKEY          = 'Nincs ilyen kulccsal Számla Agent példányosítva!';
    const NO_SZLAHU_KEY_IN_HEADER                = 'Érvénytelen válasz!';
    const DOCUMENT_DATA_IS_MISSING               = 'A bizonylat PDF adatai hiányoznak!';
    const PDF_FILE_SAVE_SUCCESS                  = 'PDF fájl mentése sikeres';
    const PDF_FILE_SAVE_FAILED                   = 'PDF fájl mentése sikertelen';
    const AGENT_RESPONSE_NO_CONTENT              = 'A Számla Agent válaszában nincs tartalom!';
    const AGENT_RESPONSE_NO_HEADER               = 'A Számla Agent válasza nem tartalmaz fejlécet!';
    const AGENT_RESPONSE_IS_EMPTY                = 'A Számla Agent válasza nem lehet üres!';
    const AGENT_ERROR                            = 'Agent hiba';
    const FILE_CREATION_FAILED                   = 'A fájl létrehozása sikertelen.';
    const ATTACHMENT_NOT_EXISTS                  = 'A csatolandó fájl nem létezik';
    const INVOICE_NOTIFICATION_SEND_FAILED       = 'Számlaértesítő kézbesítése sikertelen';
    const INVALID_JSON                           = 'Érvénytelen JSON';
    const INVOICE_EXTERNAL_ID_IS_EMPTY           = 'A külső számlaazonosító üres';
    const CONNECTION_ERROR                       = 'Sikertelen kapcsolódás';
    const XML_FILE_SAVE_FAILED                   = 'XML fálj mentése sikertelen';

    /**
     * Számla Agent egyedi kivétel létrehozása
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}