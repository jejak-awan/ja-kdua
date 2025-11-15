#!/bin/bash

# Script to update all API controllers to extend BaseApiController
# This is a helper script - manual review is still needed

CONTROLLERS_DIR="app/Http/Controllers/Api/V1"

# List of controllers to update (excluding BaseApiController and AuthController which are already done)
CONTROLLERS=(
    "RoleController"
    "SettingController"
    "TagController"
    "CategoryController"
    "ContentController"
    "MediaController"
    "CommentController"
    "FormController"
    "AnalyticsController"
    "SearchController"
    "SeoController"
    "RedirectController"
    "BackupController"
    "SecurityController"
    "SystemController"
    "ActivityLogController"
    "NotificationController"
    "ScheduledTaskController"
    "FileManagerController"
    "LogController"
    "LanguageController"
    "ThemeController"
    "MenuController"
    "WidgetController"
    "PluginController"
    "WebhookController"
    "FieldGroupController"
    "CustomFieldController"
    "FormSubmissionController"
    "EmailTemplateController"
    "ContentTemplateController"
    "ContentRevisionController"
    "MediaFolderController"
)

echo "This script will help identify controllers that need to be updated."
echo "Manual review and update is required for each controller."
echo ""
echo "Controllers to update:"
for controller in "${CONTROLLERS[@]}"; do
    if [ -f "$CONTROLLERS_DIR/$controller.php" ]; then
        echo "  - $controller.php"
    fi
done

