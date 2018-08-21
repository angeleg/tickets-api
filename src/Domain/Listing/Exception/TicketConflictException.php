<?php
declare(strict_types=1);

namespace App\Domain\Listing\Exception;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class TicketConflictException extends ConflictHttpException
{
    public static function becauseTicketAlreadyOnSale(UuidInterface $ticketId): self
    {
        return new self(
            sprintf(
                'Unable to create listing because ticket %s is already for sale in another listing',
                $ticketId->toString()
            )
        );
    }

    public static function becauseTicketAlreadySoldToSomeoneElse(UuidInterface $ticketId, string $buyer): self
    {
        return new self(
            sprintf(
                'Unable to create listing because ticket %s has already been sold by %s',
                $ticketId->toString(),
                $buyer
            )
        );
    }
}
