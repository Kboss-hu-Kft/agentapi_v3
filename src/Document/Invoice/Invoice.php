<?php

namespace Kboss\SzamlaAgent\Document\Invoice;

use Kboss\SzamlaAgent\SzamlaAgentSetting;
use Override;
use Kboss\SzamlaAgent\CreditNote\InvoiceCreditNote;
use Kboss\SzamlaAgent\Document\Document;
use Kboss\SzamlaAgent\Document\Header\DocumentHeader;
use Kboss\SzamlaAgent\Document\Header\InvoiceHeader;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\Item\InvoiceItem;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Számla
 */
class Invoice extends Document {

    /** Számla lekérdezése számlaszám alapján */
    const int FROM_INVOICE_NUMBER = 1;

    /** Számla lekérdezése rendelési szám alapján */
    const int FROM_ORDER_NUMBER = 2;

    /** Számla lekérdezése külső számlaazonosító alapján */
    const int FROM_INVOICE_EXTERNAL_ID = 3;

    /** Számlához csatolandó fájlok maximális száma */
    const int INVOICE_ATTACHMENTS_LIMIT = 5;

    /**
     * A bizonylat fejléce
     *
     * @var InvoiceHeader
     */
    public InvoiceHeader $header {
        get {
            return $this->header;
        }
        set {
            $this->header = $value;
        }
    }

    /**
     * Összeadandó-e a jóváírás
     *
     * Ha igaz, akkor nem törli a korábbi jóváírásokat,
     * hanem hozzáadja az összeget az eddigiekhez.
     *
     * @var bool
     */
    public bool $additive = true {
        get {
            return $this->additive;
        }
        set {
            $this->additive = $value;
        }
    }

    /**
     * Számlához tartozó mellékletek
     *
     * @var array
     */
    private array $attachments = [];

    /**
     * Számla létrehozása
     *
     * Átutalással fizetendő magyar nyelvű (Ft) számla kiállítása mai keltezési és
     * teljesítési dátummal, +8 nap fizetési határidővel, üres számlaelőtaggal.
     *
     * @param InvoiceType $type számla típusa (papír vagy e-számla)
     *
     * @throws SzamlaAgentException
     */
    public function __construct(InvoiceType $type = InvoiceType::P_INVOICE) {
        parent::__construct();
        $this->header = new InvoiceHeader($type);
    }

    /**
     * @param InvoiceItem $item
     */
    public function addItem(InvoiceItem $item): void {
        $this->items[] = $item;
    }

    /**
     * Jóváírás hozzáadása a számlához
     *
     * @param InvoiceCreditNote $creditNote
     */
    public function addCreditNote(InvoiceCreditNote $creditNote): void {
        if (count($this->creditNotes) < self::CREDIT_NOTES_LIMIT) {
            $this->creditNotes[] = $creditNote;
        }
    }

