<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MasterSettingsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TopicController;


Route::post('/auth/register', [AuthController::class, 'registerUser']);
Route::post('/auth/login', [AuthController::class, 'loginUser']);
Route::get('country-list', [MasterSettingsController::class, 'countryList']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Master Settings
    Route::get('syllabus-list', [MasterSettingsController::class, 'packageTypeList']);
    Route::get('grade-list', [MasterSettingsController::class, 'gradeList']);
    Route::get('category-list', [MasterSettingsController::class, 'categoryList']);

    //Package 
    Route::get('package-list', [PackageController::class, 'packageList']);
    Route::get('package-details-by-id/{package_id}', [PackageController::class, 'packageDetailsByID']);

    // Topic
    Route::get('all-topic-list', [TopicController::class, 'allTopicList']);
    Route::get('filter-topic-list', [TopicController::class, 'fillterTopicList']);

    //Admin
    Route::get('admin/syllabus-list', [MasterSettingsController::class, 'admin_PackageTypeList']);
    Route::post('admin/syllabus-save-or-update', [MasterSettingsController::class, 'saveOrUpdatePackageType']);
    Route::get('admin/grade-list', [MasterSettingsController::class, 'adminGradeList']);
    Route::post('admin/grade-save-or-update', [MasterSettingsController::class, 'saveOrUpdateGrade']);
    Route::get('admin/category-list', [MasterSettingsController::class, 'adminCategoryList']);
    Route::post('admin/category-save-or-update', [MasterSettingsController::class, 'saveOrUpdateCategory']);

    Route::get('admin/package-list', [PackageController::class, 'adminPackageList']);
    Route::post('admin/package-save-or-update', [PackageController::class, 'saveOrUpdatePackage']);
    Route::get('admin/benefit-list-by-id/{package_id}', [PackageController::class, 'adminBenefitListByID']);
    Route::post('admin/benefit-delete', [PackageController::class, 'adminDeleteBenefitByID']);

    

});
