<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * Számlakép
 */
enum InvoiceTemplate: string {
    /** Számlázz.hu ajánlott számlakép */
    case DEFAULT = 'SzlaMost';

    /** Tradicionális számlakép */
    case TRADITIONAL = 'SzlaNoEnv';

    /** Borítékbarát számlakép */
    case ENV_FRIENDLY = 'SzlaAlap';

    /** Hőnyomtatós számlakép (8 cm széles) */
    case EIGHTCM = 'Szla8cm';

    /** Retró kéziszámla számlakép */
    case RETRO = 'SzlaTomb';
}
