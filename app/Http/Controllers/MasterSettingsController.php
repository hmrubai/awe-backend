<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Grade;
use App\Models\Country;
use App\Models\Category;
use App\Models\PackageType;
use Illuminate\Http\Request;

class MasterSettingsController extends Controller
{
    public function packageTypeList(Request $request)
    {
        $package_list = PackageType::select('id', 'name', 'price', 'limit')->where('is_active', true)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $package_list
        ], 200);
    }
    
    public function gradeList(Request $request)
    {
        $grade_list = Grade::select('id', 'name')->where('is_active', true)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $grade_list
        ], 200);
    }

    public function categoryList(Request $request)
    {
        $category_list = Category::select('id', 'name')->where('is_active', true)->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $category_list
        ], 200);
    }

    public function countryList(Request $request)
    {
        $country_list = Country::select('id', 'country_name')->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $country_list
        ], 200);
    }
}
