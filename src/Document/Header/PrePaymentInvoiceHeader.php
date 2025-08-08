<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Előlegszámla fejléc
 */
class PrePaymentInvoiceHeader extends InvoiceHeader
{

    /**
     * @param InvoiceType $type
     *
     * @throws SzamlaAgentException
     */
    function __construct(InvoiceType $type = InvoiceType::P_INVOICE)
    {
        parent::__construct($type);
        $this->prePayment = true;
        $this->paid = false;
    }
}