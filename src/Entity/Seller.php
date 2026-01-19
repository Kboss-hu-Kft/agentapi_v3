<?php

namespace Kboss\SzamlaAgent\Entity;

use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Egy bizonylathoz tartozó eladó
 */
class Seller
{

    /**
     * Bank neve
     *
     * @var string
     */
    public string $bank {
        get {
            return $this->bank;
        }
        set {
            $this->bank = $value;
        }
    }

    /**
     * Bankszámlaszám
     *
     * @var string
     */
    public string $bankAccount {
        get {
            return $this->bankAccount;
        }
        set {
            $this->bankAccount = $value;
        }
    }

    /**
     * Válasz e-mail cím
     *
     * @var string
     */
    public string $emailReplyTo {
        get {
            return $this->emailReplyTo;
        }
        set {
            $this->emailReplyTo = $value;
        }
    }

    /**
     * E-mail tárgya
     *
     * @var string
     */
    public string $emailSubject {
        get {
            return $this->emailSubject;
        }
        set {
            $this->emailSubject = $value;
        }
    }

    /**
     * E-mail tartalma
     *
     * @var string
     */
    public string $emailContent {
        get {
            return $this->emailContent;
        }
        set {
            $this->emailContent = $value;
        }
    }

    /**
     * Aláíró neve
     *
     * @var String
     */
    public string $signatoryName {
        get {
            return $this->signatoryName;
        }
        set {
            $this->signatoryName = $value;
        }
    }

    /**
     * Eladó példányosítása banki adatokkal
     *
     * @param string $bank        banknév
     * @param string $bankAccount bankszámlaszám
     */
    function __construct(string $bank = '', string $bankAccount = '')
    {
        $this->bank = $bank;
        $this->bankAccount = $bankAccount;
    }

    /**
     * Létrehozza az eladó XML adatait a kérésben meghatározott XML séma alapján
     *
     * @param RequestType $requestType
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(RequestType $requestType): array
    {
        $data = [];

        switch ($requestType) {
            case RequestType::GENERATE_PROFORMA:
            case RequestType::GENERATE_INVOICE:
            case RequestType::GENERATE_PREPAYMENT_INVOICE:
            case RequestType::GENERATE_FINAL_INVOICE:
            case RequestType::GENERATE_CORRECTIVE_INVOICE:
            case RequestType::GENERATE_DELIVERY_NOTE:
                if (SzamlaAgentUtil::isNotBlank($this->bank))          $data["bank"] = $this->bank;
                if (SzamlaAgentUtil::isNotBlank($this->bankAccount))   $data["bankszamlaszam"] = $this->bankAccount;

                $emailData = $this->getXmlEmailData();
                if (!empty($emailData)) {
                    $data = array_merge($data, $emailData);
                }
                if (SzamlaAgentUtil::isNotBlank($this->signatoryName)) $data["alairoNeve"] = $this->signatoryName;
                break;
            case RequestType::GENERATE_REVERSE_INVOICE:
                $data = $this->getXmlEmailData();
                break;
            default:
                throw new SzamlaAgentException( SzamlaAgentException::INVALID_REQUEST_TYPE . ": " . $requestType->name);
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function getXmlEmailData(): array
    {
        $data = [];
        if (SzamlaAgentUtil::isNotBlank($this->emailReplyTo))  $data["emailReplyto"] = $this->emailReplyTo;
        if (SzamlaAgentUtil::isNotBlank($this->emailSubject))  $data["emailTargy"] = $this->emailSubject;
        if (SzamlaAgentUtil::isNotBlank($this->emailContent))  $data["emailSzoveg"] = $this->emailContent;
        return $data;
    }

}