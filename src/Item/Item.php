<?php

namespace Kboss\SzamlaAgent\Item;

use Kboss\SzamlaAgent\Enums\VatType;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Tétel
 */
abstract class Item
{

    /**
     * Alapértelmezett ÁFA érték
     */
    const VatType DEFAULT_VAT = VatType::VAT_27;

    /**
     * Alapértelmezett mennyiség
     */
    const float DEFAULT_QUANTITY = 1.0;

    /**
     * Alapértelmezett mennyiségi egység
     */
    const string DEFAULT_QUANTITY_UNIT = 'db';

    /**
     * Tétel azonosító
     *
     * @var string | null
     */
    public ?string $id = null {
        get {
            return $this->id;
        }

        set {
            $this->id = $value;
        }
    }

    /**
     * Tétel neve
     *
     * @var string
     */
    public string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = $value;
        }
    }

    /**
     * Tétel mennyisége
     * Az értékesített mennyiség, pl. '10' vagy '2,5'
     *
     * @var float
     */
    public float $quantity {
        get {
            return $this->quantity;
        }
        set {
            $this->quantity = $value;
        }
    }

    /**
     * Tétel mennyiségi egysége
     * (pl. darab, óra, stb.)
     *
     * @var string
     */
    public string $quantityUnit {
        get {
            return $this->quantityUnit;
        }
        set {
            $this->quantityUnit = $value;
        }
    }

    /**
     * Nettó egységár
     * A számla tétel 1 darabra (vagy más mértékegységre) vetített nettó ára
     *
     * @var float
     */
    public float $netUnitPrice {
        get {
            return $this->netUnitPrice;
        }
        set {
            $this->netUnitPrice = $value;
        }
    }

    /**
     * Áfa kulcs
     *
     * Ugyanaz adható meg, mint a számlakészítés oldalon:
     * https://www.szamlazz.hu/szamla/szamlaszerkeszto
     *
     * @var VatType
     */
    public VatType $vat {
        get {
            return $this->vat;
        }
        set {
            $this->vat = $value;
        }
    }

    /**
     * Tétel nettó értéke
     * (nettó egységár szorozva az értékesített mennyiséggel)
     *
     * @var float
     */
    public float $netPrice {
        get {
            return $this->netPrice;
        }
        set {
            $this->netPrice = $value;
        }
    }

    /**
     * Tétel ÁFA értéke
     * (a nettó érték alapján az áfakulccsal kalkulált áfa érték)
     *
     * @var float
     */
    public float $vatAmount {
        get {
            return $this->vatAmount;
        }
        set {
            $this->vatAmount = $value;
        }
    }

    /**
     * Tétel bruttó értéke
     * (a nettó érték és az áfa érték összege)
     *
     * @var float
     */
    public float $grossAmount {
        get {
            return $this->grossAmount;
        }
        set {
            $this->grossAmount = $value;
        }
    }

    /**
     * Tétel megjegyzése
     *
     * @var string
     */
    public string $comment = '' {
        get {
            return $this->comment;
        }
        set {
            $this->comment = $value;
        }
    }

    /**
     * Hány adattörlő kódot kérünk
     * @var int
     */
    public int $dataDeletionCode = 0 {
        get {
            return $this->dataDeletionCode;
        }
        set {
            $this->dataDeletionCode = max($value, 0);
        }
    }

    /**
     * @return array
     */
    public abstract function buildXmlData(): array;

    /**
     * Tétel példányosítás
     *
     * @param string  $name          tétel név
     * @param float   $netUnitPrice  nettó egységár
     * @param float   $quantity      mennyiség
     * @param string  $quantityUnit  mennyiségi egység
     * @param VatType $vat           áfatartalom
     */
    protected function __construct(string $name, float $netUnitPrice, float $quantity = self::DEFAULT_QUANTITY, string $quantityUnit = self::DEFAULT_QUANTITY_UNIT, VatType $vat = self::DEFAULT_VAT)
    {
        $this->name = $name;
        $this->netUnitPrice = $netUnitPrice;
        $this->quantity = $quantity;
        $this->quantityUnit = $quantityUnit;
        $this->vat = $vat;
    }

    /**
     * @return void
     * @throws SzamlaAgentException
     */
    protected function validate(): void
    {
        $errors = [];

        if (SzamlaAgentUtil::isBlank($this->name)) {
            $errors[] = "name";
        }

        if ($this->quantity === 0.0) {
            $errors[] = "quantity";
        }

        if ($this->netUnitPrice === 0.0) {
            $errors[] = "netUnitPrice";
        }

        if (SzamlaAgentUtil::isBlank($this->quantityUnit)) {
            $errors[] = "quantityUnit";
        }

        if (SzamlaAgentUtil::isBlank($this->vat)) {
            $errors[] = "vat";
        }

        if (!empty($errors)) {
            throw new SzamlaAgentException(SzamlaAgentException::ITEM_ERROR . implode(", ", $errors));
        }
    }
}