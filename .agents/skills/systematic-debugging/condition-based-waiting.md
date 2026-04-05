# Condition-Based Waiting

When an operation takes time to complete, don't use arbitrary `wait` or `sleep` commands. Use condition polling to wait until the task is actually finished.

## The Problem: Arbitrary Wait Times

- `wait 2 seconds` might be too short on a slow machine, causing intermittent failures.
- `wait 10 seconds` might be too long on a fast machine, wasting time and slowing down tests.
- Static wait times make tests brittle and hard to maintain across environments.

## The Solution: Condition Polling

1. **Wait Until Condition is Met**
   - Poll for a specific state change (e.g., file exists, API response success, status = 'done').
   - Use a timeout to prevent infinite loops.

2. **Wait Until Condition is NO LONGER Met**
   - Poll until a state changes from one value to another (e.g., status != 'running').

3. **Combined Approach**
   - Wait for a specific status *within* a time limit.

## Example: Waiting for an API Response

**Fixed wait:** `wait 5000ms` for the response to arrive.

**Condition-based wait:**
- Send request.
- Poll the response object every 500ms.
- Success if response is non-null.
- Fail if 10 seconds pass without a response.

## Why Condition-Based Waiting Matters

- **Consistency**: Works the same way on both fast and slow machines.
- **Speed**: Only waits as long as necessary, making the development and testing cycle faster.
- **Reliability**: Eliminates race conditions and flaky tests caused by timing issues.
- **Debuggability**: Clear timeout messages tell you *what* condition wasn't met.
