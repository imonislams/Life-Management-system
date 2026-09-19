@php
use App\Models\Setting;
use App\Support\CurrencyConfig;
@endphp
<x-app-layout>
    <x-slot name="title">Currency Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">System Currency</h2>
        <p class="card-subtitle">
            The currency selected here becomes the <strong>system-wide default</strong> used across your entire Personal Workspace —
            Dashboard, Money Analytics, Income, Salary, Expenses, Savings, Savings Transactions, Recurring Finance, forms, tables and summary cards.
        </p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.currency.update') }}">
            @csrf
            @method('PUT')

            <!-- Quick pick from common world currencies -->
            <div class="form-group">
                <label for="currency_preset" class="form-label">Quick Select Currency</label>
                <select id="currency_preset" class="form-control" onchange="applyCurrencyPreset(this)">
                    <option value="">— Choose a common currency —</option>
                    @foreach(Setting::COMMON_CURRENCIES as $currency)
                    <option
                        value="{{ $currency['code'] }}"
                        data-name="{{ $currency['name'] }}"
                        data-symbol="{{ $currency['symbol'] }}"
                        {{ $settings->currency_code === $currency['code'] ? 'selected' : '' }}>{{ $currency['code'] }} — {{ $currency['name'] }}</option>
                    @endforeach
                </select>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                    Any ISO currency code can also be entered manually below.
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="currency_name" class="form-label">Currency Name <span style="color: var(--danger-color);">*</span></label>
                    <input id="currency_name" type="text" name="currency_name" value="{{ old('currency_name', $settings->currency_name) }}" class="form-control" required>
                    @error('currency_name')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="currency_code" class="form-label">Currency Code <span style="color: var(--danger-color);">*</span></label>
                    <input id="currency_code" type="text" name="currency_code" value="{{ old('currency_code', $settings->currency_code) }}" class="form-control" maxlength="8" required>
                    @error('currency_code')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="currency_symbol" class="form-label">Currency Symbol <span style="color: var(--danger-color);">*</span></label>
                    <input id="currency_symbol" type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" class="form-control" maxlength="16" required>
                    @error('currency_symbol')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="currency_suffix" class="form-label">Amount Suffix (Optional)</label>
                    <input id="currency_suffix" type="text" name="currency_suffix" value="{{ old('currency_suffix', $settings->currency_suffix) }}" class="form-control" maxlength="8" placeholder="e.g. TK">
                    @error('currency_suffix')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="currency_decimals" class="form-label">Decimal Precision <span style="color: var(--danger-color);">*</span></label>
                    <select id="currency_decimals" name="currency_decimals" class="form-control" required>
                        @foreach([0, 1, 2, 3, 4] as $precision)
                        <option value="{{ $precision }}" {{ (int) old('currency_decimals', $settings->currency_decimals) === $precision ? 'selected' : '' }}>{{ $precision }}</option>
                        @endforeach
                    </select>
                    @error('currency_decimals')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="currency_thousands_separator" class="form-label">Thousands Separator <span style="color: var(--danger-color);">*</span></label>
                    <select id="currency_thousands_separator" name="currency_thousands_separator" class="form-control" required>
                        @php
                            $thousandOptions = [
                                ',' => 'Comma (,)',
                                '.' => 'Dot (.)',
                                chr(32) => 'Space',
                            ];
                            $currentThousand = (string) old('currency_thousands_separator', $settings->currency_thousands_separator);
                        @endphp
                        @foreach($thousandOptions as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" {{ $currentThousand === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    @error('currency_thousands_separator')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="currency_decimal_separator" class="form-label">Decimal Separator <span style="color: var(--danger-color);">*</span></label>
                    <select id="currency_decimal_separator" name="currency_decimal_separator" class="form-control" required>
                        <option value="." {{ (string) old('currency_decimal_separator', $settings->currency_decimal_separator) === '.' ? 'selected' : '' }}>Dot (.)</option>
                        <option value="," {{ (string) old('currency_decimal_separator', $settings->currency_decimal_separator) === ',' ? 'selected' : '' }}>Comma (,)</option>
                    </select>
                    @error('currency_decimal_separator')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="currency_position" class="form-label">Currency Position <span style="color: var(--danger-color);">*</span></label>
                <select id="currency_position" name="currency_position" class="form-control" required>
                    @foreach(Setting::CURRENCY_POSITIONS as $value => $label)
                    <option value="{{ $value }}" {{ old('currency_position', $settings->currency_position) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('currency_position')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="currency_active" value="1" {{ old('currency_active', $settings->currency_active) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Currency active (show suffix/symbol in formatted amounts)</span>
                </label>
            </div>

            <!-- Live preview of the current saved formatting -->
            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 0.375rem; padding: 0.875rem; margin-bottom: 1.25rem;">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.25rem;">Current system format preview</div>
                <div id="currencyPreview" style="font-weight: 700; font-size: 1.125rem;">{{ CurrencyConfig::format(auth()->id(), 1500) }}</div>
                <div style="font-size: 0.72rem; color:#94a3b8; margin-top:0.25rem;">
                    Examples — USD: $ 1,500.00 · BDT: ৳ 1,500.00 · European: € 1.500,00 · JPY: ¥ 1,500
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center;">
                <button type="submit" class="btn-primary">Save Currency Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>

    <script>
        function applyCurrencyPreset(select) {
            const option = select.options[select.selectedIndex];
            if (!option || !option.value) return;

            document.getElementById('currency_name').value = option.dataset.name || '';
            document.getElementById('currency_code').value = option.value || '';
            document.getElementById('currency_symbol').value = option.dataset.symbol || '';

            updateCurrencyPreview();
        }

        // Live preview of the formatting being edited (before saving).
        function updateCurrencyPreview() {
            const symbol = document.getElementById('currency_symbol').value || '';
            const decimals = parseInt(document.getElementById('currency_decimals').value, 10) || 0;
            const thousands = document.getElementById('currency_thousands_separator').value || ',';
            const decimal = document.getElementById('currency_decimal_separator').value || '.';
            const position = document.getElementById('currency_position').value || 'before';

            let number = (1500).toFixed(decimals);
            const parts = number.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '\u0001');
            number = parts.join('\u0002');
            number = number.replace(/\u0001/g, thousands).replace(/\u0002/g, decimal);

            const separator = String.fromCharCode(32);
            document.getElementById('currencyPreview').textContent =
                position === 'after'
                    ? (number + separator + symbol)
                    : (symbol + separator + number);
        }

        document.addEventListener('DOMContentLoaded', function () {
            ['currency_symbol', 'currency_decimals', 'currency_thousands_separator', 'currency_decimal_separator', 'currency_position']
                .forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('change', updateCurrencyPreview);
                });
        });
    </script>
</x-app-layout>