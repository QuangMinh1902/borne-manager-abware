<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="idCategory",type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $categoryEng;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $categoryEsp;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $categoryAll;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $picture;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategoryEng(): ?string
    {
        return $this->categoryEng;
    }

    public function setCategoryEng(string $categoryENG): self
    {
        $this->categoryEng = $categoryENG;

        return $this;
    }

    public function getCategoryEsp(): ?string
    {
        return $this->categoryEsp;
    }

    public function setCategoryEsp(string $categoryEsp): self
    {
        $this->categoryEsp = $categoryEsp;

        return $this;
    }

    public function getCategoryAll(): ?string
    {
        return $this->categoryAll;
    }

    public function setCategoryAll(string $categoryAll): self
    {
        $this->categoryAll = $categoryAll;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    
}
