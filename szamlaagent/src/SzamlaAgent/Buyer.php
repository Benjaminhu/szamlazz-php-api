<?php

namespace SzamlaAgent;

/**
 * Vevő
 *
 * @package SzamlaAgent
 */
class Buyer {

    /**
     * Vevő azonosítója
     *
     * @var string
     */
    protected $id;

    /**
     * Vevő neve
     *
     * @var string
     */
    protected $name;

    /**
     * Vevő országa
     *
     * @var string
     */
    protected $country;

    /**
     * Vevő irányítószáma
     *
     * @var string
     */
    protected $zipCode;

    /**
     * Vevő városa
     *
     * @var string
     */
    protected $city;

    /**
     * Vevő címe
     *
     * @var string
     */
    protected $address;

    /**
     * Vevő e-mail címe
     *
     * Ha meg van adva, akkor erre az email címre kiküldi a bizonylatot a Számlázz.hu.
     * Teszt fiók esetén biztonsági okokból nem küld a rendszer e-mailt!
     *
     * @var string
     */
    protected $email;

    /**
     * Küldjünk-e e-mailt az vevőnek
     *
     * @var bool
     */
    protected $sendEmail = true;

    /**
     * Vevő adóalany
     *
     * @var int
     */
    protected $taxPayer;

    /**
     * Vevő adószáma
     *
     * @var string
     */
    protected $taxNumber;

    /**
     * Csoport azonosító
     *
     * @var string
     */
    protected $groupIdentifier;

    /**
     * Vevó EU-s adószáma
     *
     * @var string
     */
    protected $taxNumberEU;

    /**
     * Vevő postázási neve
     * (A postázási adatok nem kötelezők)
     *
     * @var string
     */
    protected $postalName;

    /**
     * Vevő postázási országa
     *
     * @var string
     */
    protected $postalCountry;

    /**
     * Vevő postázási irányítószáma
     *
     * @var string
     */
    protected $postalZip;

    /**
     * Vevő postázási települése
     *
     * @var string
     */
    protected $postalCity;

    /**
     * Vevő postázási címe
     *
     * @var string
     */
    protected $postalAddress;

    /**
     * Vevő főkönyvi adatai
     *
     * @var BuyerLedger
     */
    protected $ledgerData;

    /**
     * Vevő aláíró neve
     *
     * Ha a beállítások oldalon (https://www.szamlazz.hu/szamla/beallitasok) be van kapcsolva,
     * akkor ez a név megjelenik az aláírásra szolgáló vonal alatt.
     *
     * @var string
     */
    protected $signatoryName;

    /**
     * Vevő telefonszáma
     *
     * @var string
     */
    protected $phone;

    /**
     * Vevőhöz tartozó megjegyzés
     *
     * @var string
     */
    protected $comment;

    /**
     * Kötelezően kitöltendő mezők
     *
     * @var array
     */
    protected $requiredFields = [];

    /**
     * Vevő példányosítása
     *
     * @param string $name    vevő név
     * @param string $zipCode vevő irányítószám
     * @param string $city    vevő település
     * @param string $address vevő cím
     */
    function __construct($name = '', $zipCode = '', $city = '', $address = '') {
        $this->setName($name);
        $this->setZipCode($zipCode);
        $this->setCity($city);
        $this->setAddress($address);
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
     * Ellenőrizzük a mező típusát
     *
     * @param $field
     * @param $value
     *
     * @return string
     * @throws SzamlaAgentException
     */
    protected function checkField($field, $value) {
        if (property_exists($this, $field)) {
            $required = in_array($field, $this->getRequiredFields());
            switch ($field) {
                case 'taxPayer':
                    SzamlaAgentUtil::checkIntField($field, $value, $required, __CLASS__);
                    break;
                case 'sendEmail':
                    SzamlaAgentUtil::checkBoolField($field, $value, $required, __CLASS__);
                    break;
                case 'id':
                case 'email':
                case 'name':
                case 'country':
                case 'zipCode':
                case 'city':
                case 'address':
                case 'taxNumber':
                case 'groupIdentifier':
                case 'taxNumberEU':
                case 'postalName':
                case 'postalCountry':
                case 'postalZip':
                case 'postalCity':
                case 'postalAddress':
                case 'signatoryName':
                case 'phone':
                case 'comment':
                    SzamlaAgentUtil::checkStrField($field, $value, $required, __CLASS__);
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
     * Létrehozza a vevő XML adatait a kérésben meghatározott XML séma alapján
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        $data = [];
        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_INVOICE:
                $this->setRequiredFields(['name', 'zip', 'city', 'address']);

                $data = [
                    "nev"       => $this->getName(),
                    "orszag"    => $this->getCountry(),
                    "irsz"      => $this->getZipCode(),
                    "telepules" => $this->getCity(),
                    "cim"       => $this->getAddress()
                ];

                if (SzamlaAgentUtil::isNotBlank($this->getEmail()))           $data["email"] = $this->getEmail();

                $data["sendEmail"] = $this->isSendEmail() ? true : false;

                if (SzamlaAgentUtil::isNotBlank($this->getTaxPayer()))        $data["adoalany"] = $this->getTaxPayer();
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumber()))       $data["adoszam"] = $this->getTaxNumber();
                if (SzamlaAgentUtil::isNotBlank($this->getGroupIdentifier())) $data["csoportazonosito"] = $this->getGroupIdentifier();
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumberEU()))     $data["adoszamEU"] = $this->getTaxNumberEU();
                if (SzamlaAgentUtil::isNotBlank($this->getPostalName()))      $data["postazasiNev"] = $this->getPostalName();
                if (SzamlaAgentUtil::isNotBlank($this->getPostalCountry()))   $data["postazasiOrszag"] = $this->getPostalCountry();
                if (SzamlaAgentUtil::isNotBlank($this->getPostalZip()))       $data["postazasiIrsz"] = $this->getPostalZip();
                if (SzamlaAgentUtil::isNotBlank($this->getPostalCity()))      $data["postazasiTelepules"] = $this->getPostalCity();
                if (SzamlaAgentUtil::isNotBlank($this->getPostalAddress()))   $data["postazasiCim"] = $this->getPostalAddress();

