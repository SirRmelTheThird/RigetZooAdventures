<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\ViewRenderer;
use Requests\RemoveCartItemRequest;
use Services\CartService;
use Services\RewardService;
use Support\Messages;

final class Cart
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly CartService $carts,
        private readonly RewardService $rewards,
        private readonly RemoveCartItemRequest $removeRequest,
    ) {
    }

    public function index(Request $request): Response
    {
        $cart = $this->carts->cart();

        return $this->views->render('cart/index', [
            'cart' => $cart->toArray(),
            'points' => $this->rewards->pointsFor($cart->total()),
        ]);
    }

    public function removeItem(Request $request): Response
    {
        $this->carts->remove($this->removeRequest->parse($request->body()));

        return $this->redirectToCartWithSuccess(Messages::ITEM_REMOVED);
    }

    public function clear(Request $request): Response
    {
        $this->carts->clear();

        return $this->redirectToCartWithSuccess(Messages::CART_CLEARED);
    }

    private function redirectToCartWithSuccess(string $message): Response
    {
        Session::flashSuccess($message);

        return Response::redirect(RedirectKey::CART);
    }
}
