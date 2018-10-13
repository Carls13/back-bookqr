<?php

namespace App\Listener;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use App\Helpers\LanguageTrait;

class ApiExceptionsHandler
{
    use LanguageTrait;

    protected $translator;

    function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * General exception for all framework
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        $this->checkLanguage($request, $this->translator);
        $response = $this->handle($request, $event->getException());

        $event->setResponse($response);
    }

    /**
     * Treats all exceptions from the app and response a correct message/http code
     * @param Request $request
     * @param $exception
     * @return JsonResponse
     */

    public function handle(Request $request, \Exception $exception)
    {
        //trying to use another http method
        if ($exception instanceof ValidationException) {
            return $this->responseErrors($exception->getMessages());
        }

        //401, unauthorized
        if ($exception instanceof UnauthorizedHttpException) {
            return $this->response($this->translator->trans('UNAUTHORIZED'), Response::HTTP_UNAUTHORIZED);
        }

        //403, access denied
        if ($exception instanceof AccessDeniedHttpException) {
            return $this->response($this->translator->trans('ACCESS_DENIED'), Response::HTTP_FORBIDDEN);
        }

        //404, probably @ParamConverter converter exception, not able to do /entity/{id} or any other not matching route
        if ($exception instanceof NotFoundHttpException) {
            return $this->response($this->translator->trans('NOT_FOUND'), Response::HTTP_NOT_FOUND);
        }

        //405, trying to use another http method
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->response($this->translator->trans('METHOD_NOT_ALLOWED'), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        //if specific http exception with a code and message
        if ($exception instanceof HttpException) {
            return $this->handleHttpExceptions($request, $exception);
        }

        //for develop throw original message
        if (getenv('APP_ENV') === 'dev') {
            return $this->response($exception, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //general exceptions, not showing specific trace or message
        return $this->response($this->translator->trans('WRONG'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Handle specific http exceptions with a code and message
     * @param Request $request
     * @param HttpException $exception
     * @return JsonResponse
     */
    private function handleHttpExceptions(Request $request, HttpException $exception)
    {	
        switch ($exception->getStatusCode()) {
            case Response::HTTP_NOT_FOUND:
                return $this->response($exception->getMessage(), Response::HTTP_NOT_FOUND);
                break;
            default:
                return $this->response($this->translator->trans('WRONG'), Response::HTTP_INTERNAL_SERVER_ERROR);
                break;
        }
    }

    /**
     * Return a json response with a http code and message
     * @param mixed $exception
     * @param int $code
     * @return JsonResponse
     */
    private function response($exception, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        //FIXME add extra metadata to this response
        $response = [
            'code' => $code
        ];

        //get message from exception or direct message
        if ($exception instanceof \Exception) {
            $response['message'] = $exception->getMessage();

            //trace only for dev
            if (getenv('APP_ENV') === 'dev') {
                $response['trace'] = $exception->getTrace();
            }

        } else if (is_string($exception)) {
            $response['message'] = $exception;
        }

        return new JsonResponse($response, $code);
    }

    /**
     * Return a json response with a http code and message
     * @param $errors
     * @return JsonResponse
     */
    private function responseErrors($errors)
    {
        $code = Response::HTTP_UNPROCESSABLE_ENTITY;

        //FIXME add extra metadata to this response
        $response = [
            'code' => $code,
            'message' => $this->translator->trans('VALIDATION_ERROR')
        ];

        $response['errors'] = $errors;

        return new JsonResponse($response, $code);
    }
}
