<?php
declare(strict_types=1);

namespace App\Domain\Ticket;

use App\Domain\Ticket\Exception\ListingNotVerifiedException;
use App\Domain\Ticket\Exception\TicketAlreadyBoughtException;
use App\Domain\Ticket\ReadRepository as TicketReadRepository;
use App\Domain\Ticket\WriteRepository as TicketWriteRepository;
use App\Domain\Listing\ReadRepository as ListingReadRepository;

final class BuyTicketHandler
{
    /**
     * @var ListingReadRepository
     */
    private $listingReadRepository;

    /**
     * @var TicketReadRepository
     */
    private $ticketReadRepository;

    /**
     * @var TicketWriteRepository
     */
    private $ticketWriteRepository;


    public function __construct(
        ListingReadRepository $listingReadRepository,
        TicketReadRepository $ticketReadRepository,
        TicketWriteRepository $ticketWriteRepository
    ) {
        $this->listingReadRepository = $listingReadRepository;
        $this->ticketReadRepository  = $ticketReadRepository;
        $this->ticketWriteRepository = $ticketWriteRepository;
    }

    /**
     * @throws TicketAlreadyBoughtException
     */
    public function handle(BuyTicketCommand $command): void
    {
        $ticketId = $command->getTicketId();
        $ticket = $this->ticketReadRepository->get($ticketId);

        $listingId = $ticket->getListingId();
        $listing = $this->listingReadRepository->get($listingId);

        if ($listing->isVerified() !== true) {
            throw ListingNotVerifiedException::forTicketInListing($ticketId, $listingId);
        }
        
        $ticket->buy($command->getBuyer());
        $this->ticketWriteRepository->save($ticket);
    }
}
