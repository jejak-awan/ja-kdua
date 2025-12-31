<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CaptchaService
{
    protected string $method;
    protected int $ttl = 300; // 5 minutes

    public function __construct()
    {
        $this->method = Setting::get('captcha_method', 'slider');
    }

    /**
     * Generate a captcha challenge based on the configured method.
     */
    public function generate(): array
    {
        return match ($this->method) {
            'math' => $this->generateMathChallenge(),
            'image' => $this->generateImageChallenge(),
            default => $this->generateSliderChallenge(),
        };
    }

    /**
     * Verify the captcha answer.
     */
    /**
     * Verify the captcha answer.
     */
    public function verify(string $token, string $answer, bool $consume = true): bool
    {
        $cacheKey = "captcha:{$token}";
        $stored = Cache::get($cacheKey);

        if (!$stored) {
            return false;
        }

        // Remove from cache after verification attempt if consume is true
        if ($consume) {
            Cache::forget($cacheKey);
        }

        return match ($stored['method']) {
            'math' => $this->verifyMath($stored, $answer),
            'image' => $this->verifyImage($stored, $answer),
            default => $this->verifySlider($stored, $answer),
        };
    }

    /**
     * Check if captcha is enabled for the given action.
     */
    public static function isEnabled(string $action = 'login'): bool
    {
        if (!Setting::get('enable_captcha', false)) {
            return false;
        }

        return match ($action) {
            'login' => (bool) Setting::get('captcha_on_login', true),
            'register' => (bool) Setting::get('captcha_on_register', true),
            default => true,
        };
    }

    /**
     * Get the current captcha method.
     */
    public static function getMethod(): string
    {
        return Setting::get('captcha_method', 'slider');
    }

    // ========================================
    // Slider Captcha
    // ========================================

    protected function generateSliderChallenge(): array
    {
        $token = Str::random(32);
        $targetPosition = rand(15, 75); // Target position 15-75% to fit within bounds on all screens

        Cache::put("captcha:{$token}", [
            'method' => 'slider',
            'target' => $targetPosition,
        ], $this->ttl);

        return [
            'method' => 'slider',
            'token' => $token,
            'target' => $targetPosition,
        ];
    }

    protected function verifySlider(array $stored, string $answer): bool
    {
        $userPosition = (int) $answer;
        $target = $stored['target'];
        $tolerance = 1; // ±1% tolerance (strict)

        return abs($userPosition - $target) <= $tolerance;
    }

    // ========================================
    // Math Captcha
    // ========================================

    protected function generateMathChallenge(): array
    {
        $token = Str::random(32);
        
        // Simplify: only addition, numbers 1-9
        $a = rand(1, 9);
        $b = rand(1, 9);
        
        $answer = $a + $b;

        Cache::put("captcha:{$token}", [
            'method' => 'math',
            'answer' => $answer,
        ], $this->ttl);

        return [
            'method' => 'math',
            'token' => $token,
            'question' => "{$a} + {$b} = ?",
        ];
    }

    protected function verifyMath(array $stored, string $answer): bool
    {
        return (int) $answer === $stored['answer'];
    }

    // ========================================
    // Image Captcha
    // ========================================

    protected function generateImageChallenge(): array
    {
        $token = Str::random(32);
        $code = strtoupper(Str::random(6)); // 6 uppercase characters

        Cache::put("captcha:{$token}", [
            'method' => 'image',
            'code' => $code,
        ], $this->ttl);

        $image = $this->createCaptchaImage($code);

        return [
            'method' => 'image',
            'token' => $token,
            'image' => 'data:image/png;base64,' . base64_encode($image),
        ];
    }

    protected function createCaptchaImage(string $code): string
    {
        $width = 140; // Reduced from 180
        $height = 50; // Reduced from 60

        // Create image
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $bgColor = imagecolorallocate($image, 245, 245, 245);
        $textColors = [
            imagecolorallocate($image, 50, 50, 150),
            imagecolorallocate($image, 150, 50, 50),
            imagecolorallocate($image, 50, 150, 50),
            imagecolorallocate($image, 100, 100, 100),
        ];
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        $noiseColor = imagecolorallocate($image, 180, 180, 180);

        // Fill background
        imagefill($image, 0, 0, $bgColor);

        // Add noise dots
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }

        // Add lines
        for ($i = 0; $i < 5; $i++) {
            imageline(
                $image,
                rand(0, $width),
                rand(0, $height),
                rand(0, $width),
                rand(0, $height),
                $lineColor
            );
        }

        // Use TrueType font for larger text
        $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        // Fallback if system font not found - normally we'd check file_exists
        $useTtf = file_exists($fontPath);
        
        $fontSize = 24; // Much larger font size
        
        // Improve spacing logic for TTF
        // Calculate approximation of text width
        $boxWidth = $width * 0.8; 
        $charPadding = $boxWidth / strlen($code);
        $startX = ($width - $boxWidth) / 2;
        
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            
            $angle = rand(-15, 15);
            $color = $textColors[array_rand($textColors)];
            
            $x = $startX + ($i * $charPadding) + rand(-2, 2);
            $y = ($height / 2) + ($fontSize / 2) + rand(-2, 2); // Baseline position
            
            if ($useTtf) {
                imagettftext($image, $fontSize, $angle, $x, $y, $color, $fontPath, $char);
            } else {
                // Legacy fallback (should ideally not happen given our search)
                // Use built-in font scaled up? No, just center it
                $baseFont = 5;
                $fw = imagefontwidth($baseFont);
                $fh = imagefontheight($baseFont);
                // Center roughly
                $lx = $x + ($charPadding/2) - ($fw/2);
                $ly = ($height - $fh) / 2;
                imagestring($image, $baseFont, $lx, $ly, $char, $color);
            }
        }

        // Output to string
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return $imageData;
    }

    protected function verifyImage(array $stored, string $answer): bool
    {
        return strtoupper(trim($answer)) === $stored['code'];
    }
}
