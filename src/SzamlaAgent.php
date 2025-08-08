<?php

namespace Kboss\SzamlaAgent;

use Kboss\SzamlaAgent\Document\DeliveryNote;
use Kboss\SzamlaAgent\Document\Document;
use Kboss\SzamlaAgent\Document\Header\DocumentHeader;
use Kboss\SzamlaAgent\Document\Invoice\CorrectiveInvoice;
use Kboss\SzamlaAgent\Document\Invoice\FinalInvoice;
use Kboss\SzamlaAgent\Document\Invoice\Invoice;
use Kboss\SzamlaAgent\Document\Invoice\PrePaymentInvoice;
use Kboss\SzamlaAgent\Document\Invoice\ReverseInvoice;
use Kboss\SzamlaAgent\Document\Proforma;
use Kboss\SzamlaAgent\Document\Receipt\Receipt;
use Kboss\SzamlaAgent\Document\Receipt\ReverseReceipt;
use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\Response\SzamlaAgentResponse;


/**
 * A Számla Agent inicializálását, az adatok küldését és fogadását kezelő osztály
 */
class SzamlaAgent {

    /**
     * Számla Agent API aktuális verzió
     */
    const string API_VERSION = '3.0.0';

    /**
     * Számla Agent API használatához szükséges minimum PHP verzió
     */
    const string MIN_PHP_VERSION = '8.4';

    /**
     * Számla Agent beállítások
     *
     * @var SzamlaAgentSetting
     */
    public SzamlaAgentSetting $setting {
        get {
            return $this->setting;
        }
        set {
            $this->setting = $value;
        }
    }

    /**
     * Az aktuális Agent kérés
     *
     * @var SzamlaAgentRequest
     */
    public SzamlaAgentRequest $request {
        get {
            return $this->request;
        }
        set {
            $this->request = $value;
        }
    }

    /**
     * Az aktuális Agent válasz
     *
     * @var SzamlaAgentResponse
     */

    public SzamlaAgentResponse $response {
        get {
            return $this->response;
        }
        set {
            $this->response = $value;
        }
    }

    /**
     * @var SzamlaAgent[]
     */
    private static array $agents = [];

    /**
     * @var Log | null
     */
    public ?Log $log {
        get {
            return $this->log;
        }
        set {
            $this->log = $value;
        }
    }

    /**
     * Számla Agent létrehozása
     *
     * @param string $apiKey Számla Agent kulcs
     * @param bool $downloadPdf elkeszült bizonylat letöltése
     * @param string $aggregator webáruházat futtató motor neve
     * @param int $logLevel naplózási szint
     * @throws SzamlaAgentException
     */
    private function __construct(string $apiKey, bool $downloadPdf = true, string $aggregator = '', int $logLevel = Log::LOG_LEVEL_DEBUG)
    {
        SzamlaAgentUtil::checkApiKey($apiKey);
        $this->log = new Log($logLevel);
        $this->setting = new SzamlaAgentSetting($apiKey, $downloadPdf, $aggregator, $this->log);
    }

    /**
     * Számla Agent létrehozása (API kulccsal)
     * Ha már létezik SzamlaAgent példány azonos api kulccsal, akkor azt adja vissza, az újonnan megadott beállításokkal
     *
     * @param string $apiKey Számla Agent kulcs
     * @param bool $downloadPdf elkeszült bizonylat letöltése
     * @param string $aggregator webáruházat futtató motor neve
     * @param int $logLevel naplózási szint
     * @return SzamlaAgent
     * @throws SzamlaAgentException
     */
    public static function create(string $apiKey, bool $downloadPdf = true, string $aggregator = '', int $logLevel = Log::LOG_LEVEL_DEBUG): SzamlaAgent
    {
        $index = self::getHash($apiKey);
        $agent = null;
        if (isset(self::$agents[$index])) {
            $agent = self::$agents[$index];
            $agent->setting->aggregator = $aggregator;
            $agent->setting->downloadPdf = $downloadPdf;
            $agent->setLoglevel($logLevel);
        }

        if (is_null($agent)) {
            return self::$agents[$index] = new self($apiKey, $downloadPdf, $aggregator, $logLevel);
        } else {
            return $agent;
        }
    }

    /**
     * @param string $apikey
     *
     * @return string
     */
    protected static function getHash(string $apikey): string
    {
        return hash('sha1', $apikey);
    }

