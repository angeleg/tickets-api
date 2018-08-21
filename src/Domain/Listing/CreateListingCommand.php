<?php
declare(strict_types=1);

namespace App\Domain\Listing;

use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateListingCommand
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Serializer\Type("string")
     *
     * @var string
     */
    private $seller;

    /**
     * @Assert\NotNull()
     * @Assert\All({
     *      @Assert\NotBlank(),
     *      @Assert\Collection(
     *         fields = {
     *              "type" = {
     *                  @Assert\Type("string"),
     *                  @Assert\NotBlank()
     *              },
     *              "value" = {
     *                  @Assert\Type("string"),
     *                  @Assert\NotBlank()
     *              }
     *         }
     *     )
     * })
     * @Serializer\Type("array<array>")
     *
     * @var array
     */
    private $barcodes;

    /**
     * @Assert\NotNull()
     * @Assert\Collection(
     *      fields = {
     *           "amount" = {
     *               @Assert\Type("integer"),
     *               @Assert\NotBlank
     *           },
     *           "currency" ={
     *               @Assert\Type("string"),
     *               @Assert\NotBlank
     *           },
     *      },
     * )
     * @Serializer\Type("array")
     *
     * @var array
     */
    private $price;

    public function __construct(string $seller, array $barcodes, array $price)
    {
        $this->generateId();
        $this->seller   = $seller;
        $this->barcodes = $barcodes;
        $this->price    = $price;
    }

    public function generateId(): void
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSeller(): string
    {
        return $this->seller;
    }

    /**
     * @return array
     */
    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    /**
     * @return array
     */
    public function getPrice(): array
    {
        return $this->price;
    }
}