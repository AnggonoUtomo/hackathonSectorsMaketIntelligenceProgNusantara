import type { AnalysisRow } from '@/components/company-analysis-chart';

export interface ResearchSource {
    endpoint: string;
    section: string | null;
    fetchedAt: string | null;
    status: 'fresh' | 'stale' | 'unknown';
}

export interface ResearchFinding {
    id: string;
    title: string;
    description: string;
    value: number;
    unit: string;
    formula: string;
    limitation: string;
    nextCheck: string;
    evidence: {
        field: string;
        label: string;
        value: number;
        unit: string;
        period: string;
        basis: 'quarterly' | 'instant';
        sourceId: 'financials';
    }[];
}

export interface ResearchSummary {
    symbol: string;
    ruleVersion: string;
    classification: { sector: string | null; subSector: string | null; industry: string | null };
    companyKind: 'bank' | 'financial_nonbank' | 'non_financial' | 'unknown';
    period: string | null;
    state: 'ready' | 'empty' | 'unavailable';
    score: null;
    sources: { profile: ResearchSource; financials: ResearchSource };
    findings: ResearchFinding[];
    checks: { id: string; title: string; reason: string; nextCheck: string }[];
    chartRows: AnalysisRow[];
}
