# Plan 001: Reject Payment Intents in the Wrong Currency

> **Executor instructions**: Follow this plan step by step. This is an implementation handoff, not an audit invitation. Run every verification command and confirm the expected result before moving on. Stop under the conditions below instead of improvising.
>
> **Drift check (run first)**: `git diff --stat c415494..HEAD -- src/Services/Checkout/CheckoutService.php src/Payments/PaymentIntentState.php tests/Unit/Services/CheckoutServiceTest.php`

## Status

- **Priority**: P1
- **Effort**: S
- **Risk**: LOW
- **Status**: DONE; focused verification passed, full suite exits nonzero on pre-existing deprecation/notices
- **Depends on**: none
- **Category**: bug
- **Planned at**: commit `c415494`, 2026-09-28

## Why this matters

Checkout is configured for GBP, but completion validates only payment success, customer metadata, and numeric amount. A succeeded payment in another currency can therefore be accepted as a GBP order when the minor-unit number matches. The fix must reject and refund the payment before the order-placement interface is called.

## Current state

- `src/Services/Checkout/CheckoutService.php` defines `CURRENCY = 'gbp'` and retrieves a `PaymentIntentState` containing `currency`, but `complete()` does not compare the field.
- `src/Payments/PaymentIntentState.php` exposes `public readonly string $currency`.
- `tests/Unit/Services/CheckoutServiceTest.php` already uses `FakeGateway`, `FakePlacer`, and paid intent fixtures; follow that style.
- Existing mismatch handling calls `refundAndFail()` and returns a `PaymentException` with the cart redirect. Preserve that behavior and do not create a second refund path.

## Commands

| Purpose | Command | Expected |
|---|---|---|
| Focused tests | `vendor/bin/phpunit tests/Unit/Services/CheckoutServiceTest.php` | All tests pass; includes the new currency mismatch test |
| Static analysis | `vendor/bin/phpstan analyse src/Services/Checkout/CheckoutService.php tests/Unit/Services/CheckoutServiceTest.php` | No new errors attributable to this change |
| Full tests | `vendor/bin/phpunit` | Existing suite passes without adding new failures |

## Scope

**In scope**
- `src/Services/Checkout/CheckoutService.php`
- `tests/Unit/Services/CheckoutServiceTest.php`
- `plans/README.md` status row

**Out of scope**
- Stripe gateway API behavior and webhook handling.
- Cart repricing, order persistence, database migrations, and UI copy.
- Formatting-only cleanup unrelated to the touched lines.

## Steps

### Step 1: Add the failing regression test

Add a test to `tests/Unit/Services/CheckoutServiceTest.php` that configures a succeeded intent for the correct customer and matching amount but uses a non-GBP currency. Assert that `PaymentException` is thrown, the fake placer has zero calls, and the gateway records one refund with the existing deterministic refund key.

**Verify**: `vendor/bin/phpunit tests/Unit/Services/CheckoutServiceTest.php --filter Currency` -> the new test fails because current checkout accepts the intent.

### Step 2: Validate currency before order placement

In `CheckoutService::complete()`, compare the retrieved intent currency with the configured checkout currency after success/customer validation and before `placePaidOrder()`. Normalize case consistently because Stripe currency codes are lowercase by convention but adapters may vary. Route mismatch through the existing refund-and-fail mechanism using the existing payment-mismatch semantics unless the repository’s message conventions require a dedicated message; do not silently continue.

**Verify**: `vendor/bin/phpunit tests/Unit/Services/CheckoutServiceTest.php --filter Currency` -> the new test passes and existing refund assertions remain green.

### Step 3: Run the focused quality checks

Run the focused PHPUnit and PHPStan commands above. Do not run formatters in write mode.

**Verify**: all focused checks complete; no source files outside the scope are modified.

## Test plan

- Succeeded, matching customer, matching amount, wrong currency: reject, refund once, place no order.
- Existing matching GBP payment: still places one order.
- Existing amount mismatch: still uses the existing refund path.
- Existing non-successful intent and wrong-customer cases: behavior remains unchanged.

## Done criteria

- [ ] Currency is checked before `placePaidOrder()`.
- [ ] Wrong-currency payments are refunded using the existing idempotency key.
- [ ] The new regression test passes.
- [ ] `vendor/bin/phpunit tests/Unit/Services/CheckoutServiceTest.php` passes.
- [ ] No unrelated files are modified.
- [ ] `plans/README.md` marks this plan DONE only after review.

## STOP conditions

- The payment gateway does not reliably provide a currency string.
- The existing refund path cannot distinguish currency mismatch without changing an out-of-scope public contract.
- The current test fixture or checkout flow has drifted from the excerpts above.
- A fix requires changing Stripe webhook handling or database schema.

## Maintenance notes

Any future currency configuration must update both payment-intent creation and completion validation. Review tests whenever supported currencies or multi-currency checkout are introduced.
