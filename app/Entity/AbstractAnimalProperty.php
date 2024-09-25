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

     public function setId(int $id): AbstractAnimalProperty
     {
        $this->id = $id;
        return $this;
     }

     public function setName(string $name): AbstractAnimalProperty
     {
        $this->name = $name;
        return $this;
     }

     public function  __toString()
     {
         return $this->name;
     }

}