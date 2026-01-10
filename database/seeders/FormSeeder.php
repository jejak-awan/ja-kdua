<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
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
            ]
        );

        $this->createContactFields($contactForm);
        $this->createSubmissions($contactForm, $user, 15);

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
            ]
        );

        $this->createNewsletterFields($newsletterForm);
        $this->createSubmissions($newsletterForm, $user, 25);

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
            ]
        );

        $this->createFeedbackFields($feedbackForm);
        $this->createSubmissions($feedbackForm, $user, 10);

        // Job Application Form (inactive)
        $jobForm = Form::updateOrCreate(
            ['slug' => 'job-application'],
            [
                'name' => 'Job Application',
                'description' => 'Apply for open positions',
                'success_message' => 'Your application has been received. We will contact you if your profile matches our requirements.',
                'redirect_url' => null,
                'is_active' => false,
                'submission_count' => 0,
            ]
        );

        $this->createJobFields($jobForm);
    }

    private function createContactFields(Form $form): void
    {
        FormField::updateOrCreate(
            ['form_id' => $form->id, 'name' => 'first_name'],
            [
                'label' => 'First Name',
                'type' => 'text',
                'placeholder' => 'Enter your first name',
                'is_required' => true,
                'sort_order' => 1,
            ]
        );

        FormField::updateOrCreate(
            ['form_id' => $form->id, 'name' => 'last_name'],
            [
                'label' => 'Last Name',
                'type' => 'text',
                'placeholder' => 'Enter your last name',
                'is_required' => true,
                'sort_order' => 2,
            ]
        );

        FormField::updateOrCreate(
            ['form_id' => $form->id, 'name' => 'email'],
            [
                'label' => 'Email Address',
                'type' => 'email',
                'placeholder' => 'Enter your email',
                'is_required' => true,
                'sort_order' => 3,
            ]
        );

        FormField::updateOrCreate(
            ['form_id' => $form->id, 'name' => 'message'],
            [
                'label' => 'Message',
                'type' => 'textarea',
                'placeholder' => 'Enter your message',
                'is_required' => true,
                'sort_order' => 4,
            ]
        );
    }

    private function createNewsletterFields(Form $form): void
    {
        FormField::create([
            'form_id' => $form->id,
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'placeholder' => 'Enter your email',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'name',
            'label' => 'Name (optional)',
            'type' => 'text',
            'placeholder' => 'Enter your name',
            'is_required' => false,
            'sort_order' => 2,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'interests',
            'label' => 'Topics of Interest',
            'type' => 'checkbox',
            'options' => ['Technology', 'Design', 'Business', 'Marketing', 'Development'],
            'is_required' => false,
            'sort_order' => 3,
        ]);
    }

    private function createFeedbackFields(Form $form): void
    {
        FormField::create([
            'form_id' => $form->id,
            'name' => 'rating',
            'label' => 'How would you rate our service?',
            'type' => 'radio',
            'options' => ['Excellent', 'Good', 'Average', 'Poor', 'Very Poor'],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'recommend',
            'label' => 'Would you recommend us?',
            'type' => 'radio',
            'options' => ['Yes', 'No', 'Maybe'],
            'is_required' => true,
            'sort_order' => 2,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'feedback',
            'label' => 'Additional Feedback',
            'type' => 'textarea',
            'placeholder' => 'Share your thoughts...',
            'is_required' => false,
            'sort_order' => 3,
        ]);
    }

    private function createJobFields(Form $form): void
    {
        FormField::create([
            'form_id' => $form->id,
            'name' => 'name',
            'label' => 'Full Name',
            'type' => 'text',
            'placeholder' => 'Enter your full name',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'placeholder' => 'Enter your email',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'position',
            'label' => 'Position Applied For',
            'type' => 'select',
            'options' => ['Software Engineer', 'Designer', 'Product Manager', 'Marketing Specialist', 'Other'],
            'is_required' => true,
            'sort_order' => 3,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'experience',
            'label' => 'Years of Experience',
            'type' => 'number',
            'placeholder' => 'Enter years of experience',
            'is_required' => true,
            'sort_order' => 4,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'portfolio',
            'label' => 'Portfolio URL',
            'type' => 'url',
            'placeholder' => 'https://your-portfolio.com',
            'is_required' => false,
            'sort_order' => 5,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'name' => 'cover_letter',
            'label' => 'Cover Letter',
            'type' => 'textarea',
            'placeholder' => 'Tell us about yourself...',
            'is_required' => true,
            'sort_order' => 6,
        ]);
    }

    private function createSubmissions(Form $form, ?User $user, int $count): void
    {
        $statuses = ['new', 'read', 'archived'];
        $names = ['John Doe', 'Jane Smith', 'Bob Wilson', 'Alice Brown', 'Charlie Davis', 'Eva Martinez', 'Frank Lee', 'Grace Kim', 'Henry Chen', 'Ivy Wang'];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'company.com'];
        $subjects = ['General Inquiry', 'Support', 'Sales', 'Partnership', 'Other'];
        $ratings = ['Excellent', 'Good', 'Average', 'Poor'];
        $messages = [
            'I would like to know more about your services.',
            'Can you help me with a technical issue?',
            'Interested in partnership opportunities.',
            'Great product! Keep up the good work.',
            'I have some feedback regarding your website.',
            'Looking forward to hearing from you.',
            'This is a test submission.',
            'Please contact me at your earliest convenience.',
        ];

        for ($i = 0; $i < $count; $i++) {
            $name = $names[array_rand($names)];
            $email = strtolower(str_replace(' ', '.', $name)).rand(1, 999).'@'.$domains[array_rand($domains)];

            $data = [];
            foreach ($form->fields as $field) {
                switch ($field->type) {
                    case 'text':
                        $data[$field->name] = $field->name === 'name' ? $name : 'Sample '.$field->label;
                        break;
                    case 'email':
                        $data[$field->name] = $email;
                        break;
                    case 'tel':
                        $data[$field->name] = '+1-'.rand(200, 999).'-'.rand(100, 999).'-'.rand(1000, 9999);
                        break;
                    case 'textarea':
                        $data[$field->name] = $messages[array_rand($messages)];
                        break;
                    case 'select':
                        $options = $field->options ?? [];
                        $data[$field->name] = ! empty($options) ? $options[array_rand($options)] : '';
                        break;
                    case 'radio':
                        $options = $field->options ?? [];
                        $data[$field->name] = ! empty($options) ? $options[array_rand($options)] : '';
                        break;
                    case 'checkbox':
                        $options = $field->options ?? [];
                        $data[$field->name] = ! empty($options) ? array_slice($options, 0, rand(1, count($options))) : [];
                        break;
                    case 'number':
                        $data[$field->name] = rand(1, 15);
                        break;
                    case 'url':
                        $data[$field->name] = 'https://example.com/'.Str::slug($name);
                        break;
                    default:
                        $data[$field->name] = 'Value for '.$field->name;
                }
            }

            FormSubmission::create([
                'form_id' => $form->id,
                'user_id' => rand(0, 1) ? $user?->id : null,
                'data' => $data,
                'ip_address' => rand(1, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);

            $form->increment('submission_count');
        }
    }
}
