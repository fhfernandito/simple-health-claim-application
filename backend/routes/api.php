<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\BenefitPlanController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;

Route::controller(AuthController::class)->group(function () {
    Route::post('/sign-in', 'signIn');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/sign-out', 'signOut');
        Route::get('/get-user', 'getUser');
    });

    Route::get('/get-dashboard-stats', [DashboardController::class, 'getDashboardStats']);

    Route::controller(MemberController::class)->group(function () {
        Route::get('/get-all-members', 'getAllMembers');
        Route::post('/add-member', 'addMember');
        Route::put('/edit-member/{id}', 'editMember');
        Route::delete('/delete-member/{id}', 'deleteMember');
    });

    Route::controller(BenefitPlanController::class)->group(function () {
        Route::get('/get-all-benefit-plans', 'getAllBenefitPlans');
        Route::post('/add-benefit-plan', 'addBenefitPlan');
        Route::put('/edit-benefit-plan/{id}', 'editBenefitPlan');
        Route::delete('/delete-benefit-plan/{id}', 'deleteBenefitPlan');
    });

    Route::controller(ClaimController::class)->group(function () {
        Route::get('/get-all-claims', 'getAllClaims');
        Route::post('/submit-claim', 'submitClaim');
        Route::delete('/delete-claim/{id}', 'deleteClaim');
        Route::get('/get-member-remaining-limit', 'getMemberRemainingLimit');
    });
});
