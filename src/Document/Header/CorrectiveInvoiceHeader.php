<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Helyesbítő számla fejléc
 */
class CorrectiveInvoiceHeader extends InvoiceHeader
{

    /**
     * @param string $invoiceNumber
     * @param InvoiceType $type
     *
     * @throws SzamlaAgentException
     */
    function __construct(string $invoiceNumber, InvoiceType $type = InvoiceType::P_INVOICE)
    {
        parent::__construct($type);
        $this->correctivedNumber = $invoiceNumber;
        $this->corrective = true;
    }
}