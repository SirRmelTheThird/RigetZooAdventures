<?php

namespace Controllers;

use Core\Response;
use Core\Session;
use Requests\BookTicketRequest;
use Repositories\TicketRepository;
use DTOs\BookingDTO;
use Core\Logger;

class Ticket extends Controller
{
    private $ticketRepo;

    public function __construct()
    {
        $this->ticketRepo = new TicketRepository();
    }

    public function index()
    {
        $standardTickets = $this->ticketRepo->findByType('Standard');
        $premiumTickets = $this->ticketRepo->findByType('Premium');

        Response::view('tickets/index', compact('standardTickets', 'premiumTickets'));
    }

    public function showStandard()
    {
        $tickets = $this->ticketRepo->findByType('Standard');
        Response::view('tickets/standard', compact('tickets'));
    }

    public function showPremium()
    {
        $tickets = $this->ticketRepo->findByType('Premium');
        Response::view('tickets/premium', compact('tickets'));
    }

    public function addStandard()
    {
        $this->addToCart('Standard');
    }

    public function addPremium()
    {
        $this->addToCart('Premium');
    }

    private function addToCart($ticketType)
    {
        $request = new BookTicketRequest($_POST);

        if (!$request->validate()) {
            $request->failWithRedirect();
            Response::back();
        }

        $booking = new BookingDTO(
            $ticketType,
            $request->getAdultCount(),
            $request->getChildCount(),
            $request->getDate()
        );

        $adultPrice = $this->ticketRepo->getPrice($ticketType, 'Adult');
        $childPrice = $this->ticketRepo->getPrice($ticketType, 'Child');
        $booking->calculateTotal($adultPrice, $childPrice);

        if (!Session::has('cart')) {
            Session::set('cart', [
                'items' => [],
                'total' => 0
            ]);
        }

        $cart = Session::get('cart');

        $key = "ticket_{$ticketType}_{$booking->date}";
        $cartItem = $booking->toCartItem();
        $cartItem['adultPrice'] = $adultPrice;
        $cartItem['childPrice'] = $childPrice;

        $cart['items'][$key] = $cartItem;
        $cart['total'] += $booking->total;

        Session::set('cart', $cart);

        Logger::info('Tickets added to cart', [
            'type' => $ticketType,
            'adult' => $booking->adultCount,
            'child' => $booking->childCount,
            'total' => $booking->total
        ]);

        Session::flash('success', 'Tickets added to cart!');
        Response::redirect('/cart');
    }
}
