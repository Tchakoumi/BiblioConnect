<?php

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, PublicationHasLanguage>
     */
    #[ORM\OneToMany(targetEntity: PublicationHasLanguage::class, mappedBy: 'language', orphanRemoval: true)]
    private Collection $publicationHasLanguages;

    public function __construct()
    {
        $this->publicationHasLanguages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $publicationHasLanguage->setLanguage($this);
        }

        return $this;
    }

    public function removePublicationHasLanguage(PublicationHasLanguage $publicationHasLanguage): static
    {
        if ($this->publicationHasLanguages->removeElement($publicationHasLanguage)) {
            // set the owning side to null (unless already changed)
            if ($publicationHasLanguage->getLanguage() === $this) {
                $publicationHasLanguage->setLanguage(null);
            }
        }

        return $this;
    }
}
