@props(['status'])

@php
$config = match($status) {
    'submitted'      => ['class' => 'badge-amber',  'label' => 'Awaiting review'],
    'under_review'   => ['class' => 'badge-amber',  'label' => 'Under review'],
    'approved'       => ['class' => 'badge-green',  'label' => 'Approved'],
    'rejected'       => ['class' => 'badge-red',    'label' => 'Not approved'],
    'flagged'        => ['class' => 'badge-red',    'label' => 'Flagged'],
    'awaiting_info'  => ['class' => 'badge-amber',  'label' => 'Info needed'],
    'expired'        => ['class' => 'badge-red',    'label' => 'Expired'],
    default          => ['class' => 'badge-lilac',  'label' => ucfirst(str_replace('_', ' ', $status))],
};
@endphp

<span class="{{ $config['class'] }}">{{ $config['label'] }}</span>
