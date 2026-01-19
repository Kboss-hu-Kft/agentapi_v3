<?php

namespace Kboss\SzamlaAgent;

use Kboss\SzamlaAgent\Document\Document;
use Kboss\SzamlaAgent\Enums\RequestType;

/**
 * A Számla Agent beállításait kezelő osztály
 */
class SzamlaAgentSetting {

    /**
     * Számla Agent válasz értékei
     */
    const int RESPONSE_TEXT = 1;
    const int RESPONSE_XML = 2;

    /**
     * Számla Agent API url
     */
    const string API_URL = 'https://www.szamlazz.hu/szamla/';

    /**
     * Alapértelmezett karakterkódolás
     */
    const string CHARSET = 'utf-8';

    /**
     * PDF dokumentumok útvonala
     */
    const string PDF_FILE_SAVE_PATH = 'pdf';

    /**
     * XML fájlok útvonala
     */
    const string XML_FILE_SAVE_PATH = 'xmls';

    /**
     * Log fájlok útvonala
     */
    const string LOG_FILE_SAVE_PATH = 'logs';

    /**
     * Alapértelmezett útvonal
     */
    const string DEFAULT_BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    /**
     * Számla Agent kéréshez használt kulcs
     *
     * @link https://www.szamlazz.hu/blog/2019/07/szamla_agent_kulcsok/
     */
    private string $apiKey {
        get {
            return $this->apiKey;
        }
        set {
            $this->apiKey = $value;
        }
    }

    /**
     * Szeretnénk-e PDF formátumban is megkapni a bizonylatot?
     *
     * @var bool
     */
    public bool $downloadPdf {
        get {
            return $this->downloadPdf;
        }
        set {
            $this->downloadPdf = $value;
        }
    }

    /**
     * XML fájlok mentésének engedélyezése
     *
     * @var bool
     */
    public bool $xmlFileSave = true {
        get {
            return $this->xmlFileSave;
        }
        set {
            $this->xmlFileSave = $value;
        }
    }

    /**
     * Generált (szervernek elküldött) XML fájlok mentésének engedélyezése
     *
     * @var bool
     */
    public bool $requestXmlFileSave = true {
        get {
            return $this->requestXmlFileSave;
        }
        set {
            $this->requestXmlFileSave = $value;
        }
    }

    /**
     * Generált (szervertől visszakapott) válasz XML fájlok mentésének engedélyezése
     *
     * @var bool
     */
    public bool $responseXmlFileSave = true {
        get {
            return $this->responseXmlFileSave;
        }
        set {
            $this->responseXmlFileSave = $value;
        }
    }

    /**
     * Generált PDF fájlok mentésének engedélyezése
     *
     * @var bool
     */
    public bool $pdfFileSave = true {
        get {
            return $this->pdfFileSave;
        }
        set {
            $this->pdfFileSave = $value;
        }
    }

    /**
     * Tanúsítvány
     *
     * @var string
     */
    public string $certificationFilePath = '' {
        get {
            return $this->certificationFilePath;
        }
        set {
            $this->certificationFilePath = $value;
        }
    }

    /**
     * Letöltendő bizonylat másolatainak száma
     *
     * Amennyiben az Agenttel papír alapú számlát készít és kéri a számlaletöltést ($downloadPdf = true),
     * akkor opcionálisan megadható, hogy nem csak a számla eredeti példányát kéri, hanem a másolatot is egyetlen pdf-ben.
     *
     * @var int
     */
    public int $downloadCopiesCount = 1 {
        get {
            return $this->downloadCopiesCount;
        }
        set {
            $this->downloadCopiesCount = $value;
        }
    }

    /**
     * @var bool
     */
    public bool $textResponse = false {
        get {
            return $this->textResponse;
        }
        set {
            $this->textResponse = $value;
        }
    }

    /**
     * Egyedi HTTP fejlécek
     *
     * @var array
     */
    private array $customHTTPHeaders = [];

