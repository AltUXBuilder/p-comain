<x-emails.layout :recipient-email="$user->email">

    <h1>Your subscription renews in 7 days</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>Just a reminder that your Prescribe &amp; Co subscription renews in <strong>7 days</strong>.</p>

    <div class="highlight-box">
        <p style="margin:0;"><strong>Plan:</strong> {{ $subscription->type ?? 'Monthly' }}</p>
        <p style="margin:4px 0 0;"><strong>Renewal date:</strong> {{ optional($subscription->ends_at ?? null)->format('j F Y') ?? 'Shortly' }}</p>
    </div>

    <p>No action is needed — your subscription will renew automatically. If you'd like to make any changes, you can manage your subscription from your account.</p>

    <a href="{{ route('patient.subscriptions.index') }}" class="btn">Manage subscription</a>

    <hr class="divider">
    <p class="muted">To cancel or pause your subscription, please do so at least 24 hours before your renewal date.</p>

</x-emails.layout>
