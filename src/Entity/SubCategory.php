<?php

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubCategoryRepository::class)
 */
class SubCategory
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
    private $idCategory;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $subCategory;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $subCategoryEng;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $subCategoryEsp;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $subCategoryAll;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCategory(): ?int
    {
        return $this->idCategory;
    }

    public function setIdCategory(int $idCategory): self
    {
        $this->idCategory = $idCategory;

        return $this;
    }

    public function getSubCategory(): ?string
    {
        return $this->subCategory;
    }

    public function setSubCategory(string $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getSubCategoryEng(): ?string
    {
        return $this->subCategoryEng;
    }

    public function setSubCategoryEng(string $subCategoryEng): self
    {
        $this->subCategoryEng = $subCategoryEng;

        return $this;
    }

    public function getSubCategoryEsp(): ?string
    {
        return $this->subCategoryEsp;
    }

    public function setSubCategoryEsp(string $subCategoryEsp): self
    {
        $this->subCategoryEsp = $subCategoryEsp;

        return $this;
    }

    public function getSubCategoryAll(): ?string
    {
        return $this->subCategoryAll;
    }

    public function setSubCategoryAll(string $subCategoryAll): self
    {
        $this->subCategoryAll = $subCategoryAll;

        return $this;
    }
}
