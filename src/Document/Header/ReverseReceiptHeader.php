<?php

namespace Kboss\SzamlaAgent\Document\Header;

/**
 * Sztornó nyugta fejléc
 */
class ReverseReceiptHeader extends ReceiptHeader
{

    /**
     * Sztornó nyugta fejléc létrehozása
     * Beállítja a nyugta fejlécének alapértelmezett adatait
     *
     * @param string $receiptNumber nyugtaszám
     */
    function __construct(string $receiptNumber = '')
    {
        parent::__construct($receiptNumber);
        $this->reverseReceipt = true;
    }
}