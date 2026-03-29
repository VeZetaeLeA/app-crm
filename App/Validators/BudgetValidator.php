<?php
namespace App\Validators;

use Core\Validator as BaseValidator;

/**
 * Budget Validator
 * Validaciones específicas para presupuestos
 */
class BudgetValidator
{
    private $validator;
    private $errors = [];

    public function __construct()
    {
        $this->validator = new BaseValidator();
    }

    /**
     * Valida datos para crear un presupuesto
     */
    public function validateCreate(array $data): bool
    {
        $rules = [
            'ticket_id' => 'required|numeric',
            'title' => 'required|min:5|max:200',
            'scope' => 'required|min:10',
            'timeline_weeks' => 'numeric',
            'subtotal' => 'required|numeric',
            'tax_rate' => 'numeric',
            'valid_days' => 'numeric'
        ];

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        // Validaciones adicionales
        if (isset($data['subtotal']) && $data['subtotal'] < 0) {
            $this->errors['subtotal'] = ['El subtotal no puede ser negativo'];
            return false;
        }

        if (isset($data['tax_rate']) && ($data['tax_rate'] < 0 || $data['tax_rate'] > 100)) {
            $this->errors['tax_rate'] = ['La tasa de impuesto debe estar entre 0 y 100'];
            return false;
        }

        return true;
    }

    /**
     * Valida item de presupuesto
     */
    public function validateItem(array $data): bool
    {
        $rules = [
            'description' => 'required|min:3|max:255',
            'type' => 'required|in:service,license,infrastructure,other',
            'quantity' => 'required|numeric',
            'unit_price' => 'required|numeric'
        ];

        $this->validator->validate($data, $rules);

        if ($this->validator->fails()) {
            $this->errors = $this->validator->errors();
            return false;
        }

        // Validaciones adicionales
        if (isset($data['quantity']) && $data['quantity'] <= 0) {
            $this->errors['quantity'] = ['La cantidad debe ser mayor a 0'];
            return false;
        }

        if (isset($data['unit_price']) && $data['unit_price'] < 0) {
            $this->errors['unit_price'] = ['El precio unitario no puede ser negativo'];
            return false;
        }

        return true;
    }

    /**
     * Valida cambio de estado de presupuesto
     */
    public function validateStatusChange(array $data): bool
    {
        $rules = [
            'budget_id' => 'required|numeric',
            'status' => 'required|in:draft,sent,approved,rejected'
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
