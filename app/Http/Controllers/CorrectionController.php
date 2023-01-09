<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Exception;
use App\Models\User;
use App\Models\Topic;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Correction;
use App\Models\PaymentDetail;
use App\Models\TopicConsume;
use App\Models\PackageType;
use Illuminate\Http\Request;

class CorrectionController extends Controller
{
    public function submitCorrection(Request $request)
    {
        $student_id = $request->user()->id;

        $package = Package::where('id', $request->package_id)->first();
        //Check is package exist or not
        if(empty($package)){
            return response()->json([
                'status' => false,
                'message' => 'Package not found!',
                'data' => []
            ], 200);
        }

        $topic = Topic::where('id', $request->topic_id)->first();
        //Check is Topic exist or not
        if(empty($topic)){
            return response()->json([
                'status' => false,
                'message' => 'Topic not found!',
                'data' => []
            ], 200);
        }

        $package_type = PackageType::where('id', $topic->package_type_id)->first();
        //Check is Syllabus exist or not
        if(empty($package_type)){
            return response()->json([
                'status' => false,
                'message' => 'Syllabus not found!',
                'data' => []
            ], 200);
        }

        if(!$request->student_correction){
            return response()->json([
                'status' => false,
                'message' => 'Please, attach student correction!',
                'data' => []
            ], 200);
        }

        $user = User::where('id', $student_id)->first();

        $correction_consume = TopicConsume::where('user_id', $student_id)->where('package_id', $request->package_id)->where('package_type_id', $topic->package_type_id)->first();

        if(empty($correction_consume)){
            return response()->json([
                'status' => false,
                'message' => 'You do not have any correction limit!, Please check your package details!',
                'data' => []
            ], 200);
        }

        if($correction_consume->balance <= $correction_consume->consumme){
            return response()->json([
                'status' => false,
                'message' => 'You do not have any correction limit!, Please check your package details!',
                'data' => []
            ], 200);
        }

        $packageDate = Carbon::parse($correction_consume->expiry_date);
        $now = Carbon::now();
        if ($now->gte($packageDate)) { 
            return response()->json([
                'status' => false,
                'message' => 'Your package has been expired. Please check your package details!',
                'data' => []
            ], 200);
        }

        $correction_date = Carbon::now();

        $correction = Correction::create([
            'user_id' => $student_id,
            'school_id' => $user->school_id,
            'topic_id' => $request->topic_id,
            'package_id' => $request->package_id,
            'package_type_id' => $topic->package_type_id,
            'status' => "Submitted",
            'student_correction' => $request->student_correction,
            'student_correction_date' => $correction_date
        ]);

        //Consume Update

        TopicConsume::where('id', $correction_consume->id)->update([
            "consumme" => $correction_consume->consumme + 1
        ]);

        $correction_details = Correction::select(
            'corrections.id',
            'corrections.user_id',
            'corrections.expert_id',
            'corrections.topic_id',
            'corrections.package_id',
            'corrections.package_type_id as syllabus_id',
            'corrections.deadline',
            'corrections.is_accepted',
            'corrections.is_seen_by_expert',
            'corrections.is_seen_by_student',
            'corrections.is_student_resubmited',
            'corrections.student_correction',
            'corrections.expert_correction_note',
            'corrections.expert_correction_feedback',
            'corrections.grade',
            'corrections.student_rewrite',
            'corrections.expert_final_note',
            'corrections.student_correction_date',
            'corrections.expert_correction_date',
            'corrections.completed_date',
            'corrections.student_resubmission_date',
            'corrections.expert_final_note_date',
            'corrections.rating',
            'corrections.rating_note',
            'corrections.status',
            'topics.title as topic_title',
            'topics.hint',
            'users.name as student_name',
            'users.email as student_email',
            'users.image as student_image',
            'packages.title as package_name',
            'package_types.name as syllabus',
            'school_information.title as school_name'
        )
        ->leftJoin('users', 'users.id', 'corrections.user_id')
        ->leftJoin('topics', 'topics.id', 'corrections.topic_id')
        ->leftJoin('packages', 'packages.id', 'corrections.package_id')
        ->leftJoin('package_types', 'package_types.id', 'corrections.package_type_id')
        ->leftJoin('school_information', 'school_information.id', 'corrections.school_id')
        ->where('corrections.id', $correction->id)
        ->first();

        $startTime = Carbon::parse(Carbon::now());
        $finishTime = Carbon::parse($correction_details->deadline);

        $now = Carbon::now();
        if ($now->gte($finishTime)) { 
            $correction_details->duration = 0;
        }else{
            $correction_details->duration = $finishTime->diffInSeconds($startTime);
        }

        $correction_details->expert_name = null;
        $correction_details->expert_email = null;
        $correction_details->expert_image = null;
        $correction_details->admin_name = null;
        $correction_details->admin_email = null;
        $correction_details->admin_image = null;

        return response()->json([
            'status' => true,
            'message' => 'Correction submitted successful.',
            'data' => $correction_details
        ], 200);
    }