    /**
     * Összeállítja a számla XML adatait
     *
     * @param SzamlaAgentSetting $setting
     * @param RequestType $requestType
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentSetting $setting, RequestType $requestType): array {

        $this->validate($requestType);

        return match ($requestType) {
            RequestType::GENERATE_PROFORMA, RequestType::GENERATE_INVOICE, RequestType::GENERATE_PREPAYMENT_INVOICE, RequestType::GENERATE_FINAL_INVOICE, RequestType::GENERATE_CORRECTIVE_INVOICE, RequestType::GENERATE_DELIVERY_NOTE => $this->buildFieldsData($setting, $requestType, ['beallitasok', 'fejlec', 'elado', 'vevo', 'tetelek']),
            RequestType::DELETE_PROFORMA => $this->buildFieldsData($setting, $requestType, ['beallitasok', 'fejlec']),
            RequestType::GENERATE_REVERSE_INVOICE => $this->buildFieldsData($setting, $requestType, ['beallitasok', 'fejlec', 'elado', 'vevo']),
            RequestType::PAY_INVOICE => array_merge($this->buildFieldsData($setting, $requestType, ['beallitasok']), $this->buildCreditsXmlData()),
            RequestType::REQUEST_INVOICE_DATA, RequestType::REQUEST_INVOICE_PDF => $this->buildFieldsData($setting, $requestType, ['beallitasok'])['beallitasok'],
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
    #[Override] protected function buildFieldsData(SzamlaAgentSetting $setting, RequestType $requestType, array $fields): array {
        $data = [];

        if (!empty($fields)) {
            foreach ($fields as $key) {
                $value = match ($key) {
                    'beallitasok' => $setting->buildXmlData($requestType, $this),
                    'fejlec' => $this->header->buildXmlData($requestType),
                    'tetelek' => $this->buildXmlItemsData(),
                    'elado' => (SzamlaAgentUtil::isNotNull($this->seller)) ? $this->seller->buildXmlData($requestType) : array(),
                    'vevo' => (SzamlaAgentUtil::isNotNull($this->buyer)) ? $this->buyer->buildXmlData($requestType) : array(),
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
     * Összeállítja a bizonylathoz tartozó tételek adatait
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] protected function buildXmlItemsData(): array {
        $data = [];

        if (!empty($this->getItems())) {
            foreach ($this->getItems() as $key => $item) {
                $data["item" . $key] = $item->buildXmlData();
            }
        }
        return $data;
    }

    /**
     * Összeállítja a számlához tartozó jóváírások adatait
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] protected function buildCreditsXmlData(): array {
        $data = [];
        if (!empty($this->creditNotes)) {
            foreach ($this->creditNotes as $key => $note) {
                $data["note" . $key] = $note->buildXmlData();
            }
        }
        return $data;
    }

    /**
     * Fájl csatolása a számlához
     *
     * Összesen 5 db mellékletet tudsz egy számlához csatolni.
     * A beküldött fájlok mérete nem haladhatja meg a 2 MB méretet. Ha valamelyik beküldött fájl csatolása valamilyen okból sikertelen,
     * akkor a nem megfelelő csatolmányokról a rendszer figyelmeztető emailt küld a beküldőnek (minden rossz fájlról külön-külön).
     *
     * Hibás csatolmány esetén is kiküldésre kerül az értesítő email úgy, hogy a megfelelő fájlok csatolva lesznek.
     * Ha nem érkezik kérés értesítő email kiküldésére, akkor a beküldött csatolmányok nem kerülnek feldolgozásra.
     *
     * @param string $filePath
     *
     * @throws SzamlaAgentException
     */
    public function addAttachment(string $filePath): void {
        if (empty($filePath)) {
            throw new SzamlaAgentException(SzamlaAgentException::ATTACHMENT_MISSING);
        } else {
            if (count($this->attachments) >= self::INVOICE_ATTACHMENTS_LIMIT) {
                throw new SzamlaAgentException('A következő fájl csatolása sikertelen: "' . $filePath. '". Egy számlához maximum ' . self::INVOICE_ATTACHMENTS_LIMIT . ' fájl csatolható!');
            }

            if (!file_exists($filePath)) {
                throw new SzamlaAgentException(SzamlaAgentException::ATTACHMENT_NOT_EXISTS . ': '. $filePath);
            }

            if (!in_array($filePath, $this->attachments)) {
                $this->attachments[] = $filePath;
            }
        }
    }

    public function getHeader() : DocumentHeader
    {
        return $this->header;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param RequestType $requestType
     * @return void
     * @throws SzamlaAgentException
     */
    protected function validate(RequestType $requestType): void
    {
        parent::validate($requestType);

        if ($requestType === RequestType::GENERATE_FINAL_INVOICE && SzamlaAgentUtil::isBlank($this->header->prePaymentInvoiceNumber) && SzamlaAgentUtil::isBlank($this->header->orderNumber)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
        }
    }
}