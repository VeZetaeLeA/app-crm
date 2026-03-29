-- Migration: Create Automation Engine Tables
-- Description: Stores rules and logs for the automated process engine.

CREATE TABLE IF NOT EXISTS automation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    event_trigger VARCHAR(100) NOT NULL, -- e.g., 'ticket_idle', 'invoice_overdue'
    conditions JSON NOT NULL, -- e.g., {"days": 2, "priority": "high"}
    actions JSON NOT NULL, -- e.g., [{"type": "notify_staff", "template": "ticket_idle_warn"}]
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS automation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_id INT NOT NULL,
    entity_id INT NOT NULL, -- ID of the ticket/invoice/user processed
    status ENUM('success', 'failed') NOT NULL,
    result TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rule_id) REFERENCES automation_rules(id) ON DELETE CASCADE
);

-- Seed an example rule: Notify staff if ticket is idle for more than 48h
INSERT INTO automation_rules (name, description, event_trigger, conditions, actions) 
VALUES (
    'Ticket Idle Warning', 
    'Notifica al staff si un ticket lleva más de 48h sin respuesta.', 
    'ticket_idle', 
    '{"idle_hours": 48}', 
    '[{"action_class": "App\\Automation\\Actions\\NotifyStaff", "params": {"template": "idle_alert"}}]'
);
