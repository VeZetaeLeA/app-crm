**Load this reference when:** writing or changing tests, adding mocks, or tempted to add test-only methods to production code.

## Overview
Tests must verify real behavior, not mock behavior. Mocks are a means to isolate, not the thing being tested.

**Core principle:** Test what the code does, not what the mocks do.

**Following strict TDD prevents these anti-patterns.**

## The Iron Laws
```
1. NEVER test mock behavior
2. NEVER add test-only methods to production classes
3. NEVER mock without understanding dependencies
```

## Anti-Pattern 1: Testing Mock Behavior
**The violation:** Asserting on mock test IDs (e.g., `sidebar-mock`) instead of real component behavior.

**Why this is wrong:**
- You're verifying the mock works, not that the component works
- Test passes when mock is present, fails when it's not
- Tells you nothing about real behavior

**The fix:** Test real component or unmock it. If sidebar must be mocked for isolation, don't assert on the mock — test Page's behavior with sidebar present.

## Anti-Pattern 2: Test-Only Methods in Production Classes
**The violation:** Adding `destroy()` or cleanup methods to production classes that are only called in tests.

**Why this is wrong:**
- Production class polluted with test-only code
- Dangerous if accidentally called in production
- Violates YAGNI and separation of concerns

**The fix:** Put cleanup logic in test utilities (`test-utils/`) instead of production classes.

## Anti-Pattern 3: Mocking Without Understanding Dependencies
**The violation:** Mocking a high-level method that has side effects the test depends on.

**Why this is wrong:**
- Mocked method may have side effects test depended on
- Over-mocking to "be safe" breaks actual behavior
- Test passes for wrong reason or fails mysteriously

**The fix:**
```
BEFORE mocking any method:
  STOP - Don't mock yet

  1. Ask: "What side effects does the real method have?"
  2. Ask: "Does this test depend on any of those side effects?"
  3. Ask: "Do I fully understand what this test needs?"

  IF depends on side effects:
    Mock at lower level (the actual slow/external operation)
    NOT the high-level method the test depends on
```

## Anti-Pattern 4: Incomplete Mocks
**The violation:** Creating mock responses with only the fields you think you need, missing fields downstream code uses.

**The Iron Rule:** Mock the COMPLETE data structure as it exists in reality, not just fields your immediate test uses.

## Anti-Pattern 5: Integration Tests as Afterthought
Testing is part of implementation, not optional follow-up. Can't claim complete without tests.

## When Mocks Become Too Complex
**Warning signs:**
- Mock setup longer than test logic
- Mocking everything to make test pass
- Mocks missing methods real components have
- Test breaks when mock changes

**Consider:** Integration tests with real components often simpler than complex mocks

## TDD Prevents These Anti-Patterns
1. **Write test first** → Forces you to think about what you're actually testing
2. **Watch it fail** → Confirms test tests real behavior, not mocks
3. **Minimal implementation** → No test-only methods creep in
4. **Real dependencies** → You see what the test actually needs before mocking

## Quick Reference
| Anti-Pattern | Fix |
|--------------|-----|
| Assert on mock elements | Test real component or unmock it |
| Test-only methods in production | Move to test utilities |
| Mock without understanding | Understand dependencies first, mock minimally |
| Incomplete mocks | Mirror real API completely |
| Tests as afterthought | TDD - tests first |
| Over-complex mocks | Consider integration tests |

## Red Flags
- Assertion checks for `*-mock` test IDs
- Methods only called in test files
- Mock setup is >50% of test
- Test fails when you remove mock
- Can't explain why mock is needed
- Mocking "just to be safe"

## The Bottom Line
**Mocks are tools to isolate, not things to test.**

If TDD reveals you're testing mock behavior, you've gone wrong.

Fix: Test real behavior or question why you're mocking at all.
