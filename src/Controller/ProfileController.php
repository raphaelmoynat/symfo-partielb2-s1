<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'app_profile')]
    public function index(ProfileRepository $profileRepository): Response
    {
        $profiles = $profileRepository->findAll();

        foreach ($profiles as $profile) {
            $profile->events = $profile->getEvents()->filter(fn($event) => $event->isPublic());
        }

        return $this->json($profiles, 200, [], ['groups' => 'profiles:read']);
    }

}