    public function getPendingCorrectionCount(Request $request)
    {
        $pending_list = Correction::where('is_accepted', true)->where('status', 'Accepted')->get();

        foreach ($pending_list as $item) {
            $correction_deadline = Carbon::parse($item->deadline);
            $now = Carbon::now();
            if ($now->gte($correction_deadline)) { 
                Correction::where('id', $item->id)->update([
                    "is_accepted" => false,
                    'status' => "Submitted",
                    'accepted_date' => null,
                    'deadline' => null,
                    'expert_id' => null
                ]);
            }
        }

        $total_pending = Correction::where('is_accepted', false)->where('status', 'Submitted')->get()->count();
        return response()->json([
            'status' => true,
            'message' => 'Successful.',
            'data' => [
                "pending" => $total_pending
            ]
        ], 200);
    }

    public function acceptPendingCorrection(Request $request)
    {   
        $expert_id = $request->user()->id;
        $pending_list = Correction::where('is_accepted', true)->where('status', 'Accepted')->get();

        foreach ($pending_list as $item) {
            $correction_deadline = Carbon::parse($item->deadline);
            $now = Carbon::now();
            if ($now->gte($correction_deadline)) { 
                Correction::where('id', $item->id)->update([
                    "is_accepted" => false,
                    'status' => "Submitted",
                    'accepted_date' => null,
                    'deadline' => null,
                    'expert_id' => null
                ]);
            }
        }

        $is_pending_exist = Correction::where('expert_id', $expert_id)->where('is_accepted', true)->where('status', 'Accepted')->get();
        if(sizeof($is_pending_exist)){
            return response()->json([
                'status' => false,
                'message' => 'You already have a pending correction. Please, solve it first!',
                'data' => []
            ], 200);
        }

        $pending = Correction::where('is_accepted', false)->first();

        if(empty($pending)){
            return response()->json([
                'status' => false,
                'message' => 'No pending correction is available! Please, try again.',
                'data' => []
            ], 200);
        }

        Correction::where('id', $pending->id)->update([
            "is_accepted" => true,
            'status' => "Accepted",
            'accepted_date' => Carbon::now(),
            'deadline' => Carbon::now()->addHours(2),
            'expert_id' => $expert_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'One correction has been accepted.',
            'data' => []
        ], 200);
    }

    public function getCorrectionList(Request $request)
    {
        $student_id = $request->user()->id;
        $correction_list = Correction::select(
            'corrections.id',
            'corrections.user_id',
            'corrections.expert_id',
            'corrections.topic_id',
            'corrections.package_id',
            'corrections.package_type_id as syllabus_id',
            'corrections.id',
            'corrections.deadline',
            'corrections.is_accepted',
            'corrections.is_seen_by_expert',
            'corrections.is_seen_by_student',
            'corrections.is_student_resubmited',
            'corrections.status',
            'topics.title as topic_title',
            'users.name as student_name',
            'users.email as student_email',
            'users.image as student_image',
            'packages.title as package_name',
            'package_types.name as syllabus',
            'school_information.title as school_name'
        )
        ->leftJoin('users', 'users.id', 'corrections.user_id')
        ->leftJoin('topics', 'topics.id', 'corrections.topic_id')
        ->leftJoin('packages', 'packages.id', 'corrections.package_id')
        ->leftJoin('package_types', 'package_types.id', 'corrections.package_type_id')
        ->leftJoin('school_information', 'school_information.id', 'corrections.school_id')
        ->where('corrections.user_id', $student_id)
        ->get();

        foreach ($correction_list as $item) 
        {
            $startTime = Carbon::parse(Carbon::now());
            $finishTime = Carbon::parse($item->deadline);

            $now = Carbon::now();
            if ($now->gte($finishTime)) { 
                $item->duration = 0;
            }else{
                $item->duration = $finishTime->diffInSeconds($startTime);
            }

            if($item->expert_id){
                $expert = User::where('id', $item->expert_id)->first();
                if(!empty($expert)){
                    $item->expert_name = $expert->name;
                    $item->expert_email = $expert->email;
                    $item->expert_image = $expert->image;
                }else{
                    $item->expert_name = null;
                    $item->expert_email = null;
                    $item->expert_image = null;
                }
            }else{
                $item->expert_name = null;
                $item->expert_email = null;
                $item->expert_image = null;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Correction list',
            'data' => $correction_list
        ], 200);
    }

