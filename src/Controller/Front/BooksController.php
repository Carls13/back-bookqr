<?php

namespace App\Controller\Front;

use App\Entity\Book;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints as Assert;
use App\Transformers\BookTransformer;
use App\Helpers\ValidationTrait;
use App\Helpers\FractableTrait;
use App\Helpers\PaginationTrait;

class BooksController extends FOSRestController
{
    use ValidationTrait, FractableTrait, PaginationTrait;

    protected $createConstraint;
    protected $updateConstraint;

    function __construct()
    {
        $this->createConstraint = new Assert\Collection(array(
            'xxxxx' => new Assert\Length(['max' => 100]),
        ));

        $this->updateConstraint = new Assert\Collection(array(
            'xxxxx' => new Optional([
                new Assert\Length(['max' => 100])
            ]),
        ));

    }

    /**
     * Show just one record
     * @Get("/books/{uuid}", name="front_books_show")
     * @param Book $book
     * @return Response
     */
    public function show(Book $book)
    {
        $book = $this->transform($book, new BookTransformer());

        $view = $this->view($book, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Get a list of records
     * @Get("/books", name="front_books_list")
     * @param Request $request
     * @return Response
     */
    public function listAll(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        $booksQuery = $repository->findAllQuery();

        $booksPaginated = $this->paginate($booksQuery, $request);

        $books = $this->transform($booksPaginated, new BookTransformer());

        $view = $this->view($books, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Create and validates a record
     * @Post("/books", name="front_books_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        //validate data
        $post = $request->request->all();
        $this->validateData($post, $this->createConstraint);

        //fill entity with request data
        $book = new Book();
        $book->fill($post);

        //save entity
        $db = $this->getDoctrine()->getManager('default');
        $db->persist($book);
        $db->flush();

        $book = $this->transform($book, new BookTransformer());

        $view = $this->view($book, Response::HTTP_CREATED);
        return $this->handleView($view);
    }

    /**
     * Updates and validates a record
     * @Put("/books/{uuid}", name="front_books_update")
     * @param Request $request
     * @param  Book $book
     * @return Response
     */
    public function update(Request $request, Book $book)
    {
        //validate data
        $post = $request->request->all();
        $this->validateData($post, $this->updateConstraint);

        //fill entity with request data
        $book->fill($post);

        //save entity
        $db = $this->getDoctrine()->getManager('default');
        $db->flush();

        $book = $this->transform($book, new BookTransformer());

        $view = $this->view($book, Response::HTTP_OK);
        return $this->handleView($view);
    }

    /**
     * Deletes a record
     * @Delete("/books/{uuid}", name="front_books_delete")
     * @param  Book $book
     * @return Response
     */
    public function delete(Book $book)
    {
        //save entity
        $db = $this->getDoctrine()->getManager('default');
        $db->remove($book);
        $db->flush();

        $view = $this->view([], Response::HTTP_NO_CONTENT);
        return $this->handleView($view);
    }
}
