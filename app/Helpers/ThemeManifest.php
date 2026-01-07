<?php

namespace App\Helpers;

class ThemeManifest
{
    protected array $manifest;

    protected string $path;

    public function __construct(string $themePath)
    {
        $this->path = $themePath;
        $this->load();
    }

    /**
     * Load manifest from theme.json
     */
    public function load(): void
    {
        $manifestPath = "{$this->path}/theme.json";

        if (! file_exists($manifestPath)) {
            throw new \Exception("Theme manifest not found: {$manifestPath}");
        }

        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid theme.json: '.json_last_error_msg());
        }

        $this->manifest = $manifest;
    }

    /**
     * Get manifest data
     */
    public function getManifest(): array
    {
        return $this->manifest;
    }

    /**
     * Get theme name
     */
    public function getName(): string
    {
        return $this->manifest['name'] ?? 'Unknown Theme';
    }

    /**
     * Get theme slug
     */
    public function getSlug(): string
    {
        return $this->manifest['slug'] ?? basename($this->path);
    }

    /**
     * Get theme version
     */
    public function getVersion(): string
    {
        return $this->manifest['version'] ?? '1.0.0';
    }

    /**
     * Get theme type
     */
    public function getType(): string
    {
        return $this->manifest['type'] ?? 'frontend';
    }

    /**
     * Get theme description
     */
    public function getDescription(): ?string
    {
        return $this->manifest['description'] ?? null;
    }

    /**
     * Get theme author
     */
    public function getAuthor(): ?string
    {
        return $this->manifest['author'] ?? null;
    }

    /**
     * Get author URL
     */
    public function getAuthorUrl(): ?string
    {
        return $this->manifest['author_url'] ?? null;
    }

    /**
     * Get license
     */
    public function getLicense(): ?string
    {
        return $this->manifest['license'] ?? null;
    }

    /**
     * Get parent theme
     */
    public function getParentTheme(): ?string
    {
        return $this->manifest['parent_theme'] ?? null;
    }

    /**
     * Get dependencies
     */
    public function getDependencies(): array
    {
        return $this->manifest['dependencies'] ?? [];
    }

    /**
     * Get supports
     */
    public function getSupports(): array
    {
        return $this->manifest['supports'] ?? [];
    }

    /**
     * Get requirements
     */
    public function getRequirements(): array
    {
        return $this->manifest['requires'] ?? [];
    }

    /**
     * Get CMS version requirement
     */
    public function getRequiredCmsVersion(): ?string
    {
        return $this->manifest['requires']['cms_version'] ?? null;
    }

    /**
     * Get PHP version requirement
     */
    public function getRequiredPhpVersion(): ?string
    {
        return $this->manifest['requires']['php'] ?? null;
    }

    /**
     * Get assets configuration
     */
    public function getAssets(): array
    {
        return $this->manifest['assets'] ?? [];
    }

    /**
     * Get CSS assets
     */
    public function getCssAssets(): array
    {
        return $this->manifest['assets']['css'] ?? [];
    }

    /**
     * Get JS assets
     */
    public function getJsAssets(): array
    {
        return $this->manifest['assets']['js'] ?? [];
    }

    /**
     * Get settings schema
     */
    public function getSettingsSchema(): array
    {
        return $this->manifest['settings_schema'] ?? [];
    }

    /**
     * Validate manifest structure
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        $required = ['name', 'version'];
        foreach ($required as $field) {
            if (! isset($this->manifest[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validate version format (semver)
        if (isset($this->manifest['version'])) {
            if (! preg_match('/^\d+\.\d+\.\d+$/', $this->manifest['version'])) {
                $errors[] = 'Invalid version format. Expected semver (e.g., 1.0.0)';
            }
        }

        // Validate type
        if (isset($this->manifest['type'])) {
            $validTypes = ['frontend', 'admin', 'email'];
            if (! in_array($this->manifest['type'], $validTypes)) {
                $errors[] = 'Invalid type. Must be one of: '.implode(', ', $validTypes);
            }
        }

        // Validate assets paths exist
        if (isset($this->manifest['assets'])) {
            $assets = $this->manifest['assets'];

            if (isset($assets['css'])) {
                foreach ($assets['css'] as $cssFile) {
                    $cssPath = "{$this->path}/{$cssFile}";
                    if (! file_exists($cssPath)) {
                        $errors[] = "CSS file not found: {$cssFile}";
                    }
                }
            }

            if (isset($assets['js'])) {
                foreach ($assets['js'] as $jsFile) {
                    $jsPath = "{$this->path}/{$jsFile}";
                    if (! file_exists($jsPath)) {
                        $errors[] = "JS file not found: {$jsFile}";
                    }
                }
            }
        }

        // Validate settings schema
        if (isset($this->manifest['settings_schema'])) {
            $schema = $this->manifest['settings_schema'];
            foreach ($schema as $key => $setting) {
                if (! isset($setting['type'])) {
                    $errors[] = "Setting '{$key}' missing type";
                }

                $validTypes = ['text', 'textarea', 'number', 'email', 'url', 'color', 'select', 'checkbox', 'radio'];
                if (isset($setting['type']) && ! in_array($setting['type'], $validTypes)) {
                    $errors[] = "Setting '{$key}' has invalid type: {$setting['type']}";
                }

                if (isset($setting['type']) && $setting['type'] === 'select' && ! isset($setting['options'])) {
                    $errors[] = "Setting '{$key}' (select) missing options";
                }
            }
        }

        return $errors;
    }

    /**
     * Check if theme meets requirements
     */
    public function checkRequirements(): array
    {
        $errors = [];

        // Check CMS version
        $requiredCmsVersion = $this->getRequiredCmsVersion();
        if ($requiredCmsVersion) {
            $currentVersion = config('app.version', '1.0.0');
            if (version_compare($currentVersion, $requiredCmsVersion, '<')) {
                $errors[] = "CMS version {$currentVersion} does not meet requirement: {$requiredCmsVersion}";
            }
        }

        // Check PHP version
        $requiredPhpVersion = $this->getRequiredPhpVersion();
        if ($requiredPhpVersion) {
            $currentPhpVersion = PHP_VERSION;
            if (version_compare($currentPhpVersion, $requiredPhpVersion, '<')) {
                $errors[] = "PHP version {$currentPhpVersion} does not meet requirement: {$requiredPhpVersion}";
            }
        }

        return $errors;
    }

    /**
     * Get screenshot path
     */
    public function getScreenshotPath(): ?string
    {
        $screenshot = $this->manifest['screenshot'] ?? 'screenshot.png';
        $screenshotPath = "{$this->path}/{$screenshot}";

        return file_exists($screenshotPath) ? $screenshotPath : null;
    }

    /**
     * Create manifest from array
     */
    public static function create(string $themePath, array $data): bool
    {
        $manifestPath = "{$themePath}/theme.json";

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return file_put_contents($manifestPath, $json) !== false;
    }
}
