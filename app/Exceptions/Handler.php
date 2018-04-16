<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Exceptions\ValidationException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

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
   * @param  \Exception  $exception
   * @return void
   */
  public function report(Exception $exception)
  {
      parent::report($exception);
  }

  /**
   * Render an exception into an HTTP response.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Exception  $exception
   * @return \Illuminate\Http\Response
   */
  public function render($request, Exception $e)
  {
    if($e instanceof ValidationException) {
      return $this->jsonResponse($e->getErrors(), 422);
    }
    if($e instanceof TokenExpiredException) {
      return $this->jsonResponse('token_expired', 401);
    }
    if($e instanceof TokenInvalidException) {
      return $this->jsonResponse('token_invalid', 401);
    }
    if($e instanceof JWTException) {
      return $this->jsonResponse('token_not_provided', 400);
    }

    return $this->jsonResponse($e->getMessage(), 500);
  }

  private function jsonResponse($error = 'error', $statusCode = 500)
  {
    return response()->json([
      'code' => $statusCode,
      'message' => $error,
      'data' => null
    ], $statusCode);
  }
}
