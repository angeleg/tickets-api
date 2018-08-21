<?php
declare(strict_types=1);

namespace App\Application\Listing;

use App\Domain\Listing\Listing;
use App\Domain\Listing\ReadRepository;
use App\Domain\Listing\WriteRepository;
use Ramsey\Uuid\UuidInterface;

final class InMemoryRepository implements ReadRepository, WriteRepository
{
    /**
     * @var array Listing[]
     */
    private $listings = [];

    public function get(UuidInterface $listingId): ?Listing
    {
        return $this->listings[$listingId->toString()] ?? null;
    }

    public function save(Listing $listing): void
    {
        $this->listings[$listing->getId()->toString()] = $listing;
    }
}