    /**
     * Számla Agent kérés elküldése és a válasz visszaadása
     *
     * @param SzamlaAgentRequest $request
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    private function sendRequest(SzamlaAgentRequest $request): SzamlaAgentResponse
    {
        $this->request = $request;
        $this->response = new SzamlaAgentResponse($this, $request->send());
        return $this->response->handleResponse();
    }

    /**
     * Számla Agent kérés elküldése és a válasz visszaadása
     *
     * @param SzamlaAgentRequest $request
     * @param array $xmlData
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    private function sendXMLRequest(SzamlaAgentRequest $request, array $xmlData): SzamlaAgentResponse
    {
        $this->request = $request;
        $response = new SzamlaAgentResponse($this, $request->sendXMLData($xmlData));
        return $response->handleResponse();
    }

    /**
     * Bizonylat elkészítése
     *
     * @param RequestType  $type
     * @param Document $document
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateDocument(RequestType $type, Document $document): SzamlaAgentResponse {
        $request = SzamlaAgentRequest::getForDocument($this, $type, $document);
        return $this->sendRequest($request);
    }

    /**
     * Számla elkészítése
     *
     * @param Invoice $invoice
     *-
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateInvoice(Invoice $invoice): SzamlaAgentResponse {
        return $this->generateDocument(RequestType::GENERATE_INVOICE, $invoice);
    }

    /**
     * Előlegszámla elkészítése
     *
     * @param PrePaymentInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generatePrePaymentInvoice(PrePaymentInvoice $invoice): SzamlaAgentResponse {
        return $this->generateInvoice($invoice);
    }

    /**
     * Végszámla elkészítése
     *
     * @param FinalInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateFinalInvoice(FinalInvoice $invoice): SzamlaAgentResponse {
        return $this->generateInvoice($invoice);
    }

    /**
     * Helyesbítő számla elkészítése
     *
     * @param CorrectiveInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateCorrectiveInvoice(CorrectiveInvoice $invoice): SzamlaAgentResponse {
        return $this->generateInvoice($invoice);
    }

    /**
     * Nyugta elkészítése
     *
     * @param Receipt $receipt
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateReceipt(Receipt $receipt): SzamlaAgentResponse {
        return $this->generateDocument(RequestType::GENERATE_RECEIPT, $receipt);
    }

    /**
     * Számla jóváírás rögzítése
     *
     * @param Invoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function payInvoice(Invoice $invoice): SzamlaAgentResponse {
        $this->setting->textResponse = true;
        return $this->generateDocument(RequestType::PAY_INVOICE, $invoice);
    }

    /**
     * Nyugta elküldése
     *
     * @param Receipt $receipt
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function sendReceipt(Receipt $receipt): SzamlaAgentResponse {
        return $this->generateDocument(RequestType::SEND_RECEIPT, $receipt);
    }

    /**
     * Számla adatok lekérdezése számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int    $type
     * @param bool   $downloadPdf
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getInvoiceData(string $data, int $type = Invoice::FROM_INVOICE_NUMBER, bool $downloadPdf = false): SzamlaAgentResponse {
        return $this->getInvoice($data, $type, $downloadPdf);
    }

    /**
     * Számla PDF lekérdezés számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int    $type
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getInvoicePdf(string $data, int $type = Invoice::FROM_INVOICE_NUMBER): SzamlaAgentResponse {
        return $this->getInvoice($data, $type, true);
    }

    /**
     * Számla PDF lekérdezés számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int $type
     * @param bool $downloadPdf
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    private function getInvoice(string $data, int $type, bool $downloadPdf): SzamlaAgentResponse {

        if (SzamlaAgentUtil::isBlank($data)) {
            $this->writeLog(SzamlaAgentException::INVOICE_ID_IS_EMPTY, Log::LOG_LEVEL_ERROR);
            throw new SzamlaAgentException(SzamlaAgentException::INVOICE_ID_IS_EMPTY);
        }
        $requestType = $downloadPdf ? RequestType::REQUEST_INVOICE_PDF : RequestType::REQUEST_INVOICE_DATA;
        $origPdfSetting = $this->setting->downloadPdf;
        $this->setting->downloadPdf = $downloadPdf;

        $invoice = new Invoice();

        if ($type === Invoice::FROM_INVOICE_NUMBER) {
            $invoice->header->documentNumber = $data;
        } else if ($type === Invoice::FROM_ORDER_NUMBER) {
            $invoice->header->orderNumber = $data;
        } else {
            $this->setting->invoiceExternalId = $data;
        }

        $result = $this->generateDocument($requestType, $invoice);
        $this->setting->downloadPdf = $origPdfSetting;
        return $result;
    }

    /**
     * Visszaadja külső számlaazonosító alapján, hogy létezik-e a számla a számlázz.hu rendszerében
     *
     * @param string $invoiceExternalId
     * @return bool
     * @throws SzamlaAgentException
     */
    public function isExistsInvoiceByExternalId(string $invoiceExternalId): bool {
        $result = false;
        try {
            $request = $this->getInvoice($invoiceExternalId, Invoice::FROM_INVOICE_EXTERNAL_ID, true);
            if ($request->isSuccess() && SzamlaAgentUtil::isNotBlank($request->getDocumentNumber())) {
                $result = true;
            }
        } catch (SzamlaAgentException $e) {
            if ($e->getCode() !== 7) {
                throw $e;
            }
        }
        return $result;
    }

