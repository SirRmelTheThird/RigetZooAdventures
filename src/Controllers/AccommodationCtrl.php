<?php

namespace Controllers;

use Core\Response;
use Core\Session;
use Models\Accommodation;

class AccommodationCtrl extends Controller
{
    public function index()
    {
        $accommodations = Accommodation::all();

        Response::view('accommodations/index', compact('accommodations'));
    }

    public function addToCart()
    {
        $id = intval($_POST['id'] ?? 0);
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $guests = intval($_POST['guests'] ?? 0);

        if (!$id) {
            Session::flash('error', 'Invalid accommodation');
            Response::back();
        }

        if (empty($startDate) || empty($endDate)) {
            Session::flash('error', 'Please select check-in and check-out dates');
            Response::back();
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $today = new \DateTime('today');

        if ($start < $today || $end < $today) {
            Session::flash('error', 'Dates must be in the future');
            Response::back();
        }

        if ($start >= $end) {
            Session::flash('error', 'Check-out date must be after check-in date');
            Response::back();
        }

        if ($guests <= 0) {
            Session::flash('error', 'Please select number of guests');
            Response::back();
        }

        $accommodation = Accommodation::find($id);

        if (!$accommodation) {
            Session::flash('error', 'Accommodation not found');
            Response::back();
        }

        // Check availability
        if (!$accommodation->isAvailable($startDate, $endDate)) {
            Session::flash('error', 'This accommodation is not available for the selected dates');
            Response::back();
        }

        $cart = Session::get('cart', ['items' => [], 'total' => 0]);

        foreach ($cart['items'] as $item) {
            if ($item['type'] === 'accommodation') {
                Session::flash('error', 'You can only book one accommodation per order');
                Response::back();
            }
        }

        $nights = $accommodation->calculateNights($startDate, $endDate);
        $totalPrice = $accommodation->calculatePrice($startDate, $endDate);

        $key = "accommodation_{$id}";
        $cart['items'][$key] = [
            'type' => 'accommodation',
            'id' => $id,
            'name' => $accommodation->name,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'nights' => $nights,
            'pricePerNight' => $accommodation->price_per_night,
            'guests' => $guests,
            'total' => $totalPrice
        ];

        $cart['total'] += $totalPrice;

        Session::set('cart', $cart);

        Session::flash('success', 'Accommodation added to cart!');
        Response::redirect('/cart');
    }
}