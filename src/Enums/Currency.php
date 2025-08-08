<?php

namespace Kboss\SzamlaAgent\Enums;

/**
 * Pénznem
 */
enum Currency
{

    // forint
    case HUF;
    // euró
    case EUR;
    // svájci frank
    case CHF;
    // amerikai dollár
    case USD;
    // Arab Emírségek dirham
    case AED;
    // albán lek
    case ALL;
    // ausztrál dollár
    case AUD;
    // bosnyák konvertibilis márka
    case BAM;
    // bolgár leva
    case BGN;
    // brazil real
    case BRL;
    // kanadai dollár
    case CAD;
    // kínai jüan
    case CNY;
    // cseh korona
    case CZK;
    // dán korona
    case DKK;
    // észt korona
    case EEK;
    // angol font
    case GBP;
    // hongkongi dollár
    case HKD;
    // horvát kún
    case HRK;
    // indonéz rúpia
    case IDR;
    // izraeli sékel
    case ILS;
    // indiai rúpia
    case INR;
    // izlandi korona
    case ISK;
    // japán jen
    case JPY;
    // dél-koreai won
    case KRW;
    // kenyai shilling
    case KSH;
    // dél-afrikai rand
    case KWD;
    // litván litas
    case LTL;
    // lett lat
    case LVL;
    // mexikói peso
    case MXN;
    // maláj ringgit
    case MYR;
    // norvég koro
    case NOK;
    // új-zélandi dollár
    case NZD;
    // fülöp-szigeteki peso
    case PHP;
    // lengyel zloty
    case PLN;
    // új román lej
    case RON;
    // szerb dínár
    case RSD;
    // orosz rubel
    case RUB;
    // svéd koron
    case SEK;
    // szingapúri dollár
    case SGD;
    // thai bát
    case THB;
    // török líra
    case TRY;
    // ukrán hryvna
    case UAH;
    // vietnámi dong
    case VND;
    // dél-afrikai rand
    case ZAR;

    public function getCode(): string
    {
        return match ($this) {
            Currency::HUF => "HUF",
            Currency::EUR => "EUR",
            Currency::CHF => "CHF",
            Currency::USD => "USD",
            Currency::AED => "AED",
            Currency::ALL => "ALL",
            Currency::AUD => "AUD",
            Currency::BAM => "BAM",
            Currency::BGN => "BGN",
            Currency::BRL => "BRL",
            Currency::CAD => "CAD",
            Currency::CNY => "CNY",
            Currency::CZK => "CZK",
            Currency::DKK => "DKK",
            Currency::EEK => "EEK",
            Currency::GBP => "GBP",
            Currency::HKD => "HKD",
            Currency::HRK => "HRK",
            Currency::IDR => "IDR",
            Currency::ILS => "ILS",
            Currency::INR => "INR",
            Currency::ISK => "ISK",
            Currency::JPY => "JPY",
            Currency::KRW => "KRW",
            Currency::KSH => "KSH",
            Currency::KWD => "KWD",
            Currency::LTL => "LTL",
            Currency::LVL => "LVL",
            Currency::MXN => "MXN",
            Currency::MYR => "MYR",
            Currency::NOK => "NOK",
            Currency::NZD => "NZD",
            Currency::PHP => "PHP",
            Currency::PLN => "PLN",
            Currency::RON => "RON",
            Currency::RSD => "RSD",
            Currency::RUB => "RUB",
            Currency::SEK => "SEK",
            Currency::SGD => "SGD",
            Currency::THB => "THB",
            Currency::TRY => "TRY",
            Currency::UAH => "UAH",
            Currency::VND => "VND",
            Currency::ZAR => "ZAR",
        };
    }

    public function getName(): string
    {
        return match ($this) {
            Currency::HUF => "forint",
            Currency::EUR => "euró",
            Currency::USD => "amerikai dollár",
            Currency::ALL => "albán lek",
            Currency::AUD => "ausztrál dollár",
            Currency::AED => "Arab Emírségek dirham",
            Currency::BAM => "bosnyák konvertibilis márka",
            Currency::BRL => "brazil real",
            Currency::CAD => "kanadai dollár",
            Currency::CHF => "svájci frank",
            Currency::CNY => "kínai jüan",
            Currency::CZK => "cseh korona",
            Currency::DKK => "dán korona",
            Currency::EEK => "észt korona",
            Currency::GBP => "angol font",
            Currency::HKD => "hongkongi dollár",
            Currency::HRK => "horvát kúna",
            Currency::ISK => "izlandi korona",
            Currency::JPY => "japán jen",
            Currency::LTL => "litván litas",
            Currency::LVL => "lett lat",
            Currency::MXN => "mexikói peso",
            Currency::NOK => "norvég koron",
            Currency::NZD => "új-zélandi dollár",
            Currency::PLN => "lengyel zloty",
            Currency::RON => "új román lej",
            Currency::RUB => "orosz rubel",
            Currency::SEK => "svéd koron",
            Currency::UAH => "ukrán hryvna",
            Currency::BGN => "bolgár leva",
            Currency::RSD => "szerb dínár",
            Currency::ILS => "izraeli sékel",
            Currency::IDR => "indonéz rúpia",
            Currency::INR => "indiai rúpia",
            Currency::TRY => "török líra",
            Currency::VND => "vietnámi dong",
            Currency::SGD => "szingapúri dollár",
            Currency::THB => "thai bát",
            Currency::KRW => "dél-koreai won",
            Currency::MYR => "maláj ringgit",
            Currency::PHP => "fülöp-szigeteki peso",
            Currency::ZAR => "dél-afrikai rand",
            Currency::KSH => "kenyai shilling",
            Currency::KWD => "kuwaiti dinár",
        };
    }

    public static function getDefault(): Currency {
        return Currency::HUF;
    }
}