<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 11pt;
        color: #1a1a1a;
        background: #fff;
        padding: 0;
    }

    /* ── Page chrome ──────────────────────────────────────────────── */
    .page {
        width: 210mm;
        min-height: 297mm;
        padding: 18mm 20mm 15mm 20mm;
        position: relative;
    }

    /* ── Header ───────────────────────────────────────────────────── */
    .header {
        background: #4A3050;
        color: #C9A8D4;
        padding: 12mm 20mm 10mm 20mm;
        margin: -18mm -20mm 8mm -20mm;
        display: table;
        width: calc(100% + 40mm);
    }
    .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
    .header-right { display: table-cell; vertical-align: middle; width: 40%; text-align: right; }

    .header h1 {
        font-size: 18pt;
        font-weight: bold;
        color: #fff;
        letter-spacing: -0.02em;
        margin-bottom: 2pt;
    }
    .header .tagline { font-size: 9pt; color: #C9A8D4; }
    .header .rx-number {
        font-size: 10pt;
        font-weight: bold;
        color: #fff;
        background: rgba(201,168,212,0.2);
        padding: 4pt 8pt;
        border-radius: 4pt;
        display: inline-block;
        margin-bottom: 4pt;
    }
    .header .rx-date { font-size: 9pt; color: #C9A8D4; }

    /* ── Section labels ───────────────────────────────────────────── */
    .section-label {
        font-size: 7.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #4A3050;
        border-bottom: 1.5pt solid #4A3050;
        padding-bottom: 2pt;
        margin-bottom: 5pt;
        margin-top: 8pt;
    }

    /* ── Data tables ──────────────────────────────────────────────── */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table td { padding: 3pt 0; vertical-align: top; font-size: 10pt; }
    .data-table td.label { width: 38%; color: #666; font-size: 9pt; }
    .data-table td.value { font-weight: 500; color: #1a1a1a; }

    /* ── Two column layout ────────────────────────────────────────── */
    .two-col { display: table; width: 100%; }
    .col-left  { display: table-cell; width: 50%; padding-right: 8mm; vertical-align: top; }
    .col-right { display: table-cell; width: 50%; vertical-align: top; }

    /* ── Medication box ───────────────────────────────────────────── */
    .medication-box {
        background: #f5f0f7;
        border: 1.5pt solid #4A3050;
        border-radius: 4pt;
        padding: 8pt 10pt;
        margin: 8pt 0;
    }
    .medication-name {
        font-size: 14pt;
        font-weight: bold;
        color: #4A3050;
        margin-bottom: 3pt;
    }
    .medication-meta { font-size: 9.5pt; color: #555; }
    .dosage-box {
        background: #ebe1ef;
        border-radius: 4pt;
        padding: 6pt 10pt;
        margin-top: 6pt;
        font-size: 10.5pt;
        font-weight: 500;
        color: #3a2540;
    }

    /* ── Legal wording ────────────────────────────────────────────── */
    .legal-box {
        border: 1pt solid #d5c3de;
        border-radius: 4pt;
        padding: 7pt 9pt;
        margin-top: 8pt;
        font-size: 8.5pt;
        color: #555;
        line-height: 1.5;
    }

    /* ── Signature block ──────────────────────────────────────────── */
    .signature-block {
        margin-top: 10pt;
        padding-top: 8pt;
        border-top: 1pt solid #d5c3de;
    }
    .signature-img {
        height: 28mm;
        max-width: 70mm;
        object-fit: contain;
        display: block;
        margin-bottom: 2pt;
    }
    .signature-line {
        border-bottom: 1pt solid #4A3050;
        width: 70mm;
        margin-bottom: 3pt;
    }
    .signature-label { font-size: 8.5pt; color: #666; }

    /* ── Footer ───────────────────────────────────────────────────── */
    .footer {
        position: absolute;
        bottom: 10mm;
        left: 20mm;
        right: 20mm;
        border-top: 1pt solid #d5c3de;
        padding-top: 4pt;
        font-size: 8pt;
        color: #888;
        display: table;
        width: calc(100% - 40mm);
    }
    .footer-left  { display: table-cell; text-align: left; }
    .footer-right { display: table-cell; text-align: right; }

    /* ── Repeat badge ─────────────────────────────────────────────── */
    .repeat-badge {
        display: inline-block;
        background: #C9A8D4;
        color: #4A3050;
        font-size: 8pt;
        font-weight: bold;
        padding: 2pt 6pt;
        border-radius: 3pt;
        margin-left: 6pt;
        vertical-align: middle;
    }

    /* ── Validity strip ───────────────────────────────────────────── */
    .validity-strip {
        background: #f5f0f7;
        border-radius: 4pt;
        padding: 5pt 10pt;
        font-size: 8.5pt;
        color: #666;
        margin-top: 4pt;
    }
    .validity-strip strong { color: #4A3050; }

</style>
</head>
<body>
<div class="page">

    {{-- ── Header ─────────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-left">
            <div class="h1">Prescribe &amp; Co</div>
            <div class="tagline">GPhC-Registered Online Pharmacy &nbsp;·&nbsp; Private Prescription</div>
        </div>
        <div class="header-right">
            <div class="rx-number">{{ $prescription->prescription_number }}</div><br>
            <div class="rx-date">
                Date: {{ $prescription->signed_at?->format('d F Y') ?? now()->format('d F Y') }}
            </div>
        </div>
    </div>

    {{-- ── Patient & Prescriber columns ───────────────────────────── --}}
    <div class="two-col">

        <div class="col-left">
            <div class="section-label">Patient</div>
            <table class="data-table">
                <tr>
                    <td class="label">Full name</td>
                    <td class="value">{{ $patient?->full_name }}</td>
                </tr>
                <tr>
                    <td class="label">Date of birth</td>
                    <td class="value">{{ $patient?->date_of_birth?->format('d M Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Address</td>
                    <td class="value">{{ $patient?->formatted_address ?: '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="col-right">
            <div class="section-label">Prescriber</div>
            <table class="data-table">
                <tr>
                    <td class="label">Name</td>
                    <td class="value">{{ $prescription->prescriber_name }}</td>
                </tr>
                <tr>
                    <td class="label">GPhC No.</td>
                    <td class="value">{{ $prescription->prescriber_gphc_number }}</td>
                </tr>
                <tr>
                    <td class="label">Pharmacy</td>
                    <td class="value">{{ $pharmacy['name'] }}</td>
                </tr>
                <tr>
                    <td class="label">Pharmacy GPhC</td>
                    <td class="value">{{ $pharmacy['gphc_number'] }}</td>
                </tr>
                <tr>
                    <td class="label">Address</td>
                    <td class="value">{{ $pharmacy['address'] }}</td>
                </tr>
            </table>
        </div>

    </div>

    {{-- ── Medication ───────────────────────────────────────────────── --}}
    <div class="section-label" style="margin-top:10pt">
        Prescribed Medication
        @if($prescription->is_repeat)
            <span class="repeat-badge">REPEAT</span>
        @endif
    </div>

    <div class="medication-box">
        <div class="medication-name">
            {{ $product?->name }}
        </div>
        <div class="medication-meta">
            @if($product?->strength) Strength: {{ $product->strength }} &nbsp;&nbsp; @endif
            @if($product?->form)     Form: {{ $product->form }}           &nbsp;&nbsp; @endif
            @if($prescription->quantity) Quantity: {{ $prescription->quantity }} @endif
        </div>
        <div class="dosage-box">
            <strong>Directions:</strong> {{ $prescription->dosage_instructions ?: 'As directed by prescriber.' }}
        </div>
    </div>

    @if($prescription->is_repeat && $prescription->repeat_interval_days)
    <div class="validity-strip">
        <strong>Repeat prescription.</strong>
        Next issue due: {{ $prescription->next_repeat_due?->format('d M Y') ?? 'As clinically required' }}
        &nbsp;·&nbsp; Interval: every {{ $prescription->repeat_interval_days }} days
    </div>
    @endif

    {{-- ── Legal wording ───────────────────────────────────────────── --}}
    <div class="legal-box">
        {{ $prescription->legal_wording }}
    </div>

    {{-- ── Prescriber notes (internal — omit if empty) ─────────────── --}}
    @if($prescription->prescriber_notes)
    <div class="section-label" style="margin-top:10pt">Clinical Notes</div>
    <p style="font-size:9.5pt;color:#555;margin-top:4pt;">{{ $prescription->prescriber_notes }}</p>
    @endif

    {{-- ── Signature block ─────────────────────────────────────────── --}}
    <div class="signature-block">
        <div class="two-col">
            <div class="col-left">
                <div style="font-size:9pt;color:#666;margin-bottom:4pt;">Prescriber signature</div>
                @if($signatureBase64)
                    <img src="{{ $signatureBase64 }}" class="signature-img" alt="Prescriber signature">
                @else
                    <div class="signature-line"></div>
                @endif
                <div class="signature-label">
                    {{ $prescription->prescriber_name }}<br>
                    GPhC: {{ $prescription->prescriber_gphc_number }}<br>
                    Signed: {{ $prescription->signed_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}
                </div>
            </div>
            <div class="col-right" style="padding-top:14pt;">
                <div style="font-size:8.5pt;color:#888;line-height:1.6;">
                    This prescription is issued digitally and is legally equivalent to a paper prescription in accordance with the Human Medicines Regulations 2012. Validity: 28 days from date of issue.
                </div>
            </div>
        </div>
    </div>

    {{-- ── Footer ──────────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">
            {{ $pharmacy['name'] }} · GPhC: {{ $pharmacy['gphc_number'] }} · {{ $pharmacy['email'] }}
        </div>
        <div class="footer-right">
            Generated: {{ $generatedAt->format('d M Y, H:i') }} · CONFIDENTIAL
        </div>
    </div>

</div>
</body>
</html>
