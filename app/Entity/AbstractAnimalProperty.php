<?php

namespace App\Entity;

abstract class AbstractAnimalProperty
{
    protected int $id;

    protected string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}