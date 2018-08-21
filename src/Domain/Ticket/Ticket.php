<?php
declare(strict_types=1);

namespace App\Domain\Ticket;

use App\Domain\Ticket\Barcode\Barcode;
use App\Domain\Ticket\Exception\TicketAlreadyBoughtException;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

final class Ticket
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var UuidInterface
     */
    private $listingId;

    /**
     * @var Barcode
     */
    private $barcode;

    /**
     * @var DateTimeImmutable
     */
    private $uploadedAt;

    /**
     * @var string|null
     */
    private $buyer;

    public function __construct(
        UuidInterface $id,
        UuidInterface $listingId,
        Barcode $barcode,
        DateTimeImmutable $uploadedAt,
        ?string $buyer = null
    ) {
        $this->id = $id;
        $this->listingId = $listingId;
        $this->barcode = $barcode;
        $this->uploadedAt = $uploadedAt;
        $this->buyer = $buyer;
        $this->listingId = $listingId;
    }

    public static function create(UuidInterface $id, UuidInterface $listingId, Barcode $barcode): self
    {
        return new self($id, $listingId, $barcode, new DateTimeImmutable('now'));
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getListingId(): UuidInterface
    {
        return $this->listingId;
    }

    public function getBarcode(): Barcode
    {
        return $this->barcode;
    }

    public function getUploadedAt(): DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function getBuyer(): ?string
    {
        return $this->buyer;
    }

    public function isBought(): bool
    {
        return $this->buyer !== null;
    }

    /**
     * @throws TicketAlreadyBoughtException
     */
    public function buy(string $buyer): void
    {
        $this->ensureTicketForSale();
        $this->buyer = $buyer;
    }

    /**
     * @throws TicketAlreadyBoughtException
     */
    private function ensureTicketForSale(): void
    {
        if ($this->buyer !== null) {
            throw TicketAlreadyBoughtException::byBuyer($this->id, $this->buyer);
        }
    }
}
