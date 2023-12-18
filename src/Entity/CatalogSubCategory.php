<?php

namespace App\Entity;

use App\Repository\CatalogSubCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CatalogSubCategoryRepository::class)
 */
class CatalogSubCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $idSubCategory;

    /**
     * @ORM\Column(type="integer")
     */
    private $idCatalog;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdSubCategory(): ?int
    {
        return $this->idSubCategory;
    }

    public function setIdSubCategory(int $idSubCategory): self
    {
        $this->idSubCategory = $idSubCategory;

        return $this;
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
}
