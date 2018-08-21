<?php
declare(strict_types=1);

namespace App\Domain\Ticket;

interface WriteRepository
{
    public function save(Ticket $ticket): void;
}