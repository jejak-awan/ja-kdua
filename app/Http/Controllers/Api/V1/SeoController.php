<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    public function generateSitemap()
    {
        // Sitemap is generated on-the-fly by SitemapController
        return response()->json([
            'message' => 'Sitemap is available at /sitemap.xml',
            'url' => url('/sitemap.xml'),
        ]);
    }

    public function getRobotsTxt()
    {
        $path = public_path('robots.txt');
        
        if (File::exists($path)) {
            $content = File::get($path);
        } else {
            $content = "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml');
        }

        return response()->json([
            'content' => $content,
        ]);
    }

    public function updateRobotsTxt(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $path = public_path('robots.txt');
        File::put($path, $validated['content']);

        return response()->json([
            'message' => 'Robots.txt updated successfully',
            'content' => $validated['content'],
        ]);
    }

    public function analyzeContent(Content $content)
    {
        $score = 0;
        $maxScore = 100;
        $issues = [];
        $suggestions = [];

        // Check title
        if ($content->title) {
            $titleLength = strlen($content->title);
            if ($titleLength >= 30 && $titleLength <= 60) {
                $score += 20;
            } else {
                $issues[] = 'Title length should be between 30-60 characters';
                $suggestions[] = 'Optimize title length for better SEO';
            }
        } else {
            $issues[] = 'Title is missing';
        }

        // Check meta title
        if ($content->meta_title) {
            $metaTitleLength = strlen($content->meta_title);
            if ($metaTitleLength >= 30 && $metaTitleLength <= 60) {
                $score += 15;
            } else {
                $issues[] = 'Meta title length should be between 30-60 characters';
            }
        } else {
            $suggestions[] = 'Add meta title for better SEO';
        }

        // Check meta description
        if ($content->meta_description) {
            $metaDescLength = strlen($content->meta_description);
            if ($metaDescLength >= 120 && $metaDescLength <= 160) {
                $score += 15;
            } else {
                $issues[] = 'Meta description length should be between 120-160 characters';
            }
        } else {
            $suggestions[] = 'Add meta description for better SEO';
        }

        // Check excerpt
        if ($content->excerpt) {
            $score += 10;
        } else {
            $suggestions[] = 'Add excerpt for better content preview';
        }

        // Check featured image
        if ($content->featured_image) {
            $score += 10;
        } else {
            $suggestions[] = 'Add featured image for better social sharing';
        }

        // Check OG image
        if ($content->og_image) {
            $score += 10;
        } else {
            $suggestions[] = 'Add OG image for better social media preview';
        }

        // Check body length
        if ($content->body) {
            $bodyLength = strlen(strip_tags($content->body));
            if ($bodyLength >= 300) {
                $score += 15;
            } else {
                $issues[] = 'Content body is too short (minimum 300 characters recommended)';
            }
        }

        // Check slug
        if ($content->slug) {
            $slugLength = strlen($content->slug);
            if ($slugLength <= 100) {
                $score += 5;
            } else {
                $issues[] = 'Slug is too long';
            }
        }

        // Check keywords
        if ($content->meta_keywords) {
            $keywords = explode(',', $content->meta_keywords);
            if (count($keywords) >= 3 && count($keywords) <= 10) {
                $score += 10;
            } else {
                $suggestions[] = 'Add 3-10 relevant keywords';
            }
        } else {
            $suggestions[] = 'Add meta keywords for better SEO';
        }

        return response()->json([
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100, 2),
            'grade' => $this->getGrade($score, $maxScore),
            'issues' => $issues,
            'suggestions' => $suggestions,
        ]);
    }

    protected function getGrade($score, $maxScore)
    {
        $percentage = ($score / $maxScore) * 100;
        
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        return 'F';
    }

    public function generateSchema(Content $content)
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $content->type === 'post' ? 'BlogPosting' : 'Article',
            'headline' => $content->title,
            'description' => $content->meta_description ?? $content->excerpt,
            'datePublished' => $content->published_at?->toIso8601String() ?? $content->created_at->toIso8601String(),
            'dateModified' => $content->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $content->author->name ?? 'Unknown',
            ],
        ];

        if ($content->featured_image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => url($content->featured_image),
            ];
        }

        if ($content->category) {
            $schema['articleSection'] = $content->category->name;
        }

        return response()->json($schema);
    }
}
