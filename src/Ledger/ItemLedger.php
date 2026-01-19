<?php

namespace Kboss\SzamlaAgent\Ledger;

/**
 * Tétel főkönyvi adatok
 */
abstract class ItemLedger {

    /**
     * Árbevétel főkönyvi szám
     *
     * @var string
     */
    public string $revenueLedgerNumber {
        get {
            return $this->revenueLedgerNumber;
        }
        set {
            $this->revenueLedgerNumber = $value;
        }
    }

    /**
     * ÁFA főkönyvi szám
     *
     * @var string
     */
    public string $vatLedgerNumber {
        get {
            return $this->vatLedgerNumber;
        }
        set {
            $this->vatLedgerNumber = $value;
        }
    }

    /**
     * Tétel főkönyvi adatok létrehozása
     *
     * @param string $revenueLedgerNumber Árbevétel főkönyvi szám
     * @param string $vatLedgerNumber     ÁFA főkönyvi szám
     */
    protected function __construct(string $revenueLedgerNumber = '', string $vatLedgerNumber = '') {
        $this->revenueLedgerNumber = $revenueLedgerNumber;
        $this->vatLedgerNumber = $vatLedgerNumber;
    }

}