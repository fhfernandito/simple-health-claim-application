<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ClaimController extends Controller
{
    public function getAllClaims()
    {
        $claims = Claim::with('member.benefitPlan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Claims retrieved successfully',
            'data' => $claims,
        ]);
    }

    public function submitClaim(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'claim_date' => 'required|date',
            'claim_amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = Member::with('benefitPlan')->findOrFail($request->member_id);
        $annualLimit = $member->benefitPlan->annual_limit;

        $claimYear = Carbon::parse($request->claim_date)->year;
        $totalPreviousApproved = Claim::where('member_id', $request->member_id)
            ->whereYear('claim_date', $claimYear)
            ->sum('approved_amount');

        $remainingLimit = max($annualLimit - $totalPreviousApproved, 0);
        $approved = min($request->claim_amount, $remainingLimit);
        $excess = $request->claim_amount - $approved;

        $claim = Claim::create([
            'member_id' => $request->member_id,
            'claim_date' => $request->claim_date,
            'claim_amount' => $request->claim_amount,
            'approved_amount' => $approved,
            'excess_amount' => $excess,
        ]);

        return response()->json([
            'message' => 'Claim submitted successfully',
            'data' => $claim->load('member.benefitPlan'),
            'calculation' => [
                'annual_limit' => $annualLimit,
                'total_previous_approved' => $totalPreviousApproved,
                'remaining_limit' => $remainingLimit,
                'approved_amount' => $approved,
                'excess_amount' => $excess,
            ],
        ]);
    }

    public function deleteClaim($id)
    {
        $claim = Claim::findOrFail($id);
        $claim->delete();

        return response()->json([
            'message' => 'Claim deleted successfully',
            'data' => $claim,
        ]);
    }

    public function getMemberRemainingLimit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'year' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = Member::with('benefitPlan')->findOrFail($request->member_id);
        $year = $request->year ?? Carbon::now()->year;
        $annualLimit = $member->benefitPlan->annual_limit;

        $totalApproved = Claim::where('member_id', $request->member_id)
            ->whereYear('claim_date', $year)
            ->sum('approved_amount');

        $remainingLimit = max($annualLimit - $totalApproved, 0);

        return response()->json([
            'message' => 'Remaining limit retrieved successfully',
            'data' => [
                'annual_limit' => $annualLimit,
                'total_approved' => $totalApproved,
                'remaining_limit' => $remainingLimit,
                'year' => $year,
            ],
        ]);
    }
}
