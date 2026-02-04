<?php

namespace App\Services;

use App\Models\Setting;

class CommentSecurityService
{
    /**
     * Check if comment content is spam
     */
    public function isSpam(string $content, ?string $authorEmail = null, ?string $authorIp = null): bool
    {
        if ($this->containsBannedWords($content)) {
            return true;
        }

        if ($this->exceedsLinkLimit($content)) {
            return true;
        }

        // Future: Check against IP blocklist or spam APIs (Akismet)

        return false;
    }

    /**
     * Check for banned words
     */
    protected function containsBannedWords(string $content): bool
    {
        $bannedWords = Setting::get('comments.security.banned_words', []);

        if (empty($bannedWords)) {
            return false;
        }

        if (is_string($bannedWords)) {
            $bannedWords = json_decode($bannedWords, true) ?? [];
        }

        foreach ($bannedWords as $word) {
            if (stripos($content, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check link limit
     */
    protected function exceedsLinkLimit(string $content): bool
    {
        $maxLinks = Setting::get('comments.security.max_links', 2);

        // Count http/https occurrences
        $linkCount = substr_count(strtolower($content), 'http://') +
                     substr_count(strtolower($content), 'https://');

        return $linkCount > $maxLinks;
    }

    /**
     * Determine initial status based on moderation settings
     */
    public function getInitialStatus(bool $isSpam): string
    {
        if ($isSpam) {
            return 'spam';
        }

        $moderationEnabled = Setting::get('comments.security.moderation_enabled', true);

        return $moderationEnabled ? 'pending' : 'approved';
    }
}
