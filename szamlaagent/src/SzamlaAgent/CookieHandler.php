<?php

namespace SzamlaAgent;

class CookieHandler {

    /**
     * Cookie-kat tartalmazó fájl neve
     */
    const JSON_FILE_NAME = "cookies.json";

    /**
     * A cookie.txt mentési helye
     */
    const COOKIE_FILE_PATH = __DIR__ . "/../../" . DIRECTORY_SEPARATOR . "cookie" . DIRECTORY_SEPARATOR;

    /**
     * Cookie file
     */
    const COOKIES_STORAGE_FILE = self::COOKIE_FILE_PATH . self::JSON_FILE_NAME;

    const COOKIE_HEADER_TEXT = "JSESSIONID=";

    const DEFAULT_COOKIE_JSON_CONTENT = "{}";

    /**
     * Cookie kezelés módja (szöveges fájl alapú)
     */
    const COOKIE_HANDLE_MODE_DEFAULT = 0;

    /**
     * Cookie kezelés módja (json fájl alapú)
     */
    const COOKIE_HANDLE_MODE_JSON = 1;

    /**
     * Cookie kezelés módja (adatbázis alapú)
     */
    const COOKIE_HANDLE_MODE_DATABASE = 2;

    /**
     * @var SzamlaAgent
     */
    private $agent;

    /**
     * A számlázási fiókhoz tartozó egyedi süti azonosító
     * @var string
     */
    private $cookieIdentifier;

    /**
     * Cookie tároló
     * @var array
     */
    private $sessions = array();

    /**
     * @var string
     */
    private $cookieSessionId = "";

    /**
     * Cookie kezelés módja
     * @var int
     */
    private $cookieHandleMode = self::COOKIE_HANDLE_MODE_DEFAULT;

    /** Default cookie handling */

    /**
     * Alapértelmezett süti fájlnév
     */
    const COOKIE_FILENAME = 'cookie.txt';

    /**
     * Cookie fájlnév
     *
     * @var string
     */
    private $cookieFileName = self::COOKIE_FILENAME;

    /**
     * @param SzamlaAgent $agent
     */
    public function __construct($agent) {
        $this->agent = $agent;
        $this->init();
    }

    /**
     * Beállítja a CookieHandler-t
     */
    private function init() {
        $this->cookieIdentifier = $this->createCookieIdentifier();
        $this->cookieFileName = $this->buildCookieFileName();
    }

    /**
     * Elmenti a számlázási fiókhoz tartozó sessionID-t
     * @param $sessionId
     * @return void
     */
    private function addSession($sessionId) {
        if (SzamlaAgentUtil::isNotNull($sessionId)) {
            $this->sessions[$this->cookieIdentifier]['sessionID'] = $sessionId;
            $this->sessions[$this->cookieIdentifier]['timestamp'] = time();
        }
    }

    /**
     * Kiszedi a header adatokból a sessionId-t, ha benne van, beállítja a cookieSessionId-t
     * JSON mód esetén frissítjük a session adatokat.
     * @param $header
     * @return void
     */
    public function handleSessionId($header) {
        $savedSessionId = array();
        preg_match_all('/(?<=JSESSIONID=)(.*?)(?=;)/', $header, $savedSessionId);

        if (isset($savedSessionId[0][0])) {
            $this->setCookieSessionId($savedSessionId[0][0]);
            if ($this->isHandleModeJson()) {
                $this->addSession($savedSessionId[0][0]);
            }
        }
    }

    /**
     * Elmenti a cookie-kat a json fáljba
     * @return void
     */
    public function saveSessions() {
        if ($this->isHandleModeJson()) {
            file_put_contents(self::COOKIES_STORAGE_FILE, json_encode($this->sessions));
        }
    }

    /**
     * Beállítja a header-be a sessionId-t
     */
    public function addCookieToHeader() {
        $this->refreshJsonSessionData();
        if (!empty($this->cookieSessionId)) {
            $this->agent->addCustomHTTPHeader('Cookie', self::COOKIE_HEADER_TEXT . $this->cookieSessionId);
        }
    }

    /**
     * Legenerálja az azonosítót
     * @return string|null
     */
    private function createCookieIdentifier() {
        $username = $this->agent->getUsername();
        $apiKey = $this->agent->getApiKey();
        $result = null;

        if (!empty($username)) {
            $result = hash('sha1', $username);

        } elseif (!empty($apiKey)) {
            $result = hash('sha1', $apiKey);
        }

        if (!$result || !SzamlaAgentUtil::isNotNull($result)) {
            $this->agent->writeLog("Süti azonosító generálás sikertelen.", Log::LOG_LEVEL_WARN);
        }
        return $result;
    }

    /**
     * Ha nem létezik a tároló akkor létrehozza ha fájl-ban tároljuk a cookie-kat
     * @return void
     */
    private function checkCookieContainer() {
        if (!file_exists(self::COOKIES_STORAGE_FILE)) {
            file_put_contents(self::COOKIES_STORAGE_FILE, self::DEFAULT_COOKIE_JSON_CONTENT);
        }
    }

