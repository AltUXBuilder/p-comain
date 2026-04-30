<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0; size: 70mm 35mm; }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 6pt;
        color: #1a1a1a;
        width: 70mm;
        height: 35mm;
        overflow: hidden;
    }

    /* ── Plum header band ───────────────────────────────────────── */
    .header-band {
        background: #4A3050;
        padding: 0.8mm 2mm;
        display: table;
        width: 100%;
    }
    .header-brand {
        display: table-cell;
        font-size: 6pt;
        font-weight: bold;
        color: #C9A8D4;
        vertical-align: middle;
        letter-spacing: 0.03em;
    }
    .header-gphc {
        display: table-cell;
        font-size: 4.5pt;
        color: #9a6daa;
        text-align: right;
        vertical-align: middle;
    }

    /* ── Body ───────────────────────────────────────────────────── */
    .body {
        padding: 1mm 2mm;
    }

    .patient-name {
        font-size: 7pt;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 0.5mm;
    }
    .cold-chain {
        display: inline-block;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 4.5pt;
        font-weight: bold;
        padding: 0.2mm 0.8mm;
        border-radius: 0.4mm;
        border: 0.2mm solid #93c5fd;
        margin-left: 1mm;
    }

    .medication {
        font-size: 6.5pt;
        font-weight: bold;
        color: #4A3050;
        margin-bottom: 0.3mm;
    }
    .dosage {
        font-size: 5.5pt;
        color: #444;
        margin-bottom: 0.6mm;
        line-height: 1.3;
    }

    .warnings {
        font-size: 4.5pt;
        color: #666;
        margin-bottom: 0.5mm;
        line-height: 1.3;
        border-top: 0.2mm solid #ebe1ef;
        padding-top: 0.4mm;
    }

    /* ── Footer ─────────────────────────────────────────────────── */
    .footer {
        font-size: 4.5pt;
        color: #888;
        border-top: 0.2mm solid #d5c3de;
        padding: 0.4mm 2mm;
        display: table;
        width: 100%;
        background: #f5f0f7;
    }
    .footer-left  { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }
    .expiry-warn  { color: #dc2626; font-weight: bold; }
</style>
</head>
<body>

    {{-- Branded plum header --}}
    <div class="header-band">
        <div class="header-brand">Prescribe &amp; Co</div>
        <div class="header-gphc">GPhC: {{ $label->pharmacy_gphc_number }}</div>
    </div>

    <div class="body">

        {{-- Patient + cold chain --}}
        <div class="patient-name">
            {{ $label->patient_name }}
            @if($label->cold_chain)
                <span class="cold-chain">❄ COLD CHAIN</span>
            @endif
        </div>

        {{-- Medication --}}
        <div class="medication">
            {{ $label->medication_name }}
            @if($label->medication_strength) {{ $label->medication_strength }}@endif
            @if($label->medication_form) · {{ $label->medication_form }}@endif
        </div>

        {{-- Dosage --}}
        <div class="dosage">{{ $label->dosage_instructions }}</div>

        {{-- Warnings --}}
        <div class="warnings">
            Keep out of the reach and sight of children.
            @if($label->cold_chain) Store 2–8°C. Do not freeze. @endif
            If symptoms persist, consult your pharmacist.
        </div>

    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">
            Dispensed {{ $label->dispensing_date->format('d/m/Y') }}
            @if($label->batch_number) · Batch {{ $label->batch_number }}@endif
            @if($label->expiry_date)
                ·
                <span class="{{ $label->isNearExpiry(30) ? 'expiry-warn' : '' }}">
                    Exp {{ $label->expiry_date->format('m/Y') }}
                </span>
            @endif
        </div>
        <div class="footer-right">
            {{ $label->dispensed_by_name }}
            @if($label->dispensed_by_gphc) ({{ $label->dispensed_by_gphc }})@endif
        </div>
    </div>

</body>
</html>
