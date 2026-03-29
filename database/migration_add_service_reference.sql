-- Migration: Add service_reference to budgets and invoices
-- Description: Adds a column to store the "Pillar - Service" reference string.

USE Vezetaelea;

-- Add column to budgets
ALTER TABLE budgets ADD COLUMN service_reference VARCHAR(255) AFTER title;

-- Add column to invoices
ALTER TABLE invoices ADD COLUMN service_reference VARCHAR(255) AFTER budget_id;

-- Optional: Populate existing budgets/invoices with a default or extracted value if possible
-- For now, we'll leave them NULL or let them be empty.
