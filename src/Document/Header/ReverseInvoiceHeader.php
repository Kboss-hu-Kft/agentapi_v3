<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Enums\RequestType;
use Override;
use Kboss\SzamlaAgent\Enums\DocumentType;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Sztornó számla fejléc
 */
class ReverseInvoiceHeader extends InvoiceHeader
{

    /**
     * @param InvoiceType $type
     *
     * @throws SzamlaAgentException
     */
    function __construct(InvoiceType $type = InvoiceType::P_INVOICE) {
        parent::__construct($type, true);
    }

    /**
     * Összeállítja a bizonylat elkészítéséhez szükséges XML fejléc adatokat
     *
     * Csak azokat az XML mezőket adjuk hozzá, amelyek kötelezőek,
     * illetve amelyek opcionálisak, de ki vannak töltve.
     *
     * @param RequestType $requestType
     *
     * @return array
     */
    #[Override] public function buildXmlData(RequestType $requestType): array
    {

        $data["szamlaszam"] = $this->documentNumber;

        if (!empty($this->issueDate))                     $data['keltDatum'] = $this->issueDate;
        if (!empty($this->fulfillment))                   $data['teljesitesDatum'] = $this->fulfillment;
        if (SzamlaAgentUtil::isNotBlank($this->comment))  $data['megjegyzes'] = $this->comment;

        $data['tipus'] = DocumentType::REVERSE_INVOICE;

        if (!empty($this->invoiceTemplate))               $data['szamlaSablon'] = $this->invoiceTemplate;

        return $data;
    }
}