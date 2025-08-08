<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Szállítólevél fejléc
 */
class DeliveryNoteHeader extends InvoiceHeader
{

    /**
     * @throws SzamlaAgentException
     */
    function __construct()
    {
        parent::__construct(InvoiceType::P_INVOICE);
        $this->deliveryNote = true;
    }
}