    /**
     * Nyugta adatok lekérdezése nyugtaszám alapján
     *
     * @param string $receiptNumber nyugtaszám
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getReceiptData(string $receiptNumber): SzamlaAgentResponse {
        return $this->generateDocument(RequestType::REQUEST_RECEIPT_DATA, new Receipt($receiptNumber));
    }

    /**
     * Nyugta PDF lekérdezése nyugtaszám alapján
     *
     * @param string $receiptNumber nyugtaszám
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getReceiptPdf(string $receiptNumber): SzamlaAgentResponse {
        return $this->generateDocument(RequestType::REQUEST_RECEIPT_PDF, new Receipt($receiptNumber));
    }

    /**
     * Adózó adatainak lekérdezése törzsszám alapján
     * A választ a NAV Online Számla XML formátumában kapjuk vissza
     *
     * @param string $taxPayerId
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function getTaxPayer(string $taxPayerId): SzamlaAgentResponse
    {
        $taxPayer = new TaxPayer($taxPayerId);
        $request  = SzamlaAgentRequest::getForTaxPayer($this);
        return $this->sendXMLRequest($request, $taxPayer->buildXmlData($this->setting));
    }

    /**
     * Sztornó számla elkészítése
     *
     * @param ReverseInvoice $invoice
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateReverseInvoice(ReverseInvoice $invoice): SzamlaAgentResponse
    {
        return $this->generateDocument(RequestType::GENERATE_REVERSE_INVOICE, $invoice);
    }

    /**
     * Sztornó nyugta elkészítése
     *
     * @param ReverseReceipt $receipt
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateReverseReceipt(ReverseReceipt $receipt): SzamlaAgentResponse
    {
        return $this->generateDocument(RequestType::GENERATE_REVERSE_RECEIPT, $receipt);
    }

    /**
     * Díjbekérő elkészítése
     *
     * @param Proforma $proforma
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateProforma(Proforma $proforma): SzamlaAgentResponse
    {
        return $this->generateDocument(RequestType::GENERATE_PROFORMA, $proforma);
    }

    /**
     * Díjbekérő törlése számlaszám vagy rendelésszám alapján
     *
     * @param string $data
     * @param int    $type
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function deleteProforma(string $data, int $type = Invoice::FROM_INVOICE_NUMBER): SzamlaAgentResponse
    {
        $proforma = new Proforma();

        if ($type == Invoice::FROM_INVOICE_NUMBER) {
            $proforma->header->documentNumber = $data;
        } else {
            $proforma->header->orderNumber = $data;
        }

        $this->setting->downloadPdf = false;

        return $this->generateDocument(RequestType::DELETE_PROFORMA, $proforma);
    }

    /**
     * Szállítólevél elkészítése
     *
     * @param DeliveryNote $deliveryNote
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function generateDeliveryNote(DeliveryNote $deliveryNote): SzamlaAgentResponse
    {
        return $this->generateDocument(RequestType::GENERATE_DELIVERY_NOTE, $deliveryNote);
    }

    /**
     * @param string $message
     * @param int $type
     *
     * @throws SzamlaAgentException
     */
    public function writeLog(string $message, int $type = Log::LOG_LEVEL_DEBUG): void
    {
        $this->log->writelog($message, $type, $this->setting->getLogPath(), $this->setting->logEmail);
    }

    /**
     * @param string $message
     * @throws SzamlaAgentException
     */
    public function logError(string $message): void
    {
        $this->writeLog($message, Log::LOG_LEVEL_ERROR);
    }

    /**
     * Visszaadja a már létrehozott Számla Agent példányokat
     *
     * @return SzamlaAgent[]
     */
    public static function getAgents(): array
    {
        return self::$agents;
    }

    /**
     * @return Document
     */
    public function getRequestEntity(): Document
    {
        return $this->request->entity;
    }

    /**
     * @return DocumentHeader
     */
    public function getRequestEntityHeader(): DocumentHeader
    {
        return $this->request->entity->header;
    }

    /**
     * Törli az xml mappából az összes xml fájlt
     */
    public function emptyXmlDir(): void
    {
        SzamlaAgentUtil::deleteFilesFromDir($this->setting->getXmlPath());
    }

    /**
     * Törli a pdf mappából az összes pdf fájlt
     */
    public function emptyPdfDir(): void
    {
        SzamlaAgentUtil::deleteFilesFromDir($this->setting->getPdfPath());
    }

    /**
     * Törli a log mappából az összes log fájlt
     */
    public function emptyLogDir(): void
    {
        SzamlaAgentUtil::deleteFilesFromDir($this->setting->getLogPath());
    }

    /**
     * @return int
     */
    public function getLoglevel(): int
    {
        return $this->log->loglevel;
    }

    /**
     * @param int $loglevel
     * @return void
     */
    public function setLoglevel(int $loglevel): void
    {
        $this->log->loglevel = $loglevel;
    }
}