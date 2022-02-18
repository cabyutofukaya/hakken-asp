<?php

namespace App\Http\Controllers\Staff;

use App\Models\DocumentCategory;
use App\Http\Controllers\Controller;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 認可チェック
        $response = \Gate::inspect('viewAny', [new DocumentCategory]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.index');
    }
}
