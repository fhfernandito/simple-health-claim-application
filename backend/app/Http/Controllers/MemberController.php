<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    public function getAllMembers()
    {
        $members = Member::with('benefitPlan')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Members retrieved successfully',
            'data' => $members,
        ]);
    }

    public function addMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'benefit_plan_id' => 'required|exists:benefit_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = Member::create([
            'name' => $request->name,
            'benefit_plan_id' => $request->benefit_plan_id,
        ]);

        return response()->json([
            'message' => 'Member created successfully',
            'data' => $member->load('benefitPlan'),
        ]);
    }

    public function editMember(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'benefit_plan_id' => 'required|exists:benefit_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = Member::findOrFail($id);
        $member->update([
            'name' => $request->name,
            'benefit_plan_id' => $request->benefit_plan_id,
        ]);

        return response()->json([
            'message' => 'Member updated successfully',
            'data' => $member->load('benefitPlan'),
        ]);
    }

    public function deleteMember($id)
    {
        $member = Member::findOrFail($id);

        if ($member->claims()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete member with existing claims. Please delete all claims first.',
            ], 422);
        }

        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully',
            'data' => $member,
        ]);
    }
}
