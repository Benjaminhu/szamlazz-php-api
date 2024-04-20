<?php

namespace SzamlaAgent;

/**
 * A Számla Agent-ben használható valuták
 *
 * @package SzamlaAgent
 */
class Currency {
    // forint
    const CURRENCY_FT  = 'Ft';
    // forint
    const CURRENCY_HUF = 'HUF';
    // euró
    const CURRENCY_EUR = 'EUR';
    // svájci frank
    const CURRENCY_CHF = 'CHF';
    // amerikai dollár
    const CURRENCY_USD = 'USD';
    // Arab Emírségek dirham
    const CURRENCY_AED = 'AED';
    // ausztrál dollár
    const CURRENCY_AUD = 'AUD';
    // bolgár leva
    const CURRENCY_BGN = 'BGN';
    // brazil real
    const CURRENCY_BRL = 'BRL';
    // kanadai dollár
    const CURRENCY_CAD = 'CAD';
    // kínai jüan
    const CURRENCY_CNY = 'CNY';
    // cseh korona
    const CURRENCY_CZK = 'CZK';
    // dán korona
    const CURRENCY_DKK = 'DKK';
    // észt korona
    const CURRENCY_EEK = 'EEK';
    // angol font
    const CURRENCY_GBP = 'GBP';
    // hongkongi dollár
    const CURRENCY_HKD = 'HKD';
    // horvát kún
    const CURRENCY_HRK = 'HRK';
    // indonéz rúpia
    const CURRENCY_IDR = 'IDR';
    // izraeli sékel
    const CURRENCY_ILS = 'ILS';
    // indiai rúpia
    const CURRENCY_INR = 'INR';
    // izlandi korona
    const CURRENCY_ISK = 'ISK';
    // japán jen
    const CURRENCY_JPY = 'JPY';
    // dél-koreai won
    const CURRENCY_KRW = 'KRW';
    // litván litas
    const CURRENCY_LTL = 'LTL';
    // lett lat
    const CURRENCY_LVL = 'LVL';
    // mexikói peso
    const CURRENCY_MXN = 'MXN';
    // maláj ringgit
    const CURRENCY_MYR = 'MYR';
    // norvég koro
    const CURRENCY_NOK = 'NOK';
    // új-zélandi dollár
    const CURRENCY_NZD = 'NZD';
    // fülöp-szigeteki peso
    const CURRENCY_PHP = 'PHP';
    // lengyel zloty
    const CURRENCY_PLN = 'PLN';
    // új román lej
    const CURRENCY_RON = 'RON';
    // szerb dínár
    const CURRENCY_RSD = 'RSD';
    // orosz rubel
    const CURRENCY_RUB = 'RUB';
    // svéd koron
    const CURRENCY_SEK = 'SEK';
    // szingapúri dollár
    const CURRENCY_SGD = 'SGD';
    // thai bát
    const CURRENCY_THB = 'THB';
    // török líra
    const CURRENCY_TRY = 'TRY';
    // ukrán hryvna
    const CURRENCY_UAH = 'UAH';
    // vietnámi dong
    const CURRENCY_VND = 'VND';
    // dél-afrikai rand
    const CURRENCY_ZAR = 'ZAR';

    /**
     * @return string
     */
    public static function getDefault() {
        return self::CURRENCY_FT;
    }

    /**
     * A valuta kódja alapján visszaadja annak elnevezését
     *
     * @param $currency
     *
     * @return string
     */
    public static function getCurrencyStr($currency) {
        if ($currency == null || $currency == '' || $currency === "Ft" || $currency == "HUF") {
            $result = "forint";
        } else {
            switch ($currency) {
                case self::CURRENCY_EUR: $result = "euró"; break;
                case self::CURRENCY_USD: $result = "amerikai dollár"; break;
                case self::CURRENCY_AUD: $result = "ausztrál dollár"; break;
                case self::CURRENCY_AED: $result = "Arab Emírségek dirham"; break;
                case self::CURRENCY_BRL: $result = "brazil real"; break;
                case self::CURRENCY_CAD: $result = "kanadai dollár"; break;
                case self::CURRENCY_CHF: $result = "svájci frank"; break;
                case self::CURRENCY_CNY: $result = "kínai jüan"; break;
                case self::CURRENCY_CZK: $result = "cseh korona"; break;
                case self::CURRENCY_DKK: $result = "dán korona"; break;
                case self::CURRENCY_EEK: $result = "észt korona"; break;
                case self::CURRENCY_GBP: $result = "angol font"; break;
                case self::CURRENCY_HKD: $result = "hongkongi dollár"; break;
                case self::CURRENCY_HRK: $result = "horvát kúna"; break;
                case self::CURRENCY_ISK: $result = "izlandi korona"; break;
                case self::CURRENCY_JPY: $result = "japán jen"; break;
                case self::CURRENCY_LTL: $result = "litván litas"; break;
                case self::CURRENCY_LVL: $result = "lett lat"; break;
                case self::CURRENCY_MXN: $result = "mexikói peso"; break;
                case self::CURRENCY_NOK: $result = "norvég koron"; break;
                case self::CURRENCY_NZD: $result = "új-zélandi dollár"; break;
                case self::CURRENCY_PLN: $result = "lengyel zloty"; break;
                case self::CURRENCY_RON: $result = "új román lej"; break;
                case self::CURRENCY_RUB: $result = "orosz rubel"; break;
                case self::CURRENCY_SEK: $result = "svéd koron"; break;
                case self::CURRENCY_UAH: $result = "ukrán hryvna"; break;
                case self::CURRENCY_BGN: $result = "bolgár leva"; break;
                case self::CURRENCY_RSD: $result = "szerb dínár"; break;
                case self::CURRENCY_ILS: $result = "izraeli sékel"; break;
                case self::CURRENCY_IDR: $result = "indonéz rúpia"; break;
                case self::CURRENCY_INR: $result = "indiai rúpia"; break;
                case self::CURRENCY_TRY: $result = "török líra"; break;
                case self::CURRENCY_VND: $result = "vietnámi dong"; break;
                case self::CURRENCY_SGD: $result = "szingapúri dollár"; break;
                case self::CURRENCY_THB: $result = "thai bát"; break;
                case self::CURRENCY_KRW: $result = "dél-koreai won"; break;
                case self::CURRENCY_MYR: $result = "maláj ringgit"; break;
                case self::CURRENCY_PHP: $result = "fülöp-szigeteki peso"; break;
                case self::CURRENCY_ZAR: $result = "dél-afrikai rand"; break;
                default:
                    $result = "ismeretlen"; break;
            }
        }
        return $result;
    }
 }