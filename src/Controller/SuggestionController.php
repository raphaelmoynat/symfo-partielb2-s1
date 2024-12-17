<?php

namespace App\Controller;

use App\Entity\Contribution;
use App\Entity\Suggestion;
use App\Entity\Charge;
use App\Repository\ChargeRepository;
use App\Repository\ContributionRepository;
use App\Repository\EventRepository;
use App\Repository\SuggestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SuggestionController extends AbstractController
{
    #[Route('/api/suggestion/create/{id}', name: 'add_suggestion', methods: ['POST'])]
    public function addSuggestion(int $id, Request $request, EventRepository $eventRepository, ContributionRepository $contributionRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $event = $eventRepository->find($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->getOrganizer() !== $profile) {
            return $this->json(['error' => 'You are not the organizer of this event'], 403);
        }

        $contribution = $contributionRepository->findOneBy(['event' => $event]);
        if (!$contribution) {
            $contribution = new Contribution();
            $contribution->setEvent($event);
            $manager->persist($contribution);
        }

        $data = json_decode($request->getContent(), true);
        $suggestion = new Suggestion();
        $suggestion->setName($data['name']);
        $suggestion->setContribution($contribution);

        $manager->persist($suggestion);
        $manager->flush();

        return $this->json(['message' => 'Suggestion added successfully'], 200);
    }

    #[Route('/api/suggestion/take/{id}', name: 'take_suggestion', methods: ['POST'])]
    public function takeSuggestion(int $id, SuggestionRepository $suggestionRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();
        $suggestion = $suggestionRepository->find($id);

        if (!$suggestion) {
            return $this->json(['error' => 'Suggestion not found'], 404);
        }

        if ($suggestion->getStatus() === 'take in charge') {
            return $this->json(['error' => 'Suggestion is already taken'], 400);
        }



        $charge = new Charge();
        $charge->setParticipant($profile);
        $charge->setName($suggestion->getName());
        $charge->setSuggestion($suggestion);
        $charge->setContribution($suggestion->getContribution());

        $suggestion->setStatus('take in charge');
        $suggestion->setCharge($charge);

        $manager->persist($charge);
        $manager->flush();

        return $this->json(['message' => 'Suggestion taken successfully'], 200);
    }

    #[Route('/api/contribution/take-charge/{id}', name: 'add_take_charge', methods: ['POST'])]
    public function addCharge(int $id, Request $request, EventRepository $eventRepository, ContributionRepository $contributionRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();

        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        if ($event->isPublic()) {
            return $this->json(['error' => 'Contributions can only be added to private events'], 403);
        }

        $isOrganizer = ($event->getOrganizer() === $profile);
        $isParticipant = $event->getParticipants()->contains($profile);

        if (!$isOrganizer && !$isParticipant) {
            return $this->json(['error' => 'You are not authorized to add contributions for this event'], 403);
        }

        $contribution = $contributionRepository->findOneBy(['event' => $event]);

        $data = json_decode($request->getContent(), true);
        $charge = new Charge();
        $charge->setName($data['name']);
        $charge->setParticipant($profile);
        $charge->setContribution($contribution);

        $manager->persist($charge);
        $manager->flush();

        return $this->json(['message' => 'Charge added successfully',], 201);
    }

    #[Route('/api/charge/delete/{id}', name: 'delete_charge', methods: ['DELETE'])]
    public function deleteCharge(int $id, ChargeRepository $chargeRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();

        $charge = $chargeRepository->find($id);
        if (!$charge) {
            return $this->json(['error' => 'Charge not found'], 404);
        }

        $event = $charge->getContribution()->getEvent();
        $isOrganizer = ($event->getOrganizer() === $profile);
        $isParticipant = ($charge->getParticipant() === $profile);

        if (!$isOrganizer && !$isParticipant) {
            return $this->json(['error' => 'You are not authorized to delete this charge'], 403);
        }


        $suggestion = $charge->getSuggestion();

        if ($suggestion) {
            $suggestion->setStatus('to take in charge');
            $suggestion->setCharge(null);
            $manager->persist($suggestion);
        }
        $manager->remove($charge);
        $manager->flush();

        return $this->json(['message' => 'Charge deleted successfully'], 200);
    }


    #[Route('/api/charge/update/{id}', name: 'update_charge', methods: ['PUT'])]
    public function updateCharge(int $id, Request $request, ChargeRepository $chargeRepository, EntityManagerInterface $manager, Security $security): JsonResponse
    {
        $profile = $security->getUser()->getProfile();

        $charge = $chargeRepository->find($id);
        if (!$charge) {
            return $this->json(['error' => 'Charge not found'], 404);
        }

        $event = $charge->getContribution()->getEvent();
        $isOrganizer = ($event->getOrganizer() === $profile);
        $isParticipant = ($charge->getParticipant() === $profile);

        if (!$isOrganizer && !$isParticipant) {
            return $this->json(['error' => 'You are not authorized to update this charge'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) {
            $charge->setName($data['name']);
        }

        $manager->persist($charge);
        $manager->flush();

        return $this->json([
            'message' => 'Charge updated successfully'], 200);
    }







}


