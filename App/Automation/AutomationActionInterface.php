<?php
namespace App\Automation;

/**
 * Trigger Interface
 * Interface for automation actions that can be executed by the RuleEngine.
 */
interface AutomationActionInterface
{
    /**
     * Execute the specific action.
     * @param array $params Configuration parameters for the action.
     * @param array $context Data context for the action (e.g., ticket data).
     * @return mixed Result of the action.
     */
    public function execute(array $params, array $context);
}
