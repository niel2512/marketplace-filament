<?php

namespace App\Http\Controllers;

use App\Services\FrontService;
use App\Models\Shoe;
use App\Models\Category;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    //membuat variabel frontservice
    protected $frontService;

    //menggunakan kelas construct (ketika frontservice digunakan dsini akan dilakukan injection) DIP Dependency Injection
    public function __construct(FrontService $frontService)
    {
        $this->frontService = $frontService; //dari variabel nilainya diisi hasil inject agar bisa digunakan ke method lain
    }


    public function index()
    {
        $data = $this -> frontService -> getFrontPageData();
        return view ('front.index', $data);
        // return 'halaman utama';
    }

    public function details(Shoe $shoe)
    {
        return view ('front.details', compact('shoe')); //compact untuk meng-inject biar bisa digunakan dihalaman selanjutnya
    }

    public function category(Category $category)
    {
        return view ('front.category', compact('category'));
    }
}
