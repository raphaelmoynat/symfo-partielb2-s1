<?php

namespace App\Controller;

use App\Entity\Bed;
use App\Entity\Event;
use App\Entity\Invitation;
use App\Entity\Room;
use App\Repository\EventRepository;
use App\Repository\InvitationRepository;
use App\Repository\ProfileRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

class EventController extends AbstractController
{
    #[Route('/api/event', name: 'app_room')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        return $this->json($events, 200, [], ['groups' => 'events:read']);
    }

    #[Route('/api/create/event/public', name: 'create_event_public', methods: ['POST'])]
    #[Route('/api/create/event/private', name: 'create_event_private', methods: ['POST'])]
    public function create(Request $request, EventRepository $eventRepository, SerializerInterface $serializer, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $organizer = $security->getUser()->getProfile();

        $event = $serializer->deserialize($request->getContent(), Event::class, 'json');
        $event->setOrganizer($organizer);

        $route = $request->attributes->get('_route');
        switch ($route) {
            case 'create_event_public':
                $event->setPublic(true);
                $event->setPlacePublic(true);
                break;

            case 'create_event_private':
                $event->setPublic(false);
                $event->setPlacePublic(false);
                break;
        }

        $manager->persist($event);
        $manager->flush();

        return $this->json(['message' => 'Event created successfully'], 200);
    }

    #[Route('/api/event/public/join/{id}', name: 'join_event', methods: ['POST'])]
    public function joinEvent(int $id, EventRepository $eventRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $guest = $security->getUser()->getProfile();

        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }


        if (!$event->isPublic()) {
            return $this->json(['error' => 'You cannot join a private event'], 403);
        }

        if ($event->getOrganizer() == $guest) {
            return $this->json(['error' => 'You cannot join your event'], 403);

        }

        foreach ($event->getParticipants() as $participant){
            if ($participant== $guest){
                return $this->json(['error' => 'You are already join this event'], 403);

            }
        }


        $event->addParticipant($guest);

        $manager->persist($event);
        $manager->flush();

        return $this->json(['message' => 'You have successfully joined the event : '. $event->getPlace()], 200);
    }



}
