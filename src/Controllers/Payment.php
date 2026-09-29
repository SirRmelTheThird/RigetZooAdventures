<?php

declare(strict_types=1);

namespace Controllers;

use Core\Constants\RedirectKey;
use Core\Constants\SessionKey;
use Core\Http\HttpStatus;
use Core\Http\Request;
use Core\Http\Response;
use Core\Session\SessionStore;
use Core\View\ViewRenderer;
use Exceptions\Payment\PaymentException;
use Payments\StripeSettings;
use Services\Checkout\CartService;
use Services\Checkout\CheckoutService;
use Services\Checkout\PaymentWebhookHandler;
use Support\Messages;

final class Payment
{
    private const SIGNATURE_HEADER = 'Stripe-Signature';

    public function __construct(
        private readonly ViewRenderer $views,
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
        private readonly PaymentWebhookHandler $webhooks,
        private readonly StripeSettings $stripe,
        private readonly SessionStore $session,
    ) {
    }

    public function checkout(Request $request): Response
    {
        $cart = $this->carts->cart();
        $viewData = [
            'cart' => $cart->toArray(),
            'stripePublishableKey' => $this->stripe->publishableKey,
            'clientSecret' => null,
            'error' => null,
        ];

        try {
            $intent = $this->checkout->begin($this->session->getUserId(), $cart);
        } catch (PaymentException $e) {
            return $this->views->render('checkout', [...$viewData, 'error' => $e->getMessage()]);
        }

        $this->session->set(SessionKey::PAYMENT_INTENT, $intent->id);
        return $this->views->render('checkout', [...$viewData, 'clientSecret' => $intent->clientSecret]);
    }

    public function process(Request $request): Response
    {
        if (!$this->session->has(SessionKey::PAYMENT_INTENT)) {
            throw new PaymentException(Messages::PAYMENT_INTENT_MISSING, RedirectKey::CHECKOUT);
        }

        $orderId = $this->checkout->complete(
            (string) $this->session->getUserId(),
            $this->carts->cart(),
            (string) $this->session->get(SessionKey::PAYMENT_INTENT),
        );

        $this->carts->clear();
        $this->session->remove(SessionKey::PAYMENT_INTENT);
        $this->session->flashSuccess(sprintf(Messages::ORDER_PLACED, $orderId));

        if ($request->wantsJson()) {
            return Response::json(['success' => true, 'order_id' => $orderId, 'redirect' => RedirectKey::PROFILE]);
        }

        return Response::redirect(RedirectKey::PROFILE);
    }

    public function webhook(Request $request): Response
    {
        $signature = $request->header(self::SIGNATURE_HEADER);

        if ($signature === null) {
            return Response::empty(HttpStatus::BadRequest);
        }

        $this->webhooks->handle($request->rawBody(), $signature);
        return Response::empty(HttpStatus::Ok);
    }
}
