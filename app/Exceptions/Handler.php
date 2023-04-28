<?php

namespace App\Exceptions;

use App\Http\Exception\AccessControlException;
use App\Http\Exception\InsufficientPrivilegeException;
use App\Http\Exception\ResourceNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        // return response([  parent::render($request, $exception)]);
        // return parent::render($request, $exception);

        // if ($request->is('api/*')) {

        if ($exception instanceof AccessControlException) {
            return new JsonResponse(["code" => $exception->getCode() ?? 422, "message" => $exception->getMessage()]);
        }

        if ($exception instanceof ResourceNotFoundException) {
            return new JsonResponse(["code" => $exception->getCode() ?? 404, "message" => $exception->getMessage()]);
        }

        if ($exception instanceof InsufficientPrivilegeException) {
            return new JsonResponse(["code" => $exception->getCode() ?? 403, "message" => $exception->getMessage()]);
        }

        if ($this->isHttpException($exception)) {

            if ($exception->getStatusCode() == 404) {

                return new JsonResponse(["code" => $exception->getStatusCode() ?? 404, "message" => "Not found.404"]);

            } else {

                return new JsonResponse(["code" => $exception->getStatusCode() ?? 405, "message" => "Not supported action" /*$exception->getMessage()*/ ?? "Resource no found"]);

            }
        }

        // }
        return parent::render($request, $exception);

    }
}
