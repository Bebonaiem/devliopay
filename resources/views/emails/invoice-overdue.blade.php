<p style="margin:0 0 20px;font-size:16px;color:#e2e8f0;">Hi {name},</p>
<p style="margin:0 0 24px;font-size:15px;color:#94a3b8;">Your invoice is past due. Please pay as soon as possible to avoid service interruption.</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,rgba(239,68,68,0.15),rgba(239,68,68,0.05));border:1px solid rgba(239,68,68,0.3);border-radius:12px;margin-bottom:24px;">
<tr><td style="padding:24px;text-align:center;">
<div style="font-size:36px;margin-bottom:8px;">🚨</div>
<span style="color:#ef4444;font-size:18px;font-weight:700;">{days_overdue} Day Overdue</span>
</td></tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a;border-radius:12px;border:1px solid #334155;margin-bottom:24px;">
<tr><td style="padding:24px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="padding-bottom:12px;"><span style="color:#94a3b8;font-size:13px;">Invoice</span></td>
<td style="padding-bottom:12px;text-align:right;"><span style="color:#e2e8f0;font-size:15px;font-weight:600;">#{invoice_number}</span></td>
</tr>
<tr>
<td style="padding-bottom:12px;"><span style="color:#94a3b8;font-size:13px;">Amount Due</span></td>
<td style="padding-bottom:12px;text-align:right;"><span style="color:#ef4444;font-size:20px;font-weight:700;">${amount}</span></td>
</tr>
<tr>
<td><span style="color:#94a3b8;font-size:13px;">Due Date</span></td>
<td style="text-align:right;"><span style="color:#94a3b8;font-size:15px;font-weight:600;">{due_date}</span></td>
</tr>
</table>
</td></tr>
</table>

<p style="margin:0;font-size:14px;color:#ef4444;">Your service may be suspended if payment is not received soon.</p>
