<x-emails.layout :recipient-email="$user->email">

    <h1>We've received your consultation</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>Thank you for submitting your consultation for <strong>{{ $consultation->product->name }}</strong>. It's now with our UK-registered prescribers for review.</p>

    <div class="highlight-box">
        <p style="margin:0;"><strong>Treatment:</strong> {{ $consultation->product->name }}</p>
        <p style="margin:4px 0 0;"><strong>Submitted:</strong> {{ $consultation->submitted_at?->format('j F Y, g:ia') }}</p>
    </div>

    <p>We aim to review consultations within a few hours during business hours. You'll receive an email as soon as your prescription has been reviewed.</p>

    <a href="{{ route('patient.consultations.show', $consultation) }}" class="btn">View my consultation</a>

    <hr class="divider">
    <p class="muted">Have a question? Reply to this email or message us through your account.</p>

</x-emails.layout>