    /**
     * JSON mód esetén beállítjuk a sütiket. Ha hibás a JSON file, akkor törli annak a tartalmát.
     * @return void
     */
    private function initJsonSessionId() {
            $cookieFileContent = file_get_contents(self::COOKIES_STORAGE_FILE);
            $this->checkFileIsValidJson($cookieFileContent);
            $this->sessions = json_decode($cookieFileContent, true);
    }

    /**
     * @param string $cookieFileContent
     * @return void
     */
    private function checkFileIsValidJson($cookieFileContent) {
        try {
            SzamlaAgentUtil::isValidJSON($cookieFileContent);
        } catch (SzamlaAgentException $e) {
            $this->agent->writeLog("Cookies.txt tartalma nem valid, törölve lett", Log::LOG_LEVEL_ERROR);
            file_put_contents(self::COOKIES_STORAGE_FILE, self::DEFAULT_COOKIE_JSON_CONTENT);
        }
    }

    /**
     * Visszaajda, hogy default módban van-e
     * @return bool
     */
    public function isHandleModeDefault() {
        return $this->cookieHandleMode == self::COOKIE_HANDLE_MODE_DEFAULT;
    }

    /**
     * Visszaajda, hogy json módban van-e
     * @return bool
     */
    public function isHandleModeJson() {
        return $this->cookieHandleMode == self::COOKIE_HANDLE_MODE_JSON;
    }

    /**
     * Visszaajda, hogy database módban van-e
     * @return bool
     */
    public function isHandleModeDatabase() {
        return $this->cookieHandleMode == self::COOKIE_HANDLE_MODE_DATABASE;
    }

    /**
     * True ha nem default módban van
     * @return bool
     */
    public function isNotHandleModeDefault() {
        return $this->cookieHandleMode != self::COOKIE_HANDLE_MODE_DEFAULT;
    }

    /**
     * True ha nem json módban van
     * @return bool
     */
    public function isNotHandleModeJson() {
        return $this->cookieHandleMode != self::COOKIE_HANDLE_MODE_JSON;
    }

    /**
     * True ha nem database módban van
     * @return bool
     */
    public function isNotHandleModeDatabase() {
        return $this->cookieHandleMode != self::COOKIE_HANDLE_MODE_DATABASE;
    }

    /**
     * Sütikezelési mód visszaadása
     * @return int
     */
    public function getCookieHandleMode() {
        return $this->cookieHandleMode;
    }

    /**
     * Sütikezelési mód beállítása
     * @param int $cookieHandleMode
     */
    public function setCookieHandleMode($cookieHandleMode) {
        $this->cookieHandleMode = $cookieHandleMode;
    }

    /**
     * Aktuális session  id-t adja vissza
     * @return string
     */
    public function getCookieSessionId() {
        return $this->cookieSessionId;
    }

    /**
     * Session id beállítása
     * @param string $cookieSessionId
     */
    public function setCookieSessionId($cookieSessionId) {
        $this->cookieSessionId = $cookieSessionId;
    }

    /**
     * @return void
     */
    private function refreshJsonSessionData() {
        if ($this->isHandleModeJson()) {
            $this->checkCookieContainer();
            $this->initJsonSessionId();
            if (isset($this->sessions[$this->cookieIdentifier])) {
                $this->cookieSessionId = $this->sessions[$this->cookieIdentifier]['sessionID'];
            }
        }
    }

    /** Default cookie handling */

    /**
     * @return string
     */
    public function getCookieFileName() {
        return $this->cookieFileName;
    }

    /**
     * Beállítja a kérés elküldéséhez csatolt cookie fájl nevét
     *
     * Erre akkor van szükség, ha több számlázási fiókhoz használod az Agent API-t.
     * Ebben az esetben számlázási fiókonként beállíthatod a session-hoz tartozó sütit
     *
     * @param $cookieFile
     */
    public function setCookieFileName($cookieFile) {
        $this->cookieFileName = $cookieFile;
    }

    /**
     * @return string
     */
    public function buildCookieFileName() {
        $fileName = 'cookie';
        return $fileName . '_' . $this->cookieIdentifier . '.txt';
    }

    /**
     * @return string
     */
    public function getCookieFilePath() {
        $fileName = $this->getCookieFileName();
        if (SzamlaAgentUtil::isBlank($fileName)) {
            $fileName = CookieHandler::COOKIE_FILENAME;
        }
        return SzamlaAgentUtil::getBasePath() . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @return string|null
     */
    public function getDefaultCookieFile() {
        return $this->getCookieFilePath();
    }

    /**
     * Ürítjük a cookie fájl-t ha nem tartalmazza a curl szöveget
     * @param $cookieFile
     * @return void
     */
    public function checkCookieFile($cookieFile) {
        if (file_exists($cookieFile) && filesize($cookieFile) > 0 && strpos(file_get_contents($cookieFile), 'curl') === false) {
            file_put_contents($cookieFile, "");
            $this->agent->writeLog("A cookie fájl tartalma megváltozott.", Log::LOG_LEVEL_DEBUG);
        }
    }

    /**
     * @param $cookieFile
     * @return bool
     */
    public function isUsableCookieFile($cookieFile) {
        return file_exists($cookieFile) && filesize($cookieFile) > 0;
    }
}