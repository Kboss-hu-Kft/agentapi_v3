<?php

namespace Kboss\SzamlaAgent;

use Kboss\SzamlaAgent\Enums\RequestType;

/**
 * Adózó
 *
 * @package SzamlaAgent
 */
class TaxPayer {

    /**
     * Törzsszám
     *
     * @var string
     */
    protected string $taxPayerId {
        get {
            return $this->taxPayerId;
        }
        set {
            $this->taxPayerId = substr($value, 0, 8);
        }
    }

    /**
     * @var bool
     */
    private bool $taxpayerValidity = false {
        get {
            return $this->taxpayerValidity;
        }
        set {
            $this->taxpayerValidity = $value;
        }
    }

    /**
     * Adózó (adóalany) példányosítás
     *
     * @param string        $taxpayerId
     */
    function __construct(string $taxpayerId = '') {
        $this->taxPayerId = $taxpayerId;
    }

    /**
     * Összeállítja az adózó XML adatait
     *
     * @param SzamlaAgentSetting $setting
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentSetting $setting): array {
        $this->validate();

        $data = [];

        $data["beallitasok"] = $setting->buildXmlData(RequestType::GET_TAX_PAYER, $this);
        $data["torzsszam"]   = $this->taxPayerId;

        return $data;
    }

    /**
     * @return void
     * @throws SzamlaAgentException
     */
    protected function validate(): void
    {
        if (SzamlaAgentUtil::isBlank($this->taxPayerId)) {
            throw new SzamlaAgentException(SzamlaAgentException::MISSING_TAXPAYERID);
        }
    }
}