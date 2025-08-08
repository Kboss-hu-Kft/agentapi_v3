<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * API hívás típusa
 */
enum RequestType
{
    case GENERATE_PROFORMA;
    case GENERATE_INVOICE;
    case GENERATE_PREPAYMENT_INVOICE;
    case GENERATE_FINAL_INVOICE;
    case GENERATE_CORRECTIVE_INVOICE;
    case GENERATE_DELIVERY_NOTE;
    case GENERATE_REVERSE_INVOICE;
    case PAY_INVOICE;
    case REQUEST_INVOICE_DATA;
    case REQUEST_INVOICE_PDF;
    case GENERATE_RECEIPT;
    case GENERATE_REVERSE_RECEIPT;
    case SEND_RECEIPT;
    case REQUEST_RECEIPT_DATA;
    case REQUEST_RECEIPT_PDF;
    case GET_TAX_PAYER;
    case DELETE_PROFORMA;

    /**
     * Számla Agent kérés XML fájlneve
     *
     * @return string
     */
    public function fileName(): string {
        return match ($this) {
            RequestType::GENERATE_PROFORMA,
            RequestType::GENERATE_INVOICE,
            RequestType::GENERATE_PREPAYMENT_INVOICE,
            RequestType::GENERATE_FINAL_INVOICE,
            RequestType::GENERATE_CORRECTIVE_INVOICE,
            RequestType::GENERATE_DELIVERY_NOTE => 'action-xmlagentxmlfile',
            RequestType::GENERATE_REVERSE_INVOICE => 'action-szamla_agent_st',
            RequestType::PAY_INVOICE => 'action-szamla_agent_kifiz',
            RequestType::REQUEST_INVOICE_DATA => 'action-szamla_agent_xml',
            RequestType::REQUEST_INVOICE_PDF => 'action-szamla_agent_pdf',
            RequestType::GENERATE_RECEIPT => 'action-szamla_agent_nyugta_create',
            RequestType::GENERATE_REVERSE_RECEIPT => 'action-szamla_agent_nyugta_storno',
            RequestType::SEND_RECEIPT => 'action-szamla_agent_nyugta_send',
            RequestType::REQUEST_RECEIPT_DATA,
            RequestType::REQUEST_RECEIPT_PDF => 'action-szamla_agent_nyugta_get',
            RequestType::GET_TAX_PAYER => 'action-szamla_agent_taxpayer',
            RequestType::DELETE_PROFORMA => 'action-szamla_agent_dijbekero_torlese',
        };
    }

    /**
     * XML gyökérelem neve
     *
     * @return string
     */
    public function xmlName(): string {
        return match ($this) {
            RequestType::GENERATE_PROFORMA,
            RequestType::GENERATE_INVOICE,
            RequestType::GENERATE_PREPAYMENT_INVOICE,
            RequestType::GENERATE_FINAL_INVOICE,
            RequestType::GENERATE_CORRECTIVE_INVOICE,
            RequestType::GENERATE_DELIVERY_NOTE => 'xmlszamla',
            RequestType::GENERATE_REVERSE_INVOICE => 'xmlszamlast',
            RequestType::PAY_INVOICE => 'xmlszamlakifiz',
            RequestType::REQUEST_INVOICE_DATA => 'xmlszamlaxml',
            RequestType::REQUEST_INVOICE_PDF => 'xmlszamlapdf',
            RequestType::GENERATE_RECEIPT => 'xmlnyugtacreate',
            RequestType::GENERATE_REVERSE_RECEIPT => 'xmlnyugtast',
            RequestType::SEND_RECEIPT => 'xmlnyugtasend',
            RequestType::REQUEST_RECEIPT_DATA,
            RequestType::REQUEST_RECEIPT_PDF => 'xmlnyugtaget',
            RequestType::GET_TAX_PAYER => 'xmltaxpayer',
            RequestType::DELETE_PROFORMA => 'xmlszamladbkdel',
        };
    }

    /**
     * XSD könyvtár
     *
     * @return string
     */
    public function xsdDir(): string {
        return match ($this) {
            RequestType::GENERATE_PROFORMA,
            RequestType::GENERATE_INVOICE,
            RequestType::GENERATE_PREPAYMENT_INVOICE,
            RequestType::GENERATE_FINAL_INVOICE,
            RequestType::GENERATE_CORRECTIVE_INVOICE,
            RequestType::GENERATE_DELIVERY_NOTE => 'agent',
            RequestType::GENERATE_REVERSE_INVOICE => 'agentst',
            RequestType::PAY_INVOICE => 'agentkifiz',
            RequestType::REQUEST_INVOICE_DATA => 'agentxml',
            RequestType::REQUEST_INVOICE_PDF => 'agentpdf',
            RequestType::GENERATE_RECEIPT => 'nyugtacreate',
            RequestType::GENERATE_REVERSE_RECEIPT => 'nyugtast',
            RequestType::SEND_RECEIPT => 'nyugtasend',
            RequestType::REQUEST_RECEIPT_DATA,
            RequestType::REQUEST_RECEIPT_PDF => 'nyugtaget',
            RequestType::GET_TAX_PAYER => 'taxpayer',
            RequestType::DELETE_PROFORMA => 'dijbekerodel',
        };
    }

    /**
     *  Visszaadja az XML séma típusát
     *  (számla, nyugta, adózó)
     *
     * @return DocumentType
     */
    public function documentType(): DocumentType {
        return match ($this) {
            RequestType::GENERATE_PROFORMA,
            RequestType::GENERATE_INVOICE,
            RequestType::GENERATE_PREPAYMENT_INVOICE,
            RequestType::GENERATE_FINAL_INVOICE,
            RequestType::GENERATE_CORRECTIVE_INVOICE,
            RequestType::GENERATE_DELIVERY_NOTE,
            RequestType::GENERATE_REVERSE_INVOICE,
            RequestType::PAY_INVOICE,
            RequestType::REQUEST_INVOICE_DATA,
            RequestType::REQUEST_INVOICE_PDF => DocumentType::INVOICE,
            RequestType::DELETE_PROFORMA => DocumentType::PROFORMA,
            RequestType::GENERATE_RECEIPT,
            RequestType::GENERATE_REVERSE_RECEIPT,
            RequestType::SEND_RECEIPT,
            RequestType::REQUEST_RECEIPT_DATA,
            RequestType::REQUEST_RECEIPT_PDF => DocumentType::RECEIPT,
            RequestType::GET_TAX_PAYER => DocumentType::TAXPAYER
        };
    }

    public static function isDocumentCreate(RequestType $type): bool
    {
        return match ($type) {
            RequestType::GENERATE_PROFORMA,
            RequestType::GENERATE_INVOICE,
            RequestType::GENERATE_PREPAYMENT_INVOICE,
            RequestType::GENERATE_FINAL_INVOICE,
            RequestType::GENERATE_CORRECTIVE_INVOICE,
            RequestType::GENERATE_DELIVERY_NOTE,
            RequestType::GENERATE_RECEIPT => true,
            default => false,
        };
    }
}
