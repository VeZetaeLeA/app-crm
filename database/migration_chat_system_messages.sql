-- ============================================================
-- Migration: Allow NULL user_id in chat_messages for system/bot messages
-- Date: 2026-03-07
-- ============================================================

-- Step 1: Drop existing FK constraint
ALTER TABLE chat_messages DROP FOREIGN KEY fk_chat_user;

-- Step 2: Make user_id nullable (needed for system/bot messages with no real user)
ALTER TABLE chat_messages MODIFY user_id INT(10) UNSIGNED NULL;

-- Step 3: Re-add FK with ON DELETE SET NULL so records survive user deletion
ALTER TABLE chat_messages
    ADD CONSTRAINT fk_chat_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;
