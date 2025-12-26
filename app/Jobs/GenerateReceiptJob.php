<?php
namespace App\Jobs;

use App\Models\Payments;
use App\Models\ReceiptsModel as Receipts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payment;

    public function __construct(Payments $payment)
    {
        $this->payment = $payment;
    }

    public function handle(): void
    {
        try {
            // 1. Prepare data for the view
            $data = [
                'payment' => $this->payment,
                'date' => now()->format('d-m-Y'),
                'company_name' => config('app.name'),
                'company_address' => '123 Business St, City, Country',
                'company_email' => config('mail.from.address'),
                'company_phone' => '+1234567890',
                
            ];

         /*    Log::info('GenerateReceiptJob data', $data); */

            // 2. Load a blade view and convert to PDF
            $pdf = Pdf::loadView('pdf.receipt', $data);

            // 3. Save the PDF to storage (storage/app/public/receipts/)
            $fileName = 'receipt_' . $this->payment->internal_reference . '.pdf';
            Storage::disk('public')->put('receipts/' . $fileName, $pdf->output());

            //Update Receipt record table
            $receipt = Receipts::create([
                'payment_id' => $this->payment->id,
                'receipt_number' => 'RCPT-' . strtoupper(uniqid()),
                'storage_path' => 'receipts/' . $fileName,
                'public_url' => Storage::url('receipts/' . $fileName),
                'issued_at' => now(),
            ]);

            Log::info('Receipt PDF generated successfully', [
                'payment_id' => $this->payment->id,
                'file' => $fileName,
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateReceiptJob failed', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
