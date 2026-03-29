<?php
namespace Core\Queue;

/**
 * RF-06: Interfaz para Jobs en cola
 */
interface JobInterface
{
    /**
     * Ejecuta la lógica del trabajo
     * 
     * @param array $payload Datos necesarios para el trabajo
     * @return void
     */
    public function handle(array $payload): void;
}
