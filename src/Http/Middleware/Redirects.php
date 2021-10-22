<?php

namespace TFD\Redirects\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TFD\Redirects\Redirect;
use TFD\Redirects\RedirectRepository;

class Redirects
{
    protected $repository;

    public function __construct(RedirectRepository $redirectRepository)
    {
        $this->repository = $redirectRepository;
    }

    public function handle(Request $request, $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() !== 404) {
            return $response;
        }

        $source = $request->path();
        if (!$this->repository->sourceExists($source)) {
            return $response;
        }

        $redirect = $this->repository->getBySource($source);
        if (!$redirect['active']) {
            return $response;
        }

        $red = new Redirect($redirect, $this->repository);
        $red->hit();
        
        return redirect($red->generateTargetUrl(), $redirect['status']);
    }
}
