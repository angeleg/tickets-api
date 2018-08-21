<?php
declare(strict_types=1);

namespace App\Domain\Ticket\Exception;

use Ramsey\Uuid\UuidInterface;
use RuntimeException;

final class ListingNotVerifiedException extends RuntimeException
{
    public static function forTicketInListing(UuidInterface $ticketId, UuidInterface $listingId): self
    {
        return new self(
            sprintf(
                'Cannot buy ticket %s because its listing %s is not yet verified',
                $ticketId->toString(),
                $listingId->toString()
            )
        );
    }
}
