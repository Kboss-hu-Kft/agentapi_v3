<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Exception;
use Kboss\SzamlaAgent\Enums\RequestType;
use Override;
use Kboss\SzamlaAgent\Enums\InvoiceTemplate;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\Enums\PaymentMethod;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Számla fejléc
 */
class InvoiceHeader extends DocumentHeader
{

    /**
     * Számla típusa
     *
     * InvoiceType::P_INVOICE : papírszámla
     * InvoiceType::E_INVOICE : e-számla
     *
     * @var InvoiceType
     */
    public InvoiceType $invoiceType = InvoiceType::P_INVOICE {
        get {
            return $this->invoiceType;
        }
        set {
            $this->invoiceType = $value;
        }
    }


    /**
     * Bizonylat kelte
     * (a bizonylat kiadásának dátuma)
     *
     * @var string
     */
    public string $issueDate {
        get {
            return $this->issueDate;
        }
        set {
            $this->issueDate = $value;
        }
    }

    /**
     * Bizonylat teljesítési dátuma
     *
     * @var string|null
     */
    public ?string $fulfillment {
        get {
            return $this->fulfillment;
        }
        set {
            $this->fulfillment = $value;
        }
    }

    /**
     * Bizonylat fizetési határideje
     *
     * @var string
     */
    public string $paymentDue {
        get {
            return $this->paymentDue;
        }
        set {
            $this->paymentDue = $value;
        }
    }

    /**
     * A bizonylaton másodikként megjelenő logó (fájl) neve.
     *
     * @var string
     */
    public string $extraLogo = '' {
        get {
            return $this->extraLogo;
        }
        set {
            $this->extraLogo = $value;
        }
    }

    /**
     * Bizonylat végösszegét korrigáló tétel.
     * Nem befolyásolja a bruttó értéket, csak mint fizetendőt kell feltüntetni.
     *
     * @var float
     */
    public float $correctionToPay = 0 {
        get {
            return $this->correctionToPay;
        }
        set {
            $this->correctionToPay = $value;
        }
    }

    /**
     * Helyesbített számlaszám
     *
     * @var string
     */
    public string $correctivedNumber = '' {
        get {
            return $this->correctivedNumber;
        }
        set {
            $this->correctivedNumber = $value;
        }
    }

    /**
     * Hivatkozás a díjbekérőre
     * A számla kibocsátásakor explicit megadhatjuk annak a díjbekérőnek a számát, amire hivatkozva történik a számlakibocsátás.
     *
     * @var string
     */
    public string $proformaNumber = '' {
        get {
            return $this->proformaNumber;
        }
        set {
            $this->proformaNumber = $value;
        }
    }

    /**
     * A bizonylat kifizetettsége
     *
     * @var bool
     */
    public bool $paid = false {
        get {
            return $this->paid;
        }
        set {
            $this->paid = $value;
        }
    }

    /**
     * Ez a bizonylat árrés alapján áfázik-e?
     *
     * @var bool
     */
    public bool $profitVat = false {
        get {
            return $this->profitVat;
        }
        set {
            $this->profitVat = $value;
        }
    }

    /**
     * Számlasablon
     * Ez a számlakép sablon lesz használva a számla kibocsátásánál.
     *
     * InvoiceTemplate::DEFAULT      : 'SzlaMost';
     * InvoiceTemplate::TRADITIONAL  : 'SzlaAlap';
     * InvoiceTemplate::ENV_FRIENDLY : 'SzlaNoEnv';
     * InvoiceTemplate::EIGHTCM      : 'Szla8cm';
     * InvoiceTemplate::RETRO        : 'SzlaTomb';
     *
     * @var InvoiceTemplate | null
     */
    public ?InvoiceTemplate $invoiceTemplate = null {
        get {
            return $this->invoiceTemplate;
        }
        set {
            $this->invoiceTemplate = $value;
        }
    }

    /**
     * Előlegszámla számlaszám
     * (ha a végszámlázandó előlegszámla nem azonosítható a rendelésszámmal, akkor itt megadhatod az előlegszámla számlaszámát)
     *
     * @var string
     */
    public string $prePaymentInvoiceNumber = '' {
        get {
            return $this->prePaymentInvoiceNumber;
        }
        set {
            $this->prePaymentInvoiceNumber = $value;
        }
    }

    /**
     * Ez a bizonylat előnézeti PDF-e?
     * Ebben az esetben bizonylat nem készül!
     *
     * @var bool
     */
    public bool $previewPdf = false {
        get {
            return $this->previewPdf;
        }
        set {
            $this->previewPdf = $value;
        }
    }

    /**
     * A bizonylat nem magyar áfát tartalmaz-e.
     * Ha tartalmaz, akkor a bizonylat adatai nem lesznek továbbítva a NAV Online Számla rendszere felé.
     *
     * @var bool
     */
    public bool $euVat = false {
        get {
            return $this->euVat;
        }
        set {
            $this->euVat = $value;
        }
    }

    /**
     * InvoiceHeader constructor.
     *
     * @param InvoiceType $type
     * @param bool $isReverseInvoice
     * @throws SzamlaAgentException
     */
    function __construct(InvoiceType $type, bool $isReverseInvoice = false)
    {
        $this->setDefaultData($type, $isReverseInvoice);
    }

