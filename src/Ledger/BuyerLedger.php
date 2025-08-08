<?php

namespace Kboss\SzamlaAgent\Ledger;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * A vevő főkönyvi adatai
 */
class BuyerLedger {

    /**
     * vevő gazdasági esemény azonosító
     *
     * @var string
     */
    public string $buyerId {
        get {
            return $this->buyerId;
        }
        set {
            $this->buyerId = $value;
        }
    }

    /**
     * Könyvelés dátum
     *
     * @var string
     */
    public string $bookingDate {
        get {
            return $this->bookingDate;
        }
        set {
            $this->bookingDate = $value;
        }
    }

    /**
     * Vevő főkönyvi szám
     *
     * @var string
     */
    public string $buyerLedgerNumber {
        get {
            return $this->buyerLedgerNumber;
        }
        set {
            $this->buyerLedgerNumber = $value;
        }
    }

    /**
     * Folyamatos teljesítés
     *
     * @var bool
     */
    public bool $continuedFulfillment {
        get {
            return $this->continuedFulfillment;
        }
        set {
            $this->continuedFulfillment = $value;
        }
    }

    /**
     * Elszámolási időszak kezdete
     *
     * @var string|null
     */
    public ?string $settlementPeriodStart = null {
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
     * @var string|null
     */
    public ?string $settlementPeriodEnd = null {
        get {
            return $this->settlementPeriodEnd;
        }
        set {
            $this->settlementPeriodEnd = $value;
        }
    }

    /**
     * Vevő főkönyvi adatok példányosítása
     *
     * @param string    $buyerId              vevő gazdasági esemény azonosító
     * @param string    $bookingDate          könyvelés dátum
     * @param string    $buyerLedgerNumber    vevő főkönyvi szám
     * @param bool      $continuedFulfillment folyamatos teljesítés
     */
    public function __construct(string $buyerId = '', string $bookingDate = '', string $buyerLedgerNumber = '', bool $continuedFulfillment = false) {
        $this->buyerId = $buyerId;
        $this->bookingDate = $bookingDate;
        $this->buyerLedgerNumber = $buyerLedgerNumber;
        $this->continuedFulfillment = $continuedFulfillment;
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array {
        $data = [];
        $this->validate();

        if (SzamlaAgentUtil::isNotBlank($this->bookingDate))           $data['konyvelesDatum'] = $this->bookingDate;
        if (SzamlaAgentUtil::isNotBlank($this->buyerId))               $data['vevoAzonosito'] = $this->buyerId;
        if (SzamlaAgentUtil::isNotBlank($this->buyerLedgerNumber))     $data['vevoFokonyviSzam'] = $this->buyerLedgerNumber;
        if ($this->continuedFulfillment)                               $data['folyamatosTelj'] = $this->continuedFulfillment;
        if (SzamlaAgentUtil::isNotBlank($this->settlementPeriodStart)) $data['elszDatumTol'] = $this->settlementPeriodStart;
        if (SzamlaAgentUtil::isNotBlank($this->settlementPeriodEnd))   $data['elszDatumIg'] = $this->settlementPeriodEnd;

        return $data;
    }

    /**
     * @throws SzamlaAgentException
     */
    private function validate(): void
    {
        $errors = [];

        if (SzamlaAgentUtil::isNotBlank($this->bookingDate)) {
            if (SzamlaAgentUtil::isNotValidDate($this->bookingDate)) {
                $errors[] = 'bookingDate';
            }
        }

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