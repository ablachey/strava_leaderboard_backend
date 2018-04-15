<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
  protected $errors;

  public function __construct($errors = [])
  {
    parent::__construct('Bad Request', 400, null);

    $this->errors = $errors;
  }

  public function __toString()
  {
    return __CLASS__ . ": [ { $this->code } ]: { $this->message }\n";
  }

  public function getErrors()
  {
    return $this->errors;
  }
}
