<?php

namespace TFD\Redirects\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionsHandler;

class Handler extends ExceptionsHandler
{
    public function render()
    {
        dd("123");
    }
}