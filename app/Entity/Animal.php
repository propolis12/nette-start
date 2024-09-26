<?php

namespace App\Entity;

class Animal
{
    private int $id;

    private string $name;

    private Category $category;

    private array $photoUrls;

    private string $status;

    private array $tags;

    public function __construct()
    {
        $this->photoUrls = [];
        $this->tags = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Animal
    {
        $this->id = $id;
        return $this;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getPhotoUrls(): array
    {
        return $this->photoUrls;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    // Setters
    public function setName(string $name): Animal
    {
        $this->name = $name;
        return $this;
    }

    public function setCategory(Category $category): Animal
    {
        $this->category = $category;
        return $this;
    }

    public function setPhotoUrls(array $photoUrls): Animal
    {
        $this->photoUrls = $photoUrls;
        return $this;
    }

    public function setStatus(string $status): Animal
    {
        $this->status = $status;
        return $this;
    }

    public function addPhotoUrl(string $photoUrl): Animal
    {
        $this->photoUrls[] = $photoUrl;
        return $this;
    }

    // Method to return array representation (for XML or API usage)
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'image' => $this->photoUrls,
            'status' => $this->status,
        ];
    }

    public function addTag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags = array_filter($this->tags, fn($t) => $t !== $tag);
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): Animal
    {
        $this->tags = $tags;
        return $this;
    }


}