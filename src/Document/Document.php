<?php

namespace Kboss\SzamlaAgent\Document;

use Kboss\SzamlaAgent\Document\Header\DocumentHeader;
use Kboss\SzamlaAgent\Entity\Buyer;
use Kboss\SzamlaAgent\Entity\Seller;
use Kboss\SzamlaAgent\Enums\DocumentType;
use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\Item\InvoiceItem;
use Kboss\SzamlaAgent\Item\Item;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentSetting;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Bizonylat
 */
abstract class Document
{

    /**
     * Jóváírások maximális száma
     */
    const int CREDIT_NOTES_LIMIT = 5;

    public function __construct() {}

    protected abstract function buildXmlItemsData(): array;
    protected abstract function buildCreditsXmlData(): array;
    protected abstract function buildFieldsData(SzamlaAgentSetting $setting, RequestType $requestType, array $fields): array;
    public abstract function buildXmlData(SzamlaAgentSetting $setting, RequestType $requestType): array;
    public abstract function getHeader(): DocumentHeader;

    /**
     * A számlán szereplő eladó adatok
     *
     * @var Seller|null
     */
    public ?Seller $seller = null {
        get {
            return $this->seller;
        }

        set {
            $this->seller = $value;
        }
    }

    /**
     * A számlán szereplő vevő adatok
     *
     * @var Buyer|null
     */
    public ?Buyer $buyer = null {
        get {
            return $this->buyer;
        }
        set {
            $this->buyer = $value;
        }
    }

    /**
     * Bizonylat tételek
     *
     * @var Item[]
     */
    protected array $items = [];

    /**
     * Számlához tartozó jóváírások
     *
     * @var array
     */
    protected array $creditNotes = [];

    /**
     * @return InvoiceItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Tételek törlése
     *
     * @return int
     */
    public function removeItems(): int
    {
        $itemNumber = count($this->items);
        $this->items = array();
        return $itemNumber;
    }

    public function getCreditNotes(): array
    {
        return $this->creditNotes;
    }

    /**
     * @param RequestType $requestType
     * @return void
     * @throws SzamlaAgentException
     */
    protected function validate(RequestType $requestType): void
    {
        if (RequestType::isDocumentCreate($requestType)) {
            if (empty($this->items)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_ITEMS);
            }

            if ($requestType->documentType() !== DocumentType::RECEIPT && is_null($this->buyer)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_BUYER);
            }
        } else if ($requestType === RequestType::REQUEST_INVOICE_DATA || $requestType === RequestType::REQUEST_RECEIPT_DATA || $requestType === RequestType::DELETE_PROFORMA) {
            if (SzamlaAgentUtil::isBlank($this->getHeader()->documentNumber) && SzamlaAgentUtil::isBlank($this->getHeader()->orderNumber)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
            }
        } else if ($requestType === RequestType::PAY_INVOICE) {
            if (SzamlaAgentUtil::isBlank($this->getHeader()->documentNumber)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
            }
        }
    }
}