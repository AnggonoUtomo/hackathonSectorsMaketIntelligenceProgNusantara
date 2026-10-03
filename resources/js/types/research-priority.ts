export type ScoreMetric = {
    key: string;
    label: string;
    value: number | null;
    percentile: number | null;
    unit: string;
    period: string;
    basis: string;
    priceDate?: string | null;
    reason: string | null;
    rank: number | null;
    populationSize: number | null;
    group: string | null;
    groupLevel: string | null;
    effectiveWeight: number;
    originalWeight: number;
    higherIsBetter: boolean;
    inputs: Record<string, unknown>;
    peers: Array<{ symbol: string; name: string; value: number; period: string; fetchedAt: string }>;
    excluded: Array<{ symbol: string; reason: string }>;
    attempts: Array<{ period: string; level: string; group: string; validPeers: number }>;
};

export type ResearchPriority = {
    marketNotice?: string;
    symbol: string;
    name: string;
    score: number | null;
    completeness: number;
    formulaVersion: string;
    evidenceId: string;
    fetchedAt: string;
    expiresAt: string;
    reason: string | null;
    populationCount: number;
    components: Array<{ key: string; label: string; weight: number; score: number | null; effectiveWeight: number; metrics: ScoreMetric[] }>;
};
