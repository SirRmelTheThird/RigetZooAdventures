<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\ViewRenderer;
use Requests\AddAccommodationToCartRequest;
use Services\AccommodationService;
use Services\CartService;
use Support\Messages;

final class Accommodation
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly AccommodationService $accommodations,
        private readonly CartService $carts,
        private readonly AddAccommodationToCartRequest $addRequest,
    ) {
    }

    public function index(Request $request): Response
    {
        $accommodations = $this->accommodations->all();
        $withUnavailable = [];
        foreach ($accommodations as $a) {
            $withUnavailable[] = [
                'accommodation' => $a,
                'unavailable' => $this->accommodations->unavailableRanges((string) $a->id),
            ];
        }
        return $this->views->render('accommodations/index', ['items' => $withUnavailable]);
    }

    public function addToCart(Request $request): Response
    {
        $this->carts->addAccommodation($this->addRequest->parse($request->body()));
        Session::flashSuccess(Messages::ACCOMMODATION_ADDED);
        return Response::redirect(RedirectKey::CART);
    }
}
