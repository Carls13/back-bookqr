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
            'id' => $book->getId(),
            'tipo' => $book->getTipo(),
            'asignatura' => $book->getAsignatura(),
            'nombre' => $book->getNombre(),
            'autor' => $book->getEditorial(),
            'editorial' => $book->getEditorial(),
            'codigo' => $book->getCodigo(),
            'status' => $book->getStatus()
        ];
    }

}