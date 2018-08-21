<?php
declare(strict_types=1);

namespace App\Tests\Integration\Ticket;

use App\Application\Listing\InMemoryRepository as ListingRepository;
use App\Application\Ticket\InMemoryRepository as TicketRepository;
use App\Domain\Listing\Listing;
use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\BuyTicketCommand;
use App\Domain\Ticket\Exception\ListingNotVerifiedException;
use App\Domain\Ticket\Exception\TicketAlreadyBoughtException;
use App\Domain\Ticket\Ticket;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Reflection;
use DateTimeImmutable;
use League\Tactician\Bundle\Middleware\InvalidCommandException;
use League\Tactician\CommandBus;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;

class BuyTicketIntegrationTest extends IntegrationTestCase
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
        $this->ticketRepository  = new TicketRepository();

        $this->commandBus = $this->getService('tactician.commandbus.default');
    }

    public function testHandleModifiesTicket(): void
    {
        $id         = Uuid::uuid4();
        $listingId  = Uuid::uuid4();
        $barcode    = new Barcode('xxx', '001');
        $uploadedAt = new DateTimeImmutable('2018-01-01');

        $this->overrideContainerServices();

        $this->ticketRepository->save(new Ticket($id, $listingId, $barcode, $uploadedAt, null));
        $this->listingRepository->save(
            new Listing($listingId, 'tom', new Money(40, new Currency('USD')), true)
        );

        $this->commandBus->handle(new BuyTicketCommand($id, 'angele'));

        $ticket = $this->ticketRepository->get($id);

        self::assertEquals($id, $ticket->getId());
        self::assertEquals($listingId, $ticket->getListingId());
        self::assertEquals($barcode, $ticket->getBarcode());
        self::assertEquals($uploadedAt, $ticket->getUploadedAt());
        self::assertEquals('angele', $ticket->getBuyer());
    }

    public function testHandleDoesntModifyTicketIfListingNotVerified(): void
    {
        $this->expectException(ListingNotVerifiedException::class);

        $id         = Uuid::uuid4();
        $listingId  = Uuid::uuid4();
        $barcode    = new Barcode('xxx', '001');
        $uploadedAt = new DateTimeImmutable('2018-01-01');

        $this->overrideContainerServices();

        $this->ticketRepository->save(new Ticket($id, $listingId, $barcode, $uploadedAt, null));
        $this->listingRepository->save(
            new Listing($listingId, 'tom', new Money(40, new Currency('USD')), false)
        );

        $this->commandBus->handle(new BuyTicketCommand($id, 'angele'));

        $ticket = $this->ticketRepository->get($id);
        self::assertEquals(null, $ticket->getBuyer());
    }

    public function testHandleDoesntModifyTicketIfAlreadyBought(): void
    {
        $this->expectException(TicketAlreadyBoughtException::class);

        $id         = Uuid::uuid4();
        $listingId  = Uuid::uuid4();
        $barcode    = new Barcode('xxx', '001');
        $uploadedAt = new DateTimeImmutable('2018-01-01');

        $this->overrideContainerServices();

        $this->ticketRepository->save(new Ticket($id, $listingId, $barcode, $uploadedAt, 'foo'));
        $this->listingRepository->save(
            new Listing($listingId, 'tom', new Money(40, new Currency('USD')), true)
        );

        $this->commandBus->handle(new BuyTicketCommand($id, 'angele'));

        $ticket = $this->ticketRepository->get($id);
        self::assertEquals(null, $ticket->getBuyer());
    }

    private function overrideContainerServices(): void
    {
        $this->overrideService('listing.repository.in-memory', $this->listingRepository);
        $this->overrideService('ticket.repository.in-memory', $this->ticketRepository);
    }

    /**
     * @dataProvider provideInvalidCommandParameters
     */
    public function testValidation(BuyTicketCommand $command): void
    {
        $this->expectException(InvalidCommandException::class);
        $this->commandBus->handle($command);
    }

    public function provideInvalidCommandParameters(): array
    {
        return [
            'command without ticket id' => [
                $this->createWithProperties(['ticketId' => null, 'buyer' => 'angele'])
            ],
            'command without buyer' => [
                $this->createWithProperties(['ticketId' => Uuid::uuid4()])
            ],
            'command with non-string buyer'  => [
                $this->createWithProperties(['ticketId' => Uuid::uuid4(), 'buyer' => ''])
            ],
        ];
    }

    /**
     * @return object|BuyTicketCommand
     *
     * @throws \ReflectionException
     */
    private function createWithProperties(array $properties)
    {
        return $this->instantiateWithoutConstructor(
            BuyTicketCommand::class,
            $properties
        );
    }

}
