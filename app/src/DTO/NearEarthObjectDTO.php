<?php

namespace App\DTO;

use DateTime;
use Symfony\Component\Validator\Constraint as Assert;

class NearEarthObjectDTO
{
    /**
     * @Assert\Type(type="date")
     * @Assert\NotBlank
     */
    public \DateTime $date;

    /**
     * @Assert\Type(type="integer")
     * @Assert\Positive
     * @Assert\NotBlank
     */
    public int $reference;

    /**
     * @Assert\Type(type="string")
     * @Assert\Length(min = 1, max = 255)
     * @Assert\NotBlank
     */
    public string $name;

    /**
     * @Assert\Positive
     * @Assert\NotBlank
     * @Assert\Type(type="float")
     */
    public float $speed;

    /**
     * @Assert\Type(
     *     type="boolean",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    public bool $isHazardous = false;

    /**
     * @param string $date
     * @param $reference
     * @param $name
     * @param $speed
     * @param bool $isHazardous
     */
    public function __construct($date, int $reference, string $name, float $speed, bool $isHazardous = false)
    {
        $this->date = $date;
        $this->reference = $reference;
        $this->name = $name;
        $this->speed = $speed;
        $this->isHazardous = $isHazardous;
    }


}
