# AI Agent Coding Style Guide

**Purpose:** paste this file into any AI coding agent's context (Claude Code, Cursor, etc.) before it writes or edits code in this codebase. It is a set of hard rules, not suggestions — an agent should refuse to write code that violates them and flag it instead.

Applies to any language; PHP/Laravel examples are given where stack-specific detail helps.

---

## 1. Hard "never do this" rules

| Rule | Why |
|---|---|
| No ternary operators (`?:`) | Ternaries hide branching logic inline and get nested into unreadable one-liners. Use an `if` or extract a named method. |
| No fallback operators as a substitute for validation (`??`, `?->`, `?:`) | A fallback that silently swallows a missing/null value hides bugs instead of surfacing them. See §2. |
| No re-checking something already checked | If a Form Request / validator / guard has already confirmed a value exists and is valid, downstream code must not re-guard it — that's dead defensive code, not safety. |
| No premature design patterns | Don't reach for Factory/Strategy/Observer/etc. until there are genuinely 2+ concrete cases that need it (Rule of Three). One implementation behind an interface "for future flexibility" is bloat, not architecture. |

---

## 2. Replace fallbacks with structural fixes, not deletions

Don't just delete a `??`/`?->` — replace whatever problem it was papering over:

| Situation | Instead of `??` / `?->` | Use |
|---|---|---|
| Value should always exist by the time this code runs (env var, required config, non-nullable column) | `config('mail.from') ?? 'noreply@...'` | Fail fast: throw a named exception at boot/request-start if it's missing. Never let it resolve silently to a default deep in business logic. |
| Value is genuinely optional (nullable relationship, external API field) | `$user->profile?->bio ?? 'No bio'` scattered everywhere | Null Object pattern — give `Profile` (or a `GuestProfile`) a default implementation so callers never branch on null. |
| Optionality needs to be resolved once, not re-checked at every layer | Repeated `?->` down a call chain | Resolve it once at the boundary (Form Request / Resource) into a DTO with non-nullable, typed properties. Everything downstream trusts the type. |
| A missing env var should be caught immediately | `env('STRIPE_KEY')` used raw, failing later with a vague error | A boot-time config validator that throws `MissingEnvironmentVariableException: STRIPE_KEY is not set` naming the exact key. |

---

## 3. Guard clauses (inverted conditionals) — always

Return/throw early on the invalid path; keep the success path unindented and last.

```php
// Bad — nested happy path
public function book(Booking $booking): void
{
    if ($booking->isConfirmed()) {
        if ($booking->hasCapacity()) {
            $this->process($booking);
        }
    }
}

// Good — guard clauses
public function book(Booking $booking): void
{
    if (! $booking->isConfirmed()) {
        throw new BookingNotConfirmedException($booking->id);
    }

    if (! $booking->hasCapacity()) {
        throw new BookingCapacityExceededException($booking->id);
    }

    $this->process($booking);
}
```

---

## 4. SOLID — applied, not recited

- **S — Single Responsibility.** One Action/Service class does one business operation. A Form Request only validates. A Model only represents data + relationships, no business rules.
- **O — Open/Closed.** New behavior extends via a new class implementing an existing interface, not by editing a growing `switch`/`match` in an existing class.
- **L — Liskov Substitution.** Any implementation of an interface must be swappable without the caller knowing — no implementation throws on an input the interface contract allows.
- **I — Interface Segregation.** Small, purpose-specific interfaces. A `Worker` interface with `work()`, `eat()`, `sleep()` should split into `Workable`, `Eatable`, `Sleepable` — a class shouldn't be forced to implement methods it doesn't need.
- **D — Dependency Inversion.** Depend on interfaces, inject via constructor. A `Switch` class should depend on a `Switchable` interface, not directly on a concrete `LightBulb` — that's what makes it swappable for any other switchable device.

---

## 5. Composition over inheritance

