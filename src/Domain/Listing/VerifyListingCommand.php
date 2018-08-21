<?php
declare(strict_types=1);

namespace App\Domain\Listing;

use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class VerifyListingCommand
{
    /**
     * @Assert\NotNull()
     * @Serializer\Type("uuid")
     *
     * @var UuidInterface
     */
    private $listingId;

    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $verifier;

    public function __construct(UuidInterface $listingId, string $verifier)
    {
        $this->listingId = $listingId;
        $this->verifier  = $verifier;
    }

    /**
     * @return UuidInterface
     */
    public function getListingId(): UuidInterface
    {
        return $this->listingId;
    }

    public function getVerifier(): string
    {
        return $this->verifier;
    }
}
