<x-emails.layout :recipient-email="$user->email">

    <h1>Your prescription is approved</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>Great news — your consultation for <strong>{{ $consultation->product->name }}</strong> has been reviewed and approved by one of our prescribers. You can now complete your order.</p>

    <div class="highlight-box">
        <p style="margin:0;"><strong>Treatment approved:</strong> {{ $consultation->product->name }}</p>
    </div>

    <p>Click below to choose your plan and complete your purchase securely.</p>

    <a href="{{ route('patient.consultation.checkout-ready', $consultation->product) }}" class="btn">
        Complete my order
    </a>

    <hr class="divider">
    <p class="muted">This approval link is linked to your account. You'll need to be signed in to complete your purchase.</p>

</x-emails.layout>
