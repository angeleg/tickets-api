<?php
declare(strict_types=1);

namespace App\Tests\Unit\Domain\Listing;

use App\Domain\Listing\Exception\UnauthorizedToVerifyListingException;
use App\Domain\Listing\Listing;
use App\Domain\Listing\ReadRepository;
use App\Domain\Listing\VerifyListingCommand;
use App\Domain\Listing\VerifyListingHandler;
use App\Domain\Listing\WriteRepository;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class VerifyListingHandlerTest extends TestCase
{
    /**
     * @var ReadRepository|ObjectProphecy
     */
    private $readRepository;

    /**
     * @var WriteRepository|ObjectProphecy
     */
    private $writeRepository;

    /**
     * @var VerifyListingHandler
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
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->listingId = Uuid::uuid4();
        $this->ticketId  = Uuid::uuid4();

        $this->readRepository  = $this->prophesize(ReadRepository::class);
        $this->writeRepository = $this->prophesize(WriteRepository::class);

        $this->subject = new VerifyListingHandler(
            $this->readRepository->reveal(),
            $this->writeRepository->reveal()
        );
    }

    public function testHandleHappyPath(): void
    {
        $listing = new Listing(
            $this->listingId,
            'angele',
            new Money('30', new Currency('EUR')),
            false
        );

        $this->readRepository->get($this->listingId)
            ->shouldBeCalled()
            ->willReturn($listing);

        $this->writeRepository->save(
            new Listing(
                $this->listingId,
                'angele',
                new Money('30', new Currency('EUR')),
                true
            )
        )->shouldBeCalled();

        $this->subject->handle(new VerifyListingCommand($this->listingId, 'admin'));
    }

    public function testHandleWillThrowExceptionIfVerifierIsNotAdmin(): void
    {
        $this->expectException(UnauthorizedToVerifyListingException::class);
        $this->expectExceptionMessageRegExp('#cannot be verified by#i');

        $this->readRepository->get(Argument::type(UuidInterface::class))
            ->shouldNotBeCalled();

        $this->writeRepository->save(Argument::type(Listing::class))
            ->shouldNotBeCalled();

        $this->subject->handle(new VerifyListingCommand($this->listingId, 'not-an-admin'));
    }
}
