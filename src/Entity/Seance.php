<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column]
    private ?int $percieved_intensity = null;

    #[ORM\Column]
    private ?int $burned_calories = null;

    #[ORM\Column(length: 10)]
    private ?string $body_temperature_delta = null;

    #[ORM\Column]
    private ?int $muscle_fatigue = null;

    #[ORM\Column]
    private ?int $heart_recovery_rate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $heartbeart_variation = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPercievedIntensity(): ?int
    {
        return $this->percieved_intensity;
    }

    public function setPercievedIntensity(int $percieved_intensity): static
    {
        $this->percieved_intensity = $percieved_intensity;

        return $this;
    }

    public function getBurnedCalories(): ?int
    {
        return $this->burned_calories;
    }

    public function setBurnedCalories(int $burned_calories): static
    {
        $this->burned_calories = $burned_calories;

        return $this;
    }

    public function getBodyTemperatureDelta(): ?string
    {
        return $this->body_temperature_delta;
    }

    public function setBodyTemperatureDelta(string $body_temperature_delta): static
    {
        $this->body_temperature_delta = $body_temperature_delta;

        return $this;
    }

    public function getMuscleFatigue(): ?int
    {
        return $this->muscle_fatigue;
    }

    public function setMuscleFatigue(int $muscle_fatigue): static
    {
        $this->muscle_fatigue = $muscle_fatigue;

        return $this;
    }

    public function getHeartRecoveryRate(): ?int
    {
        return $this->heart_recovery_rate;
    }

    public function setHeartRecoveryRate(int $heart_recovery_rate): static
    {
        $this->heart_recovery_rate = $heart_recovery_rate;

        return $this;
    }

    public function getHeartbeartVariation(): ?string
    {
        return $this->heartbeart_variation;
    }

    public function setHeartbeartVariation(string $heartbeart_variation): static
    {
        $this->heartbeart_variation = $heartbeart_variation;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }
}
