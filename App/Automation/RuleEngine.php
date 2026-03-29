<?php
namespace App\Automation;

use Core\Database;
use PDO;

/**
 * Rule Engine
 * Evaluates automation rules and executes corresponding actions.
 */
class RuleEngine
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Run all active rules for a specific event trigger.
     */
    public function run(string $eventTrigger, array $context = []): void
    {
        $stmt = $this->db->prepare("SELECT * FROM automation_rules WHERE event_trigger = ? AND is_active = 1");
        $stmt->execute([$eventTrigger]);
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rules as $rule) {
            if ($this->evaluateConditions(json_decode($rule['conditions'], true), $context)) {
                $this->executeActions(json_decode($rule['actions'], true), $context, $rule['id']);
            }
        }
    }

    /**
     * Evaluate rule conditions based on context.
     */
    private function evaluateConditions(array $conditions, array $context): bool
    {
        // Example: if condition is {"idle_hours": 48}, check if context['idle_hours'] >= 48
        foreach ($conditions as $key => $targetValue) {
            if (!isset($context[$key])) {
                return false;
            }

            if ($context[$key] < $targetValue) {
                return false;
            }
        }
        return true;
    }

    /**
     * Execute rule actions.
     */
    private function executeActions(array $actions, array $context, int $ruleId): void
    {
        foreach ($actions as $actionData) {
            $actionClass = $actionData['action_class'] ?? null;
            if ($actionClass && class_exists($actionClass)) {
                try {
                    $action = new $actionClass();
                    $result = $action->execute($actionData['params'] ?? [], $context);

                    $this->logExecution($ruleId, $context['entity_id'] ?? 0, 'success', json_encode($result));
                } catch (\Exception $e) {
                    $this->logExecution($ruleId, $context['entity_id'] ?? 0, 'failed', $e->getMessage());
                }
            }
        }
    }

    /**
     * Log the outcome of a rule execution.
     */
    private function logExecution(int $ruleId, int $entityId, string $status, string $result): void
    {
        $stmt = $this->db->prepare("INSERT INTO automation_logs (rule_id, entity_id, status, result) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ruleId, $entityId, $status, $result]);
    }
}