    /**
     * Ha bérelhető webáruházat üzemeltetsz, ebben a mezőben jelezheted a webáruházat futtató motor nevét.
     * Ha nem vagy benne biztos, akkor kérd ügyfélszolgálatunk segítségét (info@szamlazz.hu).
     * (pl. WooCommerce, OpenCart, PrestaShop, Shoprenter, Superwebáruház, Drupal invoice Agent, stb.)
     *
     * @var string
     */
    public string $aggregator {
        get {
            return $this->aggregator;
        }
        set {
            $this->aggregator = $value;
        }
    }

    /**
     * @var bool
     */
    public bool $guardian = false {
        get {
            return $this->guardian;
        }
        set {
            $this->guardian = $value;
        }
    }

    /**
     * Megjelenjen-e a számlán a cikk azonosító
     *
     * @var bool
     */
    public bool $invoiceItemIdentifier = false {
        get {
            return $this->invoiceItemIdentifier;
        }
        set {
            $this->invoiceItemIdentifier = $value;
        }
    }

    /**
     * A számlát a külső rendszer (Számla Agentet használó rendszer) ezzel az adattal azonosítja. Az adatot trimmelve tároljuk.
     * (a számla adatai később ezzel az adattal is lekérdezhetők lesznek)
     *
     * @var string
     */
    public string $invoiceExternalId = '' {
        get {
            return $this->invoiceExternalId;
        }
        set {
            $this->invoiceExternalId = $value;
        }
    }

    /**
     * @var string
     */
    public string $taxNumber = '' {
        get {
            return $this->taxNumber;
        }
        set {
            $this->taxNumber = $value;
        }
    }

    /**
     * Számla Agent API által generált fájlok alapértelmezett útvonala
     */
    public string $basePath = self::DEFAULT_BASE_PATH {
        get {
            return SzamlaAgentUtil::getRealPath($this->basePath);
        }
        set {
            $this->basePath = $value;
        }
    }

    /**
     * XML fájlok mentési helye
     */
    public string $xmlDirectory = self::XML_FILE_SAVE_PATH {
        get {
            return $this->xmlDirectory;
        }
        set {
            $this->xmlDirectory = $value;
        }
    }

    /**
     * PDF fájlok mentési helye
     */
    public string $pdfDirectory = self::PDF_FILE_SAVE_PATH {
        get {
            return $this->pdfDirectory;
        }
        set {
            $this->pdfDirectory = $value;
        }
    }

    /**
     * Log fájlok mentési helye
     */
    public string $logDirectory = self::LOG_FILE_SAVE_PATH {
        get {
            return $this->logDirectory;
        }
        set {
            $this->logDirectory = $value;
        }
    }

    /**
     * @var array
     */
    protected array $environment = [];

    /**
     * Agent kéréshez alkalmazott timeout
     *
     * @var int
     */
    public int $requestTimeout = SzamlaAgentRequest::REQUEST_TIMEOUT {
        get {
            return $this->requestTimeout;
        }
        set {
            $this->requestTimeout = $value;
        }
    }

    /**
     * Agent kéréshez alkalmazott connection timeout
     *
     * @var int
     */
    public int $requestConnectionTimeout = 0 {
        get {
            return $this->requestConnectionTimeout;
        }
        set {
            $this->requestConnectionTimeout = max(0, $value);
        }
    }

    /**
     * Naplózási e-mail cím
     * Erre az e-mail címre küldünk üzenetet, ha hiba esemény történik
     *
     * @var string
     */
    public string $logEmail = '' {
        get {
            return $this->logEmail;
        }
        set {
            if (SzamlaAgentUtil::validateEmailAddress($value)) {
                $this->logEmail = $value;
            }
        }
    }

    public bool $singleton = false {
        get {
            return $this->singleton;
        }
        set {
            $this->singleton = $value;
        }
    }

    private Log $log {
        get {
            return $this->log;
        }
    }

    /**
     * Számla Agent beállítás létrehozása
     *
     * @param string $apiKey     SzámlaAgent kulcs
     * @param bool $downloadPdf  elkeszült bizonylat letöltése
     * @param string $aggregator webáruházat futtató motor neve
     */
    function __construct(string $apiKey, bool $downloadPdf, string $aggregator, Log $log) {
        $this->apiKey = $apiKey;
        $this->downloadPdf = $downloadPdf;
        $this->aggregator = $aggregator;
        $this->log = $log;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        $url = self::API_URL;
        if (SzamlaAgentUtil::isNotBlank($this->getEnvironmentUrl())) {
            $url = $this->getEnvironmentUrl();
        }
        return $url;
    }

