<?php

namespace App\Helpers;

use App\Models\Setting;

/**
 * Helper class for media-related settings
 * Shared between Media and FileManager components
 */
class MediaSettingsHelper
{
    /**
     * Get maximum upload size in kilobytes
     */
    public static function getMaxUploadSize(): int
    {
        return (int) Setting::get('max_upload_size', 10240); // Default 10MB
    }

    /**
     * Get allowed image types (comma-separated)
     *
     * @return array<int, string>
     */
    public static function getAllowedImageTypes(): array
    {
        $types = (string) Setting::get('allowed_image_types', 'jpg,jpeg,png,gif,webp,svg');

        return array_map('trim', explode(',', $types));
    }

    /**
     * Get allowed file types (comma-separated)
     *
     * @return array<int, string>
     */
    public static function getAllowedFileTypes(): array
    {
        $types = (string) Setting::get('allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar');

        return array_map('trim', explode(',', $types));
    }

    /**
     * Get all allowed extensions (images + files)
     */
    public static function getAllowedExtensions(): array
    {
        return array_merge(
            self::getAllowedImageTypes(),
            self::getAllowedFileTypes()
        );
    }

    /**
     * Check if extension is allowed
     */
    public static function isExtensionAllowed(string $extension): bool
    {
        $extension = strtolower(trim($extension, '.'));

        return in_array($extension, self::getAllowedExtensions());
    }

    /**
     * Should auto-optimize images on upload?
     */
    public static function shouldOptimizeOnUpload(): bool
    {
        return (bool) Setting::get('auto_optimize_images', true);
    }

    /**
     * Get thumbnail width
     */
    public static function getThumbnailWidth(): int
    {
        return (int) Setting::get('thumbnail_width', 300);
    }

    /**
     * Get thumbnail height
     */
    public static function getThumbnailHeight(): int
    {
        return (int) Setting::get('thumbnail_height', 300);
    }

    /**
     * Get storage driver
     */
    public static function getStorageDriver(): string
    {
        return Setting::get('storage_driver', 'local');
    }

    /**
     * Get validation rules for file upload
     */
    public static function getUploadValidationRules(): array
    {
        $maxSize = self::getMaxUploadSize();
        $allowedExtensions = implode(',', self::getAllowedExtensions());

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxSize,
                'mimes:'.$allowedExtensions,
            ],
        ];
    }

    /**
     * Get validation rules for image upload only
     */
    public static function getImageUploadValidationRules(): array
    {
        $maxSize = self::getMaxUploadSize();
        $allowedImages = implode(',', self::getAllowedImageTypes());

        return [
            'file' => [
                'required',
                'file',
                'max:'.$maxSize,
                'mimes:'.$allowedImages,
            ],
        ];
    }
}
