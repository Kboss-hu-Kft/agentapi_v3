<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * Dokumentum típus
 */
enum DocumentType {

    /**
     * Normál számla
     */
    case INVOICE;

    /**
     * Sztornó számla
     */
    case REVERSE_INVOICE;

    /**
     * Jóváíró számla
     */
    case PAY_INVOICE;

    /**
     * Helyesbítő számla
     */
    case CORRECTIVE_INVOICE;

    /**
     * Előlegszámla
     */
    case PREPAYMENT_INVOICE;

    /**
     * Végszámla
     */
    case FINAL_INVOICE;

    /**
     * Díjbekérő
     */
    case PROFORMA;

    /**
     * Szállítólevél
     */
    case DELIVERY_NOTE;

    /**
     * Nyugta
     */
    case RECEIPT;

    /**
     * Nyugta sztornó
     */
    case RESERVE_RECEIPT;

    /**
     * Adozó lekérdezése
     */
    case TAXPAYER;


    public function getCode(): string
    {
        return match($this)
        {
            DocumentType::INVOICE => 'SZ',
            DocumentType::REVERSE_INVOICE => 'SS',
            DocumentType::PAY_INVOICE => 'JS',
            DocumentType::CORRECTIVE_INVOICE => 'HS',
            DocumentType::PREPAYMENT_INVOICE => 'ES',
            DocumentType::FINAL_INVOICE => 'VS',
            DocumentType::PROFORMA => 'D',
            DocumentType::DELIVERY_NOTE => 'SL',
            DocumentType::RECEIPT => 'NY',
            DocumentType::RESERVE_RECEIPT => 'SN',
            DocumentType::TAXPAYER => 'TAXPAYER',
        };
    }

    public function getType(): string
    {
        return match($this)
        {
            DocumentType::INVOICE => 'invoice',
            DocumentType::REVERSE_INVOICE => 'reverseInvoice',
            DocumentType::PAY_INVOICE => 'payInvoice',
            DocumentType::CORRECTIVE_INVOICE => 'correctiveInvoice',
            DocumentType::PREPAYMENT_INVOICE => 'prePaymentInvoice',
            DocumentType::FINAL_INVOICE => 'finalInvoice',
            DocumentType::PROFORMA => 'proforma',
            DocumentType::DELIVERY_NOTE => 'deliveryNote',
            DocumentType::RECEIPT => 'receipt',
            DocumentType::RESERVE_RECEIPT => 'reserveReceipt',
            DocumentType::TAXPAYER => 'taxpayer',
        };
    }
}
