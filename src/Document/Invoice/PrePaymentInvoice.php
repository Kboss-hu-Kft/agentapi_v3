<?php

namespace Kboss\SzamlaAgent\Document\Invoice;

use Kboss\SzamlaAgent\Document\Header\PrePaymentInvoiceHeader;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Előlegszámla kiállításához használható segédosztály
 */
class PrePaymentInvoice extends Invoice
{

    /**
     * Előlegszámla létrehozása
     *
     * @param InvoiceType $type számla típusa (papír vagy e-számla), alapértelmezett a papír alapú számla
     *
     * @throws SzamlaAgentException
     */
    function __construct(InvoiceType $type = InvoiceType::P_INVOICE)
    {
        parent::__construct();
        // Alapértelmezett fejléc adatok hozzáadása
        $this->header = new PrePaymentInvoiceHeader($type);
    }
 }