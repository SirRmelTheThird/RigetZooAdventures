<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\ViewRenderer;
use Enums\TicketType;
use Requests\BookTicketRequest;
use Services\CartService;
use Services\TicketCatalog;
use Support\Messages;

final class Ticket
{
    public function __construct(
        private readonly ViewRenderer $views,
        private readonly TicketCatalog $catalog,
        private readonly CartService $carts,
        private readonly BookTicketRequest $bookRequest,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->views->render('tickets/index', [
            'standardTickets' => $this->catalog->forType(TicketType::Standard),
            'premiumTickets' => $this->catalog->forType(TicketType::Premium),
        ]);
    }

    public function showStandard(Request $request): Response
    {
        return $this->views->render('tickets/standard', ['tickets' => $this->catalog->forType(TicketType::Standard)]);
    }

    public function showPremium(Request $request): Response
    {
        return $this->views->render('tickets/premium', ['tickets' => $this->catalog->forType(TicketType::Premium)]);
    }

    public function addStandard(Request $request): Response
    {
        return $this->addToCart($request, TicketType::Standard);
    }

    public function addPremium(Request $request): Response
    {
        return $this->addToCart($request, TicketType::Premium);
    }

    private function addToCart(Request $request, TicketType $type): Response
    {
        $this->carts->addTickets($this->bookRequest->parse($request->body(), $type));

        Session::flashSuccess(Messages::TICKETS_ADDED);

        return Response::redirect(RedirectKey::CART);
    }
}
