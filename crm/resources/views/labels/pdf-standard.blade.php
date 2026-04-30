<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    /*
     * 70mm × 35mm label
     * DomPDF paper set to [0, 0, 198.43, 99.21] points
     * All sizing in mm for clarity; DomPDF honours mm units
     */

    @page {
        margin: 0;
        size: 70mm 35mm;
    }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 6pt;
        color: #1a1a1a;
        width: 70mm;
        height: 35mm;
        padding: 1.5mm 2mm;
        overflow: hidden;
    }

    /* ── Pharmacy header ────────────────────────────────────────── */
    .pharmacy-header {
        font-size: 5.5pt;
        font-weight: bold;
        color: #4A3050;
        border-bottom: 0.3mm solid #4A3050;
        padding-bottom: 0.8mm;
        margin-bottom: 1mm;
        white-space: nowrap;
        overflow: hidden;
    }
    .pharmacy-gphc {
        font-size: 4.5pt;
        color: #888;
        float: right;
        font-weight: normal;
    }

    /* ── Patient row ────────────────────────────────────────────── */
    .patient-name {
        font-size: 7pt;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 0.6mm;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Medication block ───────────────────────────────────────── */
    .medication {
        font-size: 6.5pt;
        font-weight: bold;
        color: #4A3050;
        margin-bottom: 0.4mm;
        white-space: nowrap;
        overflow: hidden;
    }
    .dosage {
        font-size: 5.5pt;
        color: #333;
        margin-bottom: 0.8mm;
        line-height: 1.3;
    }

    /* ── Warnings ───────────────────────────────────────────────── */
    .warnings {
        font-size: 4.5pt;
        color: #555;
        border-top: 0.2mm solid #ccc;
        padding-top: 0.5mm;
        margin-bottom: 0.8mm;
        line-height: 1.3;
    }

    /* ── Footer row ─────────────────────────────────────────────── */
    .footer {
        font-size: 4.5pt;
        color: #777;
        border-top: 0.2mm solid #ddd;
        padding-top: 0.5mm;
        display: table;
        width: 100%;
    }
    .footer-left  { display: table-cell; text-align: left; }
    .footer-right { display: table-cell; text-align: right; }

    /* ── Cold chain badge ───────────────────────────────────────── */
    .cold-chain {
        display: inline-block;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 4.5pt;
        font-weight: bold;
        padding: 0.2mm 0.8mm;
        border-radius: 0.5mm;
        border: 0.2mm solid #93c5fd;
        margin-left: 1mm;
    }

    /* ── Expiry ─────────────────────────────────────────────────── */
    .expiry { font-size: 4.5pt; color: #555; }
    .expiry-warn { color: #dc2626; font-weight: bold; }
</style>
</head>
<body>

    {{-- Pharmacy header --}}
    <div class="pharmacy-header">
        {{ $label->pharmacy_name }}
        @if($label->pharmacy_address)
            &nbsp;·&nbsp; {{ $label->pharmacy_address }}
        @endif
        <span class="pharmacy-gphc">GPhC: {{ $label->pharmacy_gphc_number }}</span>
    </div>

    {{-- Patient name + cold chain --}}
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

    {{-- Legal warnings --}}
    <div class="warnings">
        Keep out of the reach and sight of children.
        @if($label->cold_chain)
            Store in a refrigerator (2–8°C). Do not freeze.
        @endif
        If symptoms persist, consult your pharmacist or doctor.
    </div>

    {{-- Footer: date, batch/expiry, dispensed by --}}
    <div class="footer">
        <div class="footer-left">
            Dispensed: {{ $label->dispensing_date->format('d/m/Y') }}
            @if($label->batch_number)
                &nbsp;· Batch: {{ $label->batch_number }}
            @endif
            @if($label->expiry_date)
                &nbsp;·
                <span class="{{ $label->isNearExpiry(30) ? 'expiry-warn' : '' }}">
                    Exp: {{ $label->expiry_date->format('m/Y') }}
                </span>
            @endif
        </div>
        <div class="footer-right">
            {{ $label->dispensed_by_name }}
            @if($label->dispensed_by_gphc)
                ({{ $label->dispensed_by_gphc }})
            @endif
        </div>
    </div>

</body>
</html>
