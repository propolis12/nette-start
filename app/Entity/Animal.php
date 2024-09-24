<?php

namespace App\Entity;

class Animal
{
    private string $name;
    private string $category;
    private string $image;
    private string $status;

    public function __construct(string $name, string $category, string $image, string $status)
    {
        $this->name = $name;
        $this->category = $category;
        $this->image = $image;
        $this->status = $status;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
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
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $status = $status;
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