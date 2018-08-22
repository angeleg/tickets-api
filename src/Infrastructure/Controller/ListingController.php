<?php
declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Listing\Exception\TicketConflictException;
use App\Domain\Listing\Exception\InvalidListingException;
use JMS\Serializer\SerializerInterface;
use League\Tactician\Bundle\Middleware\InvalidCommandException;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Domain\Listing\CreateListingCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TypeError;

final class ListingController extends Controller
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(CommandBus $commandbus, SerializerInterface $serializer)
    {
        $this->commandBus = $commandbus;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/listings/create", methods={"POST"})
     */
    public function createListing(Request $request)
    {
        try {
            /** @var CreateListingCommand $command */
            $command = $this->serializer->deserialize(
                $request->getContent(),
                CreateListingCommand::class,
                'json'
            );

            $command->generateId();
            $this->commandBus->handle($command);

        } catch (TypeError | InvalidCommandException | InvalidListingException $e) {
            return JsonResponse::create(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (TicketConflictException $e) {
            return JsonResponse::create(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return Response::create('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/listings/{listing_id}/verify", methods={"POST"})
     */
    public function verifyListing(Request $request, string $listingId)
    {
        // todo: implement + catch UnauthorizedToVerifyListingException and throw a 403
    }
}
