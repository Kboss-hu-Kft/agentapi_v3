<?php

namespace Kboss\SzamlaAgent\Entity;

use Kboss\SzamlaAgent\Enums\RequestType;
use Kboss\SzamlaAgent\Enums\TaxPayerType;
use Kboss\SzamlaAgent\Ledger\BuyerLedger;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Vevő
 */
class Buyer
{

    const int BUYER_EMAIL_LIMIT = 3;

    /**
     * Vevő azonosítója
     *
     * @var string
     */
    public string $id = '' {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }

    /**
     * Vevő neve
     *
     * @var string
     */
    public string $name {
        get {
            return $this->name;
        }
        set {
            $this->name = $value;
        }
    }

    /**
     * Vevő országa
     *
     * @var string
     */
    public string $country = 'Magyarország' {
        get {
            return $this->country;
        }
        set {
            $this->country = $value;
        }
    }

    /**
     * Vevő irányítószáma
     *
     * @var string
     */
    public string $zipCode {
        get {
            return $this->zipCode;
        }
        set {
            $this->zipCode = $value;
        }
    }

    /**
     * Vevő városa
     *
     * @var string
     */
    public string $city {
        get {
            return $this->city;
        }
        set {
            $this->city = $value;
        }
    }

    /**
     * Vevő címe
     *
     * @var string
     */
    public string $address {
        get {
            return $this->address;
        }
        set {
            $this->address = $value;
        }
    }

    /**
     * Vevő e-mail címe
     *
     * Ha meg van adva, akkor erre az email címre kiküldi a bizonylatot a Számlázz.hu.
     * Teszt fiók esetén biztonsági okokból nem küld a rendszer e-mailt!
     *
     * @var array
     */
    private array $email = [];

    /**
     * Küldjünk-e e-mailt az vevőnek
     *
     * @var bool
     */
    public bool $sendEmail = true {
        get {
            return $this->sendEmail;
        }
        set {
            $this->sendEmail = $value;
        }
    }

    /**
     * Vevő adóalany
     *
     * Ezt az információt a partner adatként tárolja a rendszerben, ott módosítható is.
     *
     * A következő értékeket veheti fel ez a mező:
     * 7: TaxPayer::TAXPAYER_NON_EU_ENTERPRISE - EU-n kívüli vállalkozás
     * 6: TaxPayer::TAXPAYER_EU_ENTERPRISE     - EU-s vállalkozás
     * 1: TaxPayer::TAXPAYER_HAS_TAXNUMBER     - van magyar adószáma
     * 0: TaxPayer::TAXPAYER_WE_DONT_KNOW      - nem tudjuk
     * -1: TaxPayer::TAXPAYER_NO_TAXNUMBER      - nincs adószáma
     *
     * @see https://tudastar.szamlazz.hu/gyik/vevo-adoszama-szamlan
     * @var TaxPayerType|null
     */
    public ?TaxPayerType $taxPayerType = null {
        get {
            return $this->taxPayerType;
        }
        set {
            $this->taxPayerType = $value;
        }
    }

    /**
     * Vevő adószáma
     *
     * @var string
     */
    public string $taxNumber = '' {
        get {
            return $this->taxNumber;
        }
        set {
            $this->taxNumber = $value;
        }
    }

    /**
     * Csoport azonosító
     *
     * @var string
     */
    public string $groupIdentifier = '' {
        get {
            return $this->groupIdentifier;
        }
        set {
            $this->groupIdentifier = $value;
        }
    }

    /**
     * Vevó EU-s adószáma
     *
     * @var string
     */
    public string $taxNumberEU = '' {
        get {
            return $this->taxNumberEU;
        }
        set {
            $this->taxNumberEU = $value;
        }
    }

    /**
     * Vevő postázási neve
     * (A postázási adatok nem kötelezők)
     *
     * @var string
     */
    public string $postalName = '' {
        get {
            return $this->postalName;
        }
        set {
            $this->postalName = $value;
        }
    }

    /**
     * Vevő postázási országa
     *
     * @var string
     */
    public string $postalCountry = '' {
        get {
            return $this->postalCountry;
        }
        set {
            $this->postalCountry = $value;
        }
    }

    /**
     * Vevő postázási irányítószáma
     *
     * @var string
     */
    public string $postalZip = '' {
        get {
            return $this->postalZip;
        }
        set {
            $this->postalZip = $value;
        }
    }

    /**
     * Vevő postázási települése
     *
     * @var string
     */
    public string $postalCity = '' {
        get {
            return $this->postalCity;
        }
        set {
            $this->postalCity = $value;
        }
    }

    /**
     * Vevő postázási címe
     *
     * @var string
     */
    public string $postalAddress = '' {
        get {
            return $this->postalAddress;
        }
        set {
            $this->postalAddress = $value;
        }
    }

    /**
     * Vevő főkönyvi adatai
     *
     * @var BuyerLedger | null
     */
    public ?BuyerLedger $ledgerData = null {
        get {
            return $this->ledgerData;
        }

        set {
            $this->ledgerData = $value;
        }

    }

