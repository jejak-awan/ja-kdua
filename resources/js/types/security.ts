export interface ShieldLog {
    id: number;
    event_type: string;
    ip_address: string;
    details: string;
    created_at: string;
}

export interface ShieldStats {
    verifications: number;
    failures: number;
    honeypot: number;
    currentDifficulty: number;
    isScaling: boolean;
}

export interface PaginationInfo {
    total: number;
    current_page: number;
    last_page: number;
}
