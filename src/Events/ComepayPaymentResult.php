<?php
namespace Grechanyuk\Comepay\Events;
use Illuminate\Queue\SerializesModels;

class ComepayPaymentResult {
    use SerializesModels;

    public $order_id;
    public $status;

    public function __construct($order_id, $status)
    {
        $this->order_id = $order_id;
        $this->status = $status;
    }
}