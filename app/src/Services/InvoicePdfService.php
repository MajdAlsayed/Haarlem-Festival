<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Invoice\InvoiceRenderException;
use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;

final class InvoicePdfService
{
    public function render(string $html): string
    {
        if (!class_exists(Dompdf::class) || !class_exists(Options::class)) {
            throw new InvoiceRenderException('Invoice PDF generation is not available because Dompdf is not installed.');
        }

        try {
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();

            return $dompdf->output();
        } catch (InvoiceRenderException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new InvoiceRenderException('Unable to generate the invoice PDF.', 0, $exception);
        }
    }
}
