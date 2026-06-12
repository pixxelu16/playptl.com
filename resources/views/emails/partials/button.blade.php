@php
    use App\Support\MailBranding;
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0;">
    <tr>
        <td align="left" style="border-radius:8px;background:{{ MailBranding::colorPrimary() }};">
            <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;background:{{ MailBranding::colorPrimary() }};">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
