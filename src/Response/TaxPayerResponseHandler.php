<?php

namespace Kboss\SzamlaAgent\Response;

use Exception;
use SimpleXMLElement;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Adózó adatai
 */
class TaxPayerResponseHandler
{

    /**
     * @param array $data
     * @return array
     * @throws SzamlaAgentException
     */
    public static function parseData(array $data): array
    {
        $taxPayerData = [];
        try {
            if (array_key_exists('body', $data)) {
                $xml = SzamlaAgentUtil::removeNamespaces(new SimpleXMLElement($data['body']));
                $data = SzamlaAgentUtil::toArray($xml);
            }
            $taxPayerData = array_merge($taxPayerData, $data['taxpayerData']);
            $taxPayerData['requestId'] = $data['header']['requestId'];
            $taxPayerData['taxpayerValidity'] = $data['taxpayerValidity'];
        } catch (Exception $e) {
            throw new SzamlaAgentException(SzamlaAgentException::NAV_REQUEST_ERROR);
        }
        return $taxPayerData;
    }
}