
import api from './api';

export default {
    getTemplates(params) {
        return api.get('/admin/cms/content-templates', { params });
    },
    saveTemplate(data) {
        // data: { name, body_template (json string), type: 'builder'/'section', ... }
        return api.post('/admin/cms/content-templates', data);
    },
    deleteTemplate(id) {
        return api.delete(`/admin/cms/content-templates/${id}`);
    },
    getTemplate(id) {
        return api.get(`/admin/cms/content-templates/${id}`);
    }
}
