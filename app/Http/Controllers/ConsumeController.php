<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use Auth;
use App\Models\TopicConsume;
use Illuminate\Http\Request;

class ConsumeController extends Controller
{
    public function myPackageList(Request $request)
    {

        $user_id = $request->user()->id;
        $package_list = TopicConsume::select(
                'topic_consumes.package_id', 
                'topic_consumes.package_type_id', 
                'topic_consumes.balance', 
                'topic_consumes.consumme', 
                'topic_consumes.expiry_date', 
                'topic_consumes.created_at as purchased_date',
                'packages.title as packages_title', 
                'packages.description as packages_description',
                'packages.limit as packages_limit',
                'packages.cycle as packages_cycle',
                'package_types.name as packages_type'
            )
            ->where('user_id', $user_id)
            ->whereDate('expiry_date', '>', Carbon::now())
            ->leftJoin('packages', 'packages.id', 'topic_consumes.package_id')
            ->leftJoin('package_types', 'package_types.id', 'topic_consumes.package_type_id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $package_list
        ], 200);
    }
}
