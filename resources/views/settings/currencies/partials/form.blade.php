@php
$currency = $currency ?? null;
    $isEdit = $currency !== null;
@endphp

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
    <div class="form-group">
        <label for="name" class="form-label">Currency Name <span style="color: var(--danger-color);">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $currency->name ?? '') }}" class="form-control" maxlength="64" required placeholder="e.g. United States Dollar">
        @error('name')<div class="error-msg">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="code" class="form-label">Currency Code <span style="color: var(--danger-color);">*</span></label>
        <input id="code" type="text" name="code" value="{{ old('code', $currency->code ?? '') }}" class="form-control" maxlength="8" required placeholder="e.g. USD" style="text-transform: uppercase;">
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">2–8 uppercase letters. Must be unique to your account.</div>
        @error('code')<div class="error-msg">{{ $message }}</div>@enderror
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
    <div class="form-group">
        <label for="symbol" class="form-label">Currency Symbol <span style="color: var(--danger-color);">*</span></label>
        <input id="symbol" type="text" name="symbol" value="{{ old('symbol', $currency->symbol ?? '') }}" class="form-control" maxlength="16" required placeholder="e.g. $">
        @error('symbol')<div class="error-msg">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="country" class="form-label">Country / Region (Optional)</label>
        <input id="country" type="text" name="country" value="{{ old('country', $currency->country ?? '') }}" class="form-control" maxlength="64" placeholder="e.g. United States">
        @error('country')<div class="error-msg">{{ $message }}</div>@enderror
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
    <div class="form-group">
        <label for="decimal_precision" class="form-label">Decimal Precision <span style="color: var(--danger-color);">*</span></label>
        <select id="decimal_precision" name="decimal_precision" class="form-control" required>
            @foreach([0, 1, 2, 3, 4] as $precision)
            <option value="{{ $precision }}" {{ (int) old('decimal_precision', $currency->decimal_precision ?? 2) === $precision ? 'selected' : '' }}>{{ $precision }}</option>
            @endforeach
        </select>
        @error('decimal_precision')<div class="error-msg">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="thousands_separator" class="form-label">Thousands Separator <span style="color: var(--danger-color);">*</span></label>
        <select id="thousands_separator" name="thousands_separator" class="form-control" required>
            @php
            $thousandOptions = [',' => 'Comma (,)', '.' => 'Dot (.)', chr(32) => 'Space'];
            $currentThousand = (string) old('thousands_separator', $currency->thousands_separator ?? ',');
            @endphp
            @foreach($thousandOptions as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ $currentThousand === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
            @endforeach
        </select>
        @error('thousands_separator')<div class="error-msg">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="decimal_separator" class="form-label">Decimal Separator <span style="color: var(--danger-color);">*</span></label>
        <select id="decimal_separator" name="decimal_separator" class="form-control" required>
            <option value="." {{ (string) old('decimal_separator', $currency->decimal_separator ?? '.') === '.' ? 'selected' : '' }}>Dot (.)</option>
            <option value="," {{ (string) old('decimal_separator', $currency->decimal_separator ?? '.') === ',' ? 'selected' : '' }}>Comma (,)</option>
        </select>
        @error('decimal_separator')<div class="error-msg">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-group">
    <label for="symbol_position" class="form-label">Symbol Position <span style="color: var(--danger-color);">*</span></label>
    <select id="symbol_position" name="symbol_position" class="form-control" required>
        <option value="before" {{ old('symbol_position', $currency->symbol_position ?? 'before') === 'before' ? 'selected' : '' }}>Before amount</option>
        <option value="after" {{ old('symbol_position', $currency->symbol_position ?? 'before') === 'after' ? 'selected' : '' }}>After amount</option>
    </select>
    @error('symbol_position')<div class="error-msg">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $currency->is_active ?? true) ? 'checked' : '' }} style="width: 16px; height: 16px;">
        <span class="form-label" style="margin: 0;">Active (available for new financial records)</span>
    </label>
</div>

@if($isEdit)
@unless($currency->is_default)
<div class="form-group">
    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
        <input type="checkbox" name="is_default" value="1" style="width: 16px; height: 16px;">
        <span class="form-label" style="margin: 0;">Set as my default currency</span>
    </label>
</div>
@else
<div class="alert-success" style="margin-bottom: 1rem;">This is your current default currency.</div>
@endif
@endif

<!-- Live formatting preview -->
<div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 0.375rem; padding: 1rem; margin-bottom: 1.25rem;">
    <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.35rem;">Formatting Preview</div>
    <div id="currencyPreview" style="font-weight: 700; font-size: 1.25rem;">—</div>
    <div id="currencyPreviewAlt" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">—</div>
</div>

<script>
    (function() {
        const symbolInput = document.getElementById('symbol');
        const precisionSelect = document.getElementById('decimal_precision');
        const thousandsSelect = document.getElementById('thousands_separator');
        const decimalSelect = document.getElementById('decimal_separator');
        const positionSelect = document.getElementById('symbol_position');

        const out = document.getElementById('currencyPreview');
        const outAlt = document.getElementById('currencyPreviewAlt');

        function render() {
            const symbol = symbolInput.value || '';
            const precision = parseInt(precisionSelect.value, 10) || 0;
            const thousands = thousandsSelect.value;
            const decimal = decimalSelect.value;
            const position = positionSelect.value;

            const format = function(value) {
                const parts = Number(value).toFixed(precision).split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
                const number = parts.length > 1 ? parts[0] + decimal + parts[1] : parts[0];
                const sep = String.fromCharCode(32);

                return position === 'after' ? number + sep + symbol : symbol + sep + number;
            };

            out.textContent = format(1234.5);
            outAlt.textContent = format(-9876.54) + '   |   ' + format(123456789.01);
        }

        [symbolInput, precisionSelect, thousandsSelect, decimalSelect, positionSelect].forEach(function(el) {
            if (el) {
                el.addEventListener('input', render);
                el.addEventListener('change', render);
            }
        });

        render();
    })();
</script>