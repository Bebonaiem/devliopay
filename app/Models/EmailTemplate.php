<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_enabled',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_enabled' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function render(array $data = []): string
    {
        $companyName = Setting::get('company_name', config('app.name', 'DevlioPay'));
        $companyAddress = Setting::get('company_address', '');

        $renderedBody = $this->renderBodyOnly($data);

        $viewData = array_merge($data, [
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'subject' => $this->renderSubject($data),
            'title' => $this->name,
            'actionUrl' => $data['url'] ?? '',
            'actionText' => $data['action_text'] ?? 'View',
            'slot' => $renderedBody,
        ]);

        $layoutPath = resource_path('views/emails/layout.blade.php');

        if (file_exists($layoutPath)) {
            return view('emails.layout', $viewData)->render();
        }

        return $this->wrapInBasicHtml($this->name, $renderedBody, $companyName);
    }

    public function renderBodyOnly(array $data = []): string
    {
        $body = $this->body_html;

        foreach ($data as $key => $value) {
            $body = str_replace('{'.$key.'}', $value ?? '', $body);
        }

        return $body;
    }

    public function renderSubject(array $data = []): string
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{'.$key.'}', $value ?? '', $subject);
        }

        return $subject;
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    private function wrapInBasicHtml(string $title, string $body, string $companyName): string
    {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#0f172a;font-family:sans-serif;">'
            .'<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:40px 20px;">'
            .'<table width="600" cellpadding="0" cellspacing="0" style="background:#1e293b;border-radius:16px;border:1px solid #334155;">'
            .'<tr><td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:30px 40px;text-align:center;">'
            .'<h1 style="margin:0;color:#fff;font-size:24px;">'.$title.'</h1></td></tr>'
            .'<tr><td style="padding:40px;">'.$body.'</td></tr>'
            .'<tr><td style="padding:30px 40px;text-align:center;"><p style="color:#94a3b8;font-size:13px;">'.$companyName.'</p></td></tr>'
            .'</table></td></tr></table></body></html>';
    }
}
