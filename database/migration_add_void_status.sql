-- SCRIPT DE MIGRACIÓN: Incorporación de estado "Anulado" para tickets
-- Ejecutar en MySQL / MariaDB

USE `Vezetaelea`;

-- 1. Actualizar el ENUM de la columna status en la tabla tickets
ALTER TABLE `tickets` 
MODIFY COLUMN `status` ENUM('open', 'in_analysis', 'budget_sent', 'budget_approved', 'budget_rejected', 'invoiced', 'payment_pending', 'active', 'closed', 'void') 
NOT NULL DEFAULT 'open';

-- 2. (Opcional) Si hay tickets existentes que deseen marcarse como anulados retroactivamente
-- UPDATE tickets SET status = 'void' WHERE subject LIKE '%ofrecer servicios%' OR subject LIKE '%spam%';
