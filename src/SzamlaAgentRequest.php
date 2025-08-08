<?php

namespace Kboss\SzamlaAgent;

use BackedEnum;
use CURLFile;
use DOMDocument;
use Exception;
use Kboss\SzamlaAgent\Document\Document;
use Kboss\SzamlaAgent\Document\Invoice\Invoice;
use Kboss\SzamlaAgent\Enums\DocumentType;
use Kboss\SzamlaAgent\Enums\RequestType;
use UnitEnum;

/**
 * A Számla Agent kéréseket kezelő osztály
 */
class SzamlaAgentRequest
{

    public const string CRLF = "\r\n";

    /**
     * Számla Agent XML séma alapértelmezett URL
     * (az XML generálásához használjuk, ne változtasd meg)
     */
    public const string XML_BASE_URL = 'http://www.szamlazz.hu/';

    /**
     * Számla Agent kérés maximális idő másodpercben
     */
    public const int REQUEST_TIMEOUT = 30;

    // Kérés engedélyezési módok
    public const int REQUEST_AUTHORIZATION_BASIC_AUTH = 1;

    /**
     * @var SzamlaAgent
     */
    public SzamlaAgent $agent {
        get {
            return $this->agent;
        }
        set {
            $this->agent = $value;
        }
    }

    /**
     * A Számla Agent kérés típusa
     *
     * @see SzamlaAgentRequest::getActionName()
     * @var RequestType
     */
    public RequestType $type {
        get {
            return $this->type;
        }
        set {
            $this->type = $value;
        }
    }

    /**
     * Bizonylat típusa
     *
     * @var DocumentType
     */
    public DocumentType $documentType {
        get {
            return $this->documentType;
        }
        set {
            $this->documentType = $value;
        }
    }

    /**
     * Az az entitás, amelynek adatait XML formátumban továbbítani fogjuk
     * (számla, díjbekérő, szállítólevél, adózó, stb.)
     *
     * @var Document|null
     */
    public ?Document $entity = null {
        get {
            return $this->entity;
        }
    }

    /**
     * Az Agent kéréshez összeállított XML adatok
     *
     * @var string
     */
    private string $xmlData;

    /**
     * XML fájl elérési útvonala
     *
     * @var string
     */
    private string $xmlFilePath = '';

    /**
     * Egyedi elválasztó azonosító az XML kéréshez
     *
     * @var string
     */
    private string $delim;

    /**
     * Az Agent kérésnél továbbított POST adatok
     *
     * @var string
     */
    private string $postFields;

    /**
     * Az Agent kéréshez tartozó adatok CDATA-ként lesznek átadva
     *
     * @var bool
     */
    private bool $cData = true;

    /**
     * Számla Agent kérés létrehozása
     *
     * @param SzamlaAgent $agent
     * @param RequestType $type
     */
    private function __construct(SzamlaAgent $agent, RequestType $type)
    {
        $this->setAgent($agent);
        $this->setType($type);
        $this->documentType = $type->documentType();
    }

    public static function getForDocument(SzamlaAgent $agent, RequestType $type, Document $entity): SzamlaAgentRequest
    {
        $request = new SzamlaAgentRequest($agent, $type);
        $request->setEntity($entity);
        return $request;
    }

    public static function getForTaxPayer(SzamlaAgent $agent): SzamlaAgentRequest
    {
        return new SzamlaAgentRequest($agent, RequestType::GET_TAX_PAYER);
    }

