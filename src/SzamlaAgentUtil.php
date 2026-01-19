<?php

namespace Kboss\SzamlaAgent;

use DateTime;
use DOMDocument;
use Exception;
use Kboss\SzamlaAgent\Enums\DocumentType;
use ReflectionClass;
use SimpleXMLElement;
use Kboss\SzamlaAgent\Document\Document;

/**
 * A Számla Agent közösen használt, hasznos funkcióinak osztálya
 */
class SzamlaAgentUtil {

    /**
     * Alapértelmezetten hozzáadott napok száma
     */
    const int DEFAULT_ADDED_DAYS = 8;

    /**
     * Számla Agent kulcs hossza
     */
    const int API_KEY_LENGTH = 42;

    /**
     * Pontos dátum (Y-m-d) formátumban
     */
    const string DATE_FORMAT_DATE      = 'date';

    /**
     * Pontos dátum (Y-m-d H:i:s) formátumban
     */
    const string DATE_FORMAT_DATETIME  = 'datetime';

    /**
     * Aktuális időbélyeg
     */
    const string DATE_FORMAT_TIMESTAMP = 'timestamp';

    /**
     * A kapott dátumot formázott szövegként adja vissza
     * (hozzáadva az átadott napok számát)
     *
     * @param int         $count
     * @param string|null $date
     *
     * @return mixed
     * @throws SzamlaAgentException
     * @throws Exception
     */
    public static function addDaysToDate(int $count, ?string $date = null): string
    {
        $newDate = self::getToday();

        if (!empty($date)) {
            $newDate = new DateTime($date);
        }
        $newDate->modify("+" . $count . " day");
        return self::getDateStr($newDate);
    }

    /**
     * A kapott dátumot formázott szövegként adja vissza (típustól függően)
     *
     * @param DateTime $date
     * @param string   $format
     *
     * @return mixed
     * @throws SzamlaAgentException
     */
    public static function getDateStr(DateTime $date, string $format = self::DATE_FORMAT_DATE): string
    {
        return match ($format) {
            self::DATE_FORMAT_DATE => $date->format('Y-m-d'),
            self::DATE_FORMAT_DATETIME => $date->format('Y-m-d H:i:s'),
            self::DATE_FORMAT_TIMESTAMP => $date->getTimestamp(),
            default => throw new SzamlaAgentException(SzamlaAgentException::DATE_FORMAT_NOT_EXISTS . ': ' . $format),
        };
    }

    /**
     * Visszaadja a mai dátumot
     *
     * @return DateTime
     * @throws Exception
     */
    public static function getToday(): DateTime
    {
        return new DateTime('now');
    }

    /**
     * Szövegként adja vissza a mai dátumot ('Y-m-d' formátumban)
     *
     * @return string
     * @throws Exception
     */
    public static function getTodayStr(): string
    {
        $data = self::getToday();
        return $data->format('Y-m-d');
    }

