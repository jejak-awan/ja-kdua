export interface ContentStats {
    total: number;
    published: number;
    draft?: number;
    scheduled?: number;
    pending?: number;
    archived?: number;
    trashed?: number;
}

export interface MediaStats {
    total: number;
    total_count?: number; // Some endpoints return total_count
    size?: number;
    images?: number;
    videos?: number;
    documents?: number;
}

export interface UserStats {
    total: number;
    active?: number;
    online?: number;
}

export interface TrafficItem {
    period: string;
    visits: number;
    [key: string]: any;
}

export interface SystemStats {
    contents?: ContentStats;
    media?: MediaStats;
    users?: UserStats;
}

export interface CreatorDashboardData {
    stats?: {
        myContents: ContentStats;
        myMedia: MediaStats;
    };
    charts?: {
        myContentByStatus: { status: string; count: number }[];
        contentTraffic: { date: string; count: number }[];
        recentActivity?: { date: string; count: number }[];
    };
    topContent?: TopContentItem[];
}

export type TopContentItem = {
    id: number | string;
    title: string;
    type: string;
    views: number;
    status: string;
};

export type TrafficDataPoint = {
    period: string;
    visits: number;
    [key: string]: any;
};

export type StatusDataPoint = {
    label: string;
    count: number;
    [key: string]: any;
};
