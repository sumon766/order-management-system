<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function generatePdf(Order $order): string
    {
        $pdf = PDF::loadView('invoices.order', compact('order'));

        $filename = "invoice-{$order->order_number}.pdf";
        $path = "invoices/{$filename}";

        Storage::put($path, $pdf->output());

        return $path;
    }

    public function getInvoicePath(Order $order): ?string
    {
        $filename = "invoice-{$order->order_number}.pdf";
        $path = "invoices/{$filename}";

        return Storage::exists($path) ? $path : null;
    }
}
