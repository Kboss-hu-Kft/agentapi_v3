<?php

namespace Kboss\SzamlaAgent\Document\Receipt;

use Kboss\SzamlaAgent\Document\Header\ReverseReceiptHeader;

/**
 * Sztornó nyugta
 *
 * @package szamlaagent\document\receipt
 */
class ReverseReceipt extends Receipt
{

    /**
     * Sztornó nyugta létrehozása nyugtaszám alapján
     *
     * @param string $receiptNumber
     */
    public function __construct(string $receiptNumber = '')
    {
        parent::__construct();
        $this->header = new ReverseReceiptHeader($receiptNumber);
    }
}