                if (SzamlaAgentUtil::isNotNull($this->getLedgerData())) {
                    $data["vevoFokonyv"] = $this->getLedgerData()->getXmlData();
                }

                if (SzamlaAgentUtil::isNotBlank($this->getId()))              $data["azonosito"] = $this->getId();
                if (SzamlaAgentUtil::isNotBlank($this->getSignatoryName()))   $data["alairoNeve"] = $this->getSignatoryName();
                if (SzamlaAgentUtil::isNotBlank($this->getPhone()))           $data["telefonszam"] = $this->getPhone();
                if (SzamlaAgentUtil::isNotBlank($this->getComment()))         $data["megjegyzes"] = $this->getComment();
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_INVOICE:
                if (SzamlaAgentUtil::isNotBlank($this->getEmail()))           $data["email"] = $this->getEmail();
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumber()))       $data["adoszam"] = $this->getTaxNumber();
                if (SzamlaAgentUtil::isNotBlank($this->getTaxNumberEU()))     $data["adoszamEU"] = $this->getTaxNumberEU();
                break;
            default:
                throw new SzamlaAgentException("Nincs ilyen XML séma definiálva: {$request->getXmlName()}");
        }
        $this->checkFields();

        return $data;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country) {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getZipCode() {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode($zipCode) {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city) {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Visszaadja, hogy küldünk-e e-mailt az vevőnek
     *
     * @return bool
     */
    public function isSendEmail() {
        return $this->sendEmail;
    }

    /**
     * Beállítja, hogy küldjünk-e e-mailt az vevőnek
     *
     * @param bool $sendEmail
     */
    public function setSendEmail($sendEmail) {
        $this->sendEmail = $sendEmail;
    }

    /**
     * Visszaadja, hogy a vevő milyen típusú adóalany
     *
     * @return int
     */
    public function getTaxPayer() {
        return $this->taxPayer;
    }

    /**
     * Beállítja, hogy a vevő milyen típusú adóalany.
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
     * @param int $taxPayer
     */
    public function setTaxPayer($taxPayer) {
        $this->taxPayer = $taxPayer;
    }

    /**
     * @return string
     */
    public function getTaxNumber() {
        return $this->taxNumber;
    }

    /**
     * @param string $taxNumber
     */
    public function setTaxNumber($taxNumber) {
        $this->taxNumber = $taxNumber;
    }

    /**
     * @return string
     */
    public function getGroupIdentifier() {
        return $this->groupIdentifier;
    }

    /**
     * @param string $groupIdentifier
     */
    public function setGroupIdentifier($groupIdentifier) {
        $this->groupIdentifier = $groupIdentifier;
    }

    /**
     * @return string
     */
    public function getTaxNumberEU() {
        return $this->taxNumberEU;
    }

    /**
     * @param string $taxNumberEU
     */
    public function setTaxNumberEU($taxNumberEU) {
        $this->taxNumberEU = $taxNumberEU;
    }

    /**
     * @return string
     */
    public function getPostalName() {
        return $this->postalName;
    }

    /**
     * @param string $postalName
     */
    public function setPostalName($postalName) {
        $this->postalName = $postalName;
    }

    /**
     * @return string
     */
    public function getPostalCountry() {
        return $this->postalCountry;
    }

    /**
     * @param string $postalCountry
     */
    public function setPostalCountry($postalCountry) {
        $this->postalCountry = $postalCountry;
    }

    /**
     * @return string
     */
    public function getPostalZip() {
        return $this->postalZip;
    }

    /**
     * @param string $postalZip
     */
    public function setPostalZip($postalZip) {
        $this->postalZip = $postalZip;
    }

    /**
     * @return string
     */
    public function getPostalCity() {
        return $this->postalCity;
    }

    /**
     * @param string $postalCity
     */
    public function setPostalCity($postalCity) {
        $this->postalCity = $postalCity;
    }

    /**
     * @return string
     */
    public function getPostalAddress() {
        return $this->postalAddress;
    }

    /**
     * @param string $postalAddress
     */
    public function setPostalAddress($postalAddress) {
        $this->postalAddress = $postalAddress;
    }

    /**
     * Visszaadja a vevő főkönyvi adatait
     *
     * @return BuyerLedger
     */
    public function getLedgerData() {
        return $this->ledgerData;
    }

    /**
     * Beállítja a vevő főkönyvi adatait
     *
     * @param BuyerLedger $ledgerData
     */
    public function setLedgerData(BuyerLedger $ledgerData) {
        $this->ledgerData = $ledgerData;
    }

    /**
     * Visszaadja a vevő aláírójának nevét
     *
     * @return string
     */
    public function getSignatoryName() {
        return $this->signatoryName;
    }

    /**
     * Beállítja a vevő aláírójának nevét
     *
     * Ha a beállítások oldalon (https://www.szamlazz.hu/szamla/beallitasok) be van kapcsolva,
     * akkor ez a név megjelenik az aláírásra szolgáló vonal alatt.
     *
     * @param string $signatoryName
     */
    public function setSignatoryName($signatoryName) {
        $this->signatoryName = $signatoryName;
    }

    /**
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone) {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }
}