    /**
     * Összeállítja a kérés elküldéséhez szükséges XML adatokat
     *
     * @throws SzamlaAgentException
     * @throws Exception
     */
    public function buildXmlData(array $xmlData): void
    {
        $this->agent->writeLog("XML adatok összeállítása elkezdődött.");

        try {
            $xml = new SimpleXMLExtended($this->getXmlBase());
            $this->arrayToXML($xmlData, $xml);

            libxml_use_internal_errors(true);
            libxml_clear_errors();
            $formatXml = SzamlaAgentUtil::formatXml($xml);

            if (!empty(libxml_get_errors())) {
                foreach (libxml_get_errors() as $error) {
                    $this->agent->writeLog('Az összeállított XML nem érvényes! ' . $error->line . '. sorban: ' . $error->message);
                }
            }
            $this->setXmlData($formatXml->saveXML());
            $this->agent->writeLog("XML adatok létrehozása kész.");

            if (($this->agent->setting->xmlFileSave && $this->agent->setting->requestXmlFileSave)) {
                $this->createXmlFile($formatXml);
            }
        } catch (Exception $e) {
            $this->agent->writeLog(print_r($this->getXmlData(), true));
            throw new SzamlaAgentException(SzamlaAgentException::XML_DATA_BUILD_FAILED . ": " . $e->getMessage());
        }
    }

    /**
     * @param array $xmlData
     * @param SimpleXMLExtended $xmlFields
     */
    private function arrayToXML(array $xmlData, SimpleXMLExtended $xmlFields): void
    {
        foreach ($xmlData as $key => $value) {
            if (is_array($value)) {
                $fieldKey = $key;
                if (str_contains($key, "item")) $fieldKey = 'tetel';
                if (str_contains($key, "note")) $fieldKey = 'kifizetes';
                $subNode = $xmlFields->addChild("$fieldKey");
                $this->arrayToXML($value, $subNode);
            } else {
                if (is_bool($value)) {
                    $value = ($value) ? 'true' : 'false';
                } else if ($value === 0.0 || $value === 0) {
                    $value = number_format($value, 1, ".", "");
                } else if ($value instanceof BackedEnum) {
                    $value = $value->value;
                } else if ($value instanceof UnitEnum) {
                    $value = $value->name;
                }

                if ($this->cData) {
                    $xmlFields->addChildWithCData(strval($key), $value);
                } else {
                    $value = htmlspecialchars(strval($value));
                    $xmlFields->addChild(strval($key), $value);
                }
            }
        }
    }

    /**
     * Létrehozza a kérés adatait tartalmazó XML fájlt
     *
     * @param DOMDocument $xml
     *
     * @throws SzamlaAgentException
     */
    private function createXmlFile(DOMDocument $xml): void
    {
        $fileName = SzamlaAgentUtil::getXmlFileName('request', $this->getXmlName());
        $xmlSaved = $xml->save($this->agent->setting->getXmlPath() . DIRECTORY_SEPARATOR . $fileName);

        if (!$xmlSaved) {
            throw new SzamlaAgentException(SzamlaAgentException::XML_FILE_SAVE_FAILED);
        }

        $this->setXmlFilePath(SzamlaAgentUtil::getRealPath($this->agent->setting->getXmlPath() . DIRECTORY_SEPARATOR . $fileName));
        $this->agent->writeLog("XML fájl mentése sikeres: " . SzamlaAgentUtil::getRealPath($fileName));
    }

    /**
     * Visszaadja az alapértelmezett XML fejlécet
     *
     * @return string
     */
    private function getXmlBase(): string
    {
        $xmlName = $this->getXmlName();

        $queryData = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $queryData .= '<' . $xmlName . ' xmlns="' . $this->getXmlNs($xmlName) . '" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="' . $this->getSchemaLocation($xmlName) . '">' . PHP_EOL;
        $queryData .= '</' . $xmlName . '>' . self::CRLF;

        return $queryData;
    }

    /**
     * @param string $xmlName
     *
     * @return string
     */
    private function getSchemaLocation(string $xmlName): string
    {
        return self::XML_BASE_URL . "szamla/". $xmlName . " http://www.szamlazz.hu/szamla/docs/xsds/{$this->type->xsdDir()}/" . $xmlName . ".xsd";
    }

    /**
     * Visszaadja az XML séma névterét
     *
     * @param $xmlName
     *
     * @return string
     */
    private function getXmlNs($xmlName): string
    {
        return self::XML_BASE_URL . $xmlName;
    }

    /**
     * Összeállítja az elküldendő POST adatokat
     */
    private function buildQuery(): void
    {
        $this->setDelim(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 16));

