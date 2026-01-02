
import api from './api';

export default {
    getTemplates(params) {
        return api.get('/content-templates', { params });
    },
    saveTemplate(data) {
        // data: { name, body_template (json string), type: 'builder'/'section', ... }
        return api.post('/content-templates', data);
    },
    deleteTemplate(id) {
        return api.delete(`/content-templates/${id}`);
    },
    getTemplate(id) {
        return api.get(`/content-templates/${id}`);
    }
}
