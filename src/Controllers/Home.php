<?php

namespace Controllers;

use Core\Response;
use Core\Session;
use Models\Customer;
use Models\Order;

class Home extends Controller
{
    public function index()
    {
        Response::view('home');
    }

    public function profile()
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please log in');
            Response::redirect('/login');
        }

        $userId = Session::getUserId();

        $user = Customer::with('rewardPoints')->find($userId);

        if (!$user) {
            Session::destroy();
            Session::flash('error', 'User account not found');
            Response::redirect('/login');
        }

        $orders = Order::where('customer_id', $userId)
            ->with(['items.ticket', 'items.accommodation'])
            ->orderBy('created_at', 'desc')
            ->get();

        Response::view('profile', compact('user', 'orders'));
    }
}
