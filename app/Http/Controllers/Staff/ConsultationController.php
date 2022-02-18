<?php

namespace App\Http\Controllers\Staff;

use App\Models\AgencyConsultation;
use App\Models\WebMessageHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gate;

class ConsultationController extends AppController
{
    /**
     * 相談一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function index(string $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new AgencyConsultation]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.consultation.index');
    }

    /**
     * 相談履歴一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function messageIndex(string $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new WebMessageHistory]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.consultation.message_index');
    }

}
