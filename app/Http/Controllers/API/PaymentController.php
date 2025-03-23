<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
{
    // Validate the incoming payment request
    $validated = $request->validate([
        'payment_method' => 'required|string|in:Cash on Delivery,WHISH,OMT,BOB',
    ]);

    // Get the authenticated user
    $user = auth()->user();

    // Fetch the latest cart for the user with an associated order
    $cart = $user->carts()->whereNotNull('order_id')->latest()->first();

    if (!$cart) {
        return response()->json(['error' => 'Cart not found.'], 400);
    }

    // Fetch the order associated with this cart
    $order = $cart->order;

    if (!$order) {
        return response()->json(['error' => 'Order not found.'], 400);
    }

    // Make sure the amount matches the order's total
    $amount = $order->total_price;
    $paymentStatus = 'pending';

    // Generate a unique transaction ID for online payments
    $transactionId = null;
    if (in_array($validated['payment_method'], ['WHISH', 'OMT', 'BOB'])) {
        $transactionId = uniqid('TXN-', true); // Unique transaction ID
    }

    // If payment method requires sending receipt via WhatsApp
    if (in_array($validated['payment_method'], ['WHISH', 'OMT', 'BOB'])) {
        $whatsappMessage = "I have made a payment for Order #{$order->id} of amount {$amount}. Please confirm the receipt.";
        $whatsappLink = "https://wa.me/9613110621?text=" . urlencode($whatsappMessage);

        // Create the payment record correctly
        $payment = Payment::create([
            'order_id' => $order->id, // Use order ID from the fetched order
            'user_id' => $user->id, // Use authenticated user ID
            'payment_method' => $validated['payment_method'], // Correct payment method
            'transaction_id' => $transactionId, // Use generated transaction ID
            'status' => 'pending',
            'amount' => $amount, // Use order total price
        ]);

        return response()->json([
            'message' => 'Please send your payment receipt to WhatsApp.',
            'whatsapp_link' => $whatsappLink,
            'order_id' => $order->id,
            'amount' => $amount,
            'payment' => $payment,
        ]);
    }

    // If payment method is Cash on Delivery
    if ($validated['payment_method'] === 'Cash on Delivery') {
        $order->status = 'pending';
        $order->save();

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'payment_method' => 'Cash on Delivery',
            'transaction_id' => null, // No transaction ID for Cash on Delivery
            'status' => 'pending',
            'amount' => $amount,
        ]);

        return response()->json([
            'message' => 'Payment method selected: Cash on Delivery. Your order is now pending.',
            'order' => $order,
            'payment' => $payment,
        ]);
    }

    return response()->json(['error' => 'Invalid payment method.'], 400);
}
    // Function to confirm payment
    public function confirmPayment(Request $request)
{
    // Get the authenticated user
    $user = auth()->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Find the latest order linked to the logged-in user
    $payment = Payment::where('user_id', $user->id)
                      ->where('status', 'pending') // Ensuring it's an unpaid order
                      ->latest()
                      ->first();

    if (!$payment) {
        return response()->json(['error' => 'No pending payment found'], 404);
    }

    // Generate a unique transaction ID
    $transactionId = 'TXN-' . strtoupper(uniqid());

    // Update payment details
    $payment->transaction_id = $transactionId;
    $payment->status = 'paid'; // Automatically set to 'paid'
    $payment->save();

    return response()->json([
        'message' => 'Payment confirmed successfully',
        'transaction_id' => $transactionId,
        'order_id' => $payment->order_id,
        'status' => 'paid'
    ]);
}
}
