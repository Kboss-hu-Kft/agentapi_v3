<?php

namespace Kboss\SzamlaAgent\Response;

use Exception;
use ReflectionClass;
use SimpleXMLElement;
use Kboss\SzamlaAgent\Document\Header\InvoiceHeader;
use Kboss\SzamlaAgent\SzamlaAgent;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * A Számla Agent választ kezelő osztály
 */
class SzamlaAgentResponse {

    /**
     * @var SzamlaAgent
     */
    public SzamlaAgent $agent
    {
        get {
            return $this->agent;
        }

        set {
            $this->agent = $value;
        }
    }

    /**
     * A teljes válasz (fejléc és tartalom)
     *
     * @var array
     */
    private array $response;

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @var int
     */
    private int $httpCode;

    /**
     * Válaszban kapott XML
     *
     * @var SimpleXMLElement
     */
    private SimpleXMLElement $xmlData;

    /**
     * A teljes válasz (fejléc és tartalom)
     *
     * @var array
     */
    private array $responseData = [];

    /**
     * A válasz szöveges tartalma, ha nem PDF
     *
     * @var string
     */
    private string $content;

    /**
     * A válasz adatait tartalmazó objektum
     *
     * @var Response
     */
    private Response $responseObj;

    /**
     * XML séma típusa (számla, nyugta, adózó)
     *
     * @var string
     */
    private string $xmlSchemaType;

    /**
     * Mentett PDF fálj neve
     *
     * @var string
     */
    private string $previewFileName = '';

    private string $xmlFilePath = '';


    /**
     * Számla Agent válasz létrehozása
     *
     * @param SzamlaAgent $agent
     * @param array       $response
     */
    public function __construct(SzamlaAgent $agent, array $response)
    {
        $this->agent = $agent;
        $this->response = $response;
    }

    /**
     * Számla Agent válasz feldolgozása
     *
     * @return SzamlaAgentResponse
     * @throws SzamlaAgentException
     */
    public function handleResponse(): SzamlaAgentResponse
    {
        // Válasz ellenőrzése
        $this->checkResponse();
        $this->handleXml();

        $this->responseData = json_decode(json_encode($this->xmlData), TRUE);
        $this->responseObj = new Response($this->xmlSchemaType, $this->responseData, $this->headers);

        if ($this->responseObj->hasInvoiceNotificationSendError()) {
            $this->agent->writeLog(SzamlaAgentException::INVOICE_NOTIFICATION_SEND_FAILED);
        }
        if ($this->agent->setting->xmlFileSave && $this->agent->setting->responseXmlFileSave) {
            $this->createXmlFile($this->xmlData);
        }

        $this->handlePdf();

        if ($this->isFailed()) {
            throw new SzamlaAgentException( SzamlaAgentException::AGENT_ERROR . ": [" . $this->responseObj->getErrorCode() . "], " . $this->responseObj->getErrorMessage(), $this->getResponseObj()->getErrorCode());
        } else if ($this->isSuccess()) {
            $this->agent->writeLog("Agent hívás sikeresen befejeződött.");
        }
        return $this;
    }

