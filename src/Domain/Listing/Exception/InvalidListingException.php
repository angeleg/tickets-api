<?php
declare(strict_types=1);

namespace App\Domain\Listing\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class InvalidListingException extends BadRequestHttpException
{
    public static function forDuplicateBarcodes(): self
    {
        return new self('Unable to create listing because it contains duplicate barcodes');
    }
}
