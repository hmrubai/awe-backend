<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function allTopicList(Request $request)
    {
        $topic_list = Topic::select(
                'topics.id', 
                'topics.title', 
                'topics.hint', 
                'topics.country_id',
                'countries.country_name',
                'topics.package_type_id as syllabus_id',
                'package_types.name as syllabus_name',
                'topics.catagory_id',
                'categories.name as category_name',
                'topics.grade_id',
                'grades.name as grade_name',
                'topics.school_id',
                'school_information.title as school_name',
                'topics.limit'
            )
            ->leftJoin('countries', 'countries.id', 'topics.country_id')
            ->leftJoin('package_types', 'package_types.id', 'topics.package_type_id')
            ->leftJoin('categories', 'categories.id', 'topics.catagory_id')
            ->leftJoin('grades', 'grades.id', 'topics.grade_id')
            ->leftJoin('school_information', 'school_information.id', 'topics.school_id')
            ->orderBy('topics.title', 'ASC')
            ->where('topics.is_active', true)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $topic_list
        ], 200);
    }

    public function fillterTopicList(Request $request)
    {
        $package_type_id = $request->syllabus_id ? $request->syllabus_id : 0;
        $catagory_id = $request->catagory_id ? $request->catagory_id : 0;
        $grade_id = $request->grade_id ? $request->grade_id : 0;
        $country_id = $request->country_id ? $request->country_id : 0;

        $topic_list = Topic::select(
                'topics.id', 
                'topics.title', 
                'topics.hint', 
                'topics.country_id',
                'countries.country_name',
                'topics.package_type_id as syllabus_id',
                'package_types.name as syllabus_name',
                'topics.catagory_id',
                'categories.name as category_name',
                'topics.grade_id',
                'grades.name as grade_name',
                'topics.school_id',
                'school_information.title as school_name',
                'topics.limit'
            )
            ->when($package_type_id, function ($query) use ($package_type_id){
                return $query->where('topics.package_type_id', $package_type_id);
            })
            ->when($catagory_id, function ($query) use ($catagory_id){
                return $query->where('topics.catagory_id', $catagory_id);
            })
            ->when($grade_id, function ($query) use ($grade_id){
                return $query->where('topics.grade_id', $grade_id);
            })
            ->when($country_id, function ($query) use ($country_id){
                return $query->where('topics.country_id', $country_id);
            })
            ->leftJoin('countries', 'countries.id', 'topics.country_id')
            ->leftJoin('package_types', 'package_types.id', 'topics.package_type_id')
            ->leftJoin('categories', 'categories.id', 'topics.catagory_id')
            ->leftJoin('grades', 'grades.id', 'topics.grade_id')
            ->leftJoin('school_information', 'school_information.id', 'topics.school_id')
            ->orderBy('topics.title', 'ASC')
            ->where('topics.is_active', true)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Filtered List',
            'data' => $topic_list
        ], 200);
    }

    public function adminTopicList(Request $request)
    {
        $topic_list = Topic::select(
                'topics.id', 
                'topics.title', 
                'topics.hint', 
                'topics.country_id',
                'countries.country_name',
                'topics.package_type_id as syllabus_id',
                'package_types.name as syllabus_name',
                'topics.catagory_id',
                'categories.name as category_name',
                'topics.grade_id',
                'grades.name as grade_name',
                'topics.school_id',
                'school_information.title as school_name',
                'topics.limit',
                'topics.is_active'
            )
            ->leftJoin('countries', 'countries.id', 'topics.country_id')
            ->leftJoin('package_types', 'package_types.id', 'topics.package_type_id')
            ->leftJoin('categories', 'categories.id', 'topics.catagory_id')
            ->leftJoin('grades', 'grades.id', 'topics.grade_id')
            ->leftJoin('school_information', 'school_information.id', 'topics.school_id')
            ->orderBy('topics.title', 'ASC')
            //->where('topics.is_active', true)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'List Successful',
            'data' => $topic_list
        ], 200);
    }

}
