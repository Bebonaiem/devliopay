<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfService
{
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\Pdf
    {
        $invoice->load(['items', 'user', 'currency', 'service.product']);

        $subtotal = $invoice->items->sum('amount');
        $tax = $invoice->tax ?? 0;
        $total = $invoice->total;
        $credit = $invoice->credit ?? 0;
        $amountDue = max(0, $total - $credit);

        $companyName = \App\Models\Setting::get('company_name', config('app.name', 'DevlioPay'));
        $companyEmail = \App\Models\Setting::get('company_email', config('mail.from.address', 'noreply@example.com'));

        $pdf = Pdf::loadView('pdf.invoice', compact(
            'invoice', 'subtotal', 'tax', 'total', 'credit', 'amountDue',
            'companyName', 'companyEmail'
        ));

        $pdf->setPaper('a4');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    public function download(Invoice $invoice, ?string $filename = null): Response
    {
        $filename = $filename ?? "invoice-{$invoice->number}.pdf";

        return $this->generate($invoice)
            ->download($filename);
    }

    public function stream(Invoice $invoice): Response
    {
        return $this->generate($invoice)
            ->stream("invoice-{$invoice->number}.pdf");
    }
}
