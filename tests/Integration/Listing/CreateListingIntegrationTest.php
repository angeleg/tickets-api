<?php
declare(strict_types=1);

namespace App\Tests\Integration\Listing;

use App\Application\Listing\InMemoryRepository as ListingRepository;
use App\Application\Ticket\InMemoryRepository as TicketRepository;
use App\Domain\Listing\CreateListingCommand;
use App\Domain\Listing\Listing;
use App\Domain\Ticket\Barcode\Barcode;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Reflection;
use League\Tactician\CommandBus;
use League\Tactician\Bundle\Middleware\InvalidCommandException;
use Ramsey\Uuid\Uuid;

class CreateListingIntegrationTest extends IntegrationTestCase
{
    use Reflection;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ListingRepository
     */
    private $listingRepository;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @before
     */
    public function createDependencies(): void
    {
        $this->listingRepository = new ListingRepository();
        $this->ticketRepository = new TicketRepository();

        $this->commandBus = $this->getService('tactician.commandbus.default');
    }

    public function testHandleAddsTicketsAndListingWhenTheCommandIsValid(): void
    {
        $command = new CreateListingCommand(
            'angele',
            [
                [ 'type' => 'EAN-13', 'value' => '001' ],
                [ 'type' => 'EAN-13', 'value' => '002' ],
            ],
            [ 'amount' => 50, 'currency' => 'EUR' ]
        );

        $listingId = $command->getId();

        $this->overrideContainerServices();

        $this->commandBus->handle($command);

        $tickets = $this->ticketRepository->findByBarcodeAndSortByDate(new Barcode('EAN-13', '001'));
        self::assertCount(1, $tickets);

        $tickets = $this->ticketRepository->findByBarcodeAndSortByDate(new Barcode('EAN-13', '002'));
        self::assertCount(1, $tickets);

        $listing = $this->listingRepository->get($listingId);

        self::assertInstanceOf(Listing::class, $listing);
    }

    private function overrideContainerServices(): void
    {
        $this->overrideService('listing.repository.in-memory', $this->listingRepository);
        $this->overrideService('ticket.repository.in-memory', $this->ticketRepository);
    }

    /**
     * @dataProvider provideInvalidCommandParameters
     */
    public function testCommandValidation(CreateListingCommand $command): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->commandBus->handle($command);
    }

    public function provideInvalidCommandParameters(): array
    {
        $malformedBarcodes1 = [[ 'type' => 'EAN-13', 'value' => '38974312923', 'foo' => 'bar' ]];
        $malformedBarcodes2 = [[ 'type' => 'EAN-13' ]];
        $malformedPrice1    = [ 'fdfs' => 50, 'currency' => 'EUR' ];
        $malformedPrice2    = [ 'amount' => 50 ];

        return [
            'command without seller'            => [$this->createCommandWithProperty('seller', '')],
            'command with malformed barcodes 1' => [$this->createCommandWithProperty('barcodes', $malformedBarcodes1)],
            'command with malformed barcodes 2' => [$this->createCommandWithProperty('barcodes', $malformedBarcodes2)],
            'command with malformed price 1'    => [$this->createCommandWithProperty('price', $malformedPrice1)],
            'command with malformed price 2'    => [$this->createCommandWithProperty('price', $malformedPrice2)],
        ];
    }

    /**
     * @return object|CreateListingCommand
     *
     * @throws \ReflectionException
     */
    private function createCommandWithProperty(string $property, $value)
    {
        $properties = [
            'id' => Uuid::uuid4(),
            'seller' => 'angele',
            'barcodes' => [
                [ 'type' => 'EAN-13', 'value' => '38974312923' ],
                [ 'type' => 'EAN-13', 'value' => '38974312924' ],
            ],
            'price' => [ 'amount' => 50, 'currency' => 'EUR' ]
        ];

        if (isset($properties[$property])) {
            $properties[$property] = $value;
        }

        return $this->instantiateWithoutConstructor(
            CreateListingCommand::class,
            $properties
        );
    }
}
