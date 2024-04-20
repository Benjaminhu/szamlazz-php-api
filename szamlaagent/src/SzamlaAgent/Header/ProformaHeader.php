<?php

namespace SzamlaAgent\Header;

use SzamlaAgent\SzamlaAgentException;
use SzamlaAgent\SzamlaAgentRequest;
use SzamlaAgent\SzamlaAgentUtil;

/**
 * Díjbekérő fejléc
 *
 * @package SzamlaAgent\Header
 */
class ProformaHeader extends InvoiceHeader {

    /**
     * XML-ben kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = [];

    /**
     * @throws \SzamlaAgent\SzamlaAgentException
     */
    function __construct() {
        parent::__construct();
        $this->setProforma(true);
        $this->setPaid(false);
    }

    /**
     * Összeállítja a bizonylat elkészítéséhez szükséges XML fejléc adatokat
     *
     * Csak azokat az XML mezőket adjuk hozzá, amelyek kötelezőek,
     * illetve amelyek opcionálisak, de ki vannak töltve.
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        try {
            if (empty($request)) {
                throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_NOT_AVAILABLE);
            }

            $data = [];
            switch ($request->getXmlName()) {
                case $request::XML_SCHEMA_DELETE_PROFORMA:
                    if (SzamlaAgentUtil::isNotBlank($this->getInvoiceNumber())) $data["szamlaszam"] = $this->getInvoiceNumber();
                    if (SzamlaAgentUtil::isNotBlank($this->getOrderNumber())) $data["rendelesszam"] = $this->getOrderNumber();
                    $this->checkFields();
                    break;
                default:
                    $data = parent::buildXmlData($request);
            }
            return $data;
        } catch (SzamlaAgentException $e) {
            throw $e;
        }
    }
}