<?php
namespace App\Repositories;

interface InvoiceRepositoryInterface extends RepositoryInterface
{
    public function getApprovedBudget(int $budgetId): ?array;
    public function hasInvoiceForBudget(int $budgetId): bool;
    public function getClientIdByTicket(int $ticketId): ?int;
    public function createInvoice(array $data): int;
    public function updateTicketStatus(int $ticketId, string $status): bool;
    public function getPendingPaymentReceiptsSum(int $invoiceId): float;
    public function updateInvoicePayment(int $invoiceId, string $status, float $paidAmount, bool $isFullyPaid): bool;
    public function verifyPendingReceipts(int $invoiceId, int $verifiedBy): bool;
    public function getInvoiceWithBudgetDetails(int $invoiceId): ?array;
    public function getServicePlanIdByTicket(int $ticketId): ?int;
    public function hasActiveServiceForInvoice(int $invoiceId): bool;
    public function createActiveService(array $data): int;
    public function findInvoiceById(int $invoiceId): ?array;
    public function getAll(array $filters = []): array;
    public function getById(int $id): ?array;
    public function getReceiptByInvoice(int $invoiceId): ?array;
    public function createReceipt(array $data): int;
    public function updateStatus(int $id, string $status): bool;
}