    /**
     * Visszaadja, hogy a megadott dátum használható-e
     * A következő formátum az elfogadott: 'Y-m-d'.
     *
     * @param string $date
     *
     * @return bool
     */
    public static function isValidDate(string $date): bool
    {
        $result = true;
        $parsedDate = DateTime::createFromFormat('Y-m-d', $date);

        if (!$parsedDate) {
            $result = false;
        }

        if (is_array(DateTime::getLastErrors()) && DateTime::getLastErrors()['warning_count'] > 0) {
            $result = false;
        }

        if ($parsedDate && !checkdate($parsedDate->format("m"), $parsedDate->format("d"), $parsedDate->format("Y"))) {
            $result = false;
        }

        if ($parsedDate && !preg_match("/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $parsedDate->format('Y-m-d'))) {
            $result = false;
        }
        return $result;
    }

    /**
     * Visszaadja, hogy a megadott dátum nem érvényés-e
     * A következő formátum az elfogadott: 'Y-m-d'.
     *
     * @param string $date
     *
     * @return bool
     */
    public static function isNotValidDate(string $date): bool
    {
        return !self::isValidDate($date);
    }

    /**
     * Visszaadja a létrehozandó XML fájl nevét
     * Az $entity megadása esetén a fájl neve az átadott osztály neve lesz
     *
     * @param string $prefix  a fájl előtagja
     * @param string $name    a fájl neve
     * @param ?Document $entity osztály példány
     *
     * @return string|bool
     */
    public static function getXmlFileName(string $prefix, string $name, ?Document $entity = null): string|bool
    {
        if (!empty($name) && !empty($entity)) {
            $name .= '-' . new ReflectionClass($entity)->getShortName();
        }
        return $prefix . '-' . strtolower($name) . '-' . self::getDateTimeWithMilliseconds() . '.xml';
    }


    /**
     * @return string
     */
    public static function getDateTimeWithMilliseconds(): string
    {
        return date("YmdHis").substr(microtime(), 2, 5);
    }

    /**
     * Visszaadja a SimpleXMLElement tartalmát formázott xml-ként
     *
     * @param  SimpleXMLElement|string $xml
     * @return DOMDocument
     */
    public static function formatXml(SimpleXMLElement|string $xml): DOMDocument
    {
        $xmlDocument = new DOMDocument('1.0');
        $xmlDocument->preserveWhiteSpace = false;
        $xmlDocument->formatOutput = true;
        if ($xml instanceof SimpleXMLElement) {
            $xmlDocument->loadXML($xml->asXML());
        } else {
            $xmlDocument->loadXML($xml);
        }
        return $xmlDocument;
    }

    /**
     * Visszaadja a fájl valódi útvonalát
     *
     * @param string $path
     *
     * @return bool|string
     */
    public static function getRealPath(string $path): bool|string
    {
        if (file_exists($path)) {
            $path =  realpath($path);
        }
        return $path;
    }

    /**
     * @param string $dir
     * @param string $fileName
     *
     * @return bool|string
     */
    public static function getAbsPath(string $dir, string $fileName = ''): bool|string
    {
        return self::getRealPath($dir . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * A kapott adatokból előállít egy JSON típusú objektumot
     *
     * @param $data
     *
     * @return false|string
     */
    public static function toJson($data): false|string
    {
        return json_encode($data);
    }

    /**
     * @param SimpleXMLElement $data
     *
     * @return mixed
     */
    public static function toArray(SimpleXMLElement $data): mixed
    {
        return json_decode(self::toJson($data),TRUE);
    }

    /**
     * @param mixed $value
     *
     * @return float
     */
    public static function doubleFormat(mixed $value): float
    {
        $result = 0;

        if (is_string($value)) {
            $result = floatval($value);
        }

        if (is_int($value)) {
            $result = doubleval($value);
        }

        if (is_double($value)) {
            $decimals = strlen(preg_replace('/[\d]+[\.]?/', '', $value, 1));
            if ($decimals == 0) {
                $result = number_format($value, 1, '.', '');
            } else {
                $result = $value;
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function nonNegativeInteger(mixed $value): int
    {
        if (is_string($value)) {
            $value = intval($value);
        }

        if (is_double($value)) {
            $value = (int) floor($value);
        }

        if (is_int($value)) {
            if ($value < 0) {
                $value = 0;
            }
        }
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isBlank(mixed $value): bool
    {
        $result = false;

        if (!isset($value)) {
            $result = true;
        }

        if (!$result && is_string($value) && (trim(empty($value)))) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isNotBlank(mixed $value): bool
    {
        return !self::isBlank($value);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isNotNull(mixed $value): bool
    {
        return !is_null($value);
    }

    /**
     * Remove namespaces from XML elements
     *
     * @param  SimpleXMLElement $xmlNode
     * @return SimpleXMLElement $xmlNode
     */
    public static function removeNamespaces(SimpleXMLElement $xmlNode): SimpleXMLElement
    {
        $xmlString = $xmlNode->asXML();
        $cleanedXmlString = preg_replace('/(<\/|<)[a-z0-9]+:([a-z0-9]+[ =>])/i', '$1$2', $xmlString);
        return simplexml_load_string($cleanedXmlString);
    }

    /**
     * @param string $string
     *
     * @return mixed
     * @throws SzamlaAgentException
     */
    public static function isValidJSON(string $string): mixed
    {
        // decode the JSON data
        $result = json_decode($string, true);
        // switch and check possible JSON errors
        $error = match (json_last_error()) {
            JSON_ERROR_NONE => '',
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON.',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
            JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
            JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
            JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.',
            default => 'Unknown JSON error occured.',
        };

        if ($error !== '') {
            throw new SzamlaAgentException($error);
        }

        return $result;
    }

    /**
     * Törli a fájlokat a megadott könyvtárból.
     * Ha meg van adva a törlendő fájlok kiterjesztése, akkor csak azokat a típusú fájlokat törli.
     *
     * @param string $dir
     * @param string|null $extension
     */
    public static function deleteFilesFromDir(string $dir, ?string $extension = null): void
    {
        if (self::isNotBlank($dir) && is_dir($dir)) {
            $filter = (self::isNotBlank($extension) ? '*.' . $extension  : '*');
            $files = glob($dir . DIRECTORY_SEPARATOR . $filter);
            foreach($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Ellenőrzi az átadott email címet, hogy érvényes-e
     *
     * @param string $email
     * @return string
     * @throws SzamlaAgentException
     */
    public static function validateEmailAddress(string $email): string
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_EMAIL . $email);
        }
        return $email;
    }

    /**
     * Ellenőrzi az API kulcsot
     *
     * @param string $apiKey
     * @return void
     * @throws SzamlaAgentException
     */
    public static function checkApiKey(string $apiKey): void
    {
        if (empty($apiKey)) {
            throw new SzamlaAgentException(SzamlaAgentException::APIKEY_MISSING);
        }

        if (strlen($apiKey) !== self::API_KEY_LENGTH) {
            throw new SzamlaAgentException(SzamlaAgentException::APIKEY_LENGHT);
        }
    }

    /**
     * Visszaadja, hogy a válasz tartalmaz-e PDF-et
     *
     * @param array $responseData
     *
     * @return bool
     */
    public static function isPdfResponse(array $responseData) : bool {

        $result = false;

        if (isset($responseData['pdf'])) {
            $result = true;
        }

        if (isset($responseData['nyugtaPdf'])) {
            $result = true;
        }

        if (isset($responseData['headers']['content-type']) && $responseData['headers']['content-type'] == 'application/pdf') {
            $result = true;
        }

        if (isset($responseData['headers']['content-disposition']) && stripos($responseData['headers']['content-disposition'],'pdf') !== false) {
            $result = true;
        }
        return $result;
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'adózó' típusú volt-e
     *
     * @param string $xmlSchemaType
     * @return bool
     */
    public static function isTaxPayerResponse(string $xmlSchemaType): bool
    {
        return $xmlSchemaType === DocumentType::TAXPAYER->getType();
    }

    /**
     * Visszaadja, hogy a válasz XML séma nem 'adózó' típusú volt-e
     *
     * @param string $xmlSchemaType
     * @return bool
     */
    public static function isNotTaxPayerResponse(string $xmlSchemaType): bool
    {
        return !self::isTaxPayerResponse($xmlSchemaType);
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'számla' típusú volt-e
     *
     * @param string $xmlSchemaType
     * @return bool
     */
    public static function isAgentInvoiceResponse(string $xmlSchemaType): bool
    {
        return $xmlSchemaType === DocumentType::INVOICE->getType();
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'nyugta' típusú volt-e
     *
     * @param string $xmlSchemaType
     * @return bool
     */
    public static function isAgentReceiptResponse(string $xmlSchemaType): bool
    {
        return $xmlSchemaType === DocumentType::RECEIPT->getType();
    }

    /**
     * Visszaadja, hogy a válasz XML séma 'díjbekérő' típusú volt-e
     *
     * @param string $xmlSchemaType
     * @return bool
     */
    public static function isProformaResponse(string $xmlSchemaType): bool
    {
        return $xmlSchemaType === DocumentType::PROFORMA->getType();
    }

    public static function dotCheck(string $value): string
    {
        if (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }
        return $value;
    }
}