<?php

namespace Kboss\SzamlaAgent\Item;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Override;
use Kboss\SzamlaAgent\Enums\VatType;
use Kboss\SzamlaAgent\Ledger\ReceiptItemLedger;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugtatétel
 */
class ReceiptItem extends Item
{

    /**
     * Tételhez tartozó főkönyvi adatok
     *
     * @var ReceiptItemLedger | null
     */
    public ?ReceiptItemLedger $ledgerData = null {
        get {
            return $this->ledgerData;
        }

        set {
            $this->ledgerData = $value;
        }
    }

    /**
     * Nyugtatétel példányosítás
     *
     * @param string  $name         tétel név
     * @param float   $netUnitPrice nettó egységár
     * @param float   $quantity     mennyiség
     * @param string  $quantityUnit mennyiségi egység
     * @param VatType $vat          áfatartalom
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
        if (SzamlaAgentUtil::isNotBlank($this->id))       $data['azonosito']    = $this->id;
        $data['mennyiseg']        = SzamlaAgentUtil::doubleFormat($this->quantity);
        $data['mennyisegiEgyseg'] = $this->quantityUnit;
        $data['nettoEgysegar']    = SzamlaAgentUtil::doubleFormat($this->netUnitPrice);
        $data['afakulcs']         = $this->vat->value;
        $data['netto']            = round(SzamlaAgentUtil::doubleFormat($this->netPrice));
        $data['afa']              = round(SzamlaAgentUtil::doubleFormat($this->vatAmount));
        $data['brutto']           = round(SzamlaAgentUtil::doubleFormat($this->grossAmount));

        if (SzamlaAgentUtil::isNotNull($this->ledgerData)) $data['fokonyv'] = $this->ledgerData->buildXmlData();
        if (SzamlaAgentUtil::isNotBlank($this->comment))   $data['megjegyzes'] = $this->comment;
        if ($this->dataDeletionCode > 0)                   $data['torloKod'] = SzamlaAgentUtil::nonNegativeInteger($this->dataDeletionCode);

        return $data;
    }
 }