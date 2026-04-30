<x-emails.layout :recipient-email="$user->email">

    <h1>Update on your consultation</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>Thank you for submitting your consultation for <strong>{{ $consultation->product->name }}</strong>. After careful review, our prescriber has been unable to approve a prescription at this time.</p>

    @if ($reason)
    <div class="highlight-box">
        <p style="margin:0;"><strong>Reason:</strong> {{ $reason }}</p>
    </div>
    @endif

    <p>This decision is made with your safety and wellbeing in mind. We recommend speaking to your GP if you'd like further guidance.</p>

    <a href="{{ route('treatments.index') }}" class="btn">Browse other treatments</a>

    <hr class="divider">
    <p class="muted">If you believe this decision was made in error or have further questions, please <a href="{{ route('patient.messages.index') }}" style="color:#9a6daa;">contact our team</a>.</p>

</x-emails.layout>
