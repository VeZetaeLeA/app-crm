# Defense-in-Depth

When a root cause is identified, adding validation at multiple layers prevents similar issues from propagating in the future. Don't just fix it at the source—harden the system.

## The Strategy: Layered Defense

1. **Layer 1: Input Validation**
   - Validate data as soon as it enters the system (API, CLI, forms).
   - Use schemas and type checking to ensure valid data at the boundary.

2. **Layer 2: Internal Boundaries**
   - Add checks between major internal components (controllers, services, repositories).
   - Don't assume that data passed between internal parts is "clean" if it has been modified.

3. **Layer 3: Core Logic**
   - Add defensive checks within functions that perform critical operations.
   - Use assertions or error handling to abort early if state is inconsistent.

## Example: Database Injection Prevention

**Fix:** Escape user input in SQL queries.

**Defense-in-Depth:**
- **Layer 1:** Validate the format of the user input (e.g., must be a number).
- **Layer 2:** Use parameterized queries (prepared statements) to prevent injection.
- **Layer 3:** Add database-level constraints (e.g., check constraints or foreign keys).

## Why Defense-in-Depth Matters

- **Resilience to Future Changes**: If one layer is accidentally modified or removed, subsequent layers still provide protection.
- **Early Error Detection**: Issues are caught sooner, making them easier to debug and reducing impact.
- **Clear Boundaries**: Establishing validation at each layer makes the system's contract explicit.
- **Security and Integrity**: Protects against unexpected or malicious input that might bypass a single layer of defense.
