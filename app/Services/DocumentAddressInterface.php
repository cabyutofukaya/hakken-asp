<?php

namespace App\Services;

interface DocumentAddressInterface
{
    // 書類設定の宛名設定をクリア
    public function clearDocumentAddress(int $reserveId);
}
