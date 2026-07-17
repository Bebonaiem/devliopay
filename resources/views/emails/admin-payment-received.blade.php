<p style="margin:0 0 20px;font-size:16px;color:#e2e8f0;">Hi {admin_name},</p>
<p style="margin:0 0 24px;font-size:15px;color:#94a3b8;">A payment has been received.</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,rgba(34,197,94,0.15),rgba(34,197,94,0.05));border:1px solid rgba(34,197,94,0.3);border-radius:12px;margin-bottom:24px;">
<tr><td style="padding:24px;text-align:center;">
<div style="font-size:36px;margin-bottom:8px;">💵</div>
<span style="color:#22c55e;font-size:18px;font-weight:700;">{currency_symbol}{amount} Received</span>
</td></tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;border-radius:12px;border:1px solid #334155;margin-bottom:24px;">
<tr><td style="padding:24px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="padding-bottom:12px;"><span style="color:#94a3b8;font-size:13px;">Customer</span></td>
<td style="padding-bottom:12px;text-align:right;"><span style="color:#e2e8f0;font-size:15px;font-weight:600;">{customer_name} ({customer_email})</span></td>
</tr>
<tr>
<td style="padding-bottom:12px;"><span style="color:#94a3b8;font-size:13px;">Invoice</span></td>
<td style="padding-bottom:12px;text-align:right;"><span style="color:#e2e8f0;font-size:15px;font-weight:600;">#{invoice_number}</span></td>
</tr>
<tr>
<td><span style="color:#94a3b8;font-size:13px;">Gateway</span></td>
<td style="text-align:right;"><span style="color:#e2e8f0;font-size:15px;font-weight:600;">{gateway}</span></td>
</tr>
</table>
</td></tr>
</table>
