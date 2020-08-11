<?php

namespace App\Entity;

use App\Repository\NearEarthObjectRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=NearEarthObjectRepository::class)
 * @UniqueEntity(fields={"reference"})
 */
class NearEarthObject //implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * Stored as kilometers per hour.
     */
    private $speed;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_hazardous = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getReference(): ?int
    {
        return $this->reference;
    }

    public function setReference(int $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getIsHazardous(): ?bool
    {
        return $this->is_hazardous;
    }

    public function setIsHazardous(bool $is_hazardous): self
    {
        $this->is_hazardous = $is_hazardous;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'reference' => $this->getReference(),
            'speed' => $this->getSpeed(),
            'is_hazardous' => $this->getIsHazardous(),
            'date' => $this->getDate(),
        ];
    }
}
