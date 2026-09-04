<?php

namespace Events;

class OrderCreated
{
    public $orderId;
    public $customerId;
    public $cart;
    public $createdAt;

    public function __construct($orderId, $customerId, $cart)
    {
        $this->orderId = $orderId;
        $this->customerId = $customerId;
        $this->cart = $cart;
        $this->createdAt = date('Y-m-d H:i:s');
    }
}
