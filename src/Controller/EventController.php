<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Invitation;
use App\Repository\EventRepository;
use App\Repository\InvitationRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
    #[Route('/api/event/public', name: 'app_room')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy(['isPublic'=>true]);
        return $this->json($events, 200, [], ['groups' => 'events:read:public']);
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

        ;

        $manager->persist($event);
        $manager->flush();

        return $this->json(['message' => 'Event created successfully', 'event' => $event], 200, [], ['groups' => 'events:read']);
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

        if ($event->getStatus() === 'canceled') {
            return $this->json(['error' => 'The event is canceled.'], 400);
        }

        foreach ($event->getParticipants() as $participant){
            if ($participant== $guest){
                return $this->json(['error' => 'You are already join this event'], 403);

            }
        }


        $event->addParticipant($guest);

        $manager->persist($event);
        $manager->flush();

        return $this->json(['message' => 'You have successfully joined the event', 'event' => $event], 200, [], ['groups' => 'events:read']);
    }


    #[Route('/api/event/private/organizer', name: 'private_event_organizer', methods: ['GET'])]
    public function getPrivateEventsAsOrganizer(EventRepository $eventRepository, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $privateEvents = $eventRepository->findBy(['isPublic' => false]);
        $events = new ArrayCollection();

        foreach ($privateEvents as $privateEvent) {
            $isOrganizer = ($privateEvent->getOrganizer() === $profile);

            if ($isOrganizer) {
                $events->add($privateEvent);
            }
        }

        return $this->json($events, 200, [], ['groups' => 'events:read']);
    }

    #[Route('/api/event/private/participant', name: 'private_event_participant', methods: ['GET'])]
    public function getPrivateEventsAsParticipant(EventRepository $eventRepository, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $privateEvents = $eventRepository->findBy(['isPublic' => false]);
        $events = new ArrayCollection();

        foreach ($privateEvents as $privateEvent) {
            $isOrganizer = ($privateEvent->getOrganizer() === $profile);

            if ($isOrganizer) {
                continue;
            }

            $isParticipant = $privateEvent->getParticipants()->contains($profile);

            if ($isParticipant) {
                $events->add($privateEvent);
            }
        }

        return $this->json($events, 200, [], ['groups' => 'events:read']);
    }

    #[Route('/api/event/cancel/{id}', name: 'cancel_event', methods: ['POST'])]
    #[Route('/api/event/on-schedule/{id}', name: 'on_schedule_event', methods: ['POST'])]
    public function changeEventStatus(int $id, Request $request, EventRepository $eventRepository, Security $security, EntityManagerInterface $manager): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $event = $eventRepository->find($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->getOrganizer() !== $profile) {
            return $this->json(['error' => 'You are not the organizer of this event'], 403);
        }

        $route = $request->attributes->get('_route');

        switch ($route) {
            case 'cancel_event':
                if ($event->getStatus() === 'canceled') {
                    return $this->json(['message' => 'Event is already canceled'], 200);
                }
                $event->setStatus('canceled');
                $manager->persist($event);
                $manager->flush();

                return $this->json(['message' => 'Event canceled successfully'], 200);

            case 'on_schedule_event':
                if ($event->getStatus() === 'on_schedule') {
                    return $this->json(['message' => 'Event is already on schedule'], 200);
                }
                $event->setStatus('on_schedule');
                $manager->persist($event);
                $manager->flush();

                return $this->json(['message' => 'Event status on schedule successfully'], 200);
        }

        return $this->json(['error' => 'Invalid action'], 400);
    }

    #[Route('/api/event/update/{id}', name: 'update_event', methods: ['PUT'])]
    public function updateEvent(int $id, Request $request, EventRepository $eventRepository, Security $security, EntityManagerInterface $manager): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $event = $eventRepository->find($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->getOrganizer() !== $profile) {
            return $this->json(['error' => 'You are not the organizer of this event'], 403);
        }

        if ($event->getStatus() === 'canceled') {
            return $this->json(['error' => 'The event is canceled and cannot be modified'], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['place'])) {
            $event->setPlace($data['place']);
        }

        if (isset($data['description'])) {
            $event->setDescription($data['description']);
        }

        if (isset($data['isPublic'])) {
            $event->setPublic($data['isPublic']);
        }

        if (isset($data['isPlacePublic'])) {
            $event->setPlacePublic($data['isPlacePublic']);
        }

        $currentDate = new \DateTime('now');
        if (isset($data['startDate'])) {
            $newStartDate = new \DateTime($data['startDate']);
            if ($newStartDate < $currentDate) {
                return $this->json(['error' => 'The start date cannot be in the past'], 400);
            }
            $event->setStartDate($newStartDate);
        }

        if (isset($data['endDate'])) {
            $newEndDate = new \DateTime($data['endDate']);
            $startDate = $event->getStartDate();
            if ($newEndDate <= $startDate) {
                return $this->json(['error' => 'The end date must be after the start date'], 400);
            }
            $event->setEndDate($newEndDate);
        }


        $manager->persist($event);
        $manager->flush();

        return $this->json(['message' => 'Event updated successfully', 'event' => $event], 200, [], ['groups' => 'events:read']);
    }


}
