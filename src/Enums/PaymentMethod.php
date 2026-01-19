<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * Alapértelmezett fizetési módok
 */
class PaymentMethod
{
    public const string TRANSFER         = 'átutalás';
    public const string CASH             = 'készpénz';
    public const string BANKCARD         = 'bankkártya';
    public const string CHEQUE           = 'csekk';
    public const string CASH_ON_DELIVERY = 'utánvét';
    public const string PAYPAL           = 'PayPal';
    public const string SZEP_CARD        = 'SZÉP kártya';
    public const string OTP_SIMPLE       = 'OTP Simple';
}
