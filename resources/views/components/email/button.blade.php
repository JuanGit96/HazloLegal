@props(['href', 'label', 'color' => '#2563EB', 'text' => '#FFFFFF'])

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
    <tr>
        <td align="center" style="background-color:{{ $color }}; border-radius:8px;">
            <a href="{{ $href }}"
               style="display:inline-block; padding:15px 30px; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; font-size:16px; font-weight:700; line-height:20px; color:{{ $text }}; text-decoration:none; border-radius:8px;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
