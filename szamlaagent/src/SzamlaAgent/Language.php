<?php

namespace SzamlaAgent;

/**
 * A Számla Agent-ben használható nyelvek
 *
 * @package SzamlaAgent
 */
class Language {
    /**
     * magyar nyelv
     */
    const LANGUAGE_HU = 'hu';

    /**
     * angol nyelv
     */
    const LANGUAGE_EN = 'en';

    /**
     * német nyelv
     */
    const LANGUAGE_DE = 'de';

    /**
     * olasz nyelv
     */
    const LANGUAGE_IT = 'it';

    /**
     * román nyelv
     */
    const LANGUAGE_RO = 'ro';

    /**
     * szlovák nyelv
     */
    const LANGUAGE_SK = 'sk';

    /**
     * horvát nyelv
     */
    const LANGUAGE_HR = 'hr';

    /**
     * francia nyelv
     */
    const LANGUAGE_FR = 'fr';

    /**
     * spanyol nyelv
     */
    const LANGUAGE_ES = 'es';

    /**
     * cseh nyelv
     */
    const LANGUAGE_CZ = 'cz';

    /**
     * lengyel nyelv
     */
    const LANGUAGE_PL = 'pl';

    /**
     * Számlázz.hu rendszerében használható nyelvek
     *
     * @var array
     */
    protected static $availableLanguages = [
        self::LANGUAGE_HU, self::LANGUAGE_EN, self::LANGUAGE_DE, self::LANGUAGE_IT,
        self::LANGUAGE_RO, self::LANGUAGE_SK, self::LANGUAGE_HR, self::LANGUAGE_FR,
        self::LANGUAGE_ES, self::LANGUAGE_CZ, self::LANGUAGE_PL
    ];

    /**
     * @return string
     */
    public static function getDefault() {
        return self::LANGUAGE_HU;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function getAll() {
        $reflector = new \ReflectionClass(new Language());
        $constants = $reflector->getConstants();

        $values = [];
        foreach($constants as $constant => $value) {
            $values[] = $value;
        }
        return $values;
    }

    /**
     * Nyelvkód alapján visszaadja annak elnevezését
     *
     * @param $language
     *
     * @return string
     */
    public static function getLanguageStr($language) {
        if ($language == null || $language == '' || $language === self::LANGUAGE_HU) {
            $result = "magyar";
        } else {
            switch ($language) {
                case self::LANGUAGE_EN: $result = "angol"; break;
                case self::LANGUAGE_DE: $result = "német"; break;
                case self::LANGUAGE_IT: $result = "olasz"; break;
                case self::LANGUAGE_RO: $result = "román"; break;
                case self::LANGUAGE_SK: $result = "szlovák"; break;
                case self::LANGUAGE_HR: $result = "horvát"; break;
                case self::LANGUAGE_FR: $result = "francia"; break;
                case self::LANGUAGE_ES: $result = "spanyol"; break;
                case self::LANGUAGE_CZ: $result = "cseh"; break;
                case self::LANGUAGE_PL: $result = "lengyel"; break;
                default:
                    $result = "ismeretlen"; break;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAvailableLanguages() {
        return self::$availableLanguages;
    }
 }