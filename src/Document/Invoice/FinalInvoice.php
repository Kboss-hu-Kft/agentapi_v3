<?php

namespace Kboss\SzamlaAgent\Document\Invoice;

use Kboss\SzamlaAgent\Document\Header\FinalInvoiceHeader;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Végszámla kiállításához használható segédosztály
 */
class FinalInvoice extends Invoice
{

    /**
     * Végszámla létrehozása
     *
     * @param InvoiceType $type végszámla típusa (papír vagy e-számla), alapértelmezett a papír alapú számla
     *
     * @throws SzamlaAgentException
     */
    function __construct(InvoiceType $type = InvoiceType::P_INVOICE)
    {
        parent::__construct();
        // Alapértelmezett fejléc adatok hozzáadása
        $this->header = new FinalInvoiceHeader($type);
    }
 }