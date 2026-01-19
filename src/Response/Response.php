<?php

namespace Kboss\SzamlaAgent\Response;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * API válasz
 */
class Response
{
    /**
     * Számlaértesítő kézbesítése sikertelen
     */
    const int INVOICE_NOTIFICATION_SEND_FAILED = 56;

    const array ERROR_FIELDS = ['szlahu_error', 'szlahu_error_code', 'hibauzenet', 'hibakod'];

    /**
     * Számlaszám
     *
     * @var string|null
     */
    private ?string $documentNumber = null;

    /**
     * Számlaszám
     *
     * @var array
     */
    private array $documentData = [];

    /**
     * A válaszban kapott PDF adatai
     *
     * @var string | null
     */
    private ?string $pdfData = null;
    /**
     * A válasz hibakódja
     *
     * @var int
     */
    private int $errorCode = 0;

    /**
     * A válasz hibaüzenete
     *
     * @var string | null
     */
    private ?string $errorMessage = null;

    /**
     * Sikeres-e a válasz
     *
     * @var bool
     */
    private bool $success = true;

    private string $xmlSchemaType;

    /**
     * @param string $xmlSchemaType
     * @param array $responseData
     * @param array $headers
     * @throws SzamlaAgentException
     */
    public function __construct(string $xmlSchemaType, array $responseData, array $headers)
    {
        $this->xmlSchemaType = $xmlSchemaType;
        $this->buildDocumentData($responseData, $headers);
    }

    /**
     * @param array $responseData
     * @param array $headers
     * @return void
     * @throws SzamlaAgentException
     */
    private function buildDocumentData(array $responseData, array $headers): void
    {
        if (SzamlaAgentUtil::isAgentInvoiceResponse($this->xmlSchemaType) || SzamlaAgentUtil::isProformaResponse($this->xmlSchemaType)) {
            $this->documentData = InvoiceResponseHandler::parseData($headers);
        } else if (SzamlaAgentUtil::isAgentReceiptResponse($this->xmlSchemaType)) {
            $this->documentData = ReceiptResponseHandler::parseData($responseData);
        } else if (SzamlaAgentUtil::isTaxPayerResponse($this->xmlSchemaType)) {
            $this->documentData = TaxPayerResponseHandler::parseData($responseData);
        }
        $this->checkErrors();
        if ($this->isSuccess()) {
            $this->setDocumentumNumber();
            $this->handlePdfData($responseData);
        }
    }

    /**
     * @return void
     */
    private function checkErrors(): void
    {
        if (array_any(self::ERROR_FIELDS, function ($f) { return array_key_exists($f, $this->documentData);})) {
            if(isset($this->documentData['szlahu_error_code']))     $this->errorCode = $this->documentData['szlahu_error_code'];
            if(isset($this->documentData['hibakod']))               $this->errorCode = $this->documentData['hibakod'];
            if(isset($this->documentData['szlahu_error']))          $this->errorMessage = $this->documentData['szlahu_error'];
            if(isset($this->documentData['hibauzenet']))            $this->errorMessage = $this->documentData['hibauzenet'];
            if (SzamlaAgentUtil::isAgentInvoiceResponse($this->xmlSchemaType) || SzamlaAgentUtil::isProformaResponse($this->xmlSchemaType)) {
                $this->success = SzamlaAgentUtil::isNotBlank($this->documentNumber) && $this->hasInvoiceNotificationSendError();
            } else {
                $this->success = false;
            }
        }
    }

    /**
     * @param array $responseData
     * @return void
     */
    private function handlePdfData(array $responseData): void
    {
        if (isset($this->documentData['nyugtaPdf'])) {
            $this->pdfData = $this->documentData['nyugtaPdf'];
        } else if (isset($responseData['pdf'])) {
            $this->pdfData = $responseData['pdf'];
        } else if (isset($responseData['body'])) {
            $this->pdfData = $responseData['body'];
        }

        if (SzamlaAgentUtil::isNotBlank($this->pdfData) && base64_decode($this->pdfData, true) !== false) {
            $this->pdfData = base64_decode($this->pdfData);
        }

        // elennőrizzük, hogy tényleg PDF akar lenni
        if (SzamlaAgentUtil::isNotBlank($this->pdfData) && !str_starts_with($this->pdfData, "%PDF-")) {
            $this->pdfData = null;
        }
    }

    /**
     * Beállítja a bizonylat számát, NAV adatlakérésnél a kérés azonositóját
     *
     * @return void
     */
    private function setDocumentumNumber(): void
    {
        if(isset($this->documentData['szlahu_szamlaszam'])) $this->documentNumber = $this->documentData['szlahu_szamlaszam'];
        elseif(isset($this->documentData['nyugtaszam'])) $this->documentNumber = $this->documentData['nyugtaszam'];
        elseif(isset($this->documentData['requestId'])) $this->documentNumber = $this->documentData['requestId'];
    }

    /**
     * Visszaadja, hogy a számlaértesítő kézbesítése sikertelen volt-e
     *
     * @return boolean
     */
    public function hasInvoiceNotificationSendError() : bool
    {
        return $this->errorCode == self::INVOICE_NOTIFICATION_SEND_FAILED;
    }

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function getDocumentData(): array
    {
        return $this->documentData;
    }

    public function getPdfData(): ?string
    {
        return $this->pdfData;
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function hasPdf(): bool
    {
        return SzamlaAgentUtil::isNotBlank($this->pdfData);
    }
}