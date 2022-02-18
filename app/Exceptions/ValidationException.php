<?php

namespace App\Exceptions;

use Exception;

/**
 * バリデーションエラーが発生したら投げる例外
 * 
 * コントローラ内でバリデーションする際に使用
 */
class ValidationException extends Exception {
}