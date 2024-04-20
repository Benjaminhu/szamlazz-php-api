<?php

namespace SzamlaAgent\CreditNote;

use SzamlaAgent\Document\Document;

/**
 * Jóváírás
 *
 * @package SzamlaAgent\CreditNote
 */
class CreditNote {

    /**
     * Jóváírás jogcíme
     * (fizetőeszköz megnevezése)
     *
     * @var string
     */
    protected $paymentMode;

    /**
     * Jóváírás összege
     * (fizetőeszközzel kiegyenlített összeg)
     *
     * @var double
     */
    protected $amount;

    /**
     * Jóváírás egyedi leírása
     *
     * @var string
     */
    protected $description = '';

    /**
     * Kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = ['paymentMode', 'amount'];

    /**
     * Jóváírás létrehozása
     *
     * @param string $paymentMode jóváírás jogcíme (fizetési módja)
     * @param double $amount      jóváírás összege
     * @param string $description jóváírás leírása
     */
    protected function __construct($paymentMode = Document::PAYMENT_METHOD_TRANSFER, $amount = 0.0, $description = '') {
        $this->setPaymentMode($paymentMode);
        $this->setAmount($amount);
        $this->setDescription($description);
    }

    /**
     * @return array
     */
    protected function getRequiredFields() {
        return $this->requiredFields;
    }

    /**
     * @return string
     */
    public function getPaymentMode() {
        return $this->paymentMode;
    }

    /**
     * @param string $paymentMode
     */
    public function setPaymentMode($paymentMode) {
        $this->paymentMode = $paymentMode;
    }

    /**
     * @return float
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount) {
        $this->amount = (float)$amount;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }
 }