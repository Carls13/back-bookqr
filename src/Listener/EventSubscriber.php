<?php

namespace App\Listener;

use App\Service\LoggingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Service\ApiUsersService;
use App\Helpers\LanguageTrait;
use Symfony\Component\Translation\TranslatorInterface;

class EventSubscriber implements EventSubscriberInterface
{
    use LanguageTrait;

    protected $translator;
    protected $logger;

    function __construct(TranslatorInterface $translator, LoggingService $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestPre', 10],
            ],
            KernelEvents::RESPONSE => [
                ['onKernelResponsePost', 10],
            ],
        ];
    }

    public function onKernelRequestPre(GetResponseEvent $event)
    {
	if (!$event->isMasterRequest()) {
		return;
	}
        $request = $event->getRequest();
	$method = $request->getRealMethod();
        $this->checkLanguage($request, $this->translator);

        //disabled for dev and test
        if (!in_array(getenv('APP_ENV'), ['dev', 'test'])) {
            $this->checkKongUser($request);
            $this->checkUserPermissionPerRoute($request);
        }
	if ('OPTIONS' == $method) {
		$response = new Response();
		$event->setResponse($response);
	}

    }

    public function onKernelResponsePost(FilterResponseEvent $event)
    {
	if (!$event->isMasterRequest()) {
		return;
	}
        $request = $event->getRequest();
        $response = $event->getResponse();
	$response->headers->set('Access-Control-Allow-Origin', '*');
	$response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE, PATCH, OPTIONS');
	$response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        //log custom access
        $this->logAccess($request, $response);
    }

    /**
     * Required Kong consumer id
     * @param Request $request
     */
    public function checkKongUser(Request $request)
    {
        if (!$request->headers->get('x-consumer-id')) {
            throw new UnauthorizedHttpException('Bearer');
        }
    }

    /**
     * Check
     * @param Request $request
     */
    public function checkUserPermissionPerRoute(Request $request)
    {
        $username = $request->headers->get('x-consumer-id');
        $route = $request->attributes->get('_route');

        $apiUsersService = new ApiUsersService();
        $responseCode = $apiUsersService->checkPermissionPerRoute($username, $route);

        switch ($responseCode) {
            case Response::HTTP_FORBIDDEN:
                throw new AccessDeniedHttpException();
                break;
        }

    }

    /**
     * Log in a custom logger with custom data
     * @param Request $request
     * @param Response $response
     */
    public function logAccess(Request $request, Response $response)
    {
        $httpCode = $response->getStatusCode();
        $isErrorCode = in_array((int) ($httpCode / 100), [4, 5]);

        $access = [
            'consumer' => $request->headers->get('x-consumer-id') ?? 'anonymous',
            'uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'route' => $request->attributes->get('_route'),
            'code' => $response->getStatusCode(),
        ];

        if ($isErrorCode) {
            $this->logger->logError($access);
        } else {
            $this->logger->logAccess($access);
        }

    }

}
