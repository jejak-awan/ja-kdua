import actions from './common/actions.json';
import labels from './common/labels.json';
import validation from './common/validation.json';
import messages from './common/messages.json';
import navigation from './common/navigation.json';
import pagination from './common/pagination.json';
import status from './common/status.json';
import auth from './features/auth.json';
import content from './features/content.json';
import comments from './features/comments.json';
import languages from './features/languages.json';
import dashboard from './features/dashboard.json';
import users from './features/users.json';
import security from './features/security.json';
import redis from './features/redis.json';
import settings from './features/settings.json';
import developer from './features/developer.json';
import file_manager from './features/file_manager.json';
import newsletter from './features/newsletter.json';
import notifications from './features/notifications.json';
import widgets from './features/widgets.json';
import analytics from './features/analytics.json';
import seo from './features/seo.json';
import redirects from './features/redirects.json';
import autosave from './features/autosave.json';
import media from './features/media.json';
import categories from './features/categories.json';
import tags from './features/tags.json';
import roles from './features/roles.json';
import themes from './features/themes.json';
import forms from './features/forms.json';
import menus from './features/menus.json';
import activityLogs from './features/activity_logs.json';
import loginHistory from './features/login_history.json';
import logsDashboard from './features/logs_dashboard.json';
import securityAlerts from './features/security_alerts.json';
import search from './features/search.json';
import frontend from './features/frontend.json';
import errors from './features/errors.json';
import content_templates from './features/content_templates.json';
import system from './features/system.json';
import profile from './features/profile.json';

import scheduled_tasks from './features/scheduled_tasks.json';
import command_runner from './features/command_runner.json';
import email_templates from './features/email_templates.json';

export default {
    common: {
        actions,
        labels,
        validation,
        messages,
        navigation,
        pagination,
        status,
    },
    features: {
        auth,
        content,
        comments,
        languages,
        dashboard,
        media,
        file_manager,
        newsletter,
        notifications,
        users,
        widgets,
        analytics,
        seo,
        redirects,
        security,
        redis,
        settings,
        autosave,
        categories,
        tags,
        roles,
        themes,
        forms,
        menus,
        activityLogs,
        login_history: loginHistory,
        logs_dashboard: logsDashboard,
        security_alerts: securityAlerts,
        search,
        frontend,
        errors,
        developer,
        content_templates,
        system,
        profile,
        scheduled_tasks,
        command_runner,
        email_templates
    },
};
