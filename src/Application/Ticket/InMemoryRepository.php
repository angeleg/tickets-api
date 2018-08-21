<?php
declare(strict_types=1);

namespace App\Application\Ticket;

use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\ReadRepository;
use App\Domain\Ticket\Ticket;
use App\Domain\Ticket\WriteRepository;
use Ramsey\Uuid\UuidInterface;

final class InMemoryRepository implements ReadRepository, WriteRepository
{
    /**
     * @var array
     */
    private $tickets = [];

    public function get(UuidInterface $id): ?Ticket
    {
        return $this->tickets[$id->toString()] ?? null;
    }

    /**
     * @param  UuidInterface[] $ticketIds
     * @return Ticket[]
     */
    public function findById(array $ticketIds): array
    {
        $exists = function (UuidInterface $id) use ($ticketIds) {
            return in_array($id, $ticketIds);
        };

        return array_values(array_filter($this->tickets, $exists));
    }

    public function save(Ticket $ticket): void
    {
        $this->tickets[$ticket->getId()->toString()] = $ticket;
    }

    /**
     * @return Ticket[]
     */
    public function findByBarcodeAndSortByDate(Barcode $barcode): array
    {
        $hasSameBarcode = function (Ticket $ticket) use ($barcode) {
            return (string) $barcode === (string) $ticket->getBarcode();
        };

        $ticketsWithSameBarcode = array_filter($this->tickets, $hasSameBarcode);

        $byDescBoughtDate = function (Ticket $ticket1, Ticket $ticket2) {
            return $ticket1->getUploadedAt() < $ticket2->getUploadedAt();
        };

        usort($ticketsWithSameBarcode, $byDescBoughtDate);

        return $ticketsWithSameBarcode;
    }
}
