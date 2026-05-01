<?php

namespace App\Http\Controllers;

use App\Models\BenefitPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BenefitPlanController extends Controller
{
    public function getAllBenefitPlans()
    {
        $plans = BenefitPlan::withCount('members')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Benefit plans retrieved successfully',
            'data' => $plans,
        ]);
    }

    public function addBenefitPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
            'annual_limit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $plan = BenefitPlan::create([
            'plan_name' => $request->plan_name,
            'annual_limit' => $request->annual_limit,
        ]);

        return response()->json([
            'message' => 'Benefit plan created successfully',
            'data' => $plan,
        ]);
    }

    public function editBenefitPlan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required|string|max:255',
            'annual_limit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $plan = BenefitPlan::findOrFail($id);
        $plan->update([
            'plan_name' => $request->plan_name,
            'annual_limit' => $request->annual_limit,
        ]);

        return response()->json([
            'message' => 'Benefit plan updated successfully',
            'data' => $plan,
        ]);
    }

    public function deleteBenefitPlan($id)
    {
        $plan = BenefitPlan::findOrFail($id);

        if ($plan->members()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete plan with assigned members. Please reassign or delete members first.',
            ], 422);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Benefit plan deleted successfully',
            'data' => $plan,
        ]);
    }
}
