# Root Cause Tracing

When an error occurs deep in a call stack, identifying the true origin is critical. Don't just fix the symptom at the crash site—trace it back to the original source.

## The Process: Backward Tracing

1. **Start at the Incident Site**
   - Identify the exact line where the error occurred.
   - Note the value that caused the failure (e.g., `null`, `undefined`, empty string, invalid object).

2. **Step Backward (One Frame at a Time)**
   - For each frame in the stack trace, ask:
     - "Where did this value come from?"
     - "Who passed this value to this function?"
     - "Was it modified within this frame?"

3. **Analyze the Data Flow**
   - Keep stepping back through the frames until you find the point where the value was *different* from what you expected.
   - The first point where the value deviates from expectation is the **Root Cause**.

## Example: Null Pointer Exception

**Crash site:** `UserDashboard.render()` fails because `user.profile` is `null`.

**Trace backward:**
- `UserDashboard.render(user)` called by `AppController.showDashboard(userId)`.
- `AppController.showDashboard(userId)` gets user from `UserRepository.findById(userId)`.
- `UserRepository.findById(userId)` fetches from database.
- Database returns `null` for that `userId`.

**Root cause:** The user ID being used doesn't exist in the database, but the system assumes it does.

**Fix:** Handle the missing user at the repository or controller level, or investigate why an invalid ID was generated.

## Tips for Effective Tracing

- **Use Debugging Tools**: Stepping through code with a debugger is much faster than manual tracing.
- **Log Data at Boundaries**: Logging data as it enters and leaves major components helps pinpoint where it goes "bad."
- **Check External Inputs**: Validating data from APIs, databases, and user input often reveals the root cause of "mysterious" errors.
- **Don't Assume**: Even if a function "always works," verify its inputs and outputs when tracing.
