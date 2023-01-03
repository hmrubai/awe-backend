<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Exception;
use App\Models\User;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\TopicConsume;
use App\Models\PackageType;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function makePaymentMobile (Request $request)
    {
        $user_id = $request->user()->id;
        $transaction_id = $request->transaction_id;

        //Check TRX ID 
        if($transaction_id){
            $is_payment_exist = Payment::where('transaction_id', $transaction_id)->where('status', "Completed")->first();
            if($is_payment_exist){
                return response()->json([
                    'status' => false,
                    'message' => 'Payment information already exist!',
                    'data' => []
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Please, enter valid information!',
                'data' => []
            ], 200);
        }

        //Check Items
        if(sizeof($request->items) <= 0){
            return response()->json([
                'status' => false,
                'message' => 'Please, add items.',
                'data' => []
            ], 200);
        }

        $user = User::where('id', $user_id)->first();
        $package = Package::where('id', $request->package_id)->first();

        if(empty($package)){
            return response()->json([
                'status' => false,
                'message' => 'Package not found!',
                'data' => []
            ], 200);
        }

        $expiry_date = Carbon::now()->addDay($package->cycle);

        $payment = Payment::create([
            "user_id" => $user_id,
            "school_id" => $user->school_id,
            "package_id" => $request->package_id,
            "is_promo_applied" => $request->is_promo_applied,
            "promo_id" => $request->promo_id,
            "payable_amount" => $request->payable_amount,
            "paid_amount" => $request->paid_amount,
            "discount_amount" => $request->discount_amount,
            "currency" => $request->currency,
            "transaction_id" => $request->transaction_id,
            "payment_type" => 'Mobile',
            "payment_method" => $request->payment_method,
            "status" => 'Completed'
        ]);

        foreach ($request->items as $item) {

            $package_type = PackageType::where('id', $item['package_type_id'])->first();

            PaymentDetail::create([
                "user_id" => $user_id,
                "school_id" => $user->school_id,
                "package_id" => $request->package_id,
                "package_type_id" => $item['package_type_id'],
                "payment_id" => $payment->id,
                "unit_price" => $package_type->price,
                "quantity" => $item['quantity'],
                "total" => $item['quantity'] * $package_type->price,
            ]);

            TopicConsume::create([
                "user_id" => $user_id,
                "school_id" => $user->school_id,
                "package_id" => $request->package_id,
                "package_type_id" => $item['package_type_id'],
                "payment_id" => $payment->id,
                "balance" => $item['quantity'],
                "consumme" => 0,
                "expiry_date" => $expiry_date
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment Successful',
            'data' => []
        ], 200);
    }

    public function myPaymentList (Request $request)
    {
        $user_id = $request->user()->id;
        $payment = Payment::select(
            'payments.*',
            'packages.title as packages_title', 
            'packages.feature_image', 
            'packages.description as packages_description',
        )
        ->leftJoin('packages', 'packages.id', 'payments.package_id')
        ->where('user_id', $user_id)
        ->get();

        foreach ($payment as $item) {
            $details = TopicConsume::where('payment_id', $item->id)->first();
            $item->expiry_date = $details['expiry_date'];
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment list successful',
            'data' => $payment
        ], 200);
    }

    public function packageDetailsByPaymentID(Request $request)
    {
        $user_id = $request->user()->id;
        $payment_id = $request->payment_id ? $request->payment_id : 0;
        
        $package_details = TopicConsume::select(
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
            ->where('topic_consumes.user_id', $user_id)
            ->where('topic_consumes.payment_id', $payment_id)
            ->leftJoin('packages', 'packages.id', 'topic_consumes.package_id')
            ->groupBy('topic_consumes.payment_id')
            ->get();
        
            foreach ($package_details as $item) {
                $packageDate = Carbon::parse($item->expiry_date);
                $now = Carbon::now();
                $item->balance = intval($item->balance);
                $item->consumme = intval($item->consumme);

                $item->details = TopicConsume::select(
                    'topic_consumes.package_type_id',
                    'topic_consumes.balance',
                    'topic_consumes.consumme',
                    'package_types.name as syllabus',
                )
                ->leftJoin('package_types', 'package_types.id', 'topic_consumes.package_type_id')
                ->where('topic_consumes.payment_id', $item->payment_id)
                ->get();
    
                if ($now->gte($packageDate)) { 
                    $item->is_expired = true;
                }else{
                    $item->is_expired = false; 
                }
            }

        return response()->json([
            'status' => true,
            'message' => 'Details Successful',
            'data' => $package_details
        ], 200);
    }
}
