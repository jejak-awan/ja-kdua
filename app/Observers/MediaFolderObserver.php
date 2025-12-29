<?php

namespace App\Observers;

use App\Models\MediaFolder;

class MediaFolderObserver
{
    /**
     * Handle the MediaFolder "deleting" event.
     */
    public function deleting(MediaFolder $mediaFolder): void
    {
        if ($mediaFolder->isForceDeleting()) {
            // Permanent delete - handled in controller for safety with checks
            return;
        }

        // Soft delete all children recursively
        foreach ($mediaFolder->children as $child) {
            $child->delete();
        }

        // Move all media in this folder to root (All Media) instead of deleting
        // This preserves files when folder is trashed
        $mediaFolder->media()->update(['folder_id' => null]);
    }

    /**
     * Handle the MediaFolder "restoring" event.
     */
    public function restoring(MediaFolder $mediaFolder): void
    {
        // Restore all children recursively
        foreach ($mediaFolder->children()->onlyTrashed()->get() as $child) {
            $child->restore();
        }

        // Note: Media files are NOT restored here because they were moved to root
        // (not deleted) when the folder was trashed. They remain accessible in All Media.
    }
}
