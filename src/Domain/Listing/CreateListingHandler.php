<?php
declare(strict_types=1);

namespace App\Domain\Listing;

use App\Domain\Listing\Exception\TicketConflictException;
use App\Domain\Listing\Exception\InvalidListingException;
use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\ReadRepository as TicketReadRepository;
use App\Domain\Listing\WriteRepository as ListingWriteRepository;
use App\Domain\Ticket\WriteRepository as TicketWriteRepository;
use App\Domain\Ticket\Ticket;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class CreateListingHandler
{
    /**
     * @var ListingWriteRepository
     */
    private $listingWriteRepository;

    /**
     * @var TicketReadRepository
     */
    private $ticketReadRepository;

    /**
     * @var TicketWriteRepository
     */
    private $ticketWriteRepository;

    public function __construct(
        ListingWriteRepository $listingWriteRepository,
        TicketReadRepository $ticketReadRepository,
        TicketWriteRepository $ticketWriteRepository
    ) {
        $this->listingWriteRepository = $listingWriteRepository;
        $this->ticketReadRepository   = $ticketReadRepository;
        $this->ticketWriteRepository  = $ticketWriteRepository;
    }

    /**
     * @throws TicketConflictException
     */
    public function handle(CreateListingCommand $command): void
    {
        $listingId = $command->getId();
        $seller    = $command->getSeller();
        $price     = self::instantiatePrice($command->getPrice());
        $barcodes  = self::instantiateBarcodes($command->getBarcodes());

        self::ensureBarcodesAreUniqueWithinListing($barcodes);
        self::ensureBarcodesNotAlreadyOnSaleOrSoldToSomeoneElse($barcodes, $seller);

        $this->uploadTickets($listingId, $barcodes);

        $this->listingWriteRepository->save(
            Listing::create($listingId, $seller, $price)
        );
    }

    /**
     * @param Barcode[] $barcodes
     */
    private function uploadTickets(UuidInterface $listingId, array $barcodes): void
    {
        foreach ($barcodes as $barcode) {
            $ticketId = Uuid::uuid4();

            $ticket = Ticket::create($ticketId, $listingId, $barcode);
            $this->ticketWriteRepository->save($ticket);
        }
    }

    /**
     * @throws TicketConflictException
     * @param  Barcode[] $barcodes
     */
    private function ensureBarcodesNotAlreadyOnSaleOrSoldToSomeoneElse(array $barcodes, string $seller): void
    {
        foreach ($barcodes as $barcode) {
            $ticketsWithSameBarcode = $this->ticketReadRepository->findByBarcodeAndSortByDate($barcode);

            if ($ticketsWithSameBarcode === []) {
                continue;
            }

            $lastUploaded = $ticketsWithSameBarcode[0];

            if (! $lastUploaded->isBought()) {
                throw TicketConflictException::becauseTicketAlreadyOnSale($lastUploaded->getId());
            }

            if ($lastUploaded->getBuyer() !== $seller) {
                throw TicketConflictException::becauseTicketAlreadySoldToSomeoneElse(
                    $lastUploaded->getId(),
                    $lastUploaded->getBuyer()
                );
            }
        }
    }

    /**
     * @throws TicketConflictException
     */
    private static function ensureBarcodesAreUniqueWithinListing(array $barcodes): void
    {
        foreach ($barcodes as $barcode) {
            if (isset($found[(string) $barcode])) {
                throw InvalidListingException::forDuplicateBarcodes();
            }
            $found[(string) $barcode] = true;
        }
    }

    private static function instantiatePrice(array $price): Money
    {
        return new Money($price['amount'], new Currency($price['currency']));
    }

    /**
     * @return Barcode[]
     */
    private static function instantiateBarcodes(array $barcodeArrays): array
    {
        $barcodes = [];
        foreach ($barcodeArrays as $barcodeArray) {
            $barcodes[] = Barcode::fromArray($barcodeArray);
        }
        return $barcodes;
    }
}
