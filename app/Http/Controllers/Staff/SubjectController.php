<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('staff.subject.index');
    }

    /**
     * 共通設定作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        return view('staff.subject.create_base');
    }
}
