<?php

namespace App\Http\Controllers;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $data = Brand::all();
        return view('brand.index', ['data' => $data]);
    }
}
