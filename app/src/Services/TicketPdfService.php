<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

// dompdf wrapper — html built in TicketView
final class TicketPdfService
{
    // dompdf only — html built in TicketView
    public function render(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