        $queryData = '--' . $this->getDelim() . self::CRLF;
        $queryData .= 'Content-Disposition: form-data; name="' . $this->type->fileName() . '"; filename="' . $this->type->fileName() . '"' . self::CRLF;
        $queryData .= 'Content-Type: text/xml' . self::CRLF . self::CRLF;
        $queryData .= $this->getXmlData() . self::CRLF;
        $queryData .= "--" . $this->getDelim() . "--" . self::CRLF;

        $this->setPostFields($queryData);
    }

    /**
     * Számla Agent kérés küldése a szamlazz.hu felé
     *
     * @return array
     *
     * @throws SzamlaAgentException
     * @throws
     */
    public function send(): array
    {
        return $this->sendXMLData($this->entity->buildXmlData($this->agent->setting, $this->type));
    }

    /**
     * Számla Agent kérés küldése a szamlazz.hu felé
     *
     * @param array $xmlData
     * @return array
     *
     * @throws SzamlaAgentException
     */
    public function sendXMLData(array $xmlData): array
    {
        try {
            $this->buildXmlData($xmlData);
            $this->buildQuery();
            return $this->makeCurlCall();

        } catch (Exception $e) {
            $this->agent->writeLog("Hiba történt a kérés küldése közben! " . $e->getMessage(), Log::LOG_LEVEL_ERROR);
            throw new SzamlaAgentException(SzamlaAgentException::REQUEST_ERROR);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function makeCurlCall(): array
    {
        $agent = $this->agent;

        $ch = curl_init($agent->setting->getApiUrl());

        if ($agent->setting->hasCertification()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $agent->setting->certificationFilePath);
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        if ($this->isBasicAuthRequest()) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->getBasicAuthUserPwd());
        }

        $mimeType = 'text/xml';
        if (($agent->setting->xmlFileSave && $agent->setting->requestXmlFileSave)) {
            $xmlFile = new CURLFile($this->xmlFilePath, $mimeType, basename($this->xmlFilePath));
        } else {
            $xmlContent = 'data://application/octet-stream;base64,' . base64_encode($this->getXmlData());
            $fileName = SzamlaAgentUtil::getXmlFileName('request', $this->getXmlName(), $this->entity);
            $xmlFile = new CURLFile($xmlContent, $mimeType, basename($fileName));
        }

        $postFields = array($this->type->fileName() => $xmlFile);

        $httpHeaders = array(
            'charset: ' . SzamlaAgentSetting::CHARSET,
            'PHP: ' . PHP_VERSION,
            'API: ' . SzamlaAgent::API_VERSION
        );

        $customHttpHeaders = $agent->setting->getCustomHTTPHeaders();
        if (!empty($customHttpHeaders)) {
            foreach ($customHttpHeaders as $key => $value) {
                $httpHeaders[] = $key . ': ' . $value;
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);

        if ($this->isAttachments()) {
            $attachments = $this->entity->getAttachments();
            if (!empty($attachments)) {
                for ($i = 0; $i < count($attachments); $i++) {
                    $attachCount = ($i + 1);
                    if (file_exists($attachments[$i])) {
                        $isAttachable = true;
                        foreach ($postFields as $field) {
                            if ($field->name === $attachments[$i]) {
                                $isAttachable = false;
                                $agent->writeLog($attachCount . ". számlamelléklet már csatolva van: " . $attachments[$i], Log::LOG_LEVEL_WARN);
                            }
                        }

                        if ($isAttachable) {
                            $attachment = new CURLFile($attachments[$i]);
                            $attachment->setPostFilename(basename($attachments[$i]));
                            $postFields["attachfile" . $attachCount] = $attachment;
                            $agent->writeLog($attachCount . ". számlamelléklet csatolva: " . $attachments[$i]);
                        }
                    }
                }
            }
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->agent->setting->requestTimeout);
        if ($this->agent->setting->requestConnectionTimeout > 0) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->agent->setting->requestConnectionTimeout);
        }

        $agent->writeLog("CURL adatok elküldése elkezdődött: " . $this->getPostFields());
        $result = curl_exec($ch);

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize);
        $headers = preg_split('/\n|\r\n?/', $header);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($result, $headerSize);

        $response = array(
            'headers' => $this->getHeadersFromResponse($headers),
            'http_code' => $httpCode,
            'body' => $body
        );

        $error = curl_error($ch);
        if (!empty($error)) {
            $agent->logError(SzamlaAgentException::CONNECTION_ERROR . ' - ' . $error);
            throw new SzamlaAgentException($error);
        } else {
            $keys = implode(",", array_keys($headers));
            if ($response['headers']['content-type'] == 'application/pdf' || (!preg_match('/(szlahu_)/', $keys))) {
                $msg = $response['headers'];
            } else {
                $msg = $response;
            }

            $response['headers']['schema-type'] = $this->documentType->getType();
            $agent->writeLog("CURL adatok elküldése sikeresen befejeződött: " . print_r($msg, TRUE));
        }
        curl_close($ch);

        return $response;
    }

    /**
     * Visszaadja a válasz fejléc adatait
     *
     * @param array $headerContent
     *
     * @return array
     */
    private function getHeadersFromResponse(array $headerContent): array
    {
        $headers = [];
        foreach ($headerContent as $index => $content) {
            if (SzamlaAgentUtil::isNotBlank($content)) {
                if ($index === 0) {
                    $headers['http_code'] = $content;
                } else {
                    $pos = strpos($content, ":");
                    if ($pos !== false) {
                        list ($key, $value) = explode(': ', $content);
                        $headers[strtolower($key)] = $value;
                    }
                }
            }
        }
        return $headers;
    }

    /**
     * @param SzamlaAgent $agent
     */
    private function setAgent(SzamlaAgent $agent): void
    {
        $this->agent = $agent;
    }

    /**
     * Beállítja a kérés típusát
     *
     * @param RequestType $type
     * @see   SzamlaAgentRequest::getActionName()
     */
    private function setType(RequestType $type): void
    {
        $this->type = $type;
    }

    /**
     * @param Document $entity
     */
    private function setEntity(Document $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    private function getXmlData(): string
    {
        return $this->xmlData;
    }

    /**
     * @param string $xmlData
     */
    private function setXmlData(string $xmlData): void
    {
        $this->xmlData = $xmlData;
    }

    /**
     * @return string
     */
    private function getDelim(): string
    {
        return $this->delim;
    }

    /**
     * @param string $delim
     */
    private function setDelim(string $delim): void
    {
        $this->delim = $delim;
    }

    /**
     * @return string
     */
    private function getPostFields(): string
    {
        return $this->postFields;
    }

    /**
     * @param string $postFields
     */
    private function setPostFields(string $postFields): void
    {
        $this->postFields = $postFields;
    }

    /**
     * @return string
     */
    public function getXmlName(): string
    {
        return $this->type->xmlName();
    }

    /**
     * @param string $xmlFilePath
     */
    private function setXmlFilePath(string $xmlFilePath): void
    {
        $this->xmlFilePath = $xmlFilePath;
    }

    /**
     * Visszaadja, hogy a dokumentumhoz tartozik-e csatolt fálj
     *
     * @return bool
     */
    private function isAttachments(): bool
    {
        $result = false;
        if (($this->entity instanceof Invoice)) {
            $result = (count($this->entity->getAttachments()) > 0);
        }
        return $result;
    }

    /**
     * @return bool
     */
    private function isBasicAuthRequest(): bool
    {
        $agent = $this->agent;
        return ($agent->setting->hasEnvironment() && $agent->setting->getEnvironmentAuthType() == self::REQUEST_AUTHORIZATION_BASIC_AUTH);
    }

    /**
     * @return string
     */
    private function getBasicAuthUserPwd(): string
    {
        return $this->agent->setting->getEnvironmentAuthUser() . ":" . $this->agent->setting->getEnvironmentAuthPassword();
    }

    public function getXmlFilePath(): string
    {
        return $this->xmlFilePath;
    }
}