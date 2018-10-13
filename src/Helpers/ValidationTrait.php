<?php

namespace App\Helpers;

use App\Exceptions\ValidationException;

trait ValidationTrait
{

    /**
     * Validate based on constraints and return an array of errors and 422 if fails
     * @param $data
     * @param $validationConstraints
     * @return mixed
     * @throws ValidationException
     */
    public function validateData($data, $validationConstraints)
    {
        $validator = $this->get('validator');

        $violations = $validator->validate($data, $validationConstraints);
        $errors = [];

        if ($violations->count()) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            throw new ValidationException($errors);
        }
    }

}