<?php
declare(strict_types=1);

namespace App\Domain\Listing;

interface WriteRepository
{
    public function save(Listing $listing): void;
}
