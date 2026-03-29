<?php
namespace Core\Exceptions;

/**
 * RF-10: Excepción personalizada para errores de negocio controlados.
 */
class AppException extends \Exception
{
    protected array $details = [];
    protected int $statusCode = 400;

    public function __construct(string $message, int $statusCode = 400, array $details = [])
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
