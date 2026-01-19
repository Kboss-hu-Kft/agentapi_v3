<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Végszámla fejléc
 */
class FinalInvoiceHeader extends InvoiceHeader {
    /**
     * @param InvoiceType $type
     *
     * @throws SzamlaAgentException
     */
    function __construct(InvoiceType $type = InvoiceType::P_INVOICE) {
        parent::__construct($type);
        $this->final = true;
    }
}