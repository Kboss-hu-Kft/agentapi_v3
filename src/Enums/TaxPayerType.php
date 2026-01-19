<?php

namespace Kboss\SzamlaAgent\Enums;

enum TaxPayerType: int
{

    /**
     * EU-n kívüli vállalkozás
     */
    case TAXPAYER_NON_EU_ENTERPRISE = 7;

    /**
     * EU-s vállalkozás
     */
    case TAXPAYER_EU_ENTERPRISE = 6;

    /**
     * Van magyar adószáma
     */
    case TAXPAYER_HAS_TAXNUMBER = 1;

    /**
     * Nem tudjuk, hogy adóalany-e
     */
    case TAXPAYER_WE_DONT_KNOW = 0;

    /**
     * Nincs adószáma
     */
    case TAXPAYER_NO_TAXNUMBER = -1;

}
