<?php

namespace Kboss\SzamlaAgent\Response;

/**
 * Egy számla típusú bizonylat kérésére adott válasz feldolgozó osztály
 */
class InvoiceResponseHandler {


    public static function parseData(array $headers): array
    {
        $invoiceData  = [];

        if (!empty($headers)) {

            if (array_key_exists('szlahu_szamlaszam', $headers)) {
                $invoiceData['szlahu_szamlaszam'] = $headers['szlahu_szamlaszam'];
            }

            if (array_key_exists('szlahu_id', $headers)) {
                $invoiceData['szlahu_id'] = $headers['szlahu_id'];
            }

            if (array_key_exists('szlahu_vevoifiokurl', $headers)) {
                $invoiceData['szlahu_vevoifiokurl'] = urldecode($headers['szlahu_vevoifiokurl']);
            }

            if (array_key_exists('szlahu_kintlevoseg', $headers)) {
                $invoiceData['szlahu_kintlevoseg'] = $headers['szlahu_kintlevoseg'];
            }

            if (array_key_exists('szlahu_nettovegosszeg', $headers)) {
                $invoiceData['szlahu_nettovegosszeg'] = $headers['szlahu_nettovegosszeg'];
            }

            if (array_key_exists('szlahu_bruttovegosszeg', $headers)) {
                $invoiceData['szlahu_bruttovegosszeg'] = $headers['szlahu_bruttovegosszeg'];
            }

            if (array_key_exists('szlahu_error', $headers)) {
                $invoiceData['szlahu_error'] = urldecode($headers['szlahu_error']);
            }

            if (array_key_exists('szlahu_error_code', $headers)) {
                $invoiceData['szlahu_error_code'] = (int) urldecode($headers['szlahu_error_code']);
            }
        }
        return $invoiceData;
    }
}