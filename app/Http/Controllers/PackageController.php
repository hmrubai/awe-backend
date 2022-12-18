<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\PackageType;
use App\Models\PackageBenefitDetail;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function packageList(Request $request){
        $package_list = Package::select('id', 'title', 'description', 'limit', 'cycle', 'promotion_title', 'promotion_details', 'feature_image')->where('is_active', true)->get();

        foreach ($package_list as $item) {
            $item->benefits = PackageBenefitDetail::select('id', 'benefit')->where('package_id', $item->id)->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $package_list
        ], 200);
    }

    public function packageDetailsByID(Request $request)
    {
        $package_id = $request->package_id ? $request->package_id : 0;

        $package_details = Package::select('id', 'title', 'description', 'limit', 'cycle', 'promotion_title', 'promotion_details', 'feature_image')->where('id', $package_id)->first();
        $package_details->benefits = PackageBenefitDetail::select('id', 'benefit')->where('package_id', $package_id)->get();

        return response()->json([
            'status' => true,
            'message' => 'Details Successful',
            'data' => $package_details
        ], 200);
    }
}
