<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * ÁFA típusok
 */
enum VatType: string
{
    /**
     * Áfakulcs: tárgyi adómentes
     */
    case TAM = 'TAM';

    /**
     * Áfakulcs: alanyi adómentes
     */
    case AAM = 'AAM';

    /**
     * Áfakulcs: EU-n belül
     */
    case EU = 'EU';

    /**
     * Áfakulcs: EU-n kívül
     */
    case EUK = 'EUK';

    /**
     * Áfakulcs: mentes az adó alól
     */
    case MAA = 'MAA';

    /**
     * Áfakulcs: fordított áfa
     */
    case F_AFA = 'F.AFA';

    /**
     * Áfakulcs: különbözeti áfa
     */
    case K_AFA = 'K.AFA';

    /**
     * Áfakulcs: áfakörön kívüli
     */
    case AKK = 'ÁKK';

    /**
     * Áfakulcs: áfakörön kívüli
     */
    case TAHK = 'TAHK';

    /**
     * Áfakulcs: EU-n belüli termék értékesítés
     */
    case EUT = 'EUT';

    /**
     * Áfakulcs: EU-n kívüli termék értékesítés
     */
    case EUKT = 'EUKT';

    /**
     * Áfakulcs: EU-n belüli
     */
    case KBAET = 'KBAET';

    /**
     * Áfakulcs: EU-n belüli
     */
    case KBAUK = 'KBAUK';

    /**
     * Áfakulcs: EU-n kívüli
     */
    case EAM = 'EAM';

    /**
     * Áfakulcs: Mentes az adó alól
     */
    case NAM = 'NAM';

    /**
     * Áfakulcs: áfa tárgyi hatályán kívül
     */
    case ATK = 'ATK';

    /**
     * Áfakulcs: EU-n belüli
     */
    case EUFAD37 = 'EUFAD37';

    /**
     * Áfakulcs: EU-n belüli
     */
    case EUFADE = 'EUFADE';

    /**
     * Áfakulcs: EU-n belüli
     */
    case EUE = 'EUE';

    /**
     * Áfakulcs: EU-n kívüli
     */
    case HO = 'HO';

    case VAT_0 = '0';
    case VAT_5 = '5';
    case VAT_18 = '18';
    case VAT_27 = '27';
    case VAT_1 = '1';
    case VAT_2 = '2';
    case VAT_2_1 = '2.1'; // Franciaország
    case VAT_3 = '3';
    case VAT_4 = '4';
    case VAT_4_8 = '4.8'; // Írország
    case VAT_5_5 = '5.5'; // Franciaország
    case VAT_6 = '6';
    case VAT_7 = '7';
    case VAT_7_7 = '7.7'; // Svájc
    case VAT_8 = '8';
    case VAT_8_1 = '8.1'; // Svájc
    case VAT_9 = '9';
    case VAT_9_5 = '9.5'; // Szlovénia
    case VAT_10 = '10';
    case VAT_11 = '11';
    case VAT_12 = '12';
    case VAT_13 = '13';
    case VAT_13_5 = '13.5'; // Írország
    case VAT_14 = '14';
    case VAT_15 = '15';
    case VAT_16 = '16';
    case VAT_17 = '17';
    case VAT_19 = '19';
    case VAT_20 = '20';
    case VAT_21 = '21';
    case VAT_22 = '22';
    case VAT_23 = '23';
    case VAT_24 = '24';
    case VAT_25 = '25';
    case VAT_25_5 = '25.5';  // Finnország
    case VAT_26 = '26';
}
