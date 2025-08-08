<?php

namespace Kboss\SzamlaAgent\Ledger;

use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugtatétel főkönyvi adatok
 */
class ReceiptItemLedger extends ItemLedger {

    /**
     * Tétel főkönyvi adatok létrehozása
     *
     * @param string  $revenueLedgerNumber   Árbevétel főkönyvi szám
     * @param string  $vatLedgerNumber       ÁFA főkönyvi szám
     */
    function __construct(string $revenueLedgerNumber = '', string $vatLedgerNumber = '') {
        parent::__construct($revenueLedgerNumber, $vatLedgerNumber);
    }

    /**
     * @return array
     */
    public function buildXmlData(): array {
        $data = [];

        if (SzamlaAgentUtil::isNotBlank($this->revenueLedgerNumber)) $data['arbevetel'] = $this->revenueLedgerNumber;
        if (SzamlaAgentUtil::isNotBlank($this->vatLedgerNumber))     $data['afa'] = $this->vatLedgerNumber;

        return $data;
    }
}