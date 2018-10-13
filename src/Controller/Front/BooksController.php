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
use Symfony\Component\HttpKernel\Exception\HttpException;

class BooksController extends FOSRestController
{

    use ValidationTrait,
        FractableTrait,
        PaginationTrait;

    protected $createConstraint;
    protected $updateConstraint;
    protected $deleteConstraint;
    protected $BOOKQR_SECRET_KEY = '123BOOKQR';

    function __construct()
    {
        $this->createConstraint = new Assert\Collection(array(
            'key' => new Assert\Required(),
            'tipo' => new Assert\Length(['max' => 255]),
            'asignatura' => new Assert\Length(['max' => 255]),
            'nombre' => new Assert\Length(['max' => 255]),
            'autor' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'editorial' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'codigo' => new Optional([
                new Assert\Length(['max' => 255])
                ])
        ));

        $this->updateConstraint = new Assert\Collection(array(
            'key' => new Assert\Required(),
            'tipo' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'asignatura' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'nombre' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'autor' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'editorial' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'codigo' => new Optional([
                new Assert\Length(['max' => 255])
                ]),
            'status' => new Optional([
                new Assert\Type('numeric')
                ])
        ));
    }

    /**
     * Show just one record
     * @Get("/books/{id}", name="front_books_show")
     * @param Book $book
     * @return Response
     */
    public function show(Book $book)
    {
        $book = $this->transform($book, new BookTransformer());

        $response['code'] = 200;
        $response['message'] = "OK";
        $response['response'] = $book['data'];

        $view = $this->view($response, Response::HTTP_OK);
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
        $post = $request->getContent();
        $array = json_decode($post, true);
        $this->validateData($array, $this->createConstraint);

        try {
            if ($array['key'] != $this->BOOKQR_SECRET_KEY) {
                throw new HttpException(403, 'Clave incorrecta');
            }

            //fill entity with request data
            $book = new Book();
            $book->fill($array);

            //save entity
            $db = $this->getDoctrine()->getManager('default');
            $db->persist($book);
            $db->flush();

            $book = $this->transform($book, new BookTransformer());

            $response['code'] = 201;
            $response['message'] = "OK";
            $response['response'] = $book['data'];
            $resp = Response::HTTP_CREATED;
        } catch (HttpException $e) {
            $response['code'] = 403;
            $response['message'] = "Error";
            $response['response'] = $e->getMessage();
            $resp = Response::HTTP_UNAUTHORIZED;
        }
        $view = $this->view($response, $resp);
        return $this->handleView($view);
    }

    /**
     * Updates and validates a record
     * @Put("/books/{id}", name="front_books_update")
     * @param Request $request
     * @param  Book $book
     * @return Response
     */
    public function update(Request $request, Book $book)
    {
        //validate data
        $post = $request->getContent();
        $array = json_decode($post, true);
        $this->validateData($array, $this->updateConstraint);

        try {
            if ($array['key'] != $this->BOOKQR_SECRET_KEY) {
                throw new HttpException(403, 'Clave incorrecta');
            }

            //fill entity with request data
            $book->fill($array);

            //save entity
            $db = $this->getDoctrine()->getManager('default');
            $db->flush();

            $book = $this->transform($book, new BookTransformer());

            $response['code'] = 200;
            $response['message'] = "OK";
            $response['response'] = $book['data'];
            $resp = Response::HTTP_OK;
        } catch (HttpException $e) {
            $response['code'] = 403;
            $response['message'] = "Error";
            $response['response'] = $e->getMessage();
            $resp = Response::HTTP_UNAUTHORIZED;
        }
	$view = $this->view($response, $resp);
        return $this->handleView($view);
    }

    /**
     * Deletes a record
     * @Delete("/books/{id}", name="front_books_delete")
     * @param Request $request
     * @param  Book $book
     * @return Response
     */
    public function delete(Request $request, Book $book)
    {
        //validate data
        $post = $request->getContent();
        $array = json_decode($post, true);

        try {
            if ($array['key'] != $this->BOOKQR_SECRET_KEY) {
                throw new HttpException(403, 'Clave incorrecta');
            }
            //save entity
            $db = $this->getDoctrine()->getManager('default');
            $db->remove($book);
            $db->flush();

            $response = [];
            $resp = Response::HTTP_NO_CONTENT;
        } catch (HttpException $ex) {
            $response['code'] = 403;
            $response['message'] = "Error";
            $response['response'] = $e->getMessage();
            $resp = Response::HTTP_UNAUTHORIZED;
        }

        $view = $this->view($response, $resp);
        return $this->handleView($view);
    }
}
