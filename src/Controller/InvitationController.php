<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Repository\EventRepository;
use App\Repository\InvitationRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InvitationController extends AbstractController
{
    #[Route('/api/event/invite/{id}', name: 'send_invitation', methods: ['POST'])]
    public function sendInvitation(int $id, Request $request, EventRepository $eventRepository, ProfileRepository $profileRepository, EntityManagerInterface $manager, Security $security, InvitationRepository $invitationRepository, UserRepository $userRepository): JsonResponse
    {
        $organizer = $security->getUser()->getProfile();
        $event = $eventRepository->find($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->getOrganizer() !== $organizer) {
            return $this->json(['error' => 'You are not the organizer of this event'], 403);
        }

        if ($event->isPublic()) {
            return $this->json(['error' => 'Invitations are only for private events'], 403);
        }

        if ($event->getStatus() === 'canceled') {
            return $this->json(['error' => 'Event is canceled. No invitations can be sent.'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $receiverId = $data['receiverId'] ?? null;

        $receiver = $userRepository->find($receiverId)->getProfile();

        if ($receiver === $organizer) {
            return $this->json(['error' => 'You cannot invite yourself'], 400);
        }
        if (!$receiver) {
            return $this->json(['error' => 'Receiver not found'], 404);
        }

        $existingInvitation = $invitationRepository->findOneBy(['event' => $event, 'receiver' => $receiver]);
        if ($existingInvitation) {
            return $this->json(['error' => 'An invitation has already been sent to this user'], 400);
        }

        foreach ($event->getParticipants() as $participant) {
            if ($participant === $receiver) {
                return $this->json(['error' => 'This user is already a participant'], 400);
            }
        }

        $invitation = new Invitation();

        $invitation->setStatus("pending");
        $invitation->setEvent($event);
        $invitation->setReceiver($receiver);


        $event = $invitation->getEvent();
        $currentDate = new \DateTime('now');
        $startDate = $event->getStartDate();

        if ($currentDate >= $startDate) {
            return $this->json(['error' => 'The event has already started, you cannot sent an invitation'], 400);
        }

        $manager->persist($invitation);
        $manager->flush();

        return $this->json(['message' => 'Invitation sent successfully', 'event' => $event], 200, [], ['groups' => 'events:read']);
    }

    #[Route('/api/invitations', name: 'list_invitations', methods: ['GET'])]
    public function listInvitations(InvitationRepository $invitationRepository, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();

        if (!$profile) {
            return $this->json(['error' => 'Profile not found'], 404);
        }

        $invitations = $invitationRepository->findBy(['receiver' => $profile]);

        return $this->json($invitations, 200, [], ['groups' => 'invitations:read']);
    }



    #[Route('/api/invitation/accept/{id}', name: 'accept_invitation', methods: ['POST'])]
    #[Route('/api/invitation/deny/{id}', name: 'deny_invitation', methods: ['POST'])]
    public function manageInvitation(int $id, Request $request, InvitationRepository $invitationRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $invitation = $invitationRepository->find($id);

        if (!$invitation) {
            return $this->json(['error' => 'Invitation not found'], 404);
        }

        if ($invitation->getReceiver() !== $profile) {
            return $this->json(['error' => 'You are not authorized to accept this invitation'], 403);
        }

        $event = $invitation->getEvent();
        $currentDate = new \DateTime('now');
        $startDate = $event->getStartDate();

        if ($currentDate >= $startDate) {
            return $this->json(['error' => 'The event has already started, you cannot modify this invitation'], 400);
        }


        $route = $request->attributes->get('_route');
        switch ($route) {
            case 'accept_invitation':
                if ($invitation->getStatus() == "accepted") {
                    return $this->json(['error' => 'Invitation already accepted'], 400);
                }
                $invitation->setStatus("accepted");
                $event = $invitation->getEvent();
                if ($event->getStatus() === 'canceled') {
                    return $this->json(['error' => 'The event is canceled. You cannot accept your invitation.'], 400);
                }
                $event->addParticipant($profile);
                $manager->persist($event);
                $manager->persist($invitation);
                $manager->flush();
                return $this->json(['message' => 'Invitation accepted successfully'], 200);
            case 'deny_invitation':
                if ($invitation->getStatus() == "denied") {
                    return $this->json(['error' => 'Invitation already denied'], 400);
                }
                $invitation->setStatus("denied");
                $event = $invitation->getEvent();
                if ($event->getStatus() === 'canceled') {
                    return $this->json(['error' => 'The event is canceled. You cannot deny your invitation.'], 400);
                }
                if ($event->getParticipants()->contains($profile)) {
                    $event->removeParticipant($profile);
                    $manager->persist($event);
                }

                $manager->persist($invitation);
                $manager->flush();

                return $this->json(['message' => 'Invitation denied successfully'], 200);
        }


        return $this->json(['error' => 'Invalid action'], 400);

    }

}
