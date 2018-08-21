<?php
declare(strict_types=1);

namespace App\Tests\Unit\Domain\Listing;

use App\Domain\Listing\CreateListingCommand;
use App\Domain\Listing\CreateListingHandler;

use App\Domain\Listing\Exception\TicketConflictException;
use App\Domain\Listing\Exception\InvalidListingException;
use App\Domain\Listing\Listing;
use App\Domain\Listing\WriteRepository as ListingWriteRepository;
use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\ReadRepository as TicketReadRepository;
use App\Domain\Ticket\Ticket;
use App\Domain\Ticket\WriteRepository as TicketWriteRepository;
use DateTimeImmutable;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CreateListingHandlerTest extends TestCase
{
    /**
     * @var ListingWriteRepository|ObjectProphecy
     */
    private $listingWriteRepo;

    /**
     * @var TicketReadRepository|ObjectProphecy
     */
    private $ticketReadRepo;

    /**
     * @var TicketWriteRepository|ObjectProphecy
     */
    private $ticketWriteRepo;

    /**
     * @var CreateListingHandler
     */
    private $subject;

    /**
     * @var UuidInterface
     */
    private $listingId;

    /**
     * @var UuidInterface
     */
    private $ticketId;

    /**
     * @var Listing
     */
    private $listing;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var array
     */
    private $barcodes;

    /**
     * @var array
     */
    private $price;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->listingId = Uuid::uuid4();
        $this->ticketId  = Uuid::uuid4();

        $this->listing = new Listing(
            $this->listingId,
            'pedro',
            new Money('30', new Currency('EUR')),
            true
        );

        $this->ticket = new Ticket(
            $this->ticketId,
            $this->listingId,
            new Barcode('EAN-13', '38974312923'),
            new DateTimeImmutable('2018-01-08')
        );

        $this->barcodes = [
            [ 'type' => 'EAN-13', 'value' => '38974312923' ],
            [ 'type' => 'EAN-13', 'value' => '38974312924' ],
        ];

        $this->price = [ 'amount' => 50, 'currency' => 'EUR'];

        $this->listingWriteRepo = $this->prophesize(ListingWriteRepository::class);
        $this->ticketReadRepo = $this->prophesize(TicketReadRepository::class);
        $this->ticketWriteRepo = $this->prophesize(TicketWriteRepository::class);

        $this->subject = new CreateListingHandler(
            $this->listingWriteRepo->reveal(),
            $this->ticketReadRepo->reveal(),
            $this->ticketWriteRepo->reveal()
        );
    }

    public function testHandleHappyPath(): void
    {
        $this->ticketReadRepo->findByBarcodeAndSortByDate(new Barcode('EAN-13', '38974312923'))
            ->shouldBeCalled()
            ->willReturn([]);

        $this->ticketReadRepo->findByBarcodeAndSortByDate(new Barcode('EAN-13', '38974312924'))
            ->shouldBeCalled()
            ->willReturn([]);

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldBeCalled();

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldBeCalled();

        $this->listingWriteRepo->save(Argument::type(Listing::class))
            ->shouldBeCalled();

        $this->subject->handle(
            new CreateListingCommand('angele', $this->barcodes, $this->price)
        );
    }

    public function testHandleThrowsExceptionIfListingContainsDuplicateBarcodes(): void
    {
        $this->expectException(InvalidListingException::class);
        $this->expectExceptionMessageRegExp('#duplicate#i');

        $barcodes = [ $this->barcodes[0], $this->barcodes[1], $this->barcodes[0] ];

        $this->ticketReadRepo->findByBarcodeAndSortByDate(Argument::type(Barcode::class))
            ->shouldNotBeCalled();

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldNotBeCalled();

        $this->listingWriteRepo->save(Argument::type(Listing::class))
            ->shouldNotBeCalled();

        $this->subject->handle(
            new CreateListingCommand('angele', $barcodes, $this->price)
        );
    }

    public function testHandleThrowsExceptionIfListingContainsTicketsAlreadySoldToSomeoneElse(): void
    {
        $this->expectException(TicketConflictException::class);
        $this->expectExceptionMessageRegExp('#already been sold#i');

        $this->ticketReadRepo->findByBarcodeAndSortByDate(Argument::type(Barcode::class))
            ->shouldBeCalled()
            ->willReturn(
                [
                new Ticket(
                    Uuid::uuid4(),
                    Uuid::uuid4(),
                    new Barcode('EAN-13', '38974312923'),
                    new DateTimeImmutable('2018-01-07'),
                    'tom'
                )
                ]
            );

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldNotBeCalled();

        $this->listingWriteRepo->save(Argument::type(Listing::class))
            ->shouldNotBeCalled();

        $this->subject->handle(
            new CreateListingCommand('angele', [$this->barcodes[0]], $this->price)
        );
    }

    public function testHandleThrowsExceptionIfListingContainsTicketsAlreadyCurrentlyForSale(): void
    {
        $this->expectException(TicketConflictException::class);
        $this->expectExceptionMessageRegExp('#already for sale#i');

        $this->ticketReadRepo->findByBarcodeAndSortByDate(Argument::type(Barcode::class))
            ->shouldBeCalled()
            ->willReturn(
                [
                new Ticket(
                    Uuid::uuid4(),
                    Uuid::uuid4(),
                    new Barcode('EAN-13', '38974312923'),
                    new DateTimeImmutable('2018-01-07')
                )
                ]
            );

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldNotBeCalled();

        $this->listingWriteRepo->save(Argument::type(Listing::class))
            ->shouldNotBeCalled();

        $this->subject->handle(
            new CreateListingCommand('angele', [$this->barcodes[0]], $this->price)
        );
    }


    public function testHandleWorksIfTicketAlreadyBoughtLastBySeller(): void
    {
        $this->ticketReadRepo->findByBarcodeAndSortByDate(new Barcode('EAN-13', '38974312923'))
            ->shouldBeCalled()
            ->willReturn(
                [
                new Ticket(
                    Uuid::uuid4(),
                    Uuid::uuid4(),
                    new Barcode('EAN-13', '38974312923'),
                    new DateTimeImmutable('2018-01-07'),
                    'angele'
                )
                ]
            );

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldBeCalled();

        $this->listingWriteRepo->save(Argument::type(Listing::class))
            ->shouldBeCalled();

        $this->subject->handle(
            new CreateListingCommand('angele', [$this->barcodes[0]], $this->price)
        );
    }

}
