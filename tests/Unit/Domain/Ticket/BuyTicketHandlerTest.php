<?php
declare(strict_types=1);

namespace App\Tests\Unit\Domain\Ticket;

use App\Domain\Listing\Listing;
use App\Domain\Listing\ReadRepository as ListingReadRepository;
use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\BuyTicketCommand;
use App\Domain\Ticket\BuyTicketHandler;
use App\Domain\Ticket\Exception\ListingNotVerifiedException;
use App\Domain\Ticket\Exception\TicketAlreadyBoughtException;
use App\Domain\Ticket\ReadRepository;
use App\Domain\Ticket\Ticket;
use App\Domain\Ticket\WriteRepository;
use DateTimeImmutable;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class BuyTicketHandlerTest extends TestCase
{
    /**
     * @var ListingReadRepository|ObjectProphecy
     */
    private $listingReadRepo;

    /**
     * @var ReadRepository|ObjectProphecy
     */
    private $ticketReadRepo;

    /**
     * @var WriteRepository|ObjectProphecy
     */
    private $ticketWriteRepo;

    /**
     * @var BuyTicketHandler
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

        $this->listingReadRepo = $this->prophesize(ListingReadRepository::class);
        $this->ticketReadRepo  = $this->prophesize(ReadRepository::class);
        $this->ticketWriteRepo = $this->prophesize(WriteRepository::class);

        $this->subject = new BuyTicketHandler(
            $this->listingReadRepo->reveal(),
            $this->ticketReadRepo->reveal(),
            $this->ticketWriteRepo->reveal()
        );
    }

    public function testBuyTicketHappyPath(): void
    {
        $this->ticketReadRepo->get($this->ticketId)
            ->shouldBeCalled()
            ->willReturn($this->ticket);

        $this->listingReadRepo->get($this->listingId)
            ->shouldBeCalled()
            ->willReturn($this->listing);

        $this->ticketWriteRepo->save(
            new Ticket(
                $this->ticketId,
                $this->listingId,
                new Barcode('EAN-13', '38974312923'),
                new DateTimeImmutable('2018-01-08'),
                'alice'
            )
        )
            ->shouldBeCalled();

        $this->subject->handle(new BuyTicketCommand($this->ticketId, 'alice'));
    }

    public function testBuyTicketShouldThrowExceptionIfListingNotVerified(): void
    {
        $this->expectException(ListingNotVerifiedException::class);
        $this->expectExceptionMessageRegExp('#not yet verified#i');

        $this->ticketReadRepo->get($this->ticketId)
            ->shouldBeCalled()
            ->willReturn($this->ticket);

        $this->listingReadRepo->get($this->listingId)
            ->shouldBeCalled()
            ->willReturn(
                new Listing(
                    $this->listingId,
                    'pedro',
                    new Money('30', new Currency('EUR')),
                    false
                )
            );

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldNotBeCalled();

        $this->subject->handle(new BuyTicketCommand($this->ticketId, 'alice'));
    }

    public function testBuyTicketShouldThrowExceptionIfTicketAlreadyBought(): void
    {
        $this->expectException(TicketAlreadyBoughtException::class);
        $this->expectExceptionMessageRegExp('#has already been bought#i');

        $this->ticketReadRepo->get($this->ticketId)
            ->shouldBeCalled()
            ->willReturn(
                new Ticket(
                    $this->ticketId,
                    $this->listingId,
                    new Barcode('EAN-13', '38974312923'),
                    new DateTimeImmutable('2018-01-08'),
                    'bob'
                )
            );

        $this->listingReadRepo->get($this->listingId)
            ->shouldBeCalled()
            ->willReturn($this->listing);

        $this->ticketWriteRepo->save(Argument::type(Ticket::class))
            ->shouldNotBeCalled();

        $this->subject->handle(new BuyTicketCommand($this->ticketId, 'alice'));
    }
}
