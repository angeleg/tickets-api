<?php
declare(strict_types=1);

namespace App\Domain\Ticket\Barcode;

final class Barcode
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $type, string $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    public static function fromArray(array $data)
    {
        return new self($data['type'], $data['value']);
    }

    public function __toString() : string
    {
        return sprintf('%s:%s', $this->type, $this->value);
    }
}
