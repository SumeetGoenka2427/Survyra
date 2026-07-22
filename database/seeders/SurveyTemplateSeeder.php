<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use Illuminate\Database\Seeder;

class SurveyTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $questionTypeIds = QuestionType::query()->pluck('id', 'key');

        foreach ($this->templates() as $definition) {
            $template = SurveyTemplate::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'industry_category' => $definition['industry_category'],
                    'description' => $definition['description'],
                    'is_active' => true,
                ]
            );

            $template->questions()->delete();

            foreach ($definition['questions'] as $order => $question) {
                $template->questions()->create([
                    'question_type_id' => $questionTypeIds[$question['type']],
                    'question_text' => $question['text'],
                    'options' => $question['options'] ?? null,
                    'settings' => $question['settings'] ?? null,
                    'is_required' => $question['required'] ?? true,
                    'order' => $order,
                ]);
            }
        }
    }

    private function templates(): array
    {
        return [
            [
                'name' => 'Patient Satisfaction',
                'industry_category' => 'Healthcare',
                'description' => 'Overall satisfaction survey for clinic/hospital visits.',
                'questions' => [
                    ['type' => 'nps', 'text' => 'How likely are you to recommend this clinic to friends or family?'],
                    ['type' => 'rating_stars', 'text' => "Rate your doctor's consultation experience."],
                    ['type' => 'radio', 'text' => 'Was the wait time acceptable?', 'options' => ['Yes', 'No', 'Somewhat']],
                    ['type' => 'textarea', 'text' => 'Any additional comments?', 'required' => false],
                    ['type' => 'yes_no', 'text' => 'Would you visit again?'],
                ],
            ],
            [
                'name' => 'Doctor Consultation',
                'industry_category' => 'Healthcare',
                'description' => 'Feedback focused on a specific doctor consultation.',
                'questions' => [
                    ['type' => 'csat', 'text' => "How satisfied were you with your doctor's consultation?"],
                    ['type' => 'radio', 'text' => 'Did the doctor explain your diagnosis clearly?', 'options' => ['Yes', 'No', 'Partially']],
                    ['type' => 'rating_stars', 'text' => "Rate the doctor's bedside manner."],
                    ['type' => 'textarea', 'text' => 'Suggestions for improvement?', 'required' => false],
                ],
            ],
            [
                'name' => 'Dining Experience',
                'industry_category' => 'Restaurant',
                'description' => 'General dine-in experience feedback.',
                'questions' => [
                    ['type' => 'nps', 'text' => 'How likely are you to recommend us to others?'],
                    ['type' => 'rating_stars', 'text' => 'Rate the food quality.'],
                    ['type' => 'rating_stars', 'text' => 'Rate the service.'],
                    ['type' => 'radio', 'text' => 'Was your table ready on time?', 'options' => ['Yes', 'No']],
                    ['type' => 'checkbox', 'text' => 'What did you enjoy most? (select all that apply)', 'options' => ['Food Quality', 'Service', 'Ambience', 'Price'], 'required' => false],
                    ['type' => 'textarea', 'text' => "Anything else you'd like to share?", 'required' => false],
                ],
            ],
            [
                'name' => 'Delivery Feedback',
                'industry_category' => 'Restaurant',
                'description' => 'Feedback for food delivery orders.',
                'questions' => [
                    ['type' => 'csat', 'text' => 'How satisfied were you with your delivery experience?'],
                    ['type' => 'radio', 'text' => 'Was your order delivered on time?', 'options' => ['Yes', 'No', 'Slightly late']],
                    ['type' => 'radio', 'text' => 'Was your food packaged well?', 'options' => ['Yes', 'No']],
                    ['type' => 'textarea', 'text' => 'Comments about your delivery.', 'required' => false],
                ],
            ],
            [
                'name' => 'NPS Survey',
                'industry_category' => 'Customer Support',
                'description' => 'Standard Net Promoter Score survey.',
                'questions' => [
                    ['type' => 'nps', 'text' => 'How likely are you to recommend our support team?'],
                    ['type' => 'textarea', 'text' => "What's the primary reason for your score?", 'required' => false],
                ],
            ],
            [
                'name' => 'CSAT Survey',
                'industry_category' => 'Customer Support',
                'description' => 'Customer satisfaction + effort survey for a support ticket.',
                'questions' => [
                    ['type' => 'csat', 'text' => 'How satisfied are you with the support you received?'],
                    ['type' => 'ces', 'text' => 'How easy was it to get your issue resolved?'],
                    ['type' => 'textarea', 'text' => 'Any additional feedback?', 'required' => false],
                ],
            ],
            [
                'name' => 'Course Feedback',
                'industry_category' => 'Education',
                'description' => 'Feedback for a completed training/course.',
                'questions' => [
                    ['type' => 'csat', 'text' => 'How satisfied are you with this course?'],
                    ['type' => 'rating_stars', 'text' => 'Rate the instructor.'],
                    ['type' => 'radio', 'text' => 'Was the course content relevant to your goals?', 'options' => ['Yes', 'No', 'Somewhat']],
                    ['type' => 'textarea', 'text' => 'What could be improved?', 'required' => false],
                ],
            ],
            [
                'name' => 'Student Satisfaction',
                'industry_category' => 'Education',
                'description' => 'Broader institutional satisfaction survey.',
                'questions' => [
                    ['type' => 'nps', 'text' => 'How likely are you to recommend this institution?'],
                    ['type' => 'rating_stars', 'text' => 'Rate the overall learning experience.'],
                    ['type' => 'yes_no', 'text' => 'Do you feel supported by faculty and staff?'],
                    ['type' => 'textarea', 'text' => 'Additional comments.', 'required' => false],
                ],
            ],
            [
                'name' => 'Store Experience',
                'industry_category' => 'Retail',
                'description' => 'In-store visit experience feedback.',
                'questions' => [
                    ['type' => 'nps', 'text' => 'How likely are you to recommend this store?'],
                    ['type' => 'rating_stars', 'text' => 'Rate your in-store experience.'],
                    ['type' => 'radio', 'text' => 'Was a staff member available to help you?', 'options' => ['Yes', 'No']],
                    ['type' => 'textarea', 'text' => 'Comments.', 'required' => false],
                ],
            ],
            [
                'name' => 'Purchase Feedback',
                'industry_category' => 'Retail',
                'description' => 'Feedback on a specific purchase.',
                'questions' => [
                    ['type' => 'csat', 'text' => 'How satisfied are you with your purchase?'],
                    ['type' => 'dropdown', 'text' => 'Which category did you purchase from?', 'options' => ['Electronics', 'Clothing', 'Groceries', 'Home & Living', 'Other']],
                    ['type' => 'radio', 'text' => 'Did the product meet your expectations?', 'options' => ['Yes', 'No', 'Partially']],
                    ['type' => 'yes_no', 'text' => 'Would you purchase from us again?'],
                    ['type' => 'textarea', 'text' => 'Tell us more.', 'required' => false],
                ],
            ],
        ];
    }
}
