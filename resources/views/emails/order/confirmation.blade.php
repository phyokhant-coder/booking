<p>Hello {{ $order->user?->name ?? $order->billingAddress?->full_name }},</p>
<p>Your order #{{ $order->order_no }} status has been confirmed.</p>
<p>Thank you for shopping with us!</p>

<p>Regards,</p>
<p>TerriBerryGroup</p>
