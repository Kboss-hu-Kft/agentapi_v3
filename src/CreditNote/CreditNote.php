<?php

namespace Kboss\SzamlaAgent\CreditNote;

use Kboss\SzamlaAgent\Enums\PaymentMethod;
use Kboss\SzamlaAgent\SzamlaAgentException;
use Kboss\SzamlaAgent\SzamlaAgentUtil;

/**
 * Jóváírás
 */
abstract class CreditNote {

    abstract function buildXmlData(): array;

    /**
     * Jóváírás jogcíme
     * (fizetőeszköz megnevezése)
     *
     * @var PaymentMethod|string
     */
    protected PaymentMethod|string $paymentMode {
        get {
            return $this->paymentMode;
        }
        set {
            $this->paymentMode = $value;
        }
    }

    /**
     * Jóváírás összege
     * (fizetőeszközzel kiegyenlített összeg)
     *
     * @var float
     */
    protected float $amount {
        get {
            return $this->amount;
        }
        set {
            $this->amount = $value;
        }
    }

    /**
     * Jóváírás egyedi leírása
     *
     * @var string
     */
    protected string $description {
        get {
            return $this->description;
        }
        set {
            $this->description = $value;
        }
    }

    /**
     * Jóváírás létrehozása
     *
     * @param PaymentMethod|string $paymentMode jóváírás jogcíme (fizetési módja)
     * @param float $amount      jóváírás összege
     * @param string $description jóváírás leírása
     */
    protected function __construct(PaymentMethod|string $paymentMode, float $amount, string $description) {
        $this->paymentMode = $paymentMode;
        $this->amount = $amount;
        $this->description = $description;
    }

    /**
     * @throws SzamlaAgentException
     */
    protected function validate(): void
    {
        if (SzamlaAgentUtil::isBlank($this->paymentMode)) {
            throw new SzamlaAgentException(SzamlaAgentException::MISSING_VALUE .  ': paymentMode');
        }

        if (!isset($this->amount) || $this->amount ===  0.0) {
            throw new SzamlaAgentException(SzamlaAgentException::MISSING_VALUE . ': amount');
        }
    }

}