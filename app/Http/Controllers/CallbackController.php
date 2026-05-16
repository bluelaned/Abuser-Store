<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class CallbackController extends Controller
{
    public function handle(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        try {
            $notif = new \Midtrans\Notification();
            
            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $order_id = $notif->order_id;
            $fraud = $notif->fraud_status;

            $trx = Transaction::where('reference', $order_id)->first();

            if ($trx) {
                // 1. PAYMENT FRAUD VALIDATION (Amount Mismatch)
                $paidAmount = (float) $notif->gross_amount;
                $expectedAmount = (float) $trx->price;

                if (abs($paidAmount - $expectedAmount) > 1) { // Toleransi 1 rupiah/sen
                    \Log::critical('PAYMENT FRAUD ATTEMPT: Amount mismatch in webhook.', [
                        'order_id' => $order_id,
                        'paid_amount' => $paidAmount,
                        'expected_amount' => $expectedAmount
                    ]);
                    $trx->update(['status' => 'FRAUD']);
                    return response()->json(['message' => 'Invalid Amount'], 400);
                }

                if ($transaction == 'settlement' || ($transaction == 'capture' && $fraud != 'challenge')) {
                    if ($trx->status !== 'PAID') {
                        $paymentDetails = ['type' => $type];
                        if ($type == 'credit_card') {
                            $paymentDetails['brand'] = $notif->bank ?? 'Unknown';
                            $paymentDetails['last4'] = $notif->masked_card ? substr($notif->masked_card, -4) : null;
                        } elseif ($type == 'qris') {
                            $paymentDetails['issuer'] = $notif->issuer ?? $notif->acquirer ?? 'QRIS';
                        } elseif (isset($notif->bank)) {
                            $paymentDetails['bank'] = $notif->bank;
                        }

                        // 2. MENCEGAH RACE CONDITION DI WEBHOOK DENGAN DB LOCKING
                        $issuedCodes = [];
                        
                        \Illuminate\Support\Facades\DB::transaction(function() use ($trx, &$issuedCodes, $paymentDetails) {
                            $trx->update([
                                'status' => 'PAID',
                                'payment_details' => json_encode($paymentDetails)
                            ]);
                            
                            if (empty($trx->vouchers_issued)) {
                                $vouchers = \App\Models\VoucherCode::where('variant_id', $trx->variant_id)
                                                                   ->where('status', 'AVAILABLE')
                                                                   ->lockForUpdate() // Prevent Overselling
                                                                   ->take($trx->quantity)
                                                                   ->get();
                                
                                if ($vouchers->count() >= $trx->quantity) {
                                    foreach ($vouchers as $vc) {
                                        $vc->update(['status' => 'SOLD']);
                                        $issuedCodes[] = $vc->code;
                                    }
                                    
                                    if (!empty($issuedCodes)) {
                                        $trx->update(['vouchers_issued' => implode(", ", $issuedCodes)]);
                                    }
                                } else {
                                    \Log::critical('WEBHOOK OVERSOLD PREVENTED: Order ' . $trx->reference);
                                    $trx->update(['status' => 'FAILED_OVERSOLD']);
                                }
                            }
                        });
                            
                            // Send Discord Notification
                            $user = \App\Models\User::where('email', $trx->customer_email)->first();
                            $orderData = [
                                'order_id'       => $trx->merchant_ref,
                                'variant_id'     => $trx->variant_id,
                                'product_name'   => $trx->product_name,
                                'quantity'       => $trx->quantity,
                                'unit_price'     => $trx->original_price / max($trx->quantity, 1),
                                'discount'       => 0, // Since promo logic is handled earlier
                                'total'          => $trx->price,
                                'currency'       => 'IDR',
                                'payment_method' => $trx->payment_method,
                                'customer_email' => $trx->customer_email,
                                'promo_code'     => $trx->promo_code,
                                'paid_at'        => now()->format('d M Y, H:i'),
                                'discord_name'   => $user->name ?? null,
                                'discord_id'     => $user->discord_id ?? null,
                                'vouchers'       => $issuedCodes,
                            ];
                            
                            \App\Http\Controllers\PaymentController::sendDiscordNotification($orderData);
                        }
                } else if ($transaction == 'pending') {
                    $trx->update(['status' => 'UNPAID']);
                } else if ($transaction == 'deny') {
                    $trx->update(['status' => 'FAILED']);
                } else if ($transaction == 'expire') {
                    $trx->update(['status' => 'EXPIRED']);
                } else if ($transaction == 'cancel') {
                    $trx->update(['status' => 'FAILED']);
                }
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}