    /**
     * Összeállítja a Számla Agent beállítás XML adatait
     *
     * @param RequestType $requestType
     * @param Document|TaxPayer $document
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(RequestType $requestType, Document|TaxPayer $document): array {
        $fields = ['szamlaagentkulcs'];

        return match ($requestType) {
            RequestType::GENERATE_PROFORMA, RequestType::GENERATE_INVOICE, RequestType::GENERATE_PREPAYMENT_INVOICE, RequestType::GENERATE_FINAL_INVOICE, RequestType::GENERATE_CORRECTIVE_INVOICE, RequestType::GENERATE_DELIVERY_NOTE => $this->buildFieldsData($document, array_merge($fields, ['eszamla', 'szamlaLetoltes', 'szamlaLetoltesPld', 'valaszVerzio', 'aggregator', 'guardian', 'cikkazoninvoice', 'szamlaKulsoAzon'])),
            RequestType::GENERATE_REVERSE_INVOICE => $this->buildFieldsData($document, array_merge($fields, ['eszamla', 'szamlaLetoltes', 'szamlaLetoltesPld', 'aggregator', 'guardian', 'valaszVerzio', 'szamlaKulsoAzon'])),
            RequestType::PAY_INVOICE => $this->buildFieldsData($document, array_merge($fields, ['szamlaszam', 'adoszam', 'additiv', 'aggregator', 'valaszVerzio'])),
            RequestType::REQUEST_INVOICE_DATA => $this->buildFieldsData($document, array_merge($fields, ['szamlaszam', 'rendelesSzam', 'pdf'])),
            RequestType::REQUEST_INVOICE_PDF => $this->buildFieldsData($document, array_merge($fields, ['szamlaszam', 'rendelesSzam', 'valaszVerzio', 'szamlaKulsoAzon'])),
            RequestType::GENERATE_RECEIPT, RequestType::REQUEST_RECEIPT_DATA, RequestType::REQUEST_RECEIPT_PDF, RequestType::GENERATE_REVERSE_RECEIPT => $this->buildFieldsData($document, array_merge($fields, ['pdfLetoltes'])),
            RequestType::SEND_RECEIPT, RequestType::GET_TAX_PAYER, RequestType::DELETE_PROFORMA => $this->buildFieldsData($document, $fields),
        };
    }

    /**
     * Összeállítja és visszaadja az adott mezőkhöz tartozó adatokat
     *
     * @param Document|TaxPayer $document
     * @param array             $fields
     *
     * @return array
     * @throws SzamlaAgentException
     */
    private function buildFieldsData(Document|TaxPayer $document, array $fields): array {
        $data = [];

        foreach ($fields as $key) {
            $value = match ($key) {
                'szamlaagentkulcs' => $this->apiKey,
                'eszamla' => $document->getHeader()->isEInvoice(),
                'szamlaLetoltes', 'pdf', 'pdfLetoltes' => $this->downloadPdf,
                'szamlaLetoltesPld' => $this->downloadCopiesCount,
                'valaszVerzio' => $this->textResponse ? self::RESPONSE_TEXT : self::RESPONSE_XML,
                'aggregator' => $this->aggregator,
                'guardian' => $this->guardian,
                'cikkazoninvoice' => $this->invoiceItemIdentifier,
                'szamlaKulsoAzon' => $this->invoiceExternalId,
                'additiv' => $document->additive,
                'szamlaszam' => $document->getHeader()->documentNumber,
                'rendelesSzam' => $document->getHeader()->orderNumber,
                'adoszam' => $this->taxNumber,
                default => throw new SzamlaAgentException(SzamlaAgentException::XML_KEY_NOT_EXISTS . ": ". $key),
            };

            if (isset($value) && SzamlaAgentUtil::isNotBlank($value)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Visszaadja, hogy lett-e beállítva külön certifikáció
     * Ha a beállított fájl nem létezik kivételt dob
     * @return bool
     * @throws SzamlaAgentException
     */
    public function hasCertification(): bool
    {
        if (SzamlaAgentUtil::isNotBlank($this->certificationFilePath)) {
            if (file_exists($this->certificationFilePath)) {
                return true;
            } else {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_CERTIFICATION_FILE);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isNotTextResponse(): bool
    {
        return !$this->textResponse;
    }

    /**
     * Egyedi HTTP fejléc hozzáadása
     *
     * @param $key
     * @param $value
     * @throws SzamlaAgentException
     */
    public function addCustomHTTPHeader($key, $value): void
    {
        if (SzamlaAgentUtil::isNotBlank($key)) {
            $customHeaders = $this->customHTTPHeaders;
            $customHeaders[$key] = $value;
            $this->customHTTPHeaders = $customHeaders;
        } else {
            $this->log->writeLog('Egyedi HTTP fejléchez megadott kulcs nem lehet üres', Log::LOG_LEVEL_WARN, $this->getLogPath(), $this->logEmail);
        }
    }

    /**
     * Egyedi HTTP fejléc eltávolítása
     *
     * @param string $key
     */
    public function removeCustomHTTPHeader(string $key): void
    {
        if (SzamlaAgentUtil::isNotBlank($key)) {
            $customHeaders = $this->customHTTPHeaders;
            unset($customHeaders[$key]);
            $this->customHTTPHeaders = $customHeaders;
        }
    }

    /**
     * @return string
     */
    public function getXmlPath(): string
    {
        return SzamlaAgentUtil::getRealPath($this->basePath . DIRECTORY_SEPARATOR . $this->xmlDirectory);
    }

    /**
     * @return string
     */
    public function getPdfPath(): string
    {
        return SzamlaAgentUtil::getRealPath($this->basePath . DIRECTORY_SEPARATOR . $this->pdfDirectory);
    }

    /**
     * @return string
     */
    public function getLogPath(): string
    {
        return SzamlaAgentUtil::getRealPath($this->basePath . DIRECTORY_SEPARATOR . $this->logDirectory);
    }

    /**
     * @return array
     */
    public function getEnvironment(): array
    {
        return $this->environment;
    }

    /**
     * @param string  $name
     * @param string  $url
     * @param array   $authorization
     */
    public function setEnvironment(string $name, string $url, array $authorization = []): void
    {
        $this->environment = array(
            'name' => $name,
            'url'  => $url,
            'auth' => $authorization
        );
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }



    /**
     * @return boolean
     */
    public function hasEnvironment(): bool
    {
        return !empty($this->getEnvironment());
    }

    /**
     * @return string|null
     */
    public function getEnvironmentName(): ?string
    {
        return ($this->hasEnvironment() && array_key_exists('name', $this->getEnvironment()) ? $this->getEnvironment()['name'] : null);
    }

    /**
     * @return string|null
     */
    public function getEnvironmentUrl(): ?string
    {
        return ($this->hasEnvironment() && array_key_exists('url', $this->getEnvironment()) ? $this->getEnvironment()['url'] : null);
    }

    /**
     * @return bool
     */
    public function hasEnvironmentAuth(): bool
    {
        return $this->hasEnvironment() && array_key_exists('auth', $this->getEnvironment()) && is_array($this->getEnvironment()['auth']);
    }

    /**
     * @return int|null
     */
    public function getEnvironmentAuthType(): ?int
    {
        return ($this->hasEnvironmentAuth() && array_key_exists('type', $this->getEnvironment()['auth']) ? $this->getEnvironment()['auth']['type'] : 0);
    }

    /**
     * @return string|null
     */
    public function getEnvironmentAuthUser(): ?string
    {
        return ($this->hasEnvironmentAuth() && array_key_exists('user', $this->getEnvironment()['auth']) ? $this->getEnvironment()['auth']['user'] : null);
    }

    /**
     * @return string|null
     */
    public function getEnvironmentAuthPassword(): ?string
    {
        return ($this->hasEnvironmentAuth() && array_key_exists('password', $this->getEnvironment()['auth']) ? $this->getEnvironment()['auth']['password'] : null);
    }

    public function getCustomHTTPHeaders(): array
    {
        return $this->customHTTPHeaders;
    }
}