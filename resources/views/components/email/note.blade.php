@props(['tone' => 'info', 'icon' => null])

@php
    $tones = [
        'info' => ['bg' => '#EFF6FF', 'bd' => '#BFDBFE', 'fg' => '#1D4ED8'],
        'success' => ['bg' => '#ECFDF5', 'bd' => '#6EE7B7', 'fg' => '#047857'],
        'warn' => ['bg' => '#FFFBEB', 'bd' => '#FCD34D', 'fg' => '#92400E'],
        'danger' => ['bg' => '#FEF2F2', 'bd' => '#FCA5A5', 'fg' => '#B91C1C'],
        'muted' => ['bg' => '#F8FAFC', 'bd' => '#E2E8F0', 'fg' => '#475569'],
    ];
    $t = $tones[$tone] ?? $tones['info'];
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px 0;">
    <tr>
        <td style="background-color:{{ $t['bg'] }}; border:1px solid {{ $t['bd'] }}; border-radius:8px; padding:16px 18px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14px; line-height:22px; color:{{ $t['fg'] }};">
            @if ($icon)<span style="font-size:16px;">{{ $icon }}</span>&nbsp;@endif{{ $slot }}
        </td>
    </tr>
</table>
