<?php
declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListingControllerTest extends WEbTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @before
     */
    public function setUpDependencies()
    {
        $this->client = static::createClient(['debug' => false]);
    }

    public function testCreateListingHappyPath(): void
    {
        $requestBody = json_encode([
            'seller' => 'angele',
            'barcodes' => [
                [ 'type' => 'EAN-13', 'value' => '38974312923' ],
                [ 'type' => 'EAN-13', 'value' => '38974312924' ],
            ],
            'price' => [ 'amount' => 50, 'currency' => 'EUR' ]
        ]);

        $response = $this->sendApiRequest(Request::METHOD_POST, 'listings/create', $requestBody);

        self::assertEquals('204', $response->getStatusCode());
    }

    public function testCreateListingWithDuplicateBarcodesWillYield400(): void
    {
        $requestBody = json_encode([
            'seller' => 'angele',
            'barcodes' => [
                [ 'type' => 'EAN-13', 'value' => '38974312924' ],
                [ 'type' => 'EAN-13', 'value' => '38974312924' ],
            ],
            'price' => [ 'amount' => 50, 'currency' => 'EUR' ]
        ]);

        $response = $this->sendApiRequest(Request::METHOD_POST, 'listings/create', $requestBody);

        self::assertEquals('400', $response->getStatusCode());
    }

    public function testCreateListingWithMalformedCommandWillYield400(): void
    {
        $requestBody = json_encode([
            'seller'   => 'angele',
            'barcodes' => null,
            'price'    => [ 'amount' => 50, 'currency' => 'EUR' ]
        ]);

        $response = $this->sendApiRequest(Request::METHOD_POST, 'listings/create', $requestBody);

        self::assertEquals('400', $response->getStatusCode());
    }

    private function sendApiRequest(string $method, string $uri, string $requestBody = null): Response
    {
        $this->client->request($method, $uri, [], [], [], $requestBody);
        return $this->client->getResponse();
    }
}
