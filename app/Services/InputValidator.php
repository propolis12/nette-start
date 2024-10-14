<?php

namespace App\Services;

use App\UI\Api\ApiPresenter;
use Tracy\Debugger;

class InputValidator
{

public function __construct()
{
}

    public static function validate(array $input): array
    {
        $errors = [];

        foreach ($input as $key => $value) {
//            Debugger::log($key, Debugger::INFO);

            match ($key) {
                'name' => strlen($value) < 3 || strlen($value) > 50
                    ? $errors[] = "Meno musí obsahovať od 3 do 50 znakov."
                    : null,

                'category' => is_array($value) && isset($value['id'], $value['name'])
                    ? (function() use ($value, &$errors) {
                        // Validácia 'id' pre kategóriu
                        if (!filter_var($value['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
                            $errors[] = "Pole 'category[id]' musí byť celé nezáporné číslo.";
                        }

                        // Validácia 'name' pre kategóriu
                        if (empty($value['name']) || strlen($value['name']) > 30) {
                            $errors[] = "Pole 'category[name]' musí byť vyplnené a obsahovať maximálne 30 znakov.";
                        }
                    })()
                    : $errors[] = "Pole 'category' musí obsahovať 'id' a 'name'.",

                'tags' => is_array($value) && !empty($value)
                    ? (function() use ($value, &$errors) {
                        foreach ($value as $tag) {
                            Debugger::log($tag, Debugger::INFO);
                            if (!is_string($tag)) {
                                $errors[] = "Každý tag musí byt string";
                            }
                        }
                    })()
                    : $errors[] = "Pole 'tags' musí byť neprázdne pole obsahujúce tagy.",

                'status' => in_array($value, ApiPresenter::ANIMAL_STATUSES, true)
                    ? null
                    : $errors[] = "Pole 'status' musí obsahovať hodnotu 'available', 'pending' alebo 'sold'.",

                default => null, // Iné kľúče môžeme ignorovať alebo pridať ďalšiu logiku
            };
        }

        return $errors;

    }

}