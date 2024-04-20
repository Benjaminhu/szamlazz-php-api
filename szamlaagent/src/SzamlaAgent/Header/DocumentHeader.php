<?php

namespace SzamlaAgent\Header;

/**
 * Bizonylat fejléc
 *
 * @package szamlaagent\header
 */
class DocumentHeader {

    /**
     * A bizonylat számla-e
     *
     * @var bool
     */
    protected $invoice = false;

    /**
     * A bizonylat sztornó számla-e
     *
     * @var bool
     */
    protected $reserveInvoice = false;

    /**
     * A bizonylat előlegszámla-e
     *
     * @var bool
     */
    protected $prePayment = false;

    /**
     * A bizonylat végszámla-e
     *
     * @var bool
     */
    protected $final = false;

    /**
     * A bizonylat helyesbítő számla-e
     *
     * @var bool
     */
    protected $corrective = false;

    /**
     * A bizonylat díjbekérő-e
     *
     * @var bool
     */
    protected $proforma = false;

    /**
     * A bizonylat szállítólevél-e
     *
     * @var bool
     */
    protected $deliveryNote = false;

    /**
     * A bizonylat nyugta-e
     *
     * @var bool
     */
    protected $receipt = false;

    /**
     * A bizonylat sztornó nyugta-e
     *
     * @var bool
     */
    protected $reverseReceipt = false;


    /**
     * @return bool
     */
    public function isInvoice() {
        return $this->invoice;
    }

    /**
     * @param bool $invoice
     */
    public function setInvoice($invoice) {
        $this->invoice = $invoice;
    }

    /**
     * @return bool
     */
    public function isReserveInvoice() {
        return $this->reserveInvoice;
    }

    /**
     * @return bool
     */
    public function isNotReserveInvoice() {
        return !$this->reserveInvoice;
    }

    /**
     * @param bool $reserveInvoice
     */
    public function setReserveInvoice($reserveInvoice) {
        $this->reserveInvoice = $reserveInvoice;
    }

    /**
     * @return bool
     */
    public function isPrePayment() {
        return $this->prePayment;
    }

    /**
     * @param bool $prePayment
     */
    public function setPrePayment($prePayment) {
        $this->prePayment = $prePayment;
    }

    /**
     * @return bool
     */
    public function isFinal() {
        return $this->final;
    }

    /**
     * @param bool $final
     */
    public function setFinal($final) {
        $this->final = $final;
    }

    /**
     * @return bool
     */
    public function isCorrective() {
        return $this->corrective;
    }

    /**
     * @param bool $corrective
     */
    public function setCorrective($corrective) {
        $this->corrective = $corrective;
    }

    /**
     * @return bool
     */
    public function isProforma() {
        return $this->proforma;
    }

    /**
     * @param bool $proforma
     */
    public function setProforma($proforma) {
        $this->proforma = $proforma;
    }

    /**
     * @return bool
     */
    public function isDeliveryNote() {
        return $this->deliveryNote;
    }

    /**
     * @param bool $deliveryNote
     */
    public function setDeliveryNote($deliveryNote) {
        $this->deliveryNote = $deliveryNote;
    }

    /**
     * @return bool
     */
    public function isReceipt() {
        return $this->receipt;
    }

    /**
     * @param bool $receipt
     */
    public function setReceipt($receipt) {
        $this->receipt = $receipt;
    }

    /**
     * @return bool
     */
    public function isReverseReceipt() {
        return $this->reverseReceipt;
    }

    /**
     * @param bool $reverseReceipt
     */
    public function setReverseReceipt($reverseReceipt) {
        $this->reverseReceipt = $reverseReceipt;
    }
}