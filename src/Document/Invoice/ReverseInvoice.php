<?php

namespace Kboss\SzamlaAgent\Document\Invoice;

use Kboss\SzamlaAgent\Document\Header\ReverseInvoiceHeader;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\Response\InvoiceResponse;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Sztornó számla
 */
class ReverseInvoice extends Invoice
{

    /**
     * Sztornó számla létrehozása
     *
     * @param InvoiceType $type számla típusa (papír vagy e-számla), alapértelmezett a papír alapú számla
     *
     * @throws SzamlaAgentException
     */
    public function __construct(InvoiceType $type = InvoiceType::P_INVOICE)
    {
        parent::__construct();
        // Alapértelmezett fejléc adatok hozzáadása a számlához
        $this->header = new ReverseInvoiceHeader($type);
    }
}