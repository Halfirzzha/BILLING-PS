# Phase 3 — Session Engine + Auto-stop Implementation Plan

> Executed inline (executing-plans). TDD, complete code, per-task commits.

**Goal:** Play sessions consume the member's time balance and **auto-stop** when time runs out; stations transition idle↔active and emit device commands.

**Architecture:** `App\Services\SessionService` handles start/end/expiry, all atomic. Start records `planned_end_at = now + remaining_minutes`. Two-layer auto-stop: (a) a delayed queued job `EndExpiredSession` dispatched at `planned_end_at`; (b) a scheduled sweep `sessions:auto-stop` every minute as backstop. Ending a session writes a `session_debit` time-ledger row (never exceeding available minutes → no overage) and returns the station to idle. Each transition enqueues a `StationCommand` (`refresh_state`) for the device layer (Phase 6).

## Global Constraints

- One active session per station and per user (enforced on start).
- Time debit on end = `min(elapsedMinutes, availableMinutes)` → prepaid, no negative balance.
- All mutations atomic (`DB::transaction`).
- Station: `active`+`session` while playing; `idle`+`qr` when free.

## File structure

```
app/Models/PlaySession.php                 (add elapsedMinutes helper)
app/Services/SessionService.php            (new)
app/Jobs/EndExpiredSession.php             (new)
app/Console/Commands/AutoStopSessions.php  (new)
routes/console.php                         (schedule sweep every minute)
tests/Feature/Session/SessionStartTest.php (new)
tests/Feature/Session/SessionEndTest.php   (new)
tests/Feature/Session/AutoStopTest.php     (new)
```

## Interfaces

- `PlaySession::elapsedMinutes(?Carbon $at = null): int` — `ceil(seconds/60)`, min 0.
- `SessionService::startSession(User $user, Station $station, ?User $operator = null, ?string $notes = null): PlaySession`
- `SessionService::endSession(PlaySession $session, ?User $operator = null): PlaySession`
- `SessionService::stopExpiredSessions(): int` — ends all active sessions past `planned_end_at`, returns count.

## Tasks

### Task 1: PlaySession::elapsedMinutes + SessionService::startSession
- Validations: station active, not maintenance, station free, user free, `remaining_minutes > 0`.
- Effects: create active session (`started_with_minutes`, `planned_end_at`), station→active/session + `current_session_id`, enqueue `refresh_state` command, dispatch `EndExpiredSession` delayed to `planned_end_at`.
- Tests: SessionStartTest — success + each validation throws.

### Task 2: SessionService::endSession
- Debit `min(elapsed, available)` as `session_debit`; session→completed with consumed/debited; station→idle/qr, `current_session_id=null`; enqueue command.
- Tests: SessionEndTest — debits correct minutes, station reset, double-end throws.

### Task 3: Auto-stop (job + sweep command + schedule)
- `EndExpiredSession` job ends the session if still active and expired.
- `AutoStopSessions` command calls `stopExpiredSessions`.
- Schedule `sessions:auto-stop` everyMinute.
- Tests: AutoStopTest — expired session ends & debits full time & station idle; not-yet-expired stays active; command runs.

## Self-Review
- Covers spec §6.1 (start/end), §6.2 (2-layer auto-stop), station lifecycle. Extend-during-session deferred (needs portal/Phase 5). Device command emitted here; actual dispatch = Phase 6.
