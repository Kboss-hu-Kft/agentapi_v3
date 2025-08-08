<?php

namespace Kboss\SzamlaAgent;

use Exception;

/**
 * Számla Agent egyedi kivételeket kezelő osztály
 */
class SzamlaAgentException extends Exception {

    const string SYSTEM_DOWN                            = 'Az oldal jelenleg karbantartás alatt áll. Kérjük, látogass vissza pár perc múlva.';
    const string INVALID_REQUEST_TYPE                   = 'A request típusa érvénytelen';
    const string XML_KEY_NOT_EXISTS                     = 'XML kulcs nem létezik';
    const string XML_DATA_NOT_AVAILABLE                 = 'Hiba történt az XML adatok összeállításánál: nincs adat.';
    const string XML_DATA_BUILD_FAILED                  = 'Az XML adatok összeállítása sikertelen';
    const string DATE_FORMAT_NOT_EXISTS                 = 'Nincs ilyen dátum formátum';
    const string NO_SZLAHU_KEY_IN_HEADER                = 'Érvénytelen válasz!';
    const string PDF_FILE_SAVE_SUCCESS                  = 'PDF fájl mentése sikeres';
    const string PDF_FILE_SAVE_FAILED                   = 'PDF fájl mentése sikertelen';
    const string AGENT_RESPONSE_NO_CONTENT              = 'A Számla Agent válaszában nincs tartalom!';
    const string AGENT_RESPONSE_NO_HEADER               = 'A Számla Agent válasza nem tartalmaz fejlécet!';
    const string AGENT_RESPONSE_IS_EMPTY                = 'A Számla Agent válasza nem lehet üres!';
    const string AGENT_ERROR                            = 'Agent hiba';
    const string FILE_CREATION_FAILED                   = 'A fájl létrehozása sikertelen.';
    const string ATTACHMENT_NOT_EXISTS                  = 'A csatolandó fájl nem létezik';
    const string INVOICE_NOTIFICATION_SEND_FAILED       = 'Számlaértesítő kézbesítése sikertelen';
    const string INVALID_JSON                           = 'Érvénytelen JSON';
    const string INVOICE_ID_IS_EMPTY                    = 'A számla azonosító üres';
    const string CONNECTION_ERROR                       = 'Sikertelen kapcsolódás';
    const string XML_FILE_SAVE_FAILED                   = 'XML fálj mentése sikertelen';
    const string MISSING_CERTIFICATION_FILE             = 'A megadott certifikációs fájl nem létezik';
    const string INVALID_EMAIL                          = 'A megadott email cím nem valós! Email: ';
    const string REQUEST_ERROR                          = 'Kérés küldése sikertelen';
    const string ATTACHMENT_MISSING                     = 'A csatolandó fájl neve nincs megadva!';
    const string APIKEY_MISSING                         = 'API kulcs hiányzik!';
    const string APIKEY_LENGHT                          = 'API hossza nem megfelelő';
    const string MISSING_ITEMS                          = 'Nincsenek tételek a bizonylathoz';
    const string MISSING_BUYER                          = 'Nincs vevő';
    const string MISSING_TAXPAYERID                     = 'Nincs törzsszám';
    const string MISSING_DOCUMENT_ID                    = 'Nincs dokumentum azonosító';
    const string NAV_REQUEST_ERROR                      = 'NAV adatlekérés feldolgozása sikertelen';
    const string TEXT_RESPONSE_ERROR                    = 'Válasz feldolgozása sikertelen (text)';
    const string ITEM_ERROR                             = 'Hibás termékadatok: ';
    const string RECEIPT_GROSS_AMOUNT                   = 'Nyugta végösszege túl nagy! (%d)';
    const string INVALID_RESPONSE_OBJECT                = 'Érvényelen válasz objektum!';
    const string MISSING_VALUE                          = 'Hiányzó adat';
    const string MISSING_EMAIL                          = 'Hiányzó email cím';
    const string MISSING_PREFIX                         = 'Hiányzó bizonylat előtag';
    const string LOG_ERROR                              = 'Log file létrehozása sikertelen';
    const string INVALID_VALUE                          = 'Érvénytelen adat';
    const string MISSING_CORRECTIVE_DOCUMENT_ID         = 'Nincs meg a helyesbített számla száma';

    /**
     * Számla Agent egyedi kivétel létrehozása
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message, int $code = 0, ?Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string {
        return __CLASS__ . ": [" . $this->code . "]: " . $this->message . PHP_EOL;
    }
}