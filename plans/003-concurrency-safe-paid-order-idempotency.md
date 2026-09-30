# Plan 003: Make Paid-Order Idempotency Concurrency-Safe

> **Executor instructions**: Follow this plan step by step. This plan addresses a concurrency correctness defect, not a general order refactor. Run every verification command and stop if the database adapter or transaction behavior differs from the assumptions below.
>
> **Drift check (run first)**: `git diff --stat c415494..HEAD -- src/Services/Tickets/BookingService.php src/Repositories/Contracts/Orders/OrderRepository.php src/Repositories/Eloquent/Orders/EloquentOrderRepository.php database/migrations/CreateOrdersTable.php tests/Unit/Services/BookingServiceTest.php tests/Integration/BookingServiceTest.php`

## Status

- **Priority**: P1
- **Effort**: M
- **Risk**: MED
- **Depends on**: none
- **Category**: bug
- **Planned at**: commit `c415494`, 2026-09-28

## Why this matters

Paid checkout can be retried or submitted concurrently. `BookingService` performs the existing-order lookup before its transaction, so two requests can both observe no order. The unique Stripe-payment constraint prevents duplicate rows, but the losing request receives a database exception instead of returning the already-created order, which turns a successful payment into a checkout failure.

## Current state

- `src/Services/Tickets/BookingService.php::placePaidOrder()` calls `findByStripePaymentId()` before `db->transaction()`.
- `src/Repositories/Eloquent/Orders/EloquentOrderRepository.php::findByStripePaymentId()` performs a normal query; `nextOrderNumber()` calculates `max(order_number) + 1`.
- `database/migrations/CreateOrdersTable.php` has a unique `stripe_payment_id` constraint and a unique `(customer_id, order_number)` constraint.
- `src/Services/Orders/OrderWriter.php` performs order creation, ticket reservation, accommodation locking, line persistence, and reward orchestration under the caller’s transaction.
- `tests/Unit/Services/BookingServiceTest.php` currently checks structure through reflection/source assertions; `tests/Integration/BookingServiceTest.php` is the nearest integration seam and should be extended with observable duplicate behavior where its fixtures allow it.
- The repository interface at `src/Repositories/Contracts/Orders/OrderRepository.php` is the module interface. Keep concurrency policy behind this seam rather than spreading database exception logic into controllers.

## Commands

| Purpose | Command | Expected |
|---|---|---|
| Focused unit tests | `vendor/bin/phpunit tests/Unit/Services/BookingServiceTest.php` | Existing tests and new idempotency tests pass |
| Focused integration tests | `vendor/bin/phpunit tests/Integration/BookingServiceTest.php` | Duplicate completion returns one stable order ID and does not duplicate side effects |
| Static analysis | `vendor/bin/phpstan analyse src/Services/Tickets/BookingService.php src/Repositories/Contracts/Orders/OrderRepository.php src/Repositories/Eloquent/Orders/EloquentOrderRepository.php tests/Unit/Services/BookingServiceTest.php tests/Integration/BookingServiceTest.php` | No new errors attributable to this change |
| Full tests | `vendor/bin/phpunit` | Existing suite passes without new failures |

## Scope

**In scope**
- `src/Services/Tickets/BookingService.php`
- `src/Repositories/Contracts/Orders/OrderRepository.php`
- `src/Repositories/Eloquent/Orders/EloquentOrderRepository.php`
- Relevant booking tests and fixtures
- `plans/README.md` status row

**Out of scope**
- Removing or weakening database uniqueness constraints.
- Rewriting order persistence or reward calculation.
- Changing payment/refund semantics except where needed to preserve a successful idempotent retry.
- Broad migration or architecture cleanup.

## Steps

### Step 1: Characterize duplicate completion behavior

Add a focused test at the repository/service seam that proves a second completion for the same payment intent returns the existing order ID and does not invoke order-writing side effects twice. Where a real concurrent database test is available, use two transactions or a controlled repository double to force the race. The test must distinguish an expected duplicate-key conflict from unrelated persistence errors.

**Verify**: `vendor/bin/phpunit tests/Unit/Services/BookingServiceTest.php tests/Integration/BookingServiceTest.php --filter Duplicate|Idempot` -> the new race/duplicate test fails or exposes the current exception behavior.

### Step 2: Move idempotency into the owning module

Implement the smallest repository/transaction design that makes payment-intent completion atomic. Acceptable shapes include an atomic repository operation or a transaction-local lookup plus narrow handling of the unique `stripe_payment_id` conflict followed by a re-read. Do not catch every database exception. Ensure the winner creates one order, the loser returns that order, and neither path duplicates inventory reservations or rewards.

Also review `nextOrderNumber()` under the existing unique `(customer_id, order_number)` constraint. If the selected implementation does not make order numbering safe, add a narrowly scoped strategy or document a STOP condition rather than silently leaving the same race in a new path.

**Verify**: focused unit/integration commands -> duplicate completion returns the same ID, side effects occur once, and unrelated persistence exceptions still propagate.

### Step 3: Validate transaction and failure behavior

Run the full focused suite and inspect the diff for scope. Confirm rollback behavior remains intact when inventory or accommodation persistence fails, and confirm a different payment intent still creates a separate order.

**Verify**: `vendor/bin/phpunit tests/Unit/Services/BookingServiceTest.php tests/Integration/BookingServiceTest.php` -> all pass; no out-of-scope files changed.

## Test plan

- First completion for a payment intent creates one order.
- Repeated sequential completion returns the existing order ID.
- Two concurrent/simulated completions for one payment intent result in one order and one set of inventory/reward side effects.
- A different payment intent remains independent.
- An unrelated database failure is not swallowed as an idempotent duplicate.
- Existing transaction rollback tests remain green.

## Done criteria

- [ ] Payment-intent idempotency is enforced behind the order repository/transaction seam.
- [ ] Duplicate completion returns the existing order instead of leaking a uniqueness exception.
- [ ] Inventory, line creation, and reward side effects occur once.
- [ ] Unrelated persistence errors still propagate.
- [ ] Existing uniqueness constraints remain intact.
- [ ] Focused and full tests pass.
- [ ] `plans/README.md` marks this plan DONE only after review.

## STOP conditions

- The available database/test environment cannot reproduce or deterministically simulate the race.
- The database driver does not expose a reliable way to identify the expected unique conflict without risking swallowed failures.
- Correctness requires changing payment/refund behavior outside the scoped modules.
- The current transaction or repository interface has drifted from the excerpts above.

## Maintenance notes

Payment-intent uniqueness and order-number allocation are concurrency contracts. Any future change to order creation, retries, queue processing, or reward awarding must preserve one successful order and one set of side effects per payment intent.
