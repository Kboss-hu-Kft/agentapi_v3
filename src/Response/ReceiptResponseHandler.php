<?php

namespace Kboss\SzamlaAgent\Response;

use function PHPUnit\Framework\isArray;

/**
 * Egy nyugta típusú bizonylat kérésére adott választ reprezentáló osztály
 */
class ReceiptResponseHandler {

    /**
     * @param array $data
     * @return array
     */
    public static function parseData(array $data): array
    {
        $receiptData = [];
        if (isset($data['nyugta']['alap']))        $receiptData = array_merge($receiptData,  $data['nyugta']['alap']);
        if (isset($data['nyugta']['tetelek']))     $receiptData['tetelek'] = $data['nyugta']['tetelek'];
        if (isset($data['nyugta']['osszegek']))    $receiptData['osszegek'] = $data['nyugta']['osszegek'];
        if (isset($data['nyugta']['kifizetesek'])) $receiptData['kifizetesek'] = $data['nyugta']['kifizetesek'];
        if (isset($data['sikeres']))               $receiptData['sikeres'] = $data['sikeres'] === 'true';

        if (isset($data['nyugtaPdf']))             $receiptData['nyugtaPdf'] = $data['nyugtaPdf'];
        if (isset($data['hibakod']))               $receiptData['hibakod'] = (int) $data['hibakod'];
        if (isset($data['hibauzenet']))            $receiptData['hibauzenet'] = urldecode(is_array($data['hibauzenet']) ? implode($data['hibauzenet']) : $data['hibauzenet']);

        return $receiptData;
    }
}