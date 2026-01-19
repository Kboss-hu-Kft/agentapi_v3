<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Override;
use Kboss\SzamlaAgent\Enums\PaymentMethod;
use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugta fejléc
 */
class ReceiptHeader extends DocumentHeader
{

    /**
     * A létrehozás egyedi azonosítója, megakadályozza a nyugta duplikált létrehozását
     *
     * @var string|null
     */
    public ?string $callId = null {
        get {
            return $this->callId;
        }
        set {
            $this->callId = $value;
        }
    }

    /**
     * Egyedi PDF sablon esetén annak azonosítója
     *
     * @var string|null
     */
    public ?string $pdfTemplate = null {
        get {
            return $this->pdfTemplate;
        }
        set {
            $this->pdfTemplate = $value;
        }
    }

    /**
     * Vevő főkönyvi azonosítója
     *
     * @var string|null
     */
    public ?string $buyerLedgerId = null {
        get {
            return $this->buyerLedgerId;
        }
        set {
            $this->buyerLedgerId = $value;
        }
    }


    /**
     * Nyugta fejléc létrehozása
     * Beállítja a nyugta fejlécének alapértelmezett adatait
     *
     * @param string $receiptNumber nyugtaszám
     */
    function __construct(string $receiptNumber = '')
    {
        $this->receipt = true;
        $this->setReceiptNumber($receiptNumber);
        $this->paymentMethod = PaymentMethod::CASH;
    }

    /**
     * Összeállítja a bizonylat elkészítéséhez szükséges XML fejléc adatokat
     *
     * @param RequestType $requestType
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] public function buildXmlData(RequestType $requestType): array
    {
        $this->validate($requestType);

        return match ($requestType) {
            RequestType::GENERATE_RECEIPT => $this->buildFieldsData([
                'hivasAzonosito', 'elotag', 'fizmod', 'penznem', 'devizabank', 'devizaarf', 'megjegyzes', 'pdfSablon', 'fokonyvVevo'
            ]),
            RequestType::GENERATE_REVERSE_RECEIPT => $this->buildFieldsData(['nyugtaszam', 'pdfSablon', 'hivasAzonosito']),
            RequestType::REQUEST_RECEIPT_PDF, RequestType::REQUEST_RECEIPT_DATA => $this->buildFieldsData(['nyugtaszam', 'pdfSablon']),
            RequestType::SEND_RECEIPT => $this->buildFieldsData(['nyugtaszam']),
            default => throw new SzamlaAgentException(SzamlaAgentException::INVALID_REQUEST_TYPE . ": " . $requestType->name),
        };
    }

    /**
     * Összeállítja és visszaadja az adott mezőkhöz tartozó adatokat
     *
     * @param array              $fields
     *
     * @return array
     * @throws SzamlaAgentException
     */
    private function buildFieldsData(array $fields): array
    {
        $data = [];

        if (!empty($field)) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_NOT_AVAILABLE);
        }

        foreach ($fields as $key) {
            $value = match ($key) {
                'hivasAzonosito' => $this->callId,
                'elotag' => $this->prefix,
                'fizmod' => $this->paymentMethod,
                'penznem' => $this->currency,
                'devizabank' => $this->exchangeBank,
                'devizaarf' => $this->exchangeRate !== 0.0 ? SzamlaAgentUtil::doubleFormat($this->exchangeRate) : null,
                'megjegyzes' => SzamlaAgentUtil::isNotBlank($this->comment) ? $this->comment : null,
                'pdfSablon' => $this->pdfTemplate,
                'fokonyvVevo' => $this->buyerLedgerId,
                'nyugtaszam' => $this->getReceiptNumber(),
                default => throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS . ": " . $key),
            };

            if (isset($value)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getReceiptNumber(): string {
        return $this->documentNumber;
    }

    /**
     * Nyugta sorszám beállítása
     *
     * A nyugta létrehozásánál ne használd, mert a kiállított nyugták számait a Számlázz.hu
     * a jogszabálynak megfelelően automatikusan osztja ki: 1-től indulva, kihagyásmentesen.
     * @see https://tudastar.szamlazz.hu/gyik/szamlaszam-formatumok-mikor-kell-megadni
     *
     * @param string $receiptNumber
     */
    public function setReceiptNumber(string $receiptNumber): void
    {
        $this->documentNumber = $receiptNumber;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function validate(RequestType $requestType): void
    {

        if ($requestType === RequestType::GENERATE_RECEIPT) {
            if (SzamlaAgentUtil::isBlank($this->prefix)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_PREFIX);
            }
        }

        if ($requestType === RequestType::REQUEST_RECEIPT_PDF ||
            $requestType === RequestType::REQUEST_RECEIPT_DATA ||
            $requestType === RequestType::SEND_RECEIPT) {
            if (SzamlaAgentUtil::isBlank($this->documentNumber)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
            }
        }
    }
}