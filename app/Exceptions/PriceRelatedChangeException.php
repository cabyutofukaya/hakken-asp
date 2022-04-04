<?php

namespace App\Exceptions;

use Exception;

/**
 * 料金関連する更新のバッティングを検知したら投げる例外
 */
class PriceRelatedChangeException extends Exception {
}