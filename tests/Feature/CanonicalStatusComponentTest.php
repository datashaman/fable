<?php

use App\Enums\CanonicalStatus;
use Illuminate\Support\Facades\Blade;

test('canonical statuses render as accessible icon-only indicators', function (
    CanonicalStatus $status,
    string $icon,
    string $label,
) {
    $html = Blade::render(
        '<x-fable.canonical-status :$status />',
        ['status' => $status],
    );

    expect($html)
        ->toContain('data-fable-canonical-status="'.$status->value.'"')
        ->toContain('data-icon="'.$icon.'"')
        ->toContain('aria-label="Status: '.$label.'"')
        ->not->toContain('class="fable-status"');
})->with([
    'canonical' => [CanonicalStatus::Canonical, 'check-circle', 'Canonical'],
    'proposed' => [CanonicalStatus::Proposed, 'clock', 'Proposed'],
    'disputed' => [CanonicalStatus::Disputed, 'exclamation-circle', 'Disputed'],
    'obsolete' => [CanonicalStatus::Obsolete, 'minus-circle', 'Obsolete'],
]);
