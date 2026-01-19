<?php

namespace Kboss\SzamlaAgent\Document\Receipt;

use Kboss\SzamlaAgent\SzamlaAgentSetting;
use Override;
use Kboss\SzamlaAgent\CreditNote\ReceiptCreditNote;
use Kboss\SzamlaAgent\Document\Document;
use Kboss\SzamlaAgent\Document\Header\DocumentHeader;
use Kboss\SzamlaAgent\Document\Header\ReceiptHeader;
use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\Item\ReceiptItem;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugta
 *
 * @package SzamlaAgent\Document\Receipt
 */
class Receipt extends Document
{

    /** Nyugta maximimális bruttó végössyege */
    const int MAX_RECEIPT_GROSS_AMOUNT = 900000;

    /**
     * A bizonylat fejléce
     *
     * @var ReceiptHeader
     */
    public ReceiptHeader $header {
        get {
            return $this->header;
        }
        set {
            $this->header = $value;
        }
    }

    /**
     * Nyugta létrehozása alapértelmezett fejléc adatokkal
     * (fizetési mód: átutalás, pénznem: Ft)
     *
     * @param string $receiptNumber nyugtaszám
     */
    function __construct(string $receiptNumber = '')
    {
        parent::__construct();
        $this->header = new ReceiptHeader($receiptNumber);
    }

    /**
     * Tétel hozzáadása a nyugtához
     *
     * @param ReceiptItem $item
     */
    public function addItem(ReceiptItem $item): void
    {
        $items = $this->items;
        $items[] = $item;
        $this->items = $items;
    }

    /**
     * Jóváírás hozzáadása a nyugtához
     *
     * @param ReceiptCreditNote $creditNote
     */
    public function addCreditNote(ReceiptCreditNote $creditNote): void
    {
        if (count($this->creditNotes) < self::CREDIT_NOTES_LIMIT) {
            $this->creditNotes[] = $creditNote;
        }
    }

    /**
     * Összeállítja a nyugta XML adatait
     *
     * @param SzamlaAgentSetting $setting
     * @param RequestType $requestType
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] public function buildXmlData(SzamlaAgentSetting $setting, RequestType $requestType): array
    {
        $fields = ['beallitasok', 'fejlec'];

        $this->validate($requestType);

        return match ($requestType) {
            RequestType::GENERATE_RECEIPT => $this->buildFieldsData($setting, $requestType, array_merge($fields, ['tetelek', 'kifizetesek'])),
            RequestType::GENERATE_REVERSE_RECEIPT, RequestType::REQUEST_RECEIPT_DATA, RequestType::REQUEST_RECEIPT_PDF => $this->buildFieldsData($setting, $requestType, $fields),
            RequestType::SEND_RECEIPT => $this->buildFieldsData($setting, $requestType, array_merge($fields, ['emailKuldes'])),
            default => throw new SzamlaAgentException(SzamlaAgentException::INVALID_REQUEST_TYPE . ": " . $requestType->name),
        };
    }

    /**
     * Összeállítja és visszaadja az adott mezőkhöz tartozó adatokat
     *
     * @param SzamlaAgentSetting $setting
     * @param RequestType $requestType
     * @param array $fields
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] protected function buildFieldsData(SzamlaAgentSetting $setting, RequestType $requestType, array $fields): array
    {
        $data = [];

        if (!empty($fields)) {
            if ($requestType === RequestType::SEND_RECEIPT) {
                $emailSendingData = $this->buildXmlEmailSendingData();
            }
            foreach ($fields as $key) {
                $value = match ($key) {
                    'beallitasok' => $setting->buildXmlData($requestType, $this),
                    'fejlec' => $this->header->buildXmlData($requestType),
                    'tetelek' => $this->buildXmlItemsData(),
                    'kifizetesek' => (!empty($this->creditNotes)) ? $this->buildCreditsXmlData() : null,
                    'emailKuldes' => (!empty($emailSendingData)) ? $emailSendingData : null,
                    default => throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS . ": " . $key),
                };

                if (isset($value)) {
                    $data[$key] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Összeállítjuk a nyugtához tartozó tételek adatait
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] protected function buildXmlItemsData(): array
    {
        $data = [];

        if (!empty($this->getItems())) {
            foreach ($this->getItems() as $key => $item) {
                $data["item" . $key] = $item->buildXmlData();
            }
        }
        return $data;
    }

    /**
     * Összeállítjuk a nyugtához tartozó jóváírások adatait
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] protected function buildCreditsXmlData(): array
    {
        $data = [];

        if (!empty($this->creditNotes)) {
            foreach ($this->creditNotes as $key => $note) {
                $data["note" . $key] = $note->buildXmlData();
            }
        }
        return $data;
    }

    /**
     * Összeállítjuk a nyugtához tartozó e-mail kiküldési adatokat
     *
     * @return array
     */
    protected function buildXmlEmailSendingData(): array
    {
        $data = [];

        if (SzamlaAgentUtil::isNotNull($this->buyer) && !empty($this->buyer->getEmail())) {
            $data['email'] = implode(',', $this->buyer->getEmail());
        }

        if (SzamlaAgentUtil::isNotNull($this->seller)) {
            if (SzamlaAgentUtil::isNotBlank($this->seller->emailReplyTo)) $data['emailReplyto'] = $this->seller->emailReplyTo;
            if (SzamlaAgentUtil::isNotBlank($this->seller->emailSubject)) $data['emailTargy']   = $this->seller->emailSubject;
            if (SzamlaAgentUtil::isNotBlank($this->seller->emailContent)) $data['emailSzoveg']  = $this->seller->emailContent;
        }
        return $data;
    }

    /**
     * @return DocumentHeader
     */
    public function getHeader() : DocumentHeader {
        return $this->header;
    }

    /**
     * @param RequestType $requestType
     * @return void
     * @throws SzamlaAgentException
     */
    #[Override] public function validate(RequestType $requestType): void
    {
        parent::validate($requestType);

        if ($requestType === RequestType::GENERATE_RECEIPT) {
            $gross = array_sum(array_map(fn($i): float => $i->grossAmount, $this->getItems())) > self::MAX_RECEIPT_GROSS_AMOUNT;
            if ($gross > self::MAX_RECEIPT_GROSS_AMOUNT) {
                throw new SzamlaAgentException(printf(SzamlaAgentException::RECEIPT_GROSS_AMOUNT, $gross));
            }
        } else if ($requestType === RequestType::SEND_RECEIPT) {

            if (is_null($this->buyer) || empty($this->buyer->getEmail())) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_EMAIL);
            }

            if (SzamlaAgentUtil::isBlank($this->getHeader()->documentNumber)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
            }
        }

    }
}