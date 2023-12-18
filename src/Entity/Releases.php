<?php

namespace App\Entity;

use App\Repository\ReleaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReleaseRepository::class)
 */
class Releases
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="idRelease",type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $release;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelease(): ?string
    {
        return $this->release;
    }

    public function setRelease(string $release): self
    {
        $this->release = $release;

        return $this;
    }
}