    /**
     * Beállítja a bizonylat alapértelmezett adatait
     *
     * @param $type
     * @param $isReverseInvoice
     * @throws SzamlaAgentException
     * @throws Exception
     */
    function setDefaultData($type, $isReverseInvoice): void
    {
        // A bizonylat számla típusú
        $this->invoice = true;
        // Számla típusa (papír vagy e-számla)
        $this->invoiceType = $type;
        // Számla kiállítás dátuma
        $this->issueDate = SzamlaAgentUtil::getTodayStr();
        // Számla fizetési módja (átutalás)
        $this->paymentMethod = PaymentMethod::TRANSFER;
        // Sztornó számla esetén alapértelmezetten nincs beállítva, így a rendszer a sztornózott számla teljesítési dátumát fogja beállítani a számla kiállításánál
        $this->fulfillment = !$isReverseInvoice ? SzamlaAgentUtil::getTodayStr() : null;
        // Számla fizetési határideje
        $this->paymentDue = SzamlaAgentUtil::addDaysToDate(SzamlaAgentUtil::DEFAULT_ADDED_DAYS);
        // Sztornó számla
        $this->reserveInvoice = $isReverseInvoice;
    }

    /**
     * Összeállítja a bizonylat elkészítéséhez szükséges XML fejléc adatokat
     *
     * Csak azokat az XML mezőket adjuk hozzá, amelyek kötelezőek,
     * illetve amelyek opcionálisak, de ki vannak töltve.
     *
     * @param RequestType $requestType
     *
     * @return array
     * @throws SzamlaAgentException
     */
    #[Override] public function buildXmlData(RequestType $requestType): array
    {
        $data = [];

        $this->validate($requestType);

        if ($requestType !== RequestType::DELETE_PROFORMA) {
            $data['keltDatum'] = $this->issueDate;
            $data['teljesitesDatum'] = $this->fulfillment;
            $data['fizetesiHataridoDatum'] = $this->paymentDue;
            $data['fizmod'] = $this->paymentMethod;
            $data['penznem'] = $this->currency->name;
            $data['szamlaNyelve'] = $this->language;
            if (SzamlaAgentUtil::isNotBlank($this->comment))                        $data['megjegyzes'] = $this->comment;
            if (SzamlaAgentUtil::isNotBlank($this->exchangeBank))                   $data['arfolyamBank'] = $this->exchangeBank;
            if ($this->exchangeRate > 0)                                            $data['arfolyam'] = SzamlaAgentUtil::doubleFormat($this->exchangeRate);
            if (SzamlaAgentUtil::isNotBlank($this->orderNumber))                    $data['rendelesSzam'] = $this->orderNumber;
            if (SzamlaAgentUtil::isNotBlank($this->proformaNumber))                 $data['dijbekeroSzamlaszam'] = $this->proformaNumber;
            if ($this->prePayment)                                                  $data['elolegszamla']  = $this->prePayment;
            if ($this->final)                                                       $data['vegszamla']  = $this->final;
            if (SzamlaAgentUtil::isNotBlank($this->prePaymentInvoiceNumber))        $data['elolegSzamlaszam'] = $this->prePaymentInvoiceNumber;
            if ($this->corrective)                                                  $data['helyesbitoszamla']  = $this->corrective;
            if (SzamlaAgentUtil::isNotBlank($this->correctivedNumber))              $data['helyesbitettSzamlaszam']  = $this->correctivedNumber;
            if ($this->proforma)                                                    $data['dijbekero']  = $this->proforma;
            if ($this->deliveryNote)                                                $data['szallitolevel']  = $this->deliveryNote;
            if (SzamlaAgentUtil::isNotBlank($this->extraLogo))                      $data['logoExtra']  = $this->extraLogo;
            if (SzamlaAgentUtil::isNotBlank($this->prefix))                         $data['szamlaszamElotag']  = $this->prefix;
            if ($this->correctionToPay !== 0.0)                                     $data['fizetendoKorrekcio'] = SzamlaAgentUtil::doubleFormat($this->correctionToPay);
            if ($this->paid)                                                        $data['fizetve']  = $this->paid;
            if ($this->profitVat)                                                   $data['arresAfa'] = $this->profitVat;
            if ($this->euVat)                                                       $data['eusAfa'] = $this->euVat;
            if (SzamlaAgentUtil::isNotBlank($this->invoiceTemplate))                $data['szamlaSablon'] = $this->invoiceTemplate;
            if ($this->previewPdf)                                                  $data['elonezetpdf'] = $this->previewPdf;
        } else {
            if (SzamlaAgentUtil::isNotBlank($this->documentNumber))                 $data["szamlaszam"] = $this->documentNumber;
            if (SzamlaAgentUtil::isNotBlank($this->orderNumber))                    $data["rendelesszam"] = $this->orderNumber;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->documentNumber;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber(string $invoiceNumber): void
    {
        $this->documentNumber = $invoiceNumber;
    }

    /**
     * @throws SzamlaAgentException
     */
    public function validate(RequestType $requestType): void
    {
        if ($requestType === RequestType::REQUEST_INVOICE_DATA || $requestType === RequestType::REQUEST_INVOICE_PDF) {
            if (SzamlaAgentUtil::isBlank($this->documentNumber) && SzamlaAgentUtil::isBlank($this->orderNumber)) {
                throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
            }
        }

        if ($requestType === RequestType::PAY_INVOICE && SzamlaAgentUtil::isBlank($this->documentNumber)) {
            throw new SzamlaAgentException(SzamlaAgentException::MISSING_DOCUMENT_ID);
        }

        if ($requestType === RequestType::GENERATE_CORRECTIVE_INVOICE && SzamlaAgentUtil::isBlank($this->correctivedNumber)) {
            throw new SzamlaAgentException(SzamlaAgentException::MISSING_CORRECTIVE_DOCUMENT_ID);
        }
    }
}