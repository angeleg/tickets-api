<?php
declare(strict_types=1);

namespace App\Domain\Listing\Exception;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class UnauthorizedToVerifyListingException extends UnauthorizedHttpException
{
    public static function forUser(UuidInterface $listingId, string $verifier)
    {
        return new self(
            '',
            sprintf(
                'Listing %s cannot be verified by user %s',
                $listingId->toString(),
                $verifier
            )
        );
    }
}
