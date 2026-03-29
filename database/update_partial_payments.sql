ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'unpaid', 'processing', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'unpaid';
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER total;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS paid_at TIMESTAMP NULL DEFAULT NULL AFTER status;
