<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BenefitPlan;
use App\Models\Member;
use App\Models\Claim;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
        ]);

        // Create benefit plans
        $planBasic = BenefitPlan::create([
            'plan_name' => 'Basic Plan',
            'annual_limit' => 5000000,
        ]);

        $planStandard = BenefitPlan::create([
            'plan_name' => 'Standard Plan',
            'annual_limit' => 10000000,
        ]);

        $planPremium = BenefitPlan::create([
            'plan_name' => 'Premium Plan',
            'annual_limit' => 25000000,
        ]);

        // Create members
        $member1 = Member::create([
            'name' => 'Budi Santoso',
            'benefit_plan_id' => $planBasic->id,
        ]);

        $member2 = Member::create([
            'name' => 'Siti Rahayu',
            'benefit_plan_id' => $planStandard->id,
        ]);

        $member3 = Member::create([
            'name' => 'Ahmad Wijaya',
            'benefit_plan_id' => $planPremium->id,
        ]);

        $member4 = Member::create([
            'name' => 'Dewi Lestari',
            'benefit_plan_id' => $planBasic->id,
        ]);

        $member5 = Member::create([
            'name' => 'Rudi Hartono',
            'benefit_plan_id' => $planStandard->id,
        ]);

        $member6 = Member::create([
            'name' => 'Rina Kusuma',
            'benefit_plan_id' => $planPremium->id,
        ]);

        $member7 = Member::create([
            'name' => 'Hendra Pratama',
            'benefit_plan_id' => $planStandard->id,
        ]);

        // ---- JANUARY 2026 ----
        Claim::create([
            'member_id' => $member1->id,
            'claim_date' => '2026-01-08',
            'claim_amount' => 1500000,
            'approved_amount' => 1500000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member3->id,
            'claim_date' => '2026-01-15',
            'claim_amount' => 5000000,
            'approved_amount' => 5000000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member5->id,
            'claim_date' => '2026-01-22',
            'claim_amount' => 2000000,
            'approved_amount' => 2000000,
            'excess_amount' => 0,
        ]);

        // ---- FEBRUARY 2026 ----
        Claim::create([
            'member_id' => $member2->id,
            'claim_date' => '2026-02-05',
            'claim_amount' => 4000000,
            'approved_amount' => 4000000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member4->id,
            'claim_date' => '2026-02-12',
            'claim_amount' => 1200000,
            'approved_amount' => 1200000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member6->id,
            'claim_date' => '2026-02-20',
            'claim_amount' => 3500000,
            'approved_amount' => 3500000,
            'excess_amount' => 0,
        ]);

        // ---- MARCH 2026 ----
        Claim::create([
            'member_id' => $member1->id,
            'claim_date' => '2026-03-03',
            'claim_amount' => 2000000,
            'approved_amount' => 2000000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member3->id,
            'claim_date' => '2026-03-10',
            'claim_amount' => 7000000,
            'approved_amount' => 7000000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member7->id,
            'claim_date' => '2026-03-18',
            'claim_amount' => 2500000,
            'approved_amount' => 2500000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member5->id,
            'claim_date' => '2026-03-25',
            'claim_amount' => 3000000,
            'approved_amount' => 3000000,
            'excess_amount' => 0,
        ]);

        // ---- APRIL 2026 ----
        Claim::create([
            'member_id' => $member1->id,
            'claim_date' => '2026-04-02',
            'claim_amount' => 2500000,
            'approved_amount' => 1500000,
            'excess_amount' => 1000000,
        ]);

        Claim::create([
            'member_id' => $member2->id,
            'claim_date' => '2026-04-08',
            'claim_amount' => 3500000,
            'approved_amount' => 3500000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member4->id,
            'claim_date' => '2026-04-14',
            'claim_amount' => 3000000,
            'approved_amount' => 2300000,
            'excess_amount' => 700000,
        ]);

        Claim::create([
            'member_id' => $member6->id,
            'claim_date' => '2026-04-20',
            'claim_amount' => 6000000,
            'approved_amount' => 6000000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member3->id,
            'claim_date' => '2026-04-25',
            'claim_amount' => 8000000,
            'approved_amount' => 8000000,
            'excess_amount' => 0,
        ]);

        Claim::create([
            'member_id' => $member7->id,
            'claim_date' => '2026-04-28',
            'claim_amount' => 1800000,
            'approved_amount' => 1800000,
            'excess_amount' => 0,
        ]);

        // ---- LATE APRIL 2026 ----
        Claim::create([
            'member_id' => $member5->id,
            'claim_date' => '2026-04-30',
            'claim_amount' => 4500000,
            'approved_amount' => 2500000,
            'excess_amount' => 2000000,
        ]);
    }
}
