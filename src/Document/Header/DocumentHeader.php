<?php

namespace Kboss\SzamlaAgent\Document\Header;

use Kboss\SzamlaAgent\Document\Document;
use Kboss\SzamlaAgent\Enums\Currency;
use Kboss\SzamlaAgent\Enums\InvoiceType;
use Kboss\SzamlaAgent\Enums\Language;
use Kboss\SzamlaAgent\Enums\PaymentMethod;
use Kboss\SzamlaAgent\Enums\RequestType;

/**
 * Bizonylat fejléc
 */
abstract class DocumentHeader
{

    /**
     * Bizonylat sorszáma
     *
     * @var string
     */
    public string $documentNumber = '' {
        get {
            return $this->documentNumber;
        }
        set {
            $this->documentNumber = $value;
        }
    }

    /**
     * Bizonylat fizetési módja
     *
     * A fizetési mód bármilyen szöveg lehet vagy a felületen használt értékek egyike.
     * (lásd. a bizonylat fizetési módjainál)
     *
     * @see Document
     *
     * @var string
     */
    public string $paymentMethod {
        get {
            return $this->paymentMethod;
        }
        set {
            $this->paymentMethod = $value;
        }
    }

    /**
     * Bizonylat pénzneme
     *
     * @var Currency
     */
    public Currency $currency = Currency::HUF {
        get {
            return $this->currency;
        }
        set {
            $this->currency = $value;
        }
    }

    /**
     * Bizonylat nyelve
     *
     * @var Language
     */
    public Language $language = Language::LANGUAGE_HU {
        get {
            return $this->language;
        }
        set {
            $this->language = $value;
        }
    }

    /**
     * Rendelésszám
     *
     * @var string
     */
    public string $orderNumber = '' {
        get {
            return $this->orderNumber;
        }
        set {
            $this->orderNumber = $value;
        }
    }

    /**
     * A bizonylat előtagja
     * Üres előtag esetén az alapértelmezett előtagot fogja használni a rendszer.
     *
     * @var string
     */
    public string $prefix = '' {
        get {
            return $this->prefix;
        }
        set {
            $this->prefix = $value;
        }
    }

    /**
     * Bizonylat megjegyzés
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
     * Deviza árfolyamot jegyző bank neve
     *
     * Devizás bizonylat esetén meg kell adni, hogy melyik bank árfolyamával számoltuk a bizonylaton a forintos ÁFA értéket.
     * Ha 'MNB' és nincs megadva az árfolyam ($exchangeRate), akkor az 'MNB' aktuális árfolyamát használjuk a bizonylat elkészítésekor.
     *
     * @var string|null
     */
    public ?string $exchangeBank = null {
        get {
            return $this->exchangeBank;
        }
        set {
            $this->exchangeBank = $value;
        }
    }

    /**
     * Deviza árfolyama
     *
     * Ha 0-t adunk meg az árfolyam ($exchangeRate) értékének és a megadott pénznem ($currency) létezik az MNB adatbázisában,
     * akkor az MNB aktuális árfolyamát használjuk a számlakészítéskor.
     *
     * @var float
     */
    public float $exchangeRate = 0.0 {
        get {
            return $this->exchangeRate;
        }
        set {
            $this->exchangeRate = $value;
        }
    }

    /**
     * A bizonylat számla-e
     *
     * @var bool
     */
    protected bool $invoice = false {
        get {
            return $this->invoice;
        }
        set {
            $this->invoice = $value;
        }
    }

    /**
     * A bizonylat sztornó számla-e
     *
     * @var bool
     */
    protected bool $reserveInvoice = false {
        get {
            return $this->reserveInvoice;
        }
        set {
            $this->reserveInvoice = $value;
        }
    }

    /**
     * A bizonylat előlegszámla-e
     *
     * @var bool
     */
    protected bool $prePayment = false {
        get {
            return $this->prePayment;
        }
        set {
            $this->prePayment = $value;
        }
    }

    /**
     * A bizonylat végszámla-e
     *
     * @var bool
     */
    protected bool $final = false {
        get {
            return $this->final;
        }
        set {
            $this->final = $value;
        }
    }

    /**
     * A bizonylat helyesbítő számla-e
     *
     * @var bool
     */
    protected bool $corrective = false {
        get {
            return $this->corrective;
        }
        set {
            $this->corrective = $value;
        }
    }

    /**
     * A bizonylat díjbekérő-e
     *
     * @var bool
     */
    protected bool $proforma = false {
        get {
            return $this->proforma;
        }
        set {
            $this->proforma = $value;
        }
    }

    /**
     * A bizonylat szállítólevél-e
     *
     * @var bool
     */
    protected bool $deliveryNote = false {
        get {
            return $this->deliveryNote;
        }
        set {
            $this->deliveryNote = $value;
        }
    }

    /**
     * A bizonylat nyugta-e
     *
     * @var bool
     */
    protected bool $receipt = false {
        get {
            return $this->receipt;
        }
        set {
            $this->receipt = $value;
        }
    }

    /**
     * A bizonylat sztornó nyugta-e
     *
     * @var bool
     */
    public bool $reverseReceipt = false {
        get {
            return $this->reverseReceipt;
        }
        set {
            $this->reverseReceipt = $value;
        }
    }

    public abstract function buildXmlData(RequestType $requestType): array;
    public abstract function validate(RequestType $requestType): void;

    /**
     * @return bool
     */
    public function isEInvoice(): bool {
        return ($this instanceof InvoiceHeader && $this->invoiceType === InvoiceType::E_INVOICE);
    }
}