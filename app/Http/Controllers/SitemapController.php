<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        return Cache::remember('sitemap_index', now()->addHours(24), function () {
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            $sitemap .= '  <sitemap>' . "\n";
            $sitemap .= '    <loc>' . url('/sitemap.xml/pages') . '</loc>' . "\n";
            $sitemap .= '  </sitemap>' . "\n";
            $sitemap .= '  <sitemap>' . "\n";
            $sitemap .= '    <loc>' . url('/sitemap.xml/posts') . '</loc>' . "\n";
            $sitemap .= '  </sitemap>' . "\n";
            $sitemap .= '  <sitemap>' . "\n";
            $sitemap .= '    <loc>' . url('/sitemap.xml/categories') . '</loc>' . "\n";
            $sitemap .= '  </sitemap>' . "\n";
            $sitemap .= '</sitemapindex>';

            return Response::make($sitemap, 200, [
                'Content-Type' => 'application/xml',
            ]);
        });
    }

    public function pages()
    {
        return Cache::remember('sitemap_pages', now()->addHours(12), function () {
            $pages = Content::where('type', 'page')
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->get();

            return $this->generateSitemap($pages);
        });
    }

    public function posts()
    {
        return Cache::remember('sitemap_posts', now()->addHours(12), function () {
            $posts = Content::where('type', 'post')
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->get();

            return $this->generateSitemap($posts);
        });
    }

    public function categories()
    {
        return Cache::remember('sitemap_categories', now()->addHours(24), function () {
            $categories = Category::where('is_active', true)->get();
            $urls = [];

            foreach ($categories as $category) {
                $urls[] = [
                    'loc' => url('/category/' . $category->slug),
                    'lastmod' => $category->updated_at->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }

            return $this->generateSitemapFromUrls($urls);
        });
    }

    protected function generateSitemap($items)
    {
        $urls = [];

        foreach ($items as $item) {
            $urls[] = [
                'loc' => url('/content/' . $item->slug),
                'lastmod' => $item->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $item->type === 'page' ? '0.8' : '0.6',
            ];
        }

        return $this->generateSitemapFromUrls($urls);
    }

    protected function generateSitemapFromUrls(array $urls)
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $sitemap .= '  <url>' . "\n";
            $sitemap .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . ($url['lastmod'] ?? now()->toAtomString()) . '</lastmod>' . "\n";
            $sitemap .= '    <changefreq>' . ($url['changefreq'] ?? 'weekly') . '</changefreq>' . "\n";
            $sitemap .= '    <priority>' . ($url['priority'] ?? '0.5') . '</priority>' . "\n";
            $sitemap .= '  </url>' . "\n";
        }

        $sitemap .= '</urlset>';

        return Response::make($sitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
