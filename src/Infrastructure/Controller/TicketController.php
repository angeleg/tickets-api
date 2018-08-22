<?php
declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Ticket\BuyTicketCommand;
use JMS\Serializer\SerializerInterface;
use League\Tactician\CommandBus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

final class TicketController extends Controller
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
     * @Route("/tickets/{ticketId}/buy", methods={"POST"})
     */
    public function buyTicket(Request $request, string $ticketId)
    {
        // todo: implement + catch TicketAlreadyBoughtException and throw a 500
    }
}
