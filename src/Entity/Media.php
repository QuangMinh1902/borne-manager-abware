<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="idMedia",type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $idCatalog;

    /**
     * @ORM\Column(type="string", length=127)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $mediaspace;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filePath;
 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCatalog(): ?int
    {
        return $this->idCatalog;
    }

    public function setIdCatalog(int $idCatalog): self
    {
        $this->idCatalog = $idCatalog;

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

    public function getMediaspace(): ?string
    {
        return $this->mediaspace;
    }

    public function setMediaspace(string $mediaspace): self
    {
        $this->mediaspace = $mediaspace;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }
 
}
