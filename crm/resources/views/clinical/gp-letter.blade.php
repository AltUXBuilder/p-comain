<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10.5pt; color: #1a1a1a; }
    .page { width: 210mm; min-height: 297mm; padding: 20mm 22mm; }
    .letterhead { display: table; width: 100%; margin-bottom: 10mm; padding-bottom: 6mm; border-bottom: 1.5pt solid #4A3050; }
    .lh-left  { display: table-cell; vertical-align: top; }
    .lh-right { display: table-cell; text-align: right; vertical-align: top; }
    .brand    { font-size: 16pt; font-weight: bold; color: #4A3050; }
    .address  { font-size: 8.5pt; color: #666; margin-top: 3pt; line-height: 1.5; }
    .gphc     { font-size: 8pt; color: #888; margin-top: 4pt; }
    h2        { font-size: 11pt; font-weight: bold; color: #4A3050; margin-bottom: 8pt; }
    p         { margin-bottom: 8pt; font-size: 10.5pt; line-height: 1.6; }
    .label    { font-size: 8.5pt; font-weight: bold; color: #666; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2pt; }
    .value    { font-size: 10.5pt; color: #1a1a1a; margin-bottom: 6pt; }
    table.details { width: 100%; border-collapse: collapse; margin: 8pt 0; }
    table.details td { padding: 5pt 8pt; border: .5pt solid #ddd; font-size: 10pt; }
    table.details td.key { background: #f5f0f7; font-weight: bold; width: 35%; color: #4A3050; }
    .sig-block { margin-top: 16pt; }
    .sig-line  { border-bottom: 1pt solid #4A3050; width: 70mm; margin: 20pt 0 4pt; }
    .footer    { position: absolute; bottom: 12mm; left: 22mm; right: 22mm; font-size: 7.5pt; color: #aaa; border-top: .5pt solid #ddd; padding-top: 4pt; display: table; width: calc(100% - 44mm); }
    .fl { display: table-cell; } .fr { display: table-cell; text-align: right; }
    .confidential { background: #f5f0f7; border: 1pt solid #4A3050; border-radius: 3pt; padding: 4pt 10pt; font-size: 8pt; font-weight: bold; color: #4A3050; display: inline-block; margin-bottom: 10pt; }
</style>
</head>
<body>
<div class="page">

    {{-- Letterhead --}}
    <div class="letterhead">
        <div class="lh-left">
            <div class="brand">Prescribe &amp; Co</div>
            <div class="address">{{ $pharmacy['address'] }}<br>{{ $pharmacy['email'] }}<br>{{ $pharmacy['phone'] }}</div>
            <div class="gphc">GPhC Registration: {{ $pharmacy['gphc_number'] }}</div>
        </div>
        <div class="lh-right">
            <div class="address">Date: {{ now()->format('d F Y') }}</div>
        </div>
    </div>

    {{-- Addressee --}}
    @if($surgery)
    <div style="margin-bottom:10pt;">
        <div class="label">GP Surgery</div>
        <div class="value">
            {{ $surgery->name }}<br>
            @if($surgery->address) {{ $surgery->address }}<br> @endif
            @if($surgery->ods_code) ODS Code: {{ $surgery->ods_code }} @endif
        </div>
    </div>
    @endif

    <div class="confidential">STRICTLY PRIVATE &amp; CONFIDENTIAL — MEDICAL</div>

    {{-- Salutation --}}
    <p>Dear {{ $surgery ? $surgery->name . ' Practice' : 'GP Practice' }},</p>

    <h2>RE: {{ $patient->full_name }} — DOB {{ $patient->date_of_birth?->format('d F Y') ?? 'Unknown' }}</h2>

    <p>
        I am writing to inform you that the above-named patient is currently under the care of Prescribe &amp; Co
        for the supply of prescription-only medication via our GPhC-registered online pharmacy service.
    </p>

    @if($prescription)
    <p>
        A private prescription has been issued for the following:
    </p>
    <table class="details">
        <tr><td class="key">Medication</td><td>{{ $prescription->product?->name }}</td></tr>
        <tr><td class="key">Strength / Form</td><td>{{ $prescription->product?->strength }} {{ $prescription->product?->form }}</td></tr>
        <tr><td class="key">Dosage</td><td>{{ $prescription->dosage_instructions }}</td></tr>
        <tr><td class="key">Quantity</td><td>{{ $prescription->quantity }}</td></tr>
        <tr><td class="key">Prescription Number</td><td>{{ $prescription->prescription_number }}</td></tr>
        <tr><td class="key">Date Prescribed</td><td>{{ $prescription->signed_at?->format('d F Y') }}</td></tr>
        <tr><td class="key">Prescriber</td><td>{{ $prescription->prescriber_name }} — GPhC: {{ $prescription->prescriber_gphc_number }}</td></tr>
    </table>
    @endif

    <p>
        We would be grateful if you could note this on the patient's records. If you have any concerns regarding
        this patient or the prescribing decision, please do not hesitate to contact us.
    </p>

    <p>Yours sincerely,</p>

    <div class="sig-block">
        <div class="sig-line"></div>
        <p>
            {{ $staff->display_name ?? $staff->full_name }}<br>
            {{ $staff->roleLabel() }}<br>
            @if($staff->gphc_number) GPhC: {{ $staff->gphc_number }}<br> @endif
            Prescribe &amp; Co<br>
            {{ $pharmacy['email'] }}
        </p>
    </div>

    <div class="footer">
        <div class="fl">Prescribe &amp; Co · GPhC: {{ $pharmacy['gphc_number'] }} · Private &amp; Confidential</div>
        <div class="fr">Generated {{ now()->format('d M Y') }}</div>
    </div>

</div>
</body>
</html>
