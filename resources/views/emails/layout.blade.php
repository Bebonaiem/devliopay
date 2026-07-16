<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? '' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

<!-- Logo -->
<tr><td align="center" style="padding-bottom:30px;">
<table cellpadding="0" cellspacing="0"><tr>
<td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;padding:10px 16px;">
<span style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">⚡ {{ $company_name ?? 'DevlioPay' }}</span>
</td>
</tr></table>
</td></tr>

<!-- Main Card -->
<tr><td style="background-color:#1e293b;border-radius:16px;border:1px solid #334155;overflow:hidden;">

<!-- Header Bar -->
<tr><td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px 40px;text-align:center;">
<h1 style="margin:0;font-size:24px;font-weight:700;color:#ffffff;">{{ $title ?? '' }}</h1>
</td></tr>

<!-- Content -->
<tr><td style="padding:40px;">
{!! $slot !!}
</td></tr>

<!-- Action Button -->
@if(!empty($actionUrl))
<tr><td style="padding:0 40px 40px;text-align:center;">
<a href="{{ $actionUrl }}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:10px;font-size:16px;font-weight:600;">{{ $actionText ?? 'View' }}</a>
</td></tr>
@endif

</td></tr>

<!-- Footer -->
<tr><td style="padding:30px 40px;text-align:center;">
<p style="margin:0 0 8px;font-size:13px;color:#94a3b8;">{{ $company_name ?? 'DevlioPay' }} &middot; {{ $company_address ?? '' }}</p>
<p style="margin:0;font-size:12px;color:#64748b;">This is an automated email. Please do not reply.</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