    /**
     * Ellenőrzi a kapott választ
     *
     * @return void
     * @throws SzamlaAgentException
     */
    private function checkResponse(): void
    {
        if (empty($this->response)) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_IS_EMPTY);
        }

        if (!empty($this->response['headers'])) {
            $this->headers = array_change_key_case($this->response['headers']);
            $this->xmlSchemaType = $this->response['headers']['schema-type'];

            if (isset($this->headers['szlahu_down']) && SzamlaAgentUtil::isNotBlank($this->headers['szlahu_down'])) {
                throw new SzamlaAgentException(SzamlaAgentException::SYSTEM_DOWN, 500);
            }

        } else {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_NO_HEADER);
        }

        if (!isset($this->response['body']) || empty($this->response['body'])) {
            throw new SzamlaAgentException(SzamlaAgentException::AGENT_RESPONSE_NO_CONTENT);
        }

        if (array_key_exists('http_code', $this->response)) {
            $this->httpCode = $this->response['http_code'];
        }

        if (SzamlaAgentUtil::isAgentInvoiceResponse($this->xmlSchemaType)) {
            $keys = implode(",", array_keys($this->headers));
            if (!preg_match('/(szlahu_)/', $keys)) {
                throw new SzamlaAgentException(SzamlaAgentException::NO_SZLAHU_KEY_IN_HEADER);
            }
        }
    }

    /**
     * Létrehozza a válasz adatait tartalmazó XML fájlt
     *
     * @param  SimpleXMLElement $xml
     *
     * @throws SzamlaAgentException
     */
    private function createXmlFile(SimpleXMLElement $xml): void
    {

        if (SzamlaAgentUtil::isTaxPayerResponse($this->xmlSchemaType)) {
            $response = $this->response;
            $xml = SzamlaAgentUtil::formatXml($response['body']);
        } else {
            $xml = SzamlaAgentUtil::formatXml($xml);
        }

        $name = '';
        if ($this->isFailed()) {
            $name = 'error-';
        }
        $name .= strtolower($this->agent->request->getXmlName());
        $postfix = $this->agent->setting->textResponse ? "-text" : "-xml";

        $fileName = SzamlaAgentUtil::getXmlFileName('response', $name . $postfix, $this->agent->request->entity);
        $this->xmlFilePath = $this->agent->setting->getXmlPath() . DIRECTORY_SEPARATOR . $fileName;
        $xmlSaved = $xml->save($this->xmlFilePath);

        if (!$xmlSaved) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_FILE_SAVE_FAILED);
        }
        $this->agent->writeLog("XML fájl mentése sikeres: " . SzamlaAgentUtil::getRealPath($fileName));
    }

    /**
     * Visszaadja a PDF fájl nevét, amennyiben a PDF file-ok mentése be van kapcsolva
     *
     * @param bool $withPath
     *
     * @return bool|string
     */
    public function getPdfFileName(bool $withPath = true): bool|string
    {
        $result = false;
        $header = $this->agent->getRequestEntityHeader();

        if ($header instanceof InvoiceHeader && $header->previewPdf) {
            if (SzamlaAgentUtil::isBlank($this->previewFileName)) {
                $entity = $this->agent->getRequestEntity();
                $this->previewFileName = strtolower(new ReflectionClass($entity)->getShortName() . '-') . 'preview-' . SzamlaAgentUtil::getDateTimeWithMilliseconds();
            }
            $documentNumber = $this->previewFileName;
        } else {
            $documentNumber = $this->getDocumentNumber();
        }

        if ($this->agent->setting->pdfFileSave) {
            if ($withPath) {
                $result =  $this->getPdfFileAbsPath($documentNumber . '.pdf');
            } else {
                $result = $documentNumber . '.pdf';
            }
        }
        return $result;
    }

    /**
     * XML adatok beállítása és a fájl létrehozása
     * @throws SzamlaAgentException
     */
    private function handleXml():  void
    {
        try {
            if ($this->isXmlResponse()) {
                $this->buildResponseXmlData();
                if (SzamlaAgentUtil::isNotTaxPayerResponse($this->xmlSchemaType)) {
                    $this->content = $this->response['body'];
                }
            } else {
                $this->buildResponseTextData();
            }
        } catch (Exception $e) {
            $this->agent->logError(SzamlaAgentException::XML_DATA_BUILD_FAILED . ": " . $e->getMessage());
            throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_BUILD_FAILED);
        }
    }

    /**
     * PDF kezelés
     *
     * @return void
     * @throws SzamlaAgentException
     */
    private function handlePdf(): void
    {
        try {
            if ($this->agent->setting->downloadPdf && $this->responseObj->hasPdf()) {
                if ($this->agent->setting->pdfFileSave) {
                    $file = file_put_contents($this->getPdfFileName(), $this->responseObj->getPdfData(), LOCK_EX);
                    if ($file !== false) {
                        $this->agent->writeLog(SzamlaAgentException::PDF_FILE_SAVE_SUCCESS . ': ' . $this->getPdfFileName());
                    } else {
                        throw new SzamlaAgentException(SzamlaAgentException::FILE_CREATION_FAILED);
                    }
                }
            }
        } catch (Exception $e) {
            $this->agent->writeLog(SzamlaAgentException::PDF_FILE_SAVE_FAILED . ': ' . $e);
            throw new SzamlaAgentException(SzamlaAgentException::PDF_FILE_SAVE_FAILED, 0, $e);
        }
    }

    /**
     * Visszaadja a PDF fájl teljes elérési útvonalát
     *
     * @param string$pdfFileName
     *
     * @return bool|string
     */
    private function getPdfFileAbsPath(string $pdfFileName): bool|string
    {
        return SzamlaAgentUtil::getAbsPath($this->agent->setting->getPdfPath(), $pdfFileName);
    }

    /**
     * Szöveges válasz feldolgozása
     *
     * @return void
     * @throws SzamlaAgentException
     */
    private function buildResponseTextData(): void
    {
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($this->response['body'], null, LIBXML_NOCDATA);

            if ($xml !== false) {
                $this->buildResponseXmlData($xml);
            } else {
                $this->xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response></response>');
                $content = $this->response['body'];

                if (SzamlaAgentUtil::isAgentReceiptResponse($this->xmlSchemaType) || $this->agent->setting->downloadPdf) {
                    $this->xmlData->addChild('body', base64_encode($content));
                } else {
                    $this->xmlData->addChild('body', $content);
                }
            }
        } catch (Exception $e) {
            $this->agent->logError($e->getMessage());
            throw new SzamlaAgentException(SzamlaAgentException::TEXT_RESPONSE_ERROR);
        }

    }

    /**
     * @param SimpleXMLElement|null $xml
     * @return void
     * @throws Exception
     */
    private function buildResponseXmlData(SimpleXMLElement|null $xml = null): void
    {
        $xmlData = is_null($xml) ? simplexml_load_string($this->response['body'], null, LIBXML_NOCDATA) : $xml;
        if (SzamlaAgentUtil::isTaxPayerResponse($this->xmlSchemaType)) {
            $xmlData = SzamlaAgentUtil::removeNamespaces($xmlData);
        }

        $this->xmlData = $xmlData;
    }

    /**
     * Visszaadja, hogy a válasyban van-e pdf
     *
     * @return bool
     */
    public function hasPdf(): bool
    {
        return $this->responseObj->hasPdf();
    }

    /**
     * Visszaadja a válaszban kapott PDF fájlt
     *
     * @return string
     */
    public function getPdf(): string
    {
        return $this->responseObj->getPdfData();
    }

    /**
     * Visszaadja a válasz adatait XML formátumban
     *
     * @return string | null
     */
    public function toXML(): ?string
    {
        if (!empty($this->xmlData)) {
            return $this->xmlData->asXML();
        }
        return null;
    }

    /**
     * Visszaadja a válasz adatait JSON formátumban
     *
     * @return string
     * @throws SzamlaAgentException
     */
    public function toJson(): string
    {
        $result = json_encode($this->getResponseData());
        if ($result === false || !SzamlaAgentUtil::isValidJSON($result)) {
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_JSON);
        }
        return $result;
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    protected function toArray(): array
    {
        return json_decode($this->toJson(),TRUE);
    }

    /**
     * Visszaadja a válasz adatait
     *
     * @return mixed
     * @throws SzamlaAgentException
     */
    public function getData(): array
    {
        return $this->toArray();
    }

    /**
     * Visszaadja a választ tartalmazó objektumot
     *
     * @return Response
     */
    public function getResponseObj(): Response
    {
        return $this->responseObj;
    }

    /**
     * @return array
     */
    private function getResponseData(): array
    {
        $result = [];

        if (!empty($this->xmlData)) {
            $result['result'] = $this->xmlData;
        } else {
            $result['result'] = $this->content;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string|null
     */
    public function getDocumentNumber(): ?string
    {
        return $this->responseObj->getDocumentNumber();
    }
    public function isInvoiceResponse(): bool {
        return  SzamlaAgentUtil::isAgentInvoiceResponse($this->xmlSchemaType);
    }

    /**
     * @return bool
     */
    protected function isAgentInvoiceXmlResponse(): bool
    {
        return ($this->isInvoiceResponse() && $this->agent->setting->isNotTextResponse());
    }

    /**
     * @return bool
     */
    public function isReceiptResponse(): bool
    {
        return  SzamlaAgentUtil::isAgentReceiptResponse($this->xmlSchemaType);
    }

    /**
     * @return bool
     */
    protected function isAgentReceiptXmlResponse(): bool
    {
        return ($this->isReceiptResponse() && $this->agent->setting->isNotTextResponse());
    }

    /**
     * @return bool
     */
    protected function isXmlResponse(): bool
    {
        return $this->isAgentInvoiceXmlResponse() || $this->isAgentReceiptXmlResponse() || SzamlaAgentUtil::isTaxPayerResponse($this->xmlSchemaType);
    }

    /**
     * Visszaadja a válasz sikerességét
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return !$this->isFailed();
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e hibát
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return !$this->responseObj->isSuccess();
    }

    /**
     * @return InvoiceResponse
     * @throws SzamlaAgentException
     */
    public function getInvoiceResponse(): InvoiceResponse
    {
        return new InvoiceResponse($this);
    }

    /**
     * @return ReceiptResponse
     * @throws SzamlaAgentException
     */
    public function getReceiptResponse(): ReceiptResponse
    {
        return new ReceiptResponse($this);
    }

    public function getXmlFilePath(): string
    {
        return $this->xmlFilePath;
    }
}