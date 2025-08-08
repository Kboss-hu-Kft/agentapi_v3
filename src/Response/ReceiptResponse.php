<?php

namespace Kboss\SzamlaAgent\Response;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugta adatok
 */
class ReceiptResponse
{
    /**
     * Nyugta azonosítója
     *
     * @var int
     */
    private int $id;

    /**
     * Nyugtaszám
     *
     * @var string
     */
    private(set) string $receiptNumber {
        get {
            return $this->receiptNumber;
        }
    }

    /**
     * A nyugta típusa
     *
     * @var string
     */
    private(set) string $type {
        get {
            return $this->type;
        }
    }

    /**
     * A nyugta sztornózott-e
     *
     * @var false
     */
    private(set) false $reserved {
        get {
            return $this->reserved;
        }
    }

    /**
     * Sztornózott nyugtaszám
     *
     * @var string
     */
    private(set) string $reservedReceiptNumber {
        get {
            return $this->reservedReceiptNumber;
        }
    }

    /**
     * A nyugta kelte
     *
     * @var string
     */
    private(set) string $created {
        get {
            return $this->created;
        }
    }

    /**
     * A nyugta fizetési módja
     *
     * @var string
     */
    private(set) string $paymentMethod {
        get {
            return $this->paymentMethod;
        }
    }

    /**
     * A nyugta pénzneme
     *
     * @var string
     */
    private(set) string $currency {
        get {
            return $this->currency;
        }
    }

    /**
     * Teszt vagy valós céggel lett létrehozva a nyugta
     *
     * @var boolean
     */
    private(set) bool $test {
        get {
            return $this->test;
        }
    }

    /**
     * A nyugta tételei
     *
     * @var array
     */
    private(set) array $items {
        get {
            return $this->items;
        }
    }

    /**
     * A nyugta összegei
     *
     * @var array
     */
    private(set) array $amounts {
        get {
            return $this->amounts;
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
    private string $pdfData;

    /**
     * Sikeres-e a válasz
     *
     * @var bool
     */
    private(set) bool $success {
        get {
            return $this->success;
        }
    }

    /**
     * Jóváírások
     *
     * @var array
     */
    private(set) array $creditNotes = [] {
        get {
            return $this->creditNotes;
        }
    }


    /**
     * Nyugta létrehozása nyugtaszám alapján
     *
     * @param SzamlaAgentResponse $response
     * @throws SzamlaAgentException
     */
    function __construct(SzamlaAgentResponse $response) {
        $this->init($response);
    }

    /**
     * Feldolgozás után visszaadja a nyugta válaszát objektumként
     *
     * @param SzamlaAgentResponse $response
     * @throws SzamlaAgentException
     */
    public function init(SzamlaAgentResponse $response): void
    {
        if (!$response->isReceiptResponse()) {
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_RESPONSE_OBJECT);
        }

        $receiptData = $response->getResponseObj()->getDocumentData();

        if (array_key_exists('id', $receiptData))                    $this->id = $receiptData['id'];
        if (array_key_exists('nyugtaszam', $receiptData))            $this->receiptNumber = $receiptData['nyugtaszam'];
        if (array_key_exists('tipus', $receiptData))                 $this->type = $receiptData['tipus'];
        if (array_key_exists('stornozott', $receiptData))            $this->reserved = $receiptData['stornozott'] === 'true';
        if (array_key_exists('stornozottNyugtaszam', $receiptData))  $this->reservedReceiptNumber = $receiptData['stornozottNyugtaszam'];
        if (array_key_exists('kelt', $receiptData))                  $this->created = $receiptData['kelt'];
        if (array_key_exists('fizmod', $receiptData))                $this->paymentMethod = $receiptData['fizmod'];
        if (array_key_exists('penznem', $receiptData))               $this->currency = $receiptData['penznem'];
        if (array_key_exists('teszt', $receiptData))                 $this->test = $receiptData['teszt'] === 'true';
        if (array_key_exists('tetelek', $receiptData))               $this->items = $receiptData['tetelek']['tetel'];
        if (array_key_exists('osszegek', $receiptData))              $this->amounts = $receiptData['osszegek'];
        if (array_key_exists('kifizetesek', $receiptData))           $this->creditNotes = $receiptData['kifizetesek'];
        if (array_key_exists('sikeres', $receiptData))               $this->success = $receiptData['sikeres'];
        if (array_key_exists('nyugtaPdf', $receiptData))             $this->pdfData =$receiptData['nyugtaPdf'];
        if (array_key_exists('hibakod', $receiptData))               $this->errorCode = $receiptData['hibakod'];
        if (array_key_exists('hibauzenet', $receiptData))            $this->errorMessage = $receiptData['hibauzenet'];
    }

    /**
     * Visszaadja a nyugta azonosítót
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Visszaadja a nyugta PDF adatokat
     *
     * @return string
     */
    public function getPdfData(): string
    {
        return base64_decode(SzamlaAgentUtil::isNotBlank($this->pdfData) ? $this->pdfData : '');
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e hibát
     *
     * @return bool
     */
    public function isError(): bool
    {
        return !$this->success;
    }

}