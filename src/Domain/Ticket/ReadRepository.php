<?php
declare(strict_types=1);

namespace App\Domain\Ticket;

use App\Domain\Ticket\Barcode\Barcode;
use Ramsey\Uuid\UuidInterface;

interface ReadRepository
{
    public function get(UuidInterface $id): ?Ticket;

    /**
     * @param  UuidInterface[] $ticketIds
     * @return Ticket[]
     */
    public function findById(array $ticketIds): array;

    /**
     * @return Ticket[]
     */
    public function findByBarcodeAndSortByDate(Barcode $barcode): array;
}
