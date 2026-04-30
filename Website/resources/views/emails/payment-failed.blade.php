<x-emails.layout :recipient-email="$user->email">

    <h1>Action required: payment unsuccessful</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>We were unable to process your recent payment. To avoid any interruption to your treatment, please update your payment details.</p>

    <a href="{{ route('patient.settings') }}" class="btn">Update payment details</a>

    <hr class="divider">
    <p class="muted">If you continue to experience issues, please <a href="{{ route('contact') }}" style="color:#9a6daa;">contact our team</a> and we'll be happy to help.</p>

</x-emails.layout>
