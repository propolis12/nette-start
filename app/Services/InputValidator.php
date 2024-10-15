<?php

namespace App\Services;

use App\UI\Api\ApiPresenter;

class InputValidator
{

public function __construct()
{
}

    public static function validate(array $input): array
    {
        $errors = [];

        foreach ($input as $key => $value) {

            match ($key) {
                'name' => strlen($value) < 3 || strlen($value) > 50
                    ? $errors[] = "Meno musí obsahovať od 3 do 50 znakov."
                    : null,

                'category' => is_array($value) && isset($value['id'], $value['name'])
                    ? (function() use ($value, &$errors) {
                        if (!filter_var($value['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                            $errors[] = "Pole 'category[id]' musí byť celé nezáporné číslo.";
                        }

                        if (empty($value['name']) || strlen($value['name']) > 30) {
                            $errors[] = "Pole 'category[name]' musí byť vyplnené a obsahovať maximálne 30 znakov.";
                        }
                    })()
                    : $errors[] = "Pole 'category' musí obsahovať 'id' a 'name'.",

                'tags' => is_array($value) && !empty($value)
                    ? (function() use ($value, &$errors) {
                        foreach ($value as $tag) {
                            if (!is_string($tag)) {
                                $errors[] = "Každý tag musí byt string";
                            }
                        }
                    })()
                    : $errors[] = "Pole 'tags' musí byť neprázdne pole obsahujúce tagy.",

                'status' => in_array($value, ApiPresenter::ANIMAL_STATUSES, true)
                    ? null
                    : $errors[] = "Pole 'status' musí obsahovať hodnotu 'available', 'pending' alebo 'sold'.",

                default => null,
            };
        }

        return $errors;

    }

}