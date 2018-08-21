<?php
declare(strict_types=1);

namespace App\Tests\Integration\Ticket;

use App\Domain\Listing\VerifyListingCommand;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\JsonSerialization;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class VerifyListingCommandDeserializationTest extends IntegrationTestCase
{
    use JsonSerialization;

    public function testDeserializationShouldBeDoneProperly()
    {
        $id = Uuid::uuid4();

        /**
 * @var VerifyListingCommand $command 
*/
        $command = $this->fromJson(
            ['listing_id' => $id, 'verifier' => 'angele'],
            VerifyListingCommand::class
        );

        self::assertInstanceOf(VerifyListingCommand::class, $command);
        self::assertInstanceOf(UuidInterface::class, $command->getListingId());
        self::assertSame($id->toString(), (string) $command->getListingId());
        self::assertSame('angele', $command->getVerifier());
    }
}
