<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                'topic_consumes.payment_id',
                DB::raw("SUM(topic_consumes.balance) as balance"),
                DB::raw("SUM(topic_consumes.consumme) as consumme"),
                'topic_consumes.expiry_date', 
                'topic_consumes.created_at as purchased_date',
                'packages.title as packages_title', 
                'packages.feature_image', 
                'packages.description as packages_description',
            )
            ->where('user_id', $user_id)
            ->leftJoin('packages', 'packages.id', 'topic_consumes.package_id')
            ->leftJoin('package_types', 'package_types.id', 'topic_consumes.package_type_id')
            ->groupBy('topic_consumes.payment_id')
            ->get();
        
            foreach ($package_list as $item) {
                $packageDate = Carbon::parse($item->expiry_date);
                $now = Carbon::now();
                $item->balance = intval($item->balance);
                $item->consumme = intval($item->consumme);
    
                if ($now->gte($packageDate)) { 
                    $item->is_expired = true;
                }else{
                    $item->is_expired = false; 
                }
            }

        return response()->json([
            'status' => true,
            'message' => "Successful",
            'data' => $package_list
        ], 200);
    }
}
