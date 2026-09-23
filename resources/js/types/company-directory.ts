export interface CompanyIdentity {
    symbol: string;
    name: string;
    logoUrl: string | null;
}

export interface CompanySearchResult {
    items: CompanyIdentity[];
    total: number;
    page: number;
    perPage: number;
    fetchedAt: string;
}

export interface CompanyProfile extends CompanyIdentity {
    sector: string | null;
    subSector: string | null;
    industry: string | null;
    board: string | null;
    listingDate: string | null;
    address: string | null;
    website: string | null;
    phone: string | null;
    employees: number | null;
    marketCap: number | null;
    price: number | null;
    priceDate: string | null;
    changePercent: number | null;
    indices: string[];
    fetchedAt: string;
}

export interface MarketDataError {
    reason: string;
    message: string;
}
