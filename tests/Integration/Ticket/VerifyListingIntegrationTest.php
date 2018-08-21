<?php
declare(strict_types=1);

namespace App\Tests\Integration\Ticket;

use App\Application\Listing\InMemoryRepository;
use App\Domain\Listing\Listing;
use App\Domain\Listing\VerifyListingCommand;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Reflection;
use League\Tactician\Bundle\Middleware\InvalidCommandException;
use League\Tactician\CommandBus;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;

class VerifyListingIntegrationTest extends IntegrationTestCase
{
    use Reflection;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var InMemoryRepository
     */
    private $listingRepository;

    /**
     * @before
     */
    public function createDependencies(): void
    {
        $this->listingRepository = new InMemoryRepository();
        $this->commandBus = $this->getService('tactician.commandbus.default');
    }

    public function testHandleModifiesTicket(): void
    {
        $listingId  = Uuid::uuid4();
        $this->overrideService('listing.repository.in-memory', $this->listingRepository);

        $this->listingRepository->save(
            new Listing($listingId, 'tom', new Money(40, new Currency('USD')), true)
        );

        $this->commandBus->handle(new VerifyListingCommand($listingId, 'admin'));

        $listing = $this->listingRepository->get($listingId);

        self::assertEquals($listingId, $listing->getId());
        self::assertEquals(true, $listing->isVerified());
    }

    /**
     * @dataProvider provideInvalidCommandParameters
     */
    public function testValidation(VerifyListingCommand $command): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->commandBus->handle($command);
    }

    public function provideInvalidCommandParameters(): array
    {
        return [
            'command without listing id' => [
                $this->createWithProperties(['listingId' => null, 'verifier' => 'admin'])
            ],
            'command without buyer' => [
                $this->createWithProperties(['listingId' => null])
            ],
        ];
    }

    /**
     * @return object|VerifyListingCommand
     *
     * @throws \ReflectionException
     */
    private function createWithProperties(array $properties)
    {
        return $this->instantiateWithoutConstructor(
            VerifyListingCommand::class,
            $properties
        );
    }
}
