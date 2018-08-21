<?php
declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ticket;

use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\Exception\TicketAlreadyBoughtException;
use App\Domain\Ticket\Ticket;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class TicketTest extends TestCase
{
    public function testBuyTicketThrowsExceptionIfTicketAlreadySold(): void
    {
        $this->expectException(TicketAlreadyBoughtException::class);
        $this->expectExceptionMessageRegExp('#has already been bought#i');

        $ticket = new Ticket(
            Uuid::uuid4(),
            Uuid::uuid4(),
            new Barcode('EAN-13', '38974312923'),
            new DateTimeImmutable('2018-01-08'),
            'angele'
        );

        $ticket->buy('tom');
    }

}
