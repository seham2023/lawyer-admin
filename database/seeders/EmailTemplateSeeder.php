<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => [
                    'en' => 'Legal Consultation Messages: For responding to client inquiries and providing legal advice.',
                    'ar' => 'رسائل استشارة قانونية: للرد على استفسارات العملاء وتقديم نصائح قانونية',
                ],
                'content' => [
                    'en' => '<p>Date: {{date}}<br><br>Mr./Ms. {{name}}<br>{{country}}<br>{{city}}<br><br>Subject: Legal Consultation<br><br>Mr./Ms. {{name}},<br><br>We hope you are in good health and well-being. Thank you for reaching out to us and for your trust in our legal services.<br><br>Based on your legal inquiry, we would like to provide you with the following information and advice:<br><br>[Here, you can provide specific legal advice for the client\'s case]<br><br>If you have any further questions or need additional clarification, please do not hesitate to contact us again.<br><br>We are here to assist you and provide the appropriate legal counsel. Thank you once again for choosing us as your legal consultants, and we look forward to assisting you in the future.<br><br>Best regards,<br><br>{{admin_name}}<br><br>{{phone}}</p>',
                    'ar' => '<p>التاريخ: {{date}}<br><br>السيد/السيدة {{name}}<br>{{country}}<br>{{city}}<br><br>الموضوع: استشارة قانونية<br><br>السيد/السيدة {{name}}،<br><br>نأمل أن تكون بصحة جيدة ورفاهية. نشكرك على تواصلك معنا وعلى ثقتك في خدماتنا القانونية.<br><br>استنادًا إلى استفسارك القانوني، نود أن نقدم لك المعلومات والنصيحة التالية:<br><br>[هنا يمكنك تقديم النصيحة القانونية المحددة لحالة العميل]<br><br>إذا كانت لديك أي أسئلة أخرى أو تحتاج إلى مزيد من التوضيح، فلا تتردد في الاتصال بنا مرة أخرى.<br><br>نحن هنا لمساعدتك وتقديم الاستشارة القانونية المناسبة. نشكرك مرة أخرى على اختيارنا كمستشارين قانونيين لك، ونتطلع إلى مساعدتك في المستقبل.<br><br>أطيب التحيات,<br><br>{{admin_name}}<br><br>{{phone}}</p>',
                ],
            ],
            [
                'name' => [
                    'en' => 'Appointment Messages: For scheduling meetings with clients or colleagues.',
                    'ar' => 'رسائل مواعيد: لتنظيم اجتماعات مع العملاء أو الزملاء',
                ],
                'content' => [
                    'en' => '<p>Date: {{date}}<br><br>Dear {{name}},<br><br>Your appointment has been scheduled for {{date_time}} at {{location}}. Please let us know if you have any questions or need to reschedule.<br><br>Thank you!<br><br>Best regards,<br><br>{{admin_name}}<br>{{phone}}</p>',
                    'ar' => '<p>التاريخ: {{date}}<br><br>عزيزي/عزيزتي {{name}}،<br><br>تم تحديد موعدك في {{date_time}} في {{location}}. يرجى إعلامنا إذا كان لديك أي استفسارات أو تحتاج إلى إعادة جدولة.<br><br>شكرًا لك!<br><br>أطيب التحيات،<br><br>{{admin_name}}<br>{{phone}}</p>',
                ],
            ],
            [
                'name' => [
                    'en' => 'Case Follow-up Messages: For tracking legal case developments with clients.',
                    'ar' => 'رسائل متابعة قضايا: لمتابعة تطورات القضايا القانونية مع العملاء.',
                ],
                'content' => [
                    'en' => '<p>Date: {{date}}<br><br>Dear {{name}},<br><br>This is an update regarding your case . The following progress has been made:<br><br>[Insert case updates here]<br><br>Please feel free to reach out with any questions or concerns.<br><br>Best regards,<br><br>{{admin_name}}<br>{{phone}}</p>',
                    'ar' => '<p>التاريخ: {{date}}<br><br>عزيزي/عزيزتي {{name}}،<br><br>هذا تحديث بشأن قضيتك  . تم تحقيق التقدم التالي:<br><br>[أدخل تحديثات القضية هنا]<br><br>لا تتردد في التواصل معنا إذا كان لديك أي استفسارات أو مخاوف.<br><br>أطيب التحيات،<br><br>{{admin_name}}<br>{{phone}}</p>',
                ],
            ],
            [
                'name' => [
                    'en' => 'Inquiry and Summons Messages: For sending official inquiries or legal documents to other parties.',
                    'ar' => 'رسائل استجواب واستدعاء: لإرسال استفسارات رسمية أو مستندات قانونية إلى الأطراف الأخرى.',
                ],
                'content' => [
                    'en' => '<p>Date: {{date}}<br><br>To: {{recipient}}<br><br>Subject: Legal Inquiry<br><br>We hereby summon you to provide the requested information  Your prompt response is appreciated.<br><br>Best regards,<br>{{admin_name}}<br>{{phone}}</p>',
                    'ar' => '<p>التاريخ: {{date}}<br><br>إلى: {{recipient}}<br><br>الموضوع: استفسار قانوني<br><br>نطلب منك تقديم المعلومات المطلوبة بخصوص القضية: نقدر استجابتك السريعة.<br><br>أطيب التحيات،<br>{{admin_name}}<br>{{phone}}</p>',
                ],
            ],
            [
                'name' => [
                    'en' => 'Settlement Messages: For negotiating amicable settlements in disputes.',
                    'ar' => 'رسائل تسوية: للتفاوض حول التسويات الودية في النزاعات.',
                ],
                'content' => [
                    'en' => '<p>Date: {{date}}<br><br>Dear {{name}},<br><br>Regarding the ongoing dispute, we would like to propose the following settlement terms:<br><br>[Insert settlement terms here]<br><br>Please review the terms and let us know your position.<br><br>Best regards,<br>{{admin_name}}<br>{{phone}}</p>',
                    'ar' => '<p>التاريخ: {{date}}<br><br>عزيزي/عزيزتي {{name}}،<br><br>بخصوص النزاع الجاري، نود أن نقترح الشروط التالية للتسوية:<br><br>[أدخل شروط التسوية هنا]<br><br>يرجى مراجعة الشروط وإبلاغنا بموقفك.<br><br>أطيب التحيات،<br>{{admin_name}}<br>{{phone}}</p>',
                ],
            ],
            [
                'name' => [
                    'en' => 'Complaint or Objection Messages: To express your legal position on certain matters.',
                    'ar' => 'رسائل شكوى أو اعتراض: للتعبير عن موقفك القانوني بشأن مسائل معينة.',
                ],
                'content' => [
                    'en' => '<p>Date: {{date}}<br><br>To: {{name}}<br><br>Subject: Complaint/Objection<br><br>We are writing to formally express our objection regarding {{issue_details}}. Please review this matter urgently.<br><br>Best regards,<br>{{admin_name}}<br>{{phone}}</p>',
                    'ar' => '<p>التاريخ: {{date}}<br><br>إلى: {{name}}<br><br>الموضوع: شكوى/اعتراض<br><br>نكتب للتعبير رسميًا عن اعتراضنا بشأن {{issue_details}}. يرجى مراجعة هذه المسألة بشكل عاجل.<br><br>أطيب التحيات،<br>{{admin_name}}<br>{{phone}}</p>',
                ],
            ],
        ];
        

        foreach ($templates as $template) {
            EmailTemplate::create([
                'name' => $template['name'],
                'content' => $template['content'],
            ]);
        }
    }
}
