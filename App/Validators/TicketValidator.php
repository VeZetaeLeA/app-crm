<?php
namespace App\Validators;

use Core\Validator as BaseValidator;

/**
 * Ticket Validator
 * Validaciones específicas para tickets
 */
class TicketValidator
{
    private $validator;
    private $errors = [];

    public function __construct()
    {
        $this->validator = new BaseValidator();
    }

    /**
     * Valida datos para crear un ticket
     */
    public function validateCreate(array $data): bool
    {
        $rules = [
            'subject' => 'required|min:5|max:200',
            'description' => 'required|min:10',
            'service_plan_id' => 'required|numeric',
            'priority' => 'in:low,normal,high,urgent'
        ];

        // Si es un ticket público (sin autenticación)
        if (!isset($data['client_id'])) {
            $rules['name'] = 'required|min:3|max:100';
            $rules['email'] = 'required|email';
            $rules['phone'] = 'min:8|max:20';
            $rules['company'] = 'max:100';
        }

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Valida cambio de estado
     */
    public function validateStatusChange(array $data): bool
    {
        $rules = [
            'ticket_id' => 'required|numeric',
            'status' => 'required|in:open,in_analysis,budget_sent,budget_approved,budget_rejected,invoiced,payment_pending,active,closed'
        ];

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Valida asignación de ticket
     */
    public function validateAssignment(array $data): bool
    {
        $rules = [
            'ticket_id' => 'required|numeric',
            'assigned_to' => 'required|numeric'
        ];

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Valida mensaje de chat
     */
    public function validateMessage(array $data): bool
    {
        $rules = [
            'ticket_id' => 'required|numeric',
            'message' => 'required|min:1|max:5000'
        ];

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Valida cambio de prioridad
     */
    public function validatePriorityChange(array $data): bool
    {
        $rules = [
            'ticket_id' => 'required|numeric',
            'priority' => 'required|in:low,normal,high,urgent'
        ];

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return true;
    }

    /**
     * Obtiene los errores de validación
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene el primer error de un campo
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}
