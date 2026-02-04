
import api from './api';
import type { AxiosResponse } from 'axios';

export interface TemplateData {
    name: string;
    body_template?: string;
    type?: string;
    [key: string]: unknown;
}

export default {
    getTemplates(params?: Record<string, unknown>): Promise<AxiosResponse> {
        return api.get('/admin/ja/content-templates', { params });
    },
    saveTemplate(data: TemplateData): Promise<AxiosResponse> {
        return api.post('/admin/ja/content-templates', data);
    },
    deleteTemplate(id: number | string): Promise<AxiosResponse> {
        return api.delete(`/admin/ja/content-templates/${id}`);
    },
    getTemplate(id: number | string): Promise<AxiosResponse> {
        return api.get(`/admin/ja/content-templates/${id}`);
    }
}
