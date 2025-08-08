<?php

namespace Kboss\SzamlaAgent\CreditNote;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\Enums\PaymentMethod;
use Kboss\SzamlaAgent\SzamlaAgentUtil;
use Override;

/**
 * Számla jóváírás
 */
class InvoiceCreditNote extends CreditNote
{

    /**
     * Jóváírás dátuma
     *
     * @var string
     */
    protected string $date {
        get {
            return $this->date;
        }
        set {
            $this->date = $value;
        }
    }

    /**
     * Jóváírás létrehozása
     *
     * @param string $date                      jóváírás dátuma
     * @param PaymentMethod|string $paymentMode jóváírás jogcíme (fizetési módja)
     * @param float $amount                     jóváírás összege
     * @param string $description               jóváírás leírása
     */
    function __construct(string $date, float $amount, PaymentMethod|string $paymentMode = PaymentMethod::TRANSFER, string $description = '')
    {
        parent::__construct($paymentMode, $amount, $description);
        $this->date = $date;
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array {

        $this->validate();

        $data = [];
        $data['datum']  = $this->date;
        $data['jogcim'] = $this->paymentMode;
        $data['osszeg'] = SzamlaAgentUtil::doubleFormat($this->amount);
        if (SzamlaAgentUtil::isNotBlank($this->description))        $data['leiras'] = $this->description;

        return $data;
    }

    #[Override] function validate(): void
    {
        parent::validate();

        if (!isset($this->date) || SzamlaAgentUtil::isBlank($this->date)) {
            throw new SzamlaAgentException(SzamlaAgentException::MISSING_VALUE . ': date');
        }

        if (!SzamlaAgentUtil::isValidDate($this->date)) {
            throw new SzamlaAgentException(SzamlaAgentException::INVALID_VALUE . ' date => ' . $this->date);
        }
    }
}