    public function getCorrectionDetailsByID(Request $request)
    {
        $correction_id = $request->correction_id ? $request->correction_id : 0;

        $correction_details = Correction::select(
                    'corrections.id',
                    'corrections.user_id',
                    'corrections.expert_id',
                    'corrections.topic_id',
                    'corrections.package_id',
                    'corrections.package_type_id as syllabus_id',
                    'corrections.deadline',
                    'corrections.is_accepted',
                    'corrections.is_seen_by_expert',
                    'corrections.is_seen_by_student',
                    'corrections.is_student_resubmited',
                    'corrections.student_correction',
                    'corrections.expert_correction_note',
                    'corrections.expert_correction_feedback',
                    'corrections.grade',
                    'corrections.student_rewrite',
                    'corrections.expert_final_note',
                    'corrections.student_correction_date',
                    'corrections.expert_correction_date',
                    'corrections.completed_date',
                    'corrections.student_resubmission_date',
                    'corrections.expert_final_note_date',
                    'corrections.rating',
                    'corrections.rating_note',
                    'corrections.status',
                    'topics.title as topic_title',
                    'topics.hint',
                    'users.name as student_name',
                    'users.email as student_email',
                    'users.image as student_image',
                    'packages.title as package_name',
                    'package_types.name as syllabus',
                    'school_information.title as school_name'
                )
                ->leftJoin('users', 'users.id', 'corrections.user_id')
                ->leftJoin('topics', 'topics.id', 'corrections.topic_id')
                ->leftJoin('packages', 'packages.id', 'corrections.package_id')
                ->leftJoin('package_types', 'package_types.id', 'corrections.package_type_id')
                ->leftJoin('school_information', 'school_information.id', 'corrections.school_id')
                ->where('corrections.id', $correction_id)
                ->first();

                if(empty($correction_details)){
                    return response()->json([
                        'status' => false,
                        'message' => 'No correction found!',
                        'data' => []
                    ], 200);
                }

                $startTime = Carbon::parse(Carbon::now());
                $finishTime = Carbon::parse($correction_details->deadline);

                $now = Carbon::now();
                if ($now->gte($finishTime)) { 
                    $correction_details->duration = 0;
                }else{
                    $correction_details->duration = $finishTime->diffInSeconds($startTime);
                }

                if($correction_details->expert_id){
                    $expert = User::where('id', $correction_details->expert_id)->first();
                    if(!empty($expert)){
                        $correction_details->expert_name = $expert->name;
                        $correction_details->expert_email = $expert->email;
                        $correction_details->expert_image = $expert->image;
                    }else{
                        $correction_details->expert_name = null;
                        $correction_details->expert_email = null;
                        $correction_details->expert_image = null;
                    }
                }else{
                    $correction_details->expert_name = null;
                    $correction_details->expert_email = null;
                    $correction_details->expert_image = null;
                }
    
                if($correction_details->admin_id){
                    $admin = User::where('id', $correction_details->admin_id)->first();
                    if(!empty($admin)){
                        $correction_details->admin_name = $admin->name;
                        $correction_details->admin_email = $admin->email;
                        $correction_details->admin_image = $admin->image;
                    }else{
                        $correction_details->admin_name = null;
                        $correction_details->admin_email = null;
                        $correction_details->admin_image = null;
                    }
                }else{
                    $correction_details->admin_name = null;
                    $correction_details->admin_email = null;
                    $correction_details->admin_image = null;
                }

        return response()->json([
            'status' => true,
            'message' => 'Correction Details',
            'data' => $correction_details
        ], 200);
    }

    public function getExpertCorrectionList(Request $request)
    {
        $expert_id = $request->user()->id;
        $correction_list = Correction::select(
            'corrections.id',
            'corrections.user_id',
            'corrections.expert_id',
            'corrections.topic_id',
            'corrections.package_id',
            'corrections.package_type_id as syllabus_id',
            'corrections.id',
            'corrections.deadline',
            'corrections.is_accepted',
            'corrections.is_seen_by_expert',
            'corrections.is_seen_by_student',
            'corrections.is_student_resubmited',
            'corrections.status',
            'topics.title as topic_title',
            'users.name as student_name',
            'users.email as student_email',
            'users.image as student_image',
            'packages.title as package_name',
            'package_types.name as syllabus',
            'school_information.title as school_name'
        )
        ->leftJoin('users', 'users.id', 'corrections.user_id')
        ->leftJoin('topics', 'topics.id', 'corrections.topic_id')
        ->leftJoin('packages', 'packages.id', 'corrections.package_id')
        ->leftJoin('package_types', 'package_types.id', 'corrections.package_type_id')
        ->leftJoin('school_information', 'school_information.id', 'corrections.school_id')
        ->where('corrections.expert_id', $expert_id)
        ->get();

        $expert = User::where('id', $expert_id)->first();

        foreach ($correction_list as $item) {

            $startTime = Carbon::parse(Carbon::now());
            $finishTime = Carbon::parse($item->deadline);

            $now = Carbon::now();
            if ($now->gte($finishTime)) { 
                $item->duration = 0;
            }else{
                $item->duration = $finishTime->diffInSeconds($startTime);
            }

            $item->expert_name = $expert->name;
            $item->expert_email = $expert->email;
            $item->expert_image = $expert->image;
        }

        return response()->json([
            'status' => true,
            'message' => 'Correction list',
            'data' => $correction_list
        ], 200);
    }

}
