<?php
declare(strict_types=1);

namespace App\Tests\Integration\Listing;

use App\Domain\Listing\CreateListingCommand;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\JsonSerialization;

class CreateListingCommandDeserializationTest extends IntegrationTestCase
{
    use JsonSerialization;

    public function testDeserializationShouldBeDoneProperly()
    {
        $data = [
            'seller'   => 'angele',
            'barcodes' => [
                [
                    'type'  => 'EAN-13',
                    'value' => '38974312923',
                ],
                [
                    'type'  => 'EAN-13',
                    'value' => '38974312924',
                ],
            ],
            'price' => [
                'amount'   => 40,
                'currency' => 'EUR'
            ]
        ];

        /**
         * @var CreateListingCommand $command
         */
        $command = $this->fromJson($data, CreateListingCommand::class);

        self::assertInstanceOf(CreateListingCommand::class, $command);
        self::assertSame($data['seller'], $command->getSeller());
        self::assertSame($data['price'], $command->getPrice());
        self::assertSame($data['barcodes'], $command->getBarcodes());
    }
}
