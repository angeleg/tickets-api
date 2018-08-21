<?php
declare(strict_types=1);

namespace App\Domain\Listing;

use Ramsey\Uuid\UuidInterface;

interface ReadRepository
{
    public function get(UuidInterface $listingId): ?Listing;
}
