<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * Nyelv
 */
enum Language: string
{
    case LANGUAGE_HU = 'hu';

    /**
     * angol nyelv
     */
    case LANGUAGE_EN = 'en';

    /**
     * német nyelv
     */
    case LANGUAGE_DE = 'de';

    /**
     * olasz nyelv
     */
    case LANGUAGE_IT = 'it';

    /**
     * román nyelv
     */
    case LANGUAGE_RO = 'ro';

    /**
     * szlovák nyelv
     */
    case LANGUAGE_SK = 'sk';

    /**
     * horvát nyelv
     */
    case LANGUAGE_HR = 'hr';

    /**
     * francia nyelv
     */
    case LANGUAGE_FR = 'fr';

    /**
     * spanyol nyelv
     */
    case LANGUAGE_ES = 'es';

    /**
     * cseh nyelv
     */
    case LANGUAGE_CZ = 'cz';

    /**
     * lengyel nyelv
     */
    case LANGUAGE_PL = 'pl';

    /**
     * bolgár nyelv
     */
    case LANGUAGE_BG = 'bg';

    /**
     * holland nyelv
     */
    case LANGUAGE_NL = 'nl';

    /**
     * orosz nyelv
     */
    case LANGUAGE_RU = 'ru';

    /**
     * szlovén nyelv
     */
    case LANGUAGE_SI = 'si';

    public function getName(): string
    {
        return match($this)
        {
                Language::LANGUAGE_HU => "magyar",
                Language::LANGUAGE_EN => "angol",
                Language::LANGUAGE_DE => "német",
                Language::LANGUAGE_IT => "olasz",
                Language::LANGUAGE_RO => "román",
                Language::LANGUAGE_SK => "szlovák",
                Language::LANGUAGE_HR => "horvát",
                Language::LANGUAGE_FR => "francia",
                Language::LANGUAGE_ES => "spanyol",
                Language::LANGUAGE_CZ => "cseh",
                Language::LANGUAGE_PL => "lengyel",
                Language::LANGUAGE_BG => "bolgár",
                Language::LANGUAGE_RU => "orosz",
                Language::LANGUAGE_SI => "szlovén",
                Language::LANGUAGE_NL => "holland",
        };
    }

    public static function getDefault(): Language {
        return Language::LANGUAGE_HU;
    }
}
