<?php

namespace SzamlaAgent;

/**
 * Adózó
 *
 * @package SzamlaAgent
 */
class TaxPayer {

    /**
     * EU-n kívüli vállalkozás
     */
    const TAXPAYER_NON_EU_ENTERPRISE = 7;

    /**
     * EU-s vállalkozás
     */
    const TAXPAYER_EU_ENTERPRISE = 6;

    /**
     * Társas vállalkozás (Bt., Kft., zRt.)
     *
     * @deprecated 2.9.5 Ne használd, helyette használd ezt: TaxPayer::TAXPAYER_HAS_TAXNUMBER.
     */
    const TAXPAYER_JOINT_VENTURE = 5;

    /**
     * Egyéni vállalkozó
     *
     * @deprecated 2.9.5 Ne használd, helyette használd ezt: TaxPayer::TAXPAYER_HAS_TAXNUMBER.
     */
    const TAXPAYER_INDIVIDUAL_BUSINESS = 4;

    /**
     * Adószámos magánszemély
     *
     * @deprecated 2.9.5 Ne használd, helyette használd ezt: TaxPayer::TAXPAYER_HAS_TAXNUMBER.
     */
    const TAXPAYER_PRIVATE_INDIVIDUAL_WITH_TAXNUMBER = 3;

    /**
     * Adószámos egyéb szervezet
     *
     * @deprecated 2.9.5 Ne használd, helyette használd ezt: TaxPayer::TAXPAYER_HAS_TAXNUMBER.
     */
    const TAXPAYER_OTHER_ORGANIZATION_WITH_TAXNUMBER = 2;

    /**
     * Van magyar adószáma
     */
    const TAXPAYER_HAS_TAXNUMBER = 1;

    /**
     * Nem tudjuk, hogy adóalany-e
     */
    const TAXPAYER_WE_DONT_KNOW = 0;

    /**
     * Nincs adószáma
     */
    const TAXPAYER_NO_TAXNUMBER = -1;

    /**
     * Magánszemély
     *
     * @deprecated 2.9.5 Ne használd, helyette használd ezt: TaxPayer::TAXPAYER_NO_TAXNUMBER.
     */
    const TAXPAYER_PRIVATE_INDIVIDUAL = -2;

    /**
     * Adószám nélküli egyéb szervezet
     *
     * @deprecated 2.9.5 Ne használd, helyette használd ezt: TaxPayer::TAXPAYER_NO_TAXNUMBER.
     */
    const TAXPAYER_OTHER_ORGANIZATION_WITHOUT_TAXNUMBER = -3;

    /**
     * Törzsszám
     *
     * @var string
     */
    protected $taxPayerId;

    /**
     * Az adózó milyen típusú adóalany
     *
     * @var int
     */
    protected $taxPayerType;

    /**
     * Kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = ['taxPayerId'];

    /**
     * Adózó (adóalany) példányosítás
     *
     * @param string $taxpayerId
     * @param int    $taxPayerType
     */
    function __construct($taxpayerId = '', $taxPayerType = self::TAXPAYER_WE_DONT_KNOW) {
        $this->setTaxPayerId($taxpayerId);
        $this->setTaxPayerType($taxPayerType);
    }

    /**
     * @return array
     */
    protected function getRequiredFields() {
        return $this->requiredFields;
    }

    /**
     * @param array $requiredFields
     */
    protected function setRequiredFields(array $requiredFields) {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @return int
     */
    public function getDefault() {
        return self::TAXPAYER_WE_DONT_KNOW;
    }

    /**
     * Ellenőrizzük a mező típusát
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return string
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value) {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'taxPayerType':
                    SzamlaAgentUtil::checkIntField($field, $value, $required, __CLASS__);
                    break;
                case 'taxPayerId':
                    SzamlaAgentUtil::checkStrFieldWithRegExp($field, $value, false, __CLASS__, '/[0-9]{8}/');
                    break;
            }
        }
        return $value;
    }

    /**
     * Ellenőrizzük a tulajdonságokat
     *
     * @throws SzamlaAgentException
     */
    protected function checkFields() {
        $fields = get_object_vars($this);
        foreach ($fields as $field => $value) {
            $this->checkField($field, $value);
        }
    }

    /**
     * Összeállítja az adózó XML adatait
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        $this->checkFields();

        $data = [];
        $data["beallitasok"] = $request->getAgent()->getSetting()->buildXmlData($request);
        $data["torzsszam"]   = $this->getTaxPayerId();

        return $data;
    }

    /**
     * @return string
     */
    public function getTaxPayerId() {
        return $this->taxPayerId;
    }

    /**
     * @param string $taxPayerId
     */
    public function setTaxPayerId($taxPayerId) {
        $this->taxPayerId = substr($taxPayerId, 0,8);
    }

    /**
     * @return int
     */
    public function getTaxPayerType() {
        return $this->taxPayerType;
    }

    /**
     * Adózó milyen típusú adóalany.
     * Ezt az információt a partner adatként tárolja a rendszerben, ott módosítható is.
     *
     * A következő értékeket veheti fel ez a mező:
     *  7: TaxPayer::TAXPAYER_NON_EU_ENTERPRISE - EU-n kívüli vállalkozás
     *  6: TaxPayer::TAXPAYER_EU_ENTERPRISE     - EU-s vállalkozás
     *  1: TaxPayer::TAXPAYER_HAS_TAXNUMBER     - van magyar adószáma
     *  0: TaxPayer::TAXPAYER_WE_DONT_KNOW      - nem tudjuk
     * -1: TaxPayer::TAXPAYER_NO_TAXNUMBER      - nincs adószáma
     *
     * @see https://tudastar.szamlazz.hu/gyik/vevo-adoszama-szamlan
     *
     * @param int $taxPayerType
     */
    public function setTaxPayerType($taxPayerType) {
        $this->taxPayerType = $taxPayerType;
    }
}