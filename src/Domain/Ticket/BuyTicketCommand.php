<?php
declare(strict_types=1);

namespace App\Domain\Ticket;

use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class BuyTicketCommand
{
    /**
     * @Assert\NotNull()
     * @Serializer\Type("uuid")
     *
     * @var UuidInterface
     */
    private $ticketId;

    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $buyer;

    public function __construct(UuidInterface $ticketId, string $buyer)
    {
        $this->ticketId = $ticketId;
        $this->buyer    = $buyer;
    }

    /**
     * @return UuidInterface
     */
    public function getTicketId(): UuidInterface
    {
        return $this->ticketId;
    }

    /**
     * @return string
     */
    public function getBuyer(): string
    {
        return $this->buyer;
    }
}
