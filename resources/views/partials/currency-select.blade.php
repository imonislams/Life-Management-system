@php
/**
* Currency selector for financial record forms.
*
* Expects: $currencies (collection of the user's active currencies),
* $defaultCurrency (Currency|null) and optionally $selectedId.
*/
$selectedId = old('currency_id', $selectedId ?? optional($defaultCurrency)->id);
@endphp

@if($currencies->count() > 0)
<div class="form-group">
    <label for="currency_id" class="form-label">Currency</label>
    <select id="currency_id" name="currency_id" class="form-control">
        @foreach($currencies as $currency)
        <option value="{{ $currency->id }}" {{ (string) $selectedId === (string) $currency->id ? 'selected' : '' }}>
            {{ $currency->code }} — {{ $currency->name }} ({{ $currency->symbol }})
            @if($currency->is_default)
            · default
            @endif
        </option>
        @endforeach
    </select>
    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
        Only your active currencies are listed. Changing your default currency later will not alter this record's currency.
    </div>
    @error('currency_id')<div class="error-msg">{{ $message }}</div>@enderror
</div>
@else
<div class="alert-success" style="background-color: #fff7ed; border-color: #ffedd5; color: #c2410c;">
    You have no active currencies yet.
    <a href="{{ route('settings.currencies.index') }}" style="font-weight: 600;">Add a currency</a>
    to control how amounts are formatted.
</div>
@endif