ALTER TABLE audit_logs ADD COLUMN request_id CHAR(16) AFTER id;
CREATE INDEX idx_audit_logs_request_id ON audit_logs(request_id);
