@props(['label', 'value' => null])

<tr>
    <td style="padding:9px 0; border-bottom:1px solid #E2E8F0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14px; line-height:21px; color:#64748B; width:40%; vertical-align:top;">
        {{ $label }}
    </td>
    <td style="padding:9px 0; border-bottom:1px solid #E2E8F0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:14px; line-height:21px; color:#0F172A; font-weight:600; text-align:right; vertical-align:top;">
        {{ $value ?? $slot }}
    </td>
</tr>
