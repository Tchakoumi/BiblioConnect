<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Author $author = null;

    #[ORM\ManyToOne(inversedBy: 'publications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, PublicationHasLanguage>
     */
    #[ORM\OneToMany(targetEntity: PublicationHasLanguage::class, mappedBy: 'publication', orphanRemoval: true)]
    private Collection $publicationHasLanguages;

    public function __construct()
    {
        $this->publicationHasLanguages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, PublicationHasLanguage>
     */
    public function getPublicationHasLanguages(): Collection
    {
        return $this->publicationHasLanguages;
    }

    public function addPublicationHasLanguage(PublicationHasLanguage $publicationHasLanguage): static
    {
        if (!$this->publicationHasLanguages->contains($publicationHasLanguage)) {
            $this->publicationHasLanguages->add($publicationHasLanguage);
            $publicationHasLanguage->setPublication($this);
        }

        return $this;
    }

    public function removePublicationHasLanguage(PublicationHasLanguage $publicationHasLanguage): static
    {
        if ($this->publicationHasLanguages->removeElement($publicationHasLanguage)) {
            // set the owning side to null (unless already changed)
            if ($publicationHasLanguage->getPublication() === $this) {
                $publicationHasLanguage->setPublication(null);
            }
        }

        return $this;
    }
}
