<?php

namespace App\Entity;

class Animal
{
    private int $id;

    private string $name;

    private Category $category;

    private string $image;

    private string $status;

    private Collection $tags;

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

    public function getImage(): string
    {
        return $this->image;
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

    public function setImage(string $image): Animal
    {
        $this->image = $image;
        return $this;
    }

    public function setStatus(string $status): Animal
    {
        $this->status = $status;
        return $this;
    }

    // Method to return array representation (for XML or API usage)
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'image' => $this->image,
            'status' => $this->status,
        ];
    }
}