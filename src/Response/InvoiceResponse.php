<?php

namespace Kboss\SzamlaAgent\Response;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Számla adatok
 */
class InvoiceResponse
{

    /**
     * Számlaszám
     *
     * @var string
     */
    private(set) string $invoiceNumber {
        get {
            return $this->invoiceNumber;
        }
    }


    /**
     * Vevői fiók URL
     *
     * @var string
     */
    private string $userAccountUrl;

    /**
     * Kintlévőség
     *
     * @var float
     */
    private(set) float $assetAmount {
        get {
            return $this->assetAmount;
        }
    }

    /**
     * Nettó végösszeg
     *
     * @var float
     */
    private(set) float $netPrice {
        get {
            return $this->netPrice;
        }
    }

    /**
     * Bruttó végösszeg
     *
     * @var float
     */
    private(set) float $grossAmount {
        get {
            return $this->grossAmount;
        }
    }

    /**
     * Számla azonosító
     *
     * @var string
     */
    private(set) string $invoiceIdentifier {
        get {
            return $this->invoiceIdentifier;
        }
    }

    /**
     * A válasz hibakódja
     *
     * @var string
     */
    private(set) string $errorCode = '' {
        get {
            return $this->errorCode;
        }
    }

    /**
     * A válasz hibaüzenete
     *
     * @var string
     */
    private(set) string $errorMessage = '' {
        get {
            return $this->errorMessage;
        }
    }

    /**
     * A válaszban kapott PDF adatai
     *
     * @var string
     */
    private(set) string $pdfData = '' {
        get {
            return $this->pdfData;
        }
    }

    /**
     * Sikeres-e a válasz
     *
     * @var bool
     */
    private(set) bool $success = false {
        get {
            return $this->success;
        }
    }

    /**
     * Számlaértesítő kézbesítése sikertelen volt-e
     *
     * @var bool
     */
    private bool $notificationError = false;

    /**
     * Számla válasz létrehozása
     *
     * @param SzamlaAgentResponse $response
     * @throws SzamlaAgentException
     */
    public function __construct(SzamlaAgentResponse $response) {
        $this->init($response);
    }

    /**
     * Feldolgozás után visszaadja a számla válaszát objektumként
     *
     * @param SzamlaAgentResponse $response
     * @throws SzamlaAgentException
     */
    public function init(SzamlaAgentResponse $response) : void
    {
        if (!$response->isInvoiceResponse()) {
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_RESPONSE_OBJECT);
        }

        $invoiceData = $response->getResponseObj()->getDocumentData();

        if (array_key_exists('szlahu_szamlaszam', $invoiceData))        $this->invoiceNumber = $invoiceData['szlahu_szamlaszam'];
        if (array_key_exists('szlahu_id', $invoiceData))                $this->invoiceIdentifier = $invoiceData['szlahu_id'];
        if (array_key_exists('szlahu_vevoifiokurl', $invoiceData))      $this->userAccountUrl = rawurldecode($invoiceData['szlahu_vevoifiokurl']);
        if (array_key_exists('szlahu_kintlevoseg', $invoiceData))       $this->assetAmount = SzamlaAgentUtil::doubleFormat($invoiceData['szlahu_kintlevoseg']);
        if (array_key_exists('szlahu_nettovegosszeg', $invoiceData))    $this->netPrice = SzamlaAgentUtil::doubleFormat($invoiceData['szlahu_nettovegosszeg']);
        if (array_key_exists('szlahu_bruttovegosszeg', $invoiceData))   $this->grossAmount = SzamlaAgentUtil::doubleFormat($invoiceData['szlahu_bruttovegosszeg']);
        if (array_key_exists('szlahu_error', $invoiceData))             $this->errorMessage = $invoiceData['szlahu_error'];
        if (array_key_exists('szlahu_error_code', $invoiceData))        $this->errorCode = $invoiceData['szlahu_error_code'];
        if ($response->hasPdf())                                            $this->pdfData = $response->getPdf();
        if ($response->isSuccess())                                         $this->success = true;
        if ($response->getResponseObj()->hasInvoiceNotificationSendError()) $this->notificationError = true;
    }

    /**
     * Visszaadja a vevői fiók URL-jét
     *
     * @return string
     */
    public function getUserAccountUrl(): string
    {
        return urldecode($this->userAccountUrl);
    }

    /**
     * Visszaadja, hogy a számlaértesítő kézbesítése sikertelen volt-e
     *
     * @return boolean
     */
    public function hasInvoiceNotificationSendError(): bool
    {
        return $this->notificationError;
    }

}