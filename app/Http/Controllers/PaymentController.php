<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use App\Models\WebhookLog;
use App\Services\MoMoService;
use App\Jobs\GenerateReceiptJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected MoMoService $momoService;

    public function __construct(MoMoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * Handle the Static Form Submission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string|regex:/^[0-9]{10,12}$/', // Basic MSISDN validation
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'description' => 'required|string|max:500',
        ]);

        // 1. Create the Pending Payment Record
        $payment = Payments::create([
            'payer_phone' => $validated['phone'],
            'payer_email' => $validated['email'],
            'payer_name'  => $validated['name'],
            'amount'      => $validated['amount'],
            'currency'    => strtoupper($validated['currency']),
            'description' => $validated['description'],
            'status'      => 'PENDING',
            /*  'provider_metadata' => ['payer_name' => $validated['name']] */
        ]);

        // 2. Trigger the Push Request to the User's Phone
        $initiated = $this->momoService->requestPayment($payment);

        if ($initiated) {
            return response()->json([
                'status' => 'success',
                'message' => 'Please enter your PIN on your phone',
                'internal_reference' => $payment->internal_reference,
            ]);
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'Payment initiation failed'
                
            ],
            500
        );
    }

    /**
     * Handle MoMo Webhook (Callback)
     */
    public function webhook(Request $request)
    {
        // 1. Log Raw Payload for Audit
        $log = WebhookLog::create([
            'provider' => 'MTN_MOMO',
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // 2. Identify the Payment
        // MoMo usually returns 'externalId' which we set as public_ref
        $publicRef = $request->input('externalId');
        $status = $request->input('status'); // e.g., SUCCESSFUL, FAILED

        $payment = Payments::where('public_ref', $publicRef)->first();

        if (!$payment) {
            $log->update(['processing_result' => 'Payment not found']);
            return response()->json(['message' => 'Not found'], 404);
        }

        // 3. Prevent Duplicate Processing
        if ($payment->status === 'SUCCESS') {
            return response()->json(['message' => 'Already processed']);
        }

        // 4. Update Status and Trigger Receipt
        DB::transaction(function () use ($payment, $status, $log) {
            if ($status === 'SUCCESSFUL') {
                $payment->update(['status' => 'SUCCESS']);

                // Dispatch Background Job for PDF and Notifications
                GenerateReceiptJob::dispatch($payment);

                $log->update(['is_processed' => true, 'processing_result' => 'Success']);
            } else {
                $payment->update(['status' => 'FAILED']);
                $log->update(['is_processed' => true, 'processing_result' => 'Failed']);
            }
        });

        return response()->json(['message' => 'Accepted']);
    }

    
}
