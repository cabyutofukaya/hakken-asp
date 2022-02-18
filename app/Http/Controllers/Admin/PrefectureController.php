<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PrefectureService;
use Illuminate\View\View;

class PrefectureController extends Controller
{
    private $prefectureService;

    public function __construct(PrefectureService $prefectureService)
    {
        $this->prefectureService = $prefectureService;
    }

    /**
     * 一覧表示
     */
    public function index(): View
    {
        return view("admin.prefecture.index", [
            'prefectures' => $this->prefectureService->paginate(15)
        ]);
    }
}
