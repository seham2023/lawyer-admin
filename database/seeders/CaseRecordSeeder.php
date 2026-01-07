<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseRecord;
use App\Models\Opponent;
use App\Models\OpponentLawyer;
use App\Models\Payment;
use App\Models\CaseCourtHistory;
use App\Models\Session;
use App\Models\Category;
use App\Models\Status;
use App\Models\Court;
use App\Models\Currency;

class CaseRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 11;

        // Get necessary IDs
        $caseCategory = Category::where('type', 'case')->first();
        $activeStatus = Status::where('type', 'case')->first();
        $paymentStatus = Status::where('type', 'payment')->first();
        $currency = Currency::first();
        $courts = Court::limit(3)->get();

        // Create 3 opponents
        $opponents = [
            Opponent::create([
                'name' => 'محمد أحمد العلي',
                'email' => 'mohamed.ali@example.com',
                'mobile' => '0501234567',
                'location' => 'الرياض',
                'nationality_id' => 1,
            ]),
            Opponent::create([
                'name' => 'فاطمة حسن السعيد',
                'email' => 'fatima.hassan@example.com',
                'mobile' => '0509876543',
                'location' => 'جدة',
                'nationality_id' => 1,
            ]),
            Opponent::create([
                'name' => 'عبدالله خالد المطيري',
                'email' => 'abdullah.almutairi@example.com',
                'mobile' => '0505555555',
                'location' => 'الدمام',
                'nationality_id' => 1,
            ]),
        ];

        // Create 3 opponent lawyers
        $opponentLawyers = [
            OpponentLawyer::create([
                'name' => 'المحامي سعد بن عبدالعزيز',
                'mobile' => '0502222222',
                'email' => 'saad.lawyer@example.com',
            ]),
            OpponentLawyer::create([
                'name' => 'المحامية نورة بنت سلمان',
                'mobile' => '0503333333',
                'email' => 'noura.lawyer@example.com',
            ]),
            OpponentLawyer::create([
                'name' => 'المحامي فهد بن راشد',
                'mobile' => '0504444444',
                'email' => 'fahad.lawyer@example.com',
            ]),
        ];

        // Case 1: Commercial Dispute
        $case1 = CaseRecord::create([
            'user_id' => $userId,
            'client_id' => 1357,
            'category_id' => $caseCategory->id,
            'client_type_id' => $caseCategory->id,
            'status_id' => $activeStatus->id,
            'opponent_id' => $opponents[0]->id,
            'opponent_lawyer_id' => $opponentLawyers[0]->id,
            'start_date' => now()->subMonths(6),
            'subject' => 'نزاع تجاري - عقد توريد',
            'subject_description' => 'نزاع حول عقد توريد مواد بناء بقيمة 500,000 ريال. الطرف الآخر لم يلتزم بشروط العقد المتفق عليها.',
            'notes' => 'القضية في مرحلة متقدمة، تم تقديم جميع المستندات المطلوبة.',
        ]);

        // Create payment for case 1
        $case1->payment()->create([
            'amount' => 15000,
            'tax' => 2250,
            'currency_id' => $currency->id,
            'user_id' => $userId,
            'status_id' => $paymentStatus->id,
            'pay_method_id' => 1, // Cash
        ]);

        // Create court history for case 1
        CaseCourtHistory::create([
            'case_record_id' => $case1->id,
            'court_id' => $courts[0]->id,
            'transfer_date' => now()->subMonths(6),
            'transfer_reason' => 'Initial Filing',
            'is_current' => true,
        ]);

        // Create session for case 1
        Session::create([
            'case_record_id' => $case1->id,
            'court_id' => $courts[0]->id,
            'case_number' => 'TC-2024-001',
            'title' => 'الجلسة الأولى - الاستماع للدعوى',
            'details' => 'تم الاستماع لأطراف الدعوى وتقديم المستندات الأولية',
            'datetime' => now()->subMonths(5),
            'priority' => 'high',
            'judge_name' => 'القاضي عبدالرحمن السديري',
            'decision' => 'تأجيل الجلسة لتقديم مستندات إضافية',
            'next_session_date' => now()->addWeeks(2),
            'user_id' => $userId,
        ]);

        // Case 2: Family Law Case
        $case2 = CaseRecord::create([
            'user_id' => $userId,
            'client_id' => $userId,
            'category_id' => $caseCategory->id,
            'client_type_id' => $caseCategory->id,
            'status_id' => $activeStatus->id,
            'opponent_id' => $opponents[1]->id,
            'opponent_lawyer_id' => $opponentLawyers[1]->id,
            'start_date' => now()->subMonths(3),
            'subject' => 'قضية حضانة أطفال',
            'subject_description' => 'طلب حضانة الأطفال بعد الطلاق. يوجد طفلان (8 و 5 سنوات).',
            'notes' => 'القضية حساسة وتتطلب متابعة دقيقة.',
        ]);

        // Create payment for case 2
        $case2->payment()->create([
            'amount' => 10000,
            'tax' => 1500,
            'currency_id' => $currency->id,
            'user_id' => $userId,
            'status_id' => $paymentStatus->id,
            'pay_method_id' => 1, // Cash
        ]);

        // Create court history for case 2
        CaseCourtHistory::create([
            'case_record_id' => $case2->id,
            'court_id' => $courts[1]->id,
            'transfer_date' => now()->subMonths(3),
            'transfer_reason' => 'Initial Filing',
            'is_current' => true,
        ]);

        // Create session for case 2
        Session::create([
            'case_record_id' => $case2->id,
            'court_id' => $courts[1]->id,
            'case_number' => 'FAM-2024-045',
            'title' => 'جلسة الاستماع الأولى',
            'details' => 'الاستماع لطلب الحضانة ومناقشة مصلحة الأطفال',
            'datetime' => now()->subMonths(2),
            'priority' => 'medium',
            'judge_name' => 'القاضية منى العتيبي',
            'decision' => 'طلب تقرير اجتماعي من الجهات المختصة',
            'next_session_date' => now()->addWeeks(3),
            'user_id' => $userId,
        ]);

        // Case 3: Labor Dispute
        $case3 = CaseRecord::create([
            'user_id' => $userId,
            'client_id' => 1357,
            'category_id' => $caseCategory->id,
            'client_type_id' => $caseCategory->id,
            'status_id' => $activeStatus->id,
            'opponent_id' => $opponents[2]->id,
            'opponent_lawyer_id' => $opponentLawyers[2]->id,
            'start_date' => now()->subMonth(),
            'subject' => 'نزاع عمالي - مستحقات نهاية الخدمة',
            'subject_description' => 'مطالبة بمستحقات نهاية الخدمة والبدلات المتأخرة لمدة 10 سنوات عمل.',
            'notes' => 'قضية جديدة، تم تقديم عقد العمل وكشوف الرواتب.',
        ]);

        // Create payment for case 3
        $case3->payment()->create([
            'amount' => 8000,
            'tax' => 1200,
            'currency_id' => $currency->id,
            'user_id' => $userId,
            'status_id' => $paymentStatus->id,
            'pay_method_id' => 1, // Cash
        ]);

        // Create court history for case 3
        CaseCourtHistory::create([
            'case_record_id' => $case3->id,
            'court_id' => $courts[2]->id,
            'transfer_date' => now()->subMonth(),
            'transfer_reason' => 'Initial Filing',
            'is_current' => true,
        ]);

        // Create session for case 3
        Session::create([
            'case_record_id' => $case3->id,
            'court_id' => $courts[2]->id,
            'case_number' => 'LAB-2024-089',
            'title' => 'الجلسة التمهيدية',
            'details' => 'مراجعة المستندات المقدمة والاستماع للأطراف',
            'datetime' => now()->subWeeks(2),
            'priority' => 'low',
            'judge_name' => 'القاضي خالد الشمري',
            'decision' => 'تحديد موعد الجلسة القادمة',
            'next_session_date' => now()->addMonth(),
            'user_id' => $userId,
        ]);

        $this->command->info('✅ Created 3 case records with opponents, lawyers, payments, court history, and sessions for user_id 11');
    }
}
