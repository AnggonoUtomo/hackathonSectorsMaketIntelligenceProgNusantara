import { Bar, BarChart, CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

export type AnalysisRow = { date: string; [field: string]: number | string | null };
export type AnalysisMetric = { key: string; label: string; color: string };
export const formatValue = (value: number | string | null | undefined) =>
    typeof value === 'number'
        ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value)
        : 'Belum tersedia';
export const formatPeriod = (value: string) =>
    value.length === 4
        ? value
        : new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date(value));

export default function CompanyAnalysisChart({ rows, metrics, financial }: { rows: AnalysisRow[]; metrics: AnalysisMetric[]; financial: boolean }) {
    const data = rows.map((row) => ({
        ...row,
        ...Object.fromEntries(
            metrics.map((metric) => [metric.key, typeof row[metric.key] === 'number' ? Number(row[metric.key]) / (financial ? 1e9 : 1) : null]),
        ),
    }));
    const axes = (
        <>
            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border)" />
            <XAxis
                dataKey="date"
                tickFormatter={(value) =>
                    value.length === 4 ? value : financial ? `Q${Math.ceil(Number(value.slice(5, 7)) / 3)} ${value.slice(0, 4)}` : value.slice(5)
                }
                minTickGap={28}
                tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
            />
            <YAxis
                width={64}
                tickFormatter={(value) => new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value)}
                tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
            />
            <Tooltip
                labelFormatter={(value) => formatPeriod(String(value))}
                formatter={(value) => `${formatValue(typeof value === 'number' ? value : null)}${financial ? ' miliar Rp' : ''}`}
                contentStyle={{ background: 'var(--background)', color: 'var(--foreground)', borderColor: 'var(--border)', borderRadius: 6 }}
            />
            <Legend wrapperStyle={{ fontSize: 12, paddingTop: 12 }} />
        </>
    );
    return (
        <div
            className="h-80 w-full min-w-0"
            role="img"
            aria-label={`${metrics.map((m) => m.label).join(', ')}. ${financial ? 'Miliar rupiah' : 'Nilai per periode'}`}
        >
            <ResponsiveContainer width="100%" height="100%" minWidth={0}>
                {financial ? (
                    <BarChart data={data} margin={{ top: 16, right: 12, bottom: 12, left: 0 }} accessibilityLayer>
                        {axes}
                        {metrics.map((metric) => (
                            <Bar
                                key={metric.key}
                                dataKey={metric.key}
                                name={metric.label}
                                fill={metric.color}
                                maxBarSize={48}
                                radius={[3, 3, 0, 0]}
                                isAnimationActive={false}
                            />
                        ))}
                    </BarChart>
                ) : (
                    <LineChart data={data} margin={{ top: 16, right: 12, bottom: 12, left: 0 }} accessibilityLayer>
                        {axes}
                        {metrics.map((metric) => (
                            <Line
                                key={metric.key}
                                dataKey={metric.key}
                                name={metric.label}
                                stroke={metric.color}
                                strokeWidth={2}
                                dot={data.length < 8}
                                activeDot={{ r: 5 }}
                                connectNulls={false}
                                isAnimationActive={false}
                            />
                        ))}
                    </LineChart>
                )}
            </ResponsiveContainer>
        </div>
    );
}

export function AnalysisTable({ rows, metrics, financial }: { rows: AnalysisRow[]; metrics: AnalysisMetric[]; financial: boolean }) {
    return (
        <div className="max-h-96 overflow-auto rounded-md border">
            <table className="w-full min-w-128 text-sm">
                <caption className="text-muted-foreground p-3 text-left text-xs">
                    {financial ? 'Angka lengkap dalam rupiah (IDR)' : 'Data sumber per periode'}
                </caption>
                <thead className="bg-muted sticky top-0 text-xs uppercase">
                    <tr>
                        <th scope="col" className="p-3 text-left">
                            Periode
                        </th>
                        {metrics.map((m) => (
                            <th scope="col" key={m.key} className="p-3 text-right">
                                {m.label}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {[...rows].reverse().map((row) => (
                        <tr key={row.date} className="hover:bg-muted/50 border-t">
                            <th scope="row" className="p-3 text-left font-normal whitespace-nowrap">
                                {formatPeriod(row.date)}
                            </th>
                            {metrics.map((m) => (
                                <td key={m.key} className="p-3 text-right whitespace-nowrap tabular-nums">
                                    {formatValue(row[m.key])}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
