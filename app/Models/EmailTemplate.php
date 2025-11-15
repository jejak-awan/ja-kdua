<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'text_body',
        'variables',
        'category',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function render(array $data = [])
    {
        $subject = $this->replaceVariables($this->subject, $data);
        $body = $this->replaceVariables($this->body, $data);
        $textBody = $this->text_body ? $this->replaceVariables($this->text_body, $data) : null;

        return [
            'subject' => $subject,
            'body' => $body,
            'text_body' => $textBody,
        ];
    }

    protected function replaceVariables($template, array $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
            $template = str_replace('{{ $'.$key.' }}', $value, $template);
        }

        // Replace common variables
        $template = str_replace('{{ site_name }}', \App\Models\Setting::get('site_name', 'CMS'), $template);
        $template = str_replace('{{ site_url }}', url('/'), $template);
        $template = str_replace('{{ current_year }}', date('Y'), $template);

        return $template;
    }

    public static function getBySlug($slug)
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }
}
