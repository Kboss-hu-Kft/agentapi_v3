<?php

namespace Kboss\SzamlaAgent\Item;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Override;
use Kboss\SzamlaAgent\Enums\VatType;
use Kboss\SzamlaAgent\Ledger\InvoiceItemLedger;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Számlatétel
 */
class InvoiceItem extends Item
{

    /**
     * Tételhez tartozó főkönyvi adatok
     *
     * @var InvoiceItemLedger | null
     */
    public ?InvoiceItemLedger $ledgerData = null {
        get {
            return $this->ledgerData;
        }
        set {
            $this->ledgerData = $value;
        }
    }

    /**
     * A tétel árrés ÁFA alapja
     *
     * @var float | null
     */
    protected ?float $priceGapVatBase = null {
        get {
            return $this->priceGapVatBase;
        }

        set {
            $this->priceGapVatBase = $value;
        }
    }

    /**
     * Számlatétel példányosítás
     *
     * @param string  $name          tétel név
     * @param float   $netUnitPrice  nettó egységár
     * @param float   $quantity      mennyiség
     * @param string  $quantityUnit  mennyiségi egység
     * @param VatType $vat           áfatartalom
     */
    public function __construct(string $name, float $netUnitPrice, float $quantity = self::DEFAULT_QUANTITY, string $quantityUnit = self::DEFAULT_QUANTITY_UNIT, VatType $vat = self::DEFAULT_VAT)
    {
        parent::__construct($name, $netUnitPrice, $quantity, $quantityUnit, $vat);
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] public function buildXmlData(): array {

        $this->validate();

        $data = [];

        $data['megnevezes']       = $this->name;
        if (SzamlaAgentUtil::isNotBlank($this->id))             $data['azonosito'] = $this->id;
        $data['mennyiseg']        = SzamlaAgentUtil::doubleFormat($this->quantity);
        $data['mennyisegiEgyseg'] = $this->quantityUnit;
        $data['nettoEgysegar']    = SzamlaAgentUtil::doubleFormat($this->netUnitPrice);
        $data['afakulcs']         = SzamlaAgentUtil::dotCheck($this->vat->value);
        if (SzamlaAgentUtil::isNotNull($this->priceGapVatBase)) $data['arresAfaAlap'] = SzamlaAgentUtil::doubleFormat($this->priceGapVatBase);
        $data['nettoErtek']       = SzamlaAgentUtil::doubleFormat($this->netPrice);
        $data['afaErtek']         = SzamlaAgentUtil::doubleFormat($this->vatAmount);
        $data['bruttoErtek']      = SzamlaAgentUtil::doubleFormat($this->grossAmount);

        if (SzamlaAgentUtil::isNotBlank($this->comment))        $data['megjegyzes'] = $this->comment;
        if (SzamlaAgentUtil::isNotNull($this->ledgerData))      $data['tetelFokonyv'] = $this->ledgerData->buildXmlData();
        if ($this->dataDeletionCode > 0)                        $data['torloKod'] = $this->dataDeletionCode;

        return $data;
    }
}