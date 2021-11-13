<?php

namespace App\Entity;

use App\Repository\NearEarthObjectRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=NearEarthObjectRepository::class)
 * @UniqueEntity(fields={"reference"})
 */
class NearEarthObject implements EntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private DateTimeImmutable $date;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private int $reference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="float")
     * Stored as kilometers per hour.
     */
    private float $speed;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type(
     *     type="boolean",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private bool $is_hazardous = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): self
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
}
