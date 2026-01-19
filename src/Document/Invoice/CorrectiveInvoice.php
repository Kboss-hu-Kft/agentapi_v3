<?php

namespace Kboss\SzamlaAgent\Document\Invoice;

use Kboss\SzamlaAgent\Document\Header\CorrectiveInvoiceHeader;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Helyesbítő számla kiállításához használható segédosztály
 */
class CorrectiveInvoice extends Invoice
{

    /**
     * Helyesbítő számla létrehozása
     *
     * @param InvoiceType $type számla típusa (papír vagy e-számla), alapértelmezett a papír alapú számla
     *
     * @throws SzamlaAgentException
     */
    function __construct(string $invoiceNumber, InvoiceType $type = InvoiceType::P_INVOICE)
    {
        parent::__construct();
        // Alapértelmezett fejléc adatok hozzáadása
        $this->header = new CorrectiveInvoiceHeader($invoiceNumber, $type);
    }
 }