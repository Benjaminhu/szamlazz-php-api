<?php

namespace SzamlaAgent;

/**
 * Egy bizonylathoz tartozó eladó
 *
 * @package SzamlaAgent
 */
class Seller {

    /**
     * Bank neve
     *
     * @var string
     */
    protected $bank;

    /**
     * Bankszámlaszám
     *
     * @var string
     */
    protected $bankAccount;

    /**
     * Válasz e-mail cím
     *
     * @var string
     */
    protected $emailReplyTo;

    /**
     * E-mail tárgya
     *
     * @var string
     */
    protected $emailSubject;

    /**
     * E-mail tartalma
     *
     * @var string
     */
    protected $emailContent;

    /**
     * Aláíró neve
     *
     * @var String
     */
    protected $signatoryName;

    /**
     * Eladó példányosítása banki adatokkal
     *
     * @param string $bank        banknév
     * @param string $bankAccount bankszámlaszám
     */
    function __construct($bank = '', $bankAccount = '') {
        $this->setBank($bank);
        $this->setBankAccount($bankAccount);
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
            switch ($field) {
                case 'bank':
                case 'bankAccount':
                case 'emailReplyTo':
                case 'emailSubject':
                case 'emailContent':
                case 'signatoryName':
                    SzamlaAgentUtil::checkStrField($field, $value, false, __CLASS__);
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
     * Létrehozza az eladó XML adatait a kérésben meghatározott XML séma alapján
     *
     * @param SzamlaAgentRequest $request
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request) {
        $data = [];

        $this->checkFields();

        switch ($request->getXmlName()) {
            case $request::XML_SCHEMA_CREATE_INVOICE:
                if (SzamlaAgentUtil::isNotBlank($this->getBank()))          $data["bank"] = $this->getBank();
                if (SzamlaAgentUtil::isNotBlank($this->getBankAccount()))   $data["bankszamlaszam"] = $this->getBankAccount();

                $emailData = $this->getXmlEmailData();
                if (!empty($emailData)) {
                    $data = array_merge($data, $emailData);
                }
                if (SzamlaAgentUtil::isNotBlank($this->getSignatoryName())) $data["alairoNeve"] = $this->getSignatoryName();
                break;
            case $request::XML_SCHEMA_CREATE_REVERSE_INVOICE:
                $data = $this->getXmlEmailData();
                break;
            default:
                throw new SzamlaAgentException( SzamlaAgentException::XML_SCHEMA_TYPE_NOT_EXISTS . ": {$request->getXmlName()}");
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function getXmlEmailData() {
        $data = [];
        if (SzamlaAgentUtil::isNotBlank($this->getEmailReplyTo()))  $data["emailReplyto"] = $this->getEmailReplyTo();
        if (SzamlaAgentUtil::isNotBlank($this->getEmailSubject()))  $data["emailTargy"] = $this->getEmailSubject();
        if (SzamlaAgentUtil::isNotBlank($this->getEmailContent()))  $data["emailSzoveg"] = $this->getEmailContent();
        return $data;
    }

    /**
     * Visszaadja a bank nevét
     *
     * @return string
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Beállítja a bank nevét
     *
     * @param string $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }

    /**
     * Visszaadja a bankszámlaszámot
     *
     * @return string
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Beállítja a bankszámlaszámot
     *
     * @param string $bankAccount
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
    }

    /**
     * Visszaadja a válasz e-mail címet
     *
     * @return string
     */
    public function getEmailReplyTo()
    {
        return $this->emailReplyTo;
    }

    /**
     * Beállítja a válasz e-mail címet
     *
     * @param string $emailReplyTo
     */
    public function setEmailReplyTo($emailReplyTo)
    {
        $this->emailReplyTo = $emailReplyTo;
    }

    /**
     * Visszaadja az e-mail tárgyát
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }

    /**
     * Beállítja az e-mail tárgyát
     *
     * @param string $emailSubject
     */
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;
    }

    /**
     * Visszaadja az e-mail tartalmát
     *
     * @return string
     */
    public function getEmailContent()
    {
        return $this->emailContent;
    }

    /**
     * Beállítja az e-mail tartalmát
     *
     * @param string $emailContent
     */
    public function setEmailContent($emailContent)
    {
        $this->emailContent = $emailContent;
    }

    /**
     * Visszaadja az aláíró nevét
     *
     * @return String
     */
    public function getSignatoryName()
    {
        return $this->signatoryName;
    }

    /**
     * Beállítja az aláíró nevét
     *
     * @param String $signatoryName
     */
    public function setSignatoryName($signatoryName)
    {
        $this->signatoryName = $signatoryName;
    }
}