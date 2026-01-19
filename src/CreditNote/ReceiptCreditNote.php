<?php

namespace Kboss\SzamlaAgent\CreditNote;

use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\Enums\PaymentMethod;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Nyugta jóváírás
 */
class ReceiptCreditNote extends CreditNote
{


    /**
     * Nyugta kifizetés létrehozása
     *
     * @param PaymentMethod|string $paymentMode fizetőeszköz megnevezése
     * @param float $amount                     fizetőeszköz összege
     * @param string $description               fizetőeszköz egyedi leírása
     */
    function __construct(float $amount = 0.0, PaymentMethod|string $paymentMode = PaymentMethod::CASH, string $description = '')
    {
        parent::__construct($paymentMode, $amount, $description);
    }

    /**
     * @return array
     * @throws SzamlaAgentException
     */
    public function buildXmlData(): array
    {

        $this->validate();

        $data = [];
        $data['fizetoeszkoz'] = $this->paymentMode;
        $data['osszeg'] = SzamlaAgentUtil::doubleFormat($this->amount);
        if (SzamlaAgentUtil::isNotBlank($this->description)) $data['leiras'] = $this->description;

        return $data;
    }
 }