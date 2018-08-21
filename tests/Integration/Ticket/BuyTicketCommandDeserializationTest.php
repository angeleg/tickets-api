<?php
declare(strict_types=1);

namespace App\Tests\Integration\Ticket;

use App\Domain\Ticket\BuyTicketCommand;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\JsonSerialization;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class BuyTicketCommandDeserializationTest extends IntegrationTestCase
{
    use JsonSerialization;

    public function testDeserializationShouldBeDoneProperly()
    {
        $id = Uuid::uuid4();

        /**
         * @var BuyTicketCommand $command
        */
        $command = $this->fromJson(
            [
                'ticket_id' => $id->toString(),
                'buyer'     => 'angele'
            ],
            BuyTicketCommand::class
        );

        self::assertInstanceOf(BuyTicketCommand::class, $command);
        self::assertInstanceOf(UuidInterface::class, $command->getTicketId());
        self::assertEquals($id, $command->getTicketId());
        self::assertSame('angele', $command->getBuyer());
    }
}
