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
            // Permanent delete - handled in controller/service for safety with checks
            return;
        }

        // Soft delete all children recursively
        foreach ($mediaFolder->children as $child) {
            $child->delete();
        }

        // Soft delete all media in this folder
        // This preserves the folder_id relationship while trashing the files
        foreach ($mediaFolder->media as $media) {
            $media->delete();
        }
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

        // Restore all media files that belong to this folder 
        // We only restore media that were trashed (deleted_at is NOT null)
        foreach ($mediaFolder->media()->onlyTrashed()->get() as $media) {
            $media->restore();
        }
    }
}
