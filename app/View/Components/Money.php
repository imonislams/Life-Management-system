<?php

namespace App\View\Components;

use App\Support\MoneyFormatter;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders a monetary amount using the user's currency configuration.
 *
 * Usage: <x-money :amount="$total" />
 *         <x-money :amount="$total" :currency="$record->currency" />
 *
 * Every module uses this so amounts always format identically.
 */
class Money extends Component
{
    public function __construct(
        public $amount,
        public $currency = null,
        public ?int $userId = null,
        public ?bool $withCode = null,
    ) {
        $this->userId = $this->userId ?? auth()->id();
    }

    public function render(): View
    {
        return view('components.money');
    }

    public function formatted(): string
    {
        if ($this->withCode) {
            return MoneyFormatter::formatWithCode($this->amount, $this->currency, $this->userId);
        }

        return MoneyFormatter::format($this->amount, $this->currency, $this->userId);
    }
}
