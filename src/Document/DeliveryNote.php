<?php

namespace Kboss\SzamlaAgent\Document;

use Kboss\SzamlaAgent\Document\Header\DeliveryNoteHeader;
use Kboss\SzamlaAgent\Document\Invoice\Invoice;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Szállítólevél segédosztály
 */
class DeliveryNote extends Invoice
{

    /**
     * Szállítólevél kiállítása
     *
     * @throws SzamlaAgentException
     */
    function __construct()
    {
        parent::__construct();
        // Alapértelmezett fejléc adatok hozzáadása
        $this->header = new DeliveryNoteHeader();
    }
 }