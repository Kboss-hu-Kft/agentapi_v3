<?php

namespace Kboss\SzamlaAgent\Document;

use Kboss\SzamlaAgent\Document\Header\ProformaHeader;
use Kboss\SzamlaAgent\Document\Invoice\Invoice;
use Kboss\SzamlaAgent\SzamlaAgentException;

/**
 * Díjbekérő segédosztály
 */
class Proforma extends Invoice
{

    /**
     * Díjbekérő létrehozása
     *
     * @throws SzamlaAgentException
     */
    function __construct()
    {
        parent::__construct();
        // Alapértelmezett fejléc adatok hozzáadása a díjbekérőhöz
        $this->header = new ProformaHeader();
    }
 }