Default to composition. Only use inheritance when the relationship is genuinely "is-a" **and** the base class is stable (won't need per-subclass special-casing later).

```php
// Prefer: compose behavior via injected interface
final class Switch
{
    public function __construct(private readonly Switchable $device) {}

    public function operate(): void
    {
        $this->device->toggle();
    }
}

// Over: inheritance that locks you into one hierarchy
abstract class Device { abstract public function toggle(): void; }
class LightBulbDevice extends Device { /* ... */ }
```

Abstract base classes are still the right call when multiple implementations genuinely share both a contract *and* real shared logic (e.g. an abstract `Database` class with `connect()`/`query()`, extended by `SQLDatabase`/`OracleDatabase`). Reach for that only when the shared logic is real, not just a shared method signature.

---

## 6. DRY — but not at the cost of clarity

Extract duplication into a shared method/class **once the same logic appears 3+ times**, or when duplicated logic represents one business rule (e.g. "how we calculate a booking's total price" should exist in exactly one place). Don't extract two coincidentally-similar three-line blocks into a shared helper if they represent unrelated concepts — that creates false coupling.

---

## 7. Separation of concerns — layering

```
Controller        → HTTP only: validate input, call one Action/Service, return a response
Action / Service   → business logic, one operation per class
Repository (opt.)  → wraps Eloquent/query builder so business logic doesn't depend on it directly
DTO / Value Object → structured, typed data passed between layers
Model              → data + relationships only, no business rules
Events / Jobs       → anything not required for the immediate response
```

An AI agent should refuse to put business logic directly in a Controller or a Model's `boot()`/model events beyond simple, unconditional side effects.

---

## 8. All non-database literals get named and separated

Anything inside quotation marks — string literal, magic number, status code, array key used as a "type" — must not live inline in business logic. Extract to:

- **Enums** (PHP 8.1+ backed enums) for fixed value sets: statuses, roles, types.
- **Config files** (`config/*.php`) for anything environment- or deployment-specific.
- **Constants classes/interfaces** for fixed keys used across the codebase (e.g. cache keys, session keys, event names).

```php
// Bad
if ($booking->status === 'confirmed') { ... }
Cache::put('user_' . $id . '_profile', $data);

// Good
if ($booking->status === BookingStatus::Confirmed) { ... }
Cache::put(CacheKey::UserProfile->forUser($id), $data);
```

This is not about extracting *every* string — a one-off log message or a route definition doesn't need a constant. It's about anything that (a) represents a business concept, or (b) is repeated more than once.

---

## 9. Separation of data and business logic

- Data shape lives in DTOs / Value Objects / Eloquent Models.
- Rules about that data live in Actions/Services.
- A DTO/Model should never contain a method like `calculateDiscountedPrice()` that encodes a business rule — that belongs in a `PricingService`, which takes the DTO as input.

---

## 10. Design patterns — only the three categories, only when earned

| Category | Use when | Example (only if genuinely needed) |
|---|---|---|
| **Creational** | Object construction is non-trivial or must vary by runtime config | Factory for choosing a payment gateway implementation at runtime |
| **Structural** | You need to adapt/wrap an external dependency without leaking its shape into your domain | Adapter wrapping a third-party SDK behind your own interface |
| **Behavioural** | You have 2+ interchangeable algorithms, or need decoupled event reactions | Strategy for interchangeable pricing rules; Laravel Events/Listeners as Observer for decoupled side effects |

**Do not** apply a pattern speculatively. If there's only one implementation today and no concrete second one planned, use the plain class — add the interface/pattern when the second implementation actually arrives.

---

## 11. Horizontal scalability

- No mutable static/singleton state that holds per-request data — every node must be able to serve any request statelessly.
- Sessions and cache go through Redis (or equivalent), never file/array drivers.
- Anything not required for the immediate HTTP response (emails, notifications, third-party sync) goes through a queued Job.
- Cache read-heavy queries at the repository layer with explicit invalidation on write — don't let cache and DB silently drift.
- Group logic by domain module (e.g. `app/Domain/Bookings/`, `app/Domain/Zoo/`) rather than Laravel's flat default split, so a module can be peeled out into its own service later without a rewrite.

---

## 12. Encapsulation

- Properties are `private`/`protected` by default; expose behavior through methods, not public property mutation.
- A class's internal state changes only through its own methods — no reaching into another object's internals to mutate it directly.

---

## 13. Self-check before submitting code (for the AI agent)

Before returning code, verify:
- [ ] No ternary operators
- [ ] No `??`/`?->` used as a substitute for real validation (see §2 table)
- [ ] No check duplicated after an earlier check already covered it
- [ ] Guard clauses used instead of nested conditionals
- [ ] No pattern/abstraction introduced with only one implementation behind it
- [ ] No string/magic-value literal repeated or representing a business concept without being named
- [ ] Business logic is not inside a Controller, Model, or DTO
- [ ] New code depends on interfaces, not concrete classes, where more than one implementation is plausible
