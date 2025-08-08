<?php

namespace Kboss\SzamlaAgent\Ledger;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Számlatétel főkönyvi adatok
 */
class InvoiceItemLedger extends ItemLedger {

    /**
     * Gazdasági esemény típus
     *
     * @var string
     */
    public string $economicEventType {
        get {
            return $this->economicEventType;
        }
        set {
            $this->economicEventType = $value;
        }
    }

    /**
     * ÁFA gazdasági esemény típus
     *
     * @var string
     */
    public string $vatEconomicEventType {
        get {
            return $this->vatEconomicEventType;
        }
        set {
            $this->vatEconomicEventType = $value;
        }
    }

    /**
     * Elszámolási időszak kezdete
     *
     * @var string
     */
    public string $settlementPeriodStart = '' {
        get {
            return $this->settlementPeriodStart;
        }
        set {
            $this->settlementPeriodStart = $value;
        }
    }

    /**
     * Elszámolási időszak vége
     *
     * @var string
     */
    public string $settlementPeriodEnd = '' {
        get {
            return $this->settlementPeriodEnd;
        }
        set {
            $this->settlementPeriodEnd = $value;
        }
    }

    /**
     * Tétel főkönyvi adatok létrehozása
     *
     * @param string  $economicEventType     Gazdasági esemény típus
     * @param string  $vatEconomicEventType  ÁFA gazdasági esemény típus
     * @param string  $revenueLedgerNumber   Árbevétel főkönyvi szám
     * @param string  $vatLedgerNumber       ÁFA főkönyvi szám
     */
    function __construct(string $economicEventType = '', string $vatEconomicEventType = '', string $revenueLedgerNumber = '', string $vatLedgerNumber = '') {
        parent::__construct($revenueLedgerNumber, $vatLedgerNumber);
        $this->economicEventType = $economicEventType;
        $this->vatEconomicEventType = $vatEconomicEventType;
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array {
        $data = [];

        $this->validate();

        if (SzamlaAgentUtil::isNotBlank($this->economicEventType))          $data['gazdasagiEsem'] = $this->economicEventType;
        if (SzamlaAgentUtil::isNotBlank($this->vatEconomicEventType))       $data['gazdasagiEsemAfa'] = $this->vatEconomicEventType;
        if (SzamlaAgentUtil::isNotBlank($this->revenueLedgerNumber))        $data['arbevetelFokonyviSzam'] = $this->revenueLedgerNumber;
        if (SzamlaAgentUtil::isNotBlank($this->vatLedgerNumber))            $data['afaFokonyviSzam'] = $this->vatLedgerNumber;
        if (SzamlaAgentUtil::isNotBlank($this->settlementPeriodStart))      $data['elszDatumTol'] = $this->settlementPeriodStart;
        if (SzamlaAgentUtil::isNotBlank($this->settlementPeriodEnd))        $data['elszDatumIg'] = $this->settlementPeriodEnd;

        return $data;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function validate(): void
    {
        $errors = [];

        if (SzamlaAgentUtil::isNotBlank($this->settlementPeriodStart)) {
            if (SzamlaAgentUtil::isNotValidDate($this->settlementPeriodStart)) {
                $errors[] = 'settlementPeriodStart';
            }
        }
        if (SzamlaAgentUtil::isNotBlank($this->settlementPeriodEnd)) {
            if (SzamlaAgentUtil::isNotValidDate($this->settlementPeriodEnd)) {
                $errors[] = 'settlementPeriodEnd';
            }
        }

        if (!empty($errors)) {
            $fields = implode(",", $errors);
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_VALUE . ' => ' . $fields);
        }
    }
}