<?php

namespace App\Exceptions;

use Exception;

/**
 * 会社アカウントログイン時に、
 * トライアル版でも正式版でもない場合に投げる例外
 * 
 */
class PromotionException extends Exception {
}