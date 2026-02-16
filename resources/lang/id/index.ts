import actions from './common/actions.json';
import labels from './common/labels.json';
import validation from './common/validation.json';
import messages from './common/messages.json';
import navigation from './common/navigation.json';
import placeholders from './common/placeholders.json';
import pagination from './common/pagination.json';
import status from './common/status.json';
import time from './common/time.json';

import auth from './features/auth.json';
import content from './features/content.json';
import comments from './features/comments.json';
import languages from './features/languages.json';
import dashboard from './features/dashboard.json';
import users from './features/users.json';
import security from './features/security.json';
import redis from './features/redis.json';
import settings from './features/settings';
import developer from './features/developer.json';
import file_manager from './features/file_manager.json';
import newsletter from './features/newsletter.json';
import notifications from './features/notifications.json';
import widgets from './features/widgets.json';
import analytics from './features/analytics.json';
import seo from './features/seo.json';
import redirects from './features/redirects.json';

import media from './features/media.json';
import categories from './features/categories.json';
import tags from './features/tags.json';
import roles from './features/roles.json';
import themes from './features/themes.json';
import forms from './features/forms.json';
import menus from './features/menus.json';
import activityJournal from './features/activity_journal.json';
import accessJournal from './features/access_journal.json';
import journalDashboard from './features/journal_dashboard.json';

import search from './features/search.json';
import frontend from './features/frontend.json';
import errors from './features/errors.json';
import content_templates from './features/content_templates.json';
import system from './features/system.json';
import profile from './features/profile.json';
import content_studio from './features/content_studio.json';
import autosave from './features/autosave.json';
import securityAlerts from './features/security_alerts.json';
import ispSupport from './features/isp/support.json';
import ispNetwork from './features/isp/network.json';
import ispOutages from './features/isp/outages.json';
import ispServiceRequests from './features/isp/service_requests.json';
import ispBilling from './features/isp/billing.json';
import ispInfra from './features/isp/infra.json';
import ispMember from './features/isp/member.json';
import ispMonitor from './features/isp/monitor.json';
import ispContracts from './features/isp/contracts.json';
import ispRadius from './features/isp/radius.json';
import ispCommon from './common/isp.json';
import ispAdmin from './features/isp/admin.json';

import ispCustomers from './features/isp/customers.json';
import ispInventory from './features/isp/inventory.json';
import ispVouchers from './features/isp/vouchers.json';
import ispPartners from './features/isp/partners.json';
import ispHotspot from './features/isp/hotspot.json';
import ispSettings from './features/isp/settings.json';
// import olt from './features/isp/olt.json';


import scheduled_tasks from './features/scheduled_tasks.json';
import command_runner from './features/command_runner.json';
import email_templates from './features/email_templates.json';
import editor from './features/editor.json';
import builder from './features/builder';

export default {
    // Builder translations at root level for backward compatibility with builder.* namespace
    builder,
    ispServiceRequests,
    isp: {
        common: ispCommon,
        support: ispSupport,
        network: ispNetwork,
        outages: ispOutages,
        member: ispMember,
        billing: ispBilling,
        infra: ispInfra,
        monitor: ispMonitor,
        contracts: ispContracts,
        service_requests: ispServiceRequests,
        customers: ispCustomers,
        admin: {
            ...ispAdmin,
            ...ispVouchers.admin,
            inventory: ispInventory,
            partner: ispPartners
        },
        hotspot: ispHotspot,
        settings: ispSettings,
        radius: ispRadius,
        olt: ispInfra.olt,
    },
    common: {
        actions,
        labels,
        validation,
        messages,
        navigation,
        placeholders,
        pagination,
        status,
        time,
    },
    features: {
        auth,
        content,
        comments,
        languages,
        dashboard,
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
        categories,
        tags,
        roles,
        themes,
        forms,
        menus,
        activityJournal,
        accessJournal,
        journalDashboard,
        search,
        frontend,
        errors,
        developer,
        content_templates,
        system,
        profile,
        content_studio,
        autosave,
        security_alerts: securityAlerts,
        scheduled_tasks,
        command_runner,
        email_templates,
        editor,
        media,
        isp: {
            common: ispCommon,
            support: ispSupport,
            network: ispNetwork,
            outages: ispOutages,
            member: ispMember,
            billing: ispBilling,
            infra: ispInfra,
            monitor: ispMonitor,
            contracts: ispContracts,
            service_requests: ispServiceRequests,
            customers: ispCustomers,
            admin: {
                ...ispAdmin,
                ...ispVouchers.admin,
                inventory: ispInventory,
                partner: ispPartners
            },
            hotspot: ispHotspot,
            settings: ispSettings,
            radius: ispRadius,
            olt: ispInfra.olt,
        }
    },
};
