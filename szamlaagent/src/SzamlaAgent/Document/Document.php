<?php

namespace SzamlaAgent\Document;

use SzamlaAgent\Currency;
use SzamlaAgent\Language;

/**
 * Bizonylat
 *
 * @package szamlaagent\document
 */
class Document {

    /**
     * Fizetési módok
     */
    const PAYMENT_METHOD_TRANSFER         = 'átutalás';
    const PAYMENT_METHOD_CASH             = 'készpénz';
    const PAYMENT_METHOD_BANKCARD         = 'bankkártya';
    const PAYMENT_METHOD_CHEQUE           = 'csekk';
    const PAYMENT_METHOD_CASH_ON_DELIVERY = 'utánvét';
    const PAYMENT_METHOD_PAYPAL           = 'PayPal';
    const PAYMENT_METHOD_SZEP_CARD        = 'SZÉP kártya';
    const PAYMENT_METHOD_OTP_SIMPLE       = 'OTP Simple';

    /**
     * Normál számla
     */
    const DOCUMENT_TYPE_INVOICE = 'invoice';

    /**
     * Normál számla kódja
     */
    const DOCUMENT_TYPE_INVOICE_CODE = 'SZ';

    /**
     * Sztornó számla
     */
    const DOCUMENT_TYPE_REVERSE_INVOICE = 'reverseInvoice';

    /**
     * Sztornó számla kódja
     */
    const DOCUMENT_TYPE_REVERSE_INVOICE_CODE = 'SS';

    /**
     * Jóváíró számla
     */
    const DOCUMENT_TYPE_PAY_INVOICE = 'payInvoice';

    /**
     * Jóváíró számla kódja
     */
    const DOCUMENT_TYPE_PAY_INVOICE_CODE = 'JS';

    /**
     * Helyesbítő számla
     */
    const DOCUMENT_TYPE_CORRECTIVE_INVOICE = 'correctiveInvoice';

    /**
     * Helyesbítő számla kódja
     */
    const DOCUMENT_TYPE_CORRECTIVE_INVOICE_CODE = 'HS';

    /**
     * Előlegszámla
     */
    const DOCUMENT_TYPE_PREPAYMENT_INVOICE = 'prePaymentInvoice';

    /**
     * Előlegszámla kódja
     */
    const DOCUMENT_TYPE_PREPAYMENT_INVOICE_CODE = 'ES';

    /**
     * Végszámla
     */
    const DOCUMENT_TYPE_FINAL_INVOICE = 'finalInvoice';

    /**
     * Végszámla kódja
     */
    const DOCUMENT_TYPE_FINAL_INVOICE_CODE = 'VS';

    /**
     * Díjbekérő
     */
    const DOCUMENT_TYPE_PROFORMA = 'proforma';

    /**
     * Díjbekérő kódja
     */
    const DOCUMENT_TYPE_PROFORMA_CODE = 'D';

    /**
     * Szállítólevél
     */
    const DOCUMENT_TYPE_DELIVERY_NOTE = 'deliveryNote';

    /**
     * Szállítólevél kódja
     */
    const DOCUMENT_TYPE_DELIVERY_NOTE_CODE = 'SL';

    /**
     * Nyugta
     */
    const DOCUMENT_TYPE_RECEIPT = 'receipt';

    /**
     * Nyugta kódja
     */
    const DOCUMENT_TYPE_RECEIPT_CODE = 'NY';

    /**
     * Nyugta sztornó
     */
    const DOCUMENT_TYPE_RESERVE_RECEIPT = 'reserveReceipt';

    /**
     * Nyugta sztornó kódja
     */
    const DOCUMENT_TYPE_RESERVE_RECEIPT_CODE = 'SN';


    /**
     * @return string
     */
    public static function getDefaultCurrency() {
        return Currency::getDefault();
    }

    /**
     * @return string
     */
    public static function getDefaultLanguage() {
        return Language::getDefault();
    }
}