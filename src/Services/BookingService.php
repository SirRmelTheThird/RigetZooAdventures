<?php

namespace Services;

use Models\Order;
use Models\OrderItem;
use Models\RewardPoint;
use Events\OrderCreated;
use Core\EventDispatcher;
use Core\Logger;
use Illuminate\Database\Capsule\Manager as DB;

class BookingService
{
    public function createOrder($customerId, $cart)
    {
        try {
            return DB::transaction(function() use ($customerId, $cart) {
                // Create the order
                $order = Order::create([
                    'customer_id' => $customerId,
                    'total_amount' => $cart['total'],
                    'order_status' => 'Pending'
                ]);

                if (!$order) {
                    throw new \Exception('Failed to create order');
                }

                // Add items to the order
                foreach ($cart['items'] as $item) {
                    if ($item['type'] === 'ticket') {
                        $this->addTicketToOrder($order->id, $item);
                    } else {
                        $this->addAccommodationToOrder($order->id, $item);
                    }
                }

                // Calculate and award reward points
                $points = RewardPoint::calculatePoints($cart['total']);
                RewardPoint::awardPoints($customerId, $points, $order->id);

                Logger::info('Order created', [
                    'order_id' => $order->id,
                    'customer_id' => $customerId,
                    'total' => $cart['total'],
                    'points_awarded' => $points
                ]);

                // Dispatch event
                EventDispatcher::dispatch(new OrderCreated($order->id, $customerId, $cart));

                return $order->id;
            });

        } catch (\Exception $e) {
            Logger::exception($e, [
                'customer_id' => $customerId,
                'cart_total' => $cart['total'] ?? 0
            ]);

            throw $e;
        }
    }

    private function addTicketToOrder($orderId, $item)
    {
        $ticketType = $item['ticketType'] ?? 'Standard';
        $adultCount = (int)($item['adult'] ?? 0);
        $childCount = (int)($item['child'] ?? 0);

        if ($adultCount > 0) {
            $adultTicket = \Models\Ticket::where('type', $ticketType)->where('category', 'Adult')->first();
            $adultPrice = isset($item['adultPrice']) ? (float)$item['adultPrice'] : ($adultTicket ? (float)$adultTicket->price : 0);

            OrderItem::create([
                'order_id' => $orderId,
                'item_type' => 'Ticket',
                'ticket_id' => $adultTicket ? $adultTicket->id : null,
                'quantity' => $adultCount,
                'price' => $adultCount * $adultPrice,
                'start_date' => $item['date'] ?? null,
                'end_date' => null
            ]);

            if ($adultTicket) {
                $adultTicket->decreaseQuantity($adultCount);
            }
        }

        if ($childCount > 0) {
            $childTicket = \Models\Ticket::where('type', $ticketType)->where('category', 'Child')->first();
            $childPrice = isset($item['childPrice']) ? (float)$item['childPrice'] : ($childTicket ? (float)$childTicket->price : 0);

            OrderItem::create([
                'order_id' => $orderId,
                'item_type' => 'Ticket',
                'ticket_id' => $childTicket ? $childTicket->id : null,
                'quantity' => $childCount,
                'price' => $childCount * $childPrice,
                'start_date' => $item['date'] ?? null,
                'end_date' => null
            ]);

            if ($childTicket) {
                $childTicket->decreaseQuantity($childCount);
            }
        }

        if ($adultCount === 0 && $childCount === 0) {
            OrderItem::create([
                'order_id' => $orderId,
                'item_type' => 'Ticket',
                'ticket_id' => null,
                'quantity' => 1,
                'price' => (float)($item['total'] ?? 0),
                'start_date' => $item['date'] ?? null,
                'end_date' => null
            ]);
        }
    }

    private function addAccommodationToOrder($orderId, $item)
    {
        OrderItem::create([
            'order_id' => $orderId,
            'item_type' => 'Accommodation',
            'accommodation_id' => $item['id'],
            'quantity' => 1,
            'price' => $item['total'],
            'start_date' => $item['startDate'],
            'end_date' => $item['endDate']
        ]);
    }

    public function getOrder($orderId)
    {
        return Order::with(['items', 'customer'])->find($orderId);
    }

    public function getCustomerOrders($customerId)
    {
        return Order::with(['items'])
            ->forCustomer($customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}