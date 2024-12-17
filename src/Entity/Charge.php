<?php

namespace App\Entity;

use App\Repository\ChargeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ChargeRepository::class)]
class Charge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["events:read"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'charges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["events:read"])]
    private ?Profile $participant = null;

    #[ORM\OneToOne(inversedBy: 'charge', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Suggestion $suggestion = null;

    #[ORM\ManyToOne(inversedBy: 'charges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contribution $contribution = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["events:read"])]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Profile
    {
        return $this->participant;
    }

    public function setParticipant(?Profile $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    public function getSuggestion(): ?Suggestion
    {
        return $this->suggestion;
    }

    public function setSuggestion(Suggestion $suggestion): static
    {
        $this->suggestion = $suggestion;

        return $this;
    }

    public function getContribution(): ?Contribution
    {
        return $this->contribution;
    }

    public function setContribution(?Contribution $contribution): static
    {
        $this->contribution = $contribution;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
