<?php

namespace App\Helpers;

trait EntityTrait
{
    /**
     * Fill all properties from an array
     * @param $data
     */
    public function fill($data)
    {
        foreach ($data as $property => $value) {
            //does not allow to fill uuid field
            if ($property === 'uuid') {
                continue;
            }

            $this->{$property} = $value;
        }
    }


}