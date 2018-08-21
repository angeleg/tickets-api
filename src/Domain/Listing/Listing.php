<?php
declare(strict_types=1);

namespace App\Domain\Listing;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

final class Listing
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var string
     */
    private $seller;

    /**
     * @var Money
     */
    private $price;

    /**
     * @var bool
     */
    private $verified;

    public function __construct(
        UuidInterface $id,
        string $seller,
        Money $price,
        bool $verified
    ) {
        $this->id       = $id;
        $this->seller   = $seller;
        $this->price    = $price;
        $this->verified = $verified;
    }

    public static function create(UuidInterface $id, string $seller, Money $price)
    {
        return new self($id, $seller, $price, false);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getSeller(): string
    {
        return $this->seller;
    }

    public function getPrice() : Money
    {
        return $this->price;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function verify(): void
    {
        $this->verified = true;
    }
}
