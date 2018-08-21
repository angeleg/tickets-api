<?php
declare(strict_types=1);

namespace App\Domain\Listing;

use App\Domain\Listing\Exception\UnauthorizedToVerifyListingException;

final class VerifyListingHandler
{
    /**
     * @var ReadRepository
     */
    private $readRepository;

    /**
     * @var WriteRepository
     */
    private $writeRepository;

    public function __construct(ReadRepository $readRepository, WriteRepository $writeRepository)
    {
        $this->readRepository  = $readRepository;
        $this->writeRepository = $writeRepository;
    }

    /**
     * @throws UnauthorizedToVerifyListingException
     */
    public function handle(VerifyListingCommand $command): void
    {
        $listingId = $command->getListingId();
        $verifier  = $command->getVerifier();

        if ($verifier !== 'admin') {
            throw UnauthorizedToVerifyListingException::forUser($listingId, $verifier);
        }

        $listing = $this->readRepository->get($listingId);

        $listing->verify();
        $this->writeRepository->save($listing);
    }
}
