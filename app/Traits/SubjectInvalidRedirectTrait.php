<?php

namespace App\Traits;

/**
 * 科目マスターのバリデーションエラー時のリダイレクト先変更
 */
trait SubjectInvalidRedirectTrait
{
    // バリデーションエラー時は科目カテゴリの選択に応じてリダイレクト先を変更
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        return $url->route('staff.master.subject.create', ['agencyAccount' => $this->agencyAccount, 'tab' => $this->category]);
    }
}
