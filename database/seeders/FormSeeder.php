<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        // Contact Form
        $contactForm = Form::updateOrCreate(
            ['slug' => 'contact'],
            [
                'name' => 'Contact Form',
                'description' => 'General contact form for website visitors',
                'success_message' => 'Thank you for contacting us! We will get back to you soon.',
                'redirect_url' => null,
                'is_active' => true,
                'submission_count' => 0,
                'blocks' => $this->createContactBlocks(),
            ]
        );

        $this->createContactSubmissions($contactForm, $user, 15);

        // Newsletter Signup Form
        $newsletterForm = Form::updateOrCreate(
            ['slug' => 'newsletter-signup'],
            [
                'name' => 'Newsletter Signup',
                'description' => 'Subscribe to our newsletter',
                'success_message' => 'You have been subscribed to our newsletter!',
                'redirect_url' => null,
                'is_active' => true,
                'submission_count' => 0,
                'blocks' => $this->createNewsletterBlocks(),
            ]
        );

        $this->createNewsletterSubmissions($newsletterForm, $user, 25);

        // Feedback Form
        $feedbackForm = Form::updateOrCreate(
            ['slug' => 'feedback-form'],
            [
                'name' => 'Feedback Form',
                'description' => 'Collect feedback from users',
                'success_message' => 'Thank you for your feedback!',
                'redirect_url' => null,
                'is_active' => true,
                'submission_count' => 0,
                'blocks' => $this->createFeedbackBlocks(),
            ]
        );

        $this->createFeedbackSubmissions($feedbackForm, $user, 10);

        // Job Application Form (inactive)
        Form::updateOrCreate(
            ['slug' => 'job-application'],
            [
                'name' => 'Job Application',
                'description' => 'Apply for open positions',
                'success_message' => 'Your application has been received. We will contact you if your profile matches our requirements.',
                'redirect_url' => null,
                'is_active' => false,
                'submission_count' => 0,
                'blocks' => $this->createJobBlocks(),
            ]
        );
    }

    /**
     * Helper to create a block structure with fields inside a row/column
     */
    private function wrapInLayout(array $fieldBlocks): array
    {
        return [
            [
                'id' => 'row-' . Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                        'id' => 'col-' . Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => $fieldBlocks,
                    ]
                ],
            ]
        ];
    }

    private function createContactBlocks(): array
    {
        return $this->wrapInLayout([
            [
                'id' => 'contact-first-name',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'first_name',
                    'label' => 'First Name',
                    'type' => 'text',
                    'placeholder' => 'Enter your first name',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'contact-last-name',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'last_name',
                    'label' => 'Last Name',
                    'type' => 'text',
                    'placeholder' => 'Enter your last name',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'contact-email',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'email',
                    'label' => 'Email Address',
                    'type' => 'email',
                    'placeholder' => 'Enter your email',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'contact-message',
                'type' => 'form_textarea',
                'settings' => [
                    'field_id' => 'message',
                    'label' => 'Message',
                    'placeholder' => 'Enter your message',
                    'is_required' => true,
                ],
                'children' => [],
            ],
        ]);
    }

    private function createNewsletterBlocks(): array
    {
        return $this->wrapInLayout([
            [
                'id' => 'newsletter-email',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'email',
                    'label' => 'Email Address',
                    'type' => 'email',
                    'placeholder' => 'Enter your email',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'newsletter-name',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'name',
                    'label' => 'Name (optional)',
                    'type' => 'text',
                    'placeholder' => 'Enter your name',
                    'is_required' => false,
                ],
                'children' => [],
            ],
            [
                'id' => 'newsletter-interests',
                'type' => 'form_checkbox',
                'settings' => [
                    'field_id' => 'interests',
                    'label' => 'Topics of Interest',
                    'options' => [
                        ['value' => 'Technology', 'label' => 'Technology'],
                        ['value' => 'Design', 'label' => 'Design'],
                        ['value' => 'Business', 'label' => 'Business'],
                        ['value' => 'Marketing', 'label' => 'Marketing'],
                        ['value' => 'Development', 'label' => 'Development'],
                    ],
                    'is_required' => false,
                ],
                'children' => [],
            ],
        ]);
    }

    private function createFeedbackBlocks(): array
    {
        return $this->wrapInLayout([
            [
                'id' => 'feedback-rating',
                'type' => 'form_radio',
                'settings' => [
                    'field_id' => 'rating',
                    'label' => 'How would you rate our service?',
                    'options' => [
                        ['value' => 'Excellent', 'label' => 'Excellent'],
                        ['value' => 'Good', 'label' => 'Good'],
                        ['value' => 'Average', 'label' => 'Average'],
                        ['value' => 'Poor', 'label' => 'Poor'],
                        ['value' => 'Very Poor', 'label' => 'Very Poor'],
                    ],
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'feedback-recommend',
                'type' => 'form_radio',
                'settings' => [
                    'field_id' => 'recommend',
                    'label' => 'Would you recommend us?',
                    'options' => [
                        ['value' => 'Yes', 'label' => 'Yes'],
                        ['value' => 'No', 'label' => 'No'],
                        ['value' => 'Maybe', 'label' => 'Maybe'],
                    ],
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'feedback-feedback',
                'type' => 'form_textarea',
                'settings' => [
                    'field_id' => 'feedback',
                    'label' => 'Additional Feedback',
                    'placeholder' => 'Share your thoughts...',
                    'is_required' => false,
                ],
                'children' => [],
            ],
        ]);
    }

    private function createJobBlocks(): array
    {
        return $this->wrapInLayout([
            [
                'id' => 'job-name',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'name',
                    'label' => 'Full Name',
                    'type' => 'text',
                    'placeholder' => 'Enter your full name',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'job-email',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'email',
                    'label' => 'Email Address',
                    'type' => 'email',
                    'placeholder' => 'Enter your email',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'job-position',
                'type' => 'form_select',
                'settings' => [
                    'field_id' => 'position',
                    'label' => 'Position Applied For',
                    'options' => [
                        ['value' => 'Software Engineer', 'label' => 'Software Engineer'],
                        ['value' => 'Designer', 'label' => 'Designer'],
                        ['value' => 'Product Manager', 'label' => 'Product Manager'],
                        ['value' => 'Marketing Specialist', 'label' => 'Marketing Specialist'],
                        ['value' => 'Other', 'label' => 'Other'],
                    ],
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'job-experience',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'experience',
                    'label' => 'Years of Experience',
                    'type' => 'number',
                    'placeholder' => 'Enter years of experience',
                    'is_required' => true,
                ],
                'children' => [],
            ],
            [
                'id' => 'job-portfolio',
                'type' => 'form_input',
                'settings' => [
                    'field_id' => 'portfolio',
                    'label' => 'Portfolio URL',
                    'type' => 'url',
                    'placeholder' => 'https://your-portfolio.com',
                    'is_required' => false,
                ],
                'children' => [],
            ],
            [
                'id' => 'job-cover-letter',
                'type' => 'form_textarea',
                'settings' => [
                    'field_id' => 'cover_letter',
                    'label' => 'Cover Letter',
                    'placeholder' => 'Tell us about yourself...',
                    'is_required' => true,
                ],
                'children' => [],
            ],
        ]);
    }

    private function createContactSubmissions(Form $form, ?User $user, int $count): void
    {
        $names = ['John Doe', 'Jane Smith', 'Bob Wilson', 'Alice Brown', 'Charlie Davis'];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $messages = [
            'I would like to know more about your services.',
            'Can you help me with a technical issue?',
            'Interested in partnership opportunities.',
            'Great product! Keep up the good work.',
        ];

        for ($i = 0; $i < $count; $i++) {
            $firstName = explode(' ', $names[array_rand($names)])[0];
            $lastName = explode(' ', $names[array_rand($names)])[1] ?? 'Doe';
            $email = strtolower($firstName . '.' . $lastName) . rand(1, 999) . '@' . $domains[array_rand($domains)];

            FormSubmission::create([
                'form_id' => $form->id,
                'user_id' => rand(0, 1) ? $user?->id : null,
                'data' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'message' => $messages[array_rand($messages)],
                ],
                'ip_address' => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'status' => ['new', 'read', 'archived'][array_rand(['new', 'read', 'archived'])],
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            $form->increment('submission_count');
        }
    }

    private function createNewsletterSubmissions(Form $form, ?User $user, int $count): void
    {
        $names = ['John Doe', 'Jane Smith', 'Bob Wilson', 'Alice Brown', 'Charlie Davis'];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $interests = ['Technology', 'Design', 'Business', 'Marketing', 'Development'];

        for ($i = 0; $i < $count; $i++) {
            $name = $names[array_rand($names)];
            $email = strtolower(str_replace(' ', '.', $name)) . rand(1, 999) . '@' . $domains[array_rand($domains)];

            FormSubmission::create([
                'form_id' => $form->id,
                'user_id' => rand(0, 1) ? $user?->id : null,
                'data' => [
                    'email' => $email,
                    'name' => rand(0, 1) ? $name : '',
                    'interests' => array_slice($interests, 0, rand(1, 3)),
                ],
                'ip_address' => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'status' => ['new', 'read', 'archived'][array_rand(['new', 'read', 'archived'])],
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            $form->increment('submission_count');
        }
    }

    private function createFeedbackSubmissions(Form $form, ?User $user, int $count): void
    {
        $ratings = ['Excellent', 'Good', 'Average', 'Poor', 'Very Poor'];
        $recommends = ['Yes', 'No', 'Maybe'];
        $feedbacks = [
            'Great service, very satisfied!',
            'Could be improved in some areas.',
            'Nothing to add.',
            'Keep up the good work!',
        ];

        for ($i = 0; $i < $count; $i++) {
            FormSubmission::create([
                'form_id' => $form->id,
                'user_id' => rand(0, 1) ? $user?->id : null,
                'data' => [
                    'rating' => $ratings[array_rand($ratings)],
                    'recommend' => $recommends[array_rand($recommends)],
                    'feedback' => rand(0, 1) ? $feedbacks[array_rand($feedbacks)] : '',
                ],
                'ip_address' => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'status' => ['new', 'read', 'archived'][array_rand(['new', 'read', 'archived'])],
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            $form->increment('submission_count');
        }
    }
}
