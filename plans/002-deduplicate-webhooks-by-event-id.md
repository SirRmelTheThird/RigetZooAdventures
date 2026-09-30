# Plan 002: Deduplicate Webhooks by Event Identity

> **Executor instructions**: Follow this plan step by step. Run each verification command before continuing. Stop and report if the event identity contract is different from the current state described here.
>
> **Drift check (run first)**: `git diff --stat c415494..HEAD -- src/Services/Checkout/PaymentWebhookHandler.php src/Payments/WebhookEvent.php tests/Unit/Services/PaymentServiceTest.php`

## Status

- **Priority**: P1
- **Effort**: S
- **Risk**: LOW
- **Status**: DONE; focused verification passed, full suite exits nonzero on pre-existing deprecation/notices
- **Depends on**: none
- **Category**: bug
- **Planned at**: commit `c415494`, 2026-09-28

## Why this matters

Stripe event identity and payment-intent identity are different concepts. The handler currently uses the intent ID as its replay key, so two distinct lifecycle events for one intent can suppress each other. Deduplication must ignore only a replay of the same verified event, not a later event with a different event ID.

## Current state

- `src/Payments/WebhookEvent.php` contains both `eventId` and `intentId`.
- `src/Payments/WebhookEventType.php` defines `payment_intent.succeeded` and `payment_intent.payment_failed`.
- `src/Services/Checkout/PaymentWebhookHandler.php` currently sets `$key = $event->intentId ?? md5($payload)` and records it before dispatching the event handler.
- `tests/Unit/Services/PaymentServiceTest.php` currently verifies replay only indirectly with `assertTrue(true)` and should be strengthened without changing external Stripe behavior.
- `DiscordNotificationService` is the existing notification interface used by the handler; preserve its public methods.

## Commands

| Purpose | Command | Expected |
|---|---|---|
| Focused tests | `vendor/bin/phpunit tests/Unit/Services/PaymentServiceTest.php` | All webhook tests pass, including distinct-event coverage |
| Static analysis | `vendor/bin/phpstan analyse src/Services/Checkout/PaymentWebhookHandler.php tests/Unit/Services/PaymentServiceTest.php` | No new errors attributable to this change |
| Full tests | `vendor/bin/phpunit` | Existing suite passes without new failures |

## Scope

**In scope**
- `src/Services/Checkout/PaymentWebhookHandler.php`
- `tests/Unit/Services/PaymentServiceTest.php`
- `plans/README.md` status row

**Out of scope**
- Stripe signature verification and gateway parsing.
- Durable cross-process replay storage; that is a separate reliability/security decision.
- Discord transport behavior and unrelated logging/formatting changes.

## Steps

### Step 1: Add regression coverage for event identity

Replace the current no-op replay assertion with observable notification assertions using the existing test doubles or a narrowly scoped spy/transport. Cover these cases:

1. The exact same event ID delivered twice triggers one notification.
2. Two events with different event IDs, the same intent ID, and different event types are both processed.

Keep the payload/signature parsing seam mocked as the current test does; do not call Stripe or a real network endpoint.

**Verify**: `vendor/bin/phpunit tests/Unit/Services/PaymentServiceTest.php --filter Webhook` -> the distinct-event test fails against the current intent-key implementation.

### Step 2: Key replay tracking by verified event ID

Change the handler’s replay key to use `WebhookEvent::$eventId`, retaining a deterministic fallback only if the interface permits an empty event ID. The key must distinguish event IDs even when `intentId` is identical. Preserve unsupported-event ignoring and invalid-signature behavior.

**Verify**: `vendor/bin/phpunit tests/Unit/Services/PaymentServiceTest.php --filter Webhook` -> same-event replay is ignored and distinct events are both handled.

### Step 3: Run focused quality checks

Run the focused PHPUnit and PHPStan commands above. Confirm only the scoped implementation/test files and plan index changed.

**Verify**: all focused checks complete and no source files outside scope are modified.

## Test plan

- Same event ID, same intent ID, same type: exactly one notification.
- Different event IDs, same intent ID, succeeded then failed: both notifications are processed.
- Unsupported event type: ignored as before.
- Invalid webhook: exception remains rethrown as before.

## Done criteria

- [ ] Replay key is based on event identity, not intent identity.
- [ ] Same-event replay is ignored.
- [ ] Distinct lifecycle events for one intent are both processed.
- [ ] `vendor/bin/phpunit tests/Unit/Services/PaymentServiceTest.php` passes.
- [ ] No network call or Stripe contract is introduced in tests.
- [ ] `plans/README.md` marks this plan DONE only after review.

## STOP conditions

- The payment adapter cannot guarantee a stable verified event ID.
- Existing consumers depend on intent-level suppression for a documented reason.
- Making notification behavior observable requires modifying out-of-scope production adapters.
- The current handler lifecycle differs materially from the excerpts above.

## Maintenance notes

If durable webhook processing is later introduced, persist Stripe event IDs with a uniqueness constraint and make processing state explicit. Do not revert to payment-intent ID as the deduplication identity.
