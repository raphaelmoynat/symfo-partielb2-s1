<?php

namespace App\Entity;

use App\Repository\SuggestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SuggestionRepository::class)]
class Suggestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["events:read"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'suggestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contribution $contribution = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["events:read"])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(["events:read"])]
    private ?string $status = "to take in charge";

    #[ORM\OneToOne(mappedBy: 'suggestion')]
    #[Groups(["events:read"])]
    private ?Charge $charge = null;



    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCharge(): ?Charge
    {
        return $this->charge;
    }

    public function setCharge(?Charge $charge): static
    {
        $this->charge = $charge;
        return $this;
    }


}