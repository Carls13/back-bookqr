<?php

namespace App\Transformers;

use App\Entity\Book;
use League\Fractal\TransformerAbstract;

class BookTransformer extends TransformerAbstract
{

    /**
     * Transform a simple entity
     * @param Book $book
     * @return array
     */
    public function transform(Book $book)
    {
        return [
            'id' => $book->getUuid(),
            'xxx' => $book->getXXXX(),
            // extra fields goes here...
        ];
    }

}