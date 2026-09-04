<?php

namespace Controllers;

use Core\Response;
use Core\Session;

class Cart extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', ['items' => [], 'total' => 0]);

        $points = 0;
        if ($cart['total'] > 0) {
            $points = intval($cart['total'] * 10);
        }

        Response::view('cart/index', compact('cart', 'points'));
    }

    public function removeItem()
    {
        $key = $_POST['key'] ?? null;

        if (!$key) {
            Session::flash('error', 'Invalid item');
            Response::back();
        }

        $cart = Session::get('cart', ['items' => [], 'total' => 0]);

        if (isset($cart['items'][$key])) {
            $item = $cart['items'][$key];
            $cart['total'] -= $item['total'];
            unset($cart['items'][$key]);

            if (empty($cart['items'])) {
                Session::remove('cart');
            } else {
                Session::set('cart', $cart);
            }

            Session::flash('success', 'Item removed from cart');
        }

        Response::redirect('/cart');
    }

    public function clear()
    {
        Session::remove('cart');
        Session::flash('success', 'Cart cleared');
        Response::redirect('/cart');
    }
}