    /**
     * Vevő aláíró neve
     *
     * Ha a beállítások oldalon (https://www.szamlazz.hu/szamla/beallitasok) be van kapcsolva,
     * akkor ez a név megjelenik az aláírásra szolgáló vonal alatt.
     *
     * @var string
     */
    public string $signatoryName = '' {
        get {
            return $this->signatoryName;
        }
        set {
            $this->signatoryName = $value;
        }
    }

    /**
     * Vevő telefonszáma
     *
     * @var string
     */
    public string $phone = '' {
        get {
            return $this->phone;
        }
        set {
            $this->phone = $value;
        }
    }

    /**
     * Vevőhöz tartozó megjegyzés
     *
     * @var string
     */
    public string $comment = '' {
        get {
            return $this->comment;
        }
        set {
            $this->comment = $value;
        }
    }

    /**
     * Vevő példányosítása
     *
     * @param string $name    vevő név
     * @param string $zipCode vevő irányítószám
     * @param string $city    vevő település
     * @param string $address vevő cím
     */
    public function __construct(string $name = '', string $zipCode = '', string $city = '', string $address = '')
    {
        $this->name = $name;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->address = $address;
    }

    /**
     * Létrehozza a vevő XML adatait a kérésben meghatározott XML séma alapján
     *
     * @param RequestType $requestType
     *
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(RequestType $requestType): array
    {
        $data = [];
        switch ($requestType) {
            case RequestType::GENERATE_PROFORMA:
            case RequestType::GENERATE_INVOICE:
            case RequestType::GENERATE_PREPAYMENT_INVOICE:
            case RequestType::GENERATE_FINAL_INVOICE:
            case RequestType::GENERATE_CORRECTIVE_INVOICE:
            case RequestType::GENERATE_DELIVERY_NOTE:

                $data = [
                    "nev"       => $this->name,
                    "orszag"    => $this->country,
                    "irsz"      => $this->zipCode,
                    "telepules" => $this->city,
                    "cim"       => $this->address
                ];

                if (!empty($this->email))                                $data["email"] = implode(',', $this->getEmail());

                $data["sendEmail"] = $this->sendEmail;

                if (SzamlaAgentUtil::isNotNull($this->taxPayerType))         $data["adoalany"] = $this->taxPayerType;
                if (SzamlaAgentUtil::isNotBlank($this->taxNumber))       $data["adoszam"] = $this->taxNumber;
                if (SzamlaAgentUtil::isNotBlank($this->groupIdentifier)) $data["csoportazonosito"] = $this->groupIdentifier;
                if (SzamlaAgentUtil::isNotBlank($this->taxNumberEU))     $data["adoszamEU"] = $this->taxNumberEU;
                if (SzamlaAgentUtil::isNotBlank($this->postalName))      $data["postazasiNev"] = $this->postalName;
                if (SzamlaAgentUtil::isNotBlank($this->postalCountry))   $data["postazasiOrszag"] = $this->postalCountry;
                if (SzamlaAgentUtil::isNotBlank($this->postalZip))       $data["postazasiIrsz"] = $this->postalZip;
                if (SzamlaAgentUtil::isNotBlank($this->postalCity))      $data["postazasiTelepules"] = $this->postalCity;
                if (SzamlaAgentUtil::isNotBlank($this->postalAddress))   $data["postazasiCim"] = $this->postalAddress;
                if (SzamlaAgentUtil::isNotNull($this->ledgerData))       $data["vevoFokonyv"] = $this->ledgerData->buildXmlData();
                if (SzamlaAgentUtil::isNotBlank($this->id))              $data["azonosito"] = $this->id;
                if (SzamlaAgentUtil::isNotBlank($this->signatoryName))   $data["alairoNeve"] = $this->signatoryName;
                if (SzamlaAgentUtil::isNotBlank($this->phone))           $data["telefonszam"] = $this->phone;
                if (SzamlaAgentUtil::isNotBlank($this->comment))         $data["megjegyzes"] = $this->comment;
                break;
            case RequestType::GENERATE_REVERSE_INVOICE:
                if (!empty($this->email))                                $data["email"] = implode(',', $this->getEmail());
                if (SzamlaAgentUtil::isNotBlank($this->taxNumber))       $data["adoszam"] = $this->taxNumber;
                if (SzamlaAgentUtil::isNotBlank($this->taxNumberEU))     $data["adoszamEU"] = $this->taxNumberEU;
                break;
            default:
                throw new SzamlaAgentException("Nincs ilyen XML séma definiálva: " . $requestType->name);
        }

        return $data;
    }

    /**
     * A vevőhöz hozzáad egy email címet, többet is hozzá lehet adni
     * Üres string esetén nem csinál semmit, msá esetben validáció után hozzáadja
     *
     * @throws SzamlaAgentException
     */
    public function addEmail(string $email): void
    {
        if (SzamlaAgentUtil::isNotBlank($email) && count($this->email) < self::BUYER_EMAIL_LIMIT) {
            SzamlaAgentUtil::validateEmailAddress($email);
            if (!in_array($email, $this->email)) {
                $this->email[] = $email;
            }
        }
    }

    public function getEmail(): array
    {
        return $this->email;
    }

    public function clearEmail(): void
    {
        $this->email = [];
    }
}