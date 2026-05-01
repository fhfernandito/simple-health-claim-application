<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Claim;
use App\Models\BenefitPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getDashboardStats()
    {
        $totalMembers = Member::count();
        $totalPlans = BenefitPlan::count();
        $totalClaims = Claim::count();
        $totalApproved = Claim::sum('approved_amount');
        $totalExcess = Claim::sum('excess_amount');
        $totalClaimAmount = Claim::sum('claim_amount');

        $recentClaims = Claim::with('member.benefitPlan')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $monthlyClaims = $this->getMonthlyClaims();

        $claimsByPlan = $this->getClaimsByPlan();

        $activityLog = $this->getActivityLog();

        return response()->json([
            'message' => 'Dashboard stats retrieved successfully',
            'data' => [
                'total_members' => $totalMembers,
                'total_plans' => $totalPlans,
                'total_claims' => $totalClaims,
                'total_claim_amount' => $totalClaimAmount,
                'total_approved' => $totalApproved,
                'total_excess' => $totalExcess,
                'recent_claims' => $recentClaims,
                'monthly_claims' => $monthlyClaims,
                'claims_by_plan' => $claimsByPlan,
                'activity_log' => $activityLog,
            ],
        ]);
    }

    private function getMonthlyClaims(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $claims = Claim::whereYear('claim_date', $date->year)
                ->whereMonth('claim_date', $date->month)
                ->selectRaw('
                    COALESCE(SUM(claim_amount), 0) as total_claimed,
                    COALESCE(SUM(approved_amount), 0) as total_approved,
                    COALESCE(SUM(excess_amount), 0) as total_excess,
                    COUNT(*) as count
                ')
                ->first();

            $months[] = [
                'month' => $monthLabel,
                'month_key' => $monthKey,
                'total_claimed' => (float) $claims->total_claimed,
                'total_approved' => (float) $claims->total_approved,
                'total_excess' => (float) $claims->total_excess,
                'count' => (int) $claims->count,
            ];
        }

        return $months;
    }

    private function getClaimsByPlan(): array
    {
        return BenefitPlan::select('benefit_plans.id', 'benefit_plans.plan_name')
            ->selectRaw('COUNT(claims.id) as total_claims')
            ->selectRaw('COALESCE(SUM(claims.claim_amount), 0) as total_amount')
            ->leftJoin('members', 'benefit_plans.id', '=', 'members.benefit_plan_id')
            ->leftJoin('claims', 'members.id', '=', 'claims.member_id')
            ->groupBy('benefit_plans.id', 'benefit_plans.plan_name')
            ->get()
            ->toArray();
    }

    private function getActivityLog(): array
    {
        return Claim::with('member')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($claim) {
                $hasExcess = (float) $claim->excess_amount > 0;
                $fullyApproved = (float) $claim->approved_amount === (float) $claim->claim_amount;

                if ($hasExcess && (float) $claim->approved_amount > 0) {
                    $status = 'partial';
                    $statusLabel = 'Partially Approved';
                } elseif ($hasExcess) {
                    $status = 'excess';
                    $statusLabel = 'Exceeded Limit';
                } elseif ($fullyApproved) {
                    $status = 'approved';
                    $statusLabel = 'Fully Approved';
                } else {
                    $status = 'pending';
                    $statusLabel = 'Processed';
                }

                return [
                    'id' => $claim->id,
                    'member_name' => $claim->member?->name ?? 'Unknown',
                    'policy_number' => $claim->member?->policy_number ?? '-',
                    'claim_amount' => (float) $claim->claim_amount,
                    'approved_amount' => (float) $claim->approved_amount,
                    'excess_amount' => (float) $claim->excess_amount,
                    'claim_date' => $claim->claim_date->format('Y-m-d'),
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'created_at' => $claim->created_at->toISOString(),
                    'time_ago' => $claim->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}
