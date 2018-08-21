<?php
declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use Ramsey\Uuid\UuidInterface;
use RuntimeException;

final class TicketAlreadyBoughtException extends RuntimeException
{
    public static function byBuyer(UuidInterface $ticketId, string $buyer)
    {
        return new self(
            sprintf(
                'Ticket (%s) has already been bought by buyer %s',
                $ticketId->toString(),
                $buyer
            )
        );
    }
}
