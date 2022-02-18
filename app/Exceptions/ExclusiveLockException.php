<?php

namespace App\Exceptions;

use Exception;

/**
 * 同時編集を検知したら投げる例外
 * 
 * 同時編集か否かはupdated_atの値を比較して行う
 */
class ExclusiveLockException extends Exception {
}