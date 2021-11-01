<?php

namespace TFD\Redirects\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TFD\Redirects\Modules\NotFound\NotFound;
use TFD\Redirects\Modules\NotFound\NotFoundRepository;
use TFD\Redirects\Modules\Redirect\Redirect;
use TFD\Redirects\Modules\Redirect\RedirectRepository;

class Redirects
{
    protected $redirectRepository;
    protected $notFoundRepository;

    public function __construct(RedirectRepository $redirectRepository, NotFoundRepository $notFoundRepository)
    {
        $this->redirectRepository = $redirectRepository;
        $this->notFoundRepository = $notFoundRepository;
    }

    public function handle(Request $request, $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $source = $request->path();
        if (!$this->redirectRepository->sourceExists($source)) {
            return $this->handle404($response, $request, $next);
        }

        $redirect = $this->redirectRepository->getBySource($source);

        $red = new Redirect($redirect, $this->redirectRepository);
        if (!$red->isActive()) {
            return $response;
        }

        $red->hit();
        
        return redirect($red->generateTargetUrl(), $redirect['status']);
    }

    protected function handle404($response, request $request, $next)
    {
        $notFound = new NotFound(['url' => $request->url()], $this->notFoundRepository);
        $notFound->hit();

        return $response;
    }
}
