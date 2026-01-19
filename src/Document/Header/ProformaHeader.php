<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Díjbekérő fejléc
 */
class ProformaHeader extends InvoiceHeader
{

    /**
     * @throws SzamlaAgentException
     */
    function __construct() {
        parent::__construct(InvoiceType::P_INVOICE);
        $this->proforma = true;
        $this->paid = false;
    }
}