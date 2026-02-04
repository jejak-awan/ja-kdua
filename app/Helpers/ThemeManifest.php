<?php

namespace App\Helpers;

class ThemeManifest
{
    /** @var array<string, mixed> */
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
        if ($content === false) {
            throw new \Exception("Failed to read theme manifest: {$manifestPath}");
        }

        $manifest = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($manifest)) {
            throw new \Exception('Invalid theme.json: '.json_last_error_msg());
        }

        $this->manifest = $manifest;
    }

    /**
     * Get manifest data
     *
     * @return array<string, mixed>
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
     *
     * @return array<string, string>
     */
    public function getDependencies(): array
    {
        return (array) ($this->manifest['dependencies'] ?? []);
    }

    /**
     * Get supports
     *
     * @return array<string, bool>
     */
    public function getSupports(): array
    {
        return (array) ($this->manifest['supports'] ?? []);
    }

    /**
     * Get requirements
     *
     * @return array<string, string>
     */
    public function getRequirements(): array
    {
        return (array) ($this->manifest['requires'] ?? []);
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
     *
     * @return array<string, array<int, string>>
     */
    public function getAssets(): array
    {
        return (array) ($this->manifest['assets'] ?? []);
    }

    /**
     * Get CSS assets
     *
     * @return array<int, string>
     */
    public function getCssAssets(): array
    {
        return (array) ($this->manifest['assets']['css'] ?? []);
    }

    /**
     * Get JS assets
     *
     * @return array<int, string>
     */
    public function getJsAssets(): array
    {
        return (array) ($this->manifest['assets']['js'] ?? []);
    }

    /**
     * Get settings schema
     *
     * @return array<string, array<string, mixed>>
     */
    public function getSettingsSchema(): array
    {
        return (array) ($this->manifest['settings_schema'] ?? []);
    }

    /**
     * Validate manifest structure
     *
     * @return array<int, string>
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
            $version = (string) $this->manifest['version'];
            if (! preg_match('/^\d+\.\d+\.\d+$/', $version)) {
                $errors[] = 'Invalid version format. Expected semver (e.g., 1.0.0)';
            }
        }

        // Validate type
        if (isset($this->manifest['type'])) {
            $validTypes = ['frontend', 'admin', 'email'];
            $type = (string) $this->manifest['type'];
            if (! in_array($type, $validTypes)) {
                $errors[] = 'Invalid type. Must be one of: '.implode(', ', $validTypes);
            }
        }

        // Validate assets paths exist
        if (isset($this->manifest['assets']) && is_array($this->manifest['assets'])) {
            $assets = $this->manifest['assets'];

            if (isset($assets['css']) && is_array($assets['css'])) {
                foreach ($assets['css'] as $cssFile) {
                    $cssPath = "{$this->path}/{$cssFile}";
                    if (! file_exists($cssPath)) {
                        $errors[] = "CSS file not found: {$cssFile}";
                    }
                }
            }

            if (isset($assets['js']) && is_array($assets['js'])) {
                foreach ($assets['js'] as $jsFile) {
                    $jsPath = "{$this->path}/{$jsFile}";
                    if (! file_exists($jsPath)) {
                        $errors[] = "JS file not found: {$jsFile}";
                    }
                }
            }
        }

        // Validate settings schema
        if (isset($this->manifest['settings_schema']) && is_array($this->manifest['settings_schema'])) {
            $schema = $this->manifest['settings_schema'];
            foreach ($schema as $key => $setting) {
                if (! is_array($setting)) {
                    continue;
                }
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
     *
     * @return array<int, string>
     */
    public function checkRequirements(): array
    {
        $errors = [];

        // Check CMS version
        $requiredCmsVersion = $this->getRequiredCmsVersion();
        if ($requiredCmsVersion) {
            $currentVersion = (string) config('app.version', '1.0.0');
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
     *
     * @param  array<string, mixed>  $data
     */
    public static function create(string $themePath, array $data): bool
    {
        $manifestPath = "{$themePath}/theme.json";

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return file_put_contents($manifestPath, $json) !== false;
    }
}
