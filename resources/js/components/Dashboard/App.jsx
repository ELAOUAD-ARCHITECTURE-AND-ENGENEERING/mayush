import { useState, useEffect, useRef } from "react";
import {
  AreaChart, Area, BarChart, Bar, LineChart, Line,
  XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell, RadialBarChart, RadialBar, Legend
} from "recharts";

/**
 * MarketOps Technical Dashboard
 * High-Fidelity Professional Implementation
 */

const API = (path) => {
    const base = (window.APP_BASE_URL || '').replace(/\/$/, '');
    return `${base}${path}`;
};

const safeArr = (val) => (Array.isArray(val) ? val : []);

const fmt = (n) => n >= 1000 ? `${(n / 1000).toFixed(1)}k` : n;
const fmtCurrency = (n, symbol = '$') => `${symbol}${n >= 1000 ? (n / 1000).toFixed(1) + "k" : n}`;

// ── SUB-COMPONENTS ────────────────────────────────────────────────────────────
const KpiCard = ({ label, value, sub, delta, positive, icon, sparkData }) => (
  <div style={{
    background: "linear-gradient(135deg, #1e1e2e 0%, #16213e 100%)",
    border: "1px solid rgba(99,102,241,0.18)",
    borderRadius: 16,
    padding: "20px 22px",
    display: "flex",
    flexDirection: "column",
    gap: 8,
    position: "relative",
    overflow: "hidden",
    flex: 1,
    minWidth: 0,
  }}>
    <div style={{ position: "absolute", top: 16, right: 16, fontSize: 22, opacity: .7 }}>{icon}</div>
    <div style={{ fontSize: 11, fontWeight: 700, letterSpacing: "0.1em", color: "#6366f1", textTransform: "uppercase" }}>{label}</div>
    <div style={{ fontSize: 30, fontWeight: 800, color: "#f8fafc", letterSpacing: "-0.03em" }}>{value}</div>
    {sparkData && sparkData.length > 0 && (
      <ResponsiveContainer width="100%" height={36}>
        <AreaChart data={sparkData.map(v => ({v}))} margin={{ top: 0, right: 0, bottom: 0, left: 0 }}>
          <defs>
            <linearGradient id={`sg-${label}`} x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={positive ? "#6ee7b7" : "#f87171"} stopOpacity={0.4} />
              <stop offset="95%" stopColor={positive ? "#6ee7b7" : "#f87171"} stopOpacity={0} />
            </linearGradient>
          </defs>
          <Area type="monotone" dataKey="v" stroke={positive ? "#6ee7b7" : "#f87171"} strokeWidth={1.5} fill={`url(#sg-${label})`} dot={false} />
        </AreaChart>
      </ResponsiveContainer>
    )}
    <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
      {delta != null && (
        <span style={{
          background: positive ? "rgba(110,231,183,0.15)" : "rgba(248,113,113,0.15)",
          color: positive ? "#6ee7b7" : "#f87171",
          borderRadius: 6, padding: "2px 8px", fontSize: 11, fontWeight: 700
        }}>{delta}</span>
      )}
      <span style={{ fontSize: 11, color: "#64748b" }}>{sub}</span>
    </div>
  </div>
);

const SectionHeader = ({ title, sub, action, onAction }) => (
  <div style={{ display: "flex", justifyContent: "space-between", alignItems: "flex-start", marginBottom: 16 }}>
    <div>
      <div style={{ fontSize: 15, fontWeight: 700, color: "#f1f5f9", letterSpacing: "-0.01em" }}>{title}</div>
      <div style={{ fontSize: 11, color: "#64748b", marginTop: 2 }}>{sub}</div>
    </div>
    {action && (
      <button 
        onClick={onAction}
        style={{
          background: "rgba(99,102,241,0.12)", border: "1px solid rgba(99,102,241,0.3)",
          borderRadius: 8, color: "#818cf8", fontSize: 11, fontWeight: 600,
          padding: "5px 12px", cursor: "pointer"
        }}
      >
        {action}
      </button>
    )}
  </div>
);

const StatusDot = ({ status }) => (
  <span style={{
    display: "inline-flex", alignItems: "center", gap: 5,
    color: status === "ok" ? "#6ee7b7" : "#fbbf24",
    fontSize: 11, fontWeight: 600
  }}>
    <span style={{
      width: 7, height: 7, borderRadius: "50%",
      background: status === "ok" ? "#6ee7b7" : "#fbbf24",
      boxShadow: `0 0 6px ${status === "ok" ? "#6ee7b7" : "#fbbf24"}`,
      animation: "pulse 2s infinite"
    }} />
    {status === "ok" ? "Operational" : "Degraded"}
  </span>
);

const Card = ({ children, style = {} }) => (
  <div style={{
    background: "linear-gradient(135deg, #1e1e2e 0%, #16213e 100%)",
    border: "1px solid rgba(99,102,241,0.14)",
    borderRadius: 18,
    padding: "22px 24px",
    ...style
  }}>{children}</div>
);

const CustomTooltip = ({ active, payload, label }) => {
  if (!active || !payload?.length) return null;
  return (
    <div style={{
      background: "#0f172a", border: "1px solid rgba(99,102,241,0.3)",
      borderRadius: 10, padding: "10px 14px", fontSize: 12, color: "#e2e8f0"
    }}>
      <div style={{ fontWeight: 700, marginBottom: 4, color: "#818cf8" }}>{label}</div>
      {payload.map((p, i) => (
        <div key={i} style={{ display: "flex", gap: 8, alignItems: "center" }}>
          <span style={{ width: 8, height: 8, borderRadius: "50%", background: p.color, display: "inline-block" }} />
          {p.name}: <strong>{typeof p.value === "number" ? (p.name.toLowerCase().includes('revenue') || p.name.toLowerCase().includes('actual') || p.name.toLowerCase().includes('forecast') ? fmtCurrency(p.value) : p.value) : p.value}</strong>
        </div>
      ))}
    </div>
  );
};

// --- REPLACED BY RECHARTS ---

const PageAnalysisTable = ({ data, onSelectPage }) => (
    <div style={{ overflowX: 'auto' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
            <thead>
                <tr style={{ textAlign: 'left', borderBottom: '2px solid #f1f3f5' }}>
                    <th style={{ padding: '12px 10px', color: '#718096', fontSize: '0.8rem', fontWeight: '700' }}>PAGE PATH</th>
                    <th style={{ padding: '12px 10px', color: '#718096', fontSize: '0.8rem', fontWeight: '700' }}>VIEWS</th>
                    <th style={{ padding: '12px 10px', color: '#718096', fontSize: '0.8rem', fontWeight: '700' }}>UNIQUE</th>
                    <th style={{ padding: '12px 10px', color: '#718096', fontSize: '0.8rem', fontWeight: '700' }}>AVG TIME</th>
                    <th style={{ padding: '12px 10px', color: '#718096', fontSize: '0.8rem', fontWeight: '700' }}>ACTION</th>
                </tr>
            </thead>
            <tbody>
                {safeArr(data).map((page, i) => (
                    <tr key={i} style={{ borderBottom: '1px solid #0f172a', transition: 'background 0.2s' }}>
                        <td style={{ padding: '14px 10px', fontSize: '0.85rem', color: '#3182ce', fontWeight: '600' }}>{page.url}</td>
                        <td style={{ padding: '14px 10px', fontSize: '0.85rem', fontWeight: '700' }}>{page.views}</td>
                        <td style={{ padding: '14px 10px', fontSize: '0.85rem', color: '#4a5568' }}>{page.unique_views}</td>
                        <td style={{ padding: '14px 10px', fontSize: '0.85rem', color: '#4a5568' }}>{Math.round(page.avg_time)}s</td>
                        <td style={{ padding: '14px 10px', fontSize: '0.85rem' }}>
                            <button 
                                onClick={() => onSelectPage(page.url)}
                                style={{ padding: '4px 8px', borderRadius: '4px', background: '#ebf8ff', color: '#3182ce', border: '1px solid #bee3f8', fontSize: '0.75rem', fontWeight: '700', cursor: 'pointer' }}
                            >
                                Heatmap
                            </button>
                        </td>
                    </tr>
                ))}
            </tbody>
        </table>
    </div>
);

const BehaviorFlow = ({ data }) => {
    const flows = safeArr(data);
    const nodes = Array.from(new Set(flows.flatMap(f => [f.source, f.target])));
    
    return (
        <div style={{ padding: '20px', background: '#0f172a', borderRadius: '12px' }}>
            <h4 style={{ fontSize: '0.9rem', color: '#4a5568', margin: '0 0 15px 0' }}>Top Conversion Paths</h4>
            {flows.map((f, i) => (
                <div key={i} style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '12px' }}>
                    <div style={{ flex: 1, padding: '8px', background: '#fff', borderRadius: '6px', fontSize: '0.75rem', border: '1px solid #1e293b', textAlign: 'center', fontWeight: '600' }}>
                        {f.source.substring(0, 20)}
                    </div>
                    <div style={{ color: '#cbd5e0' }}>→</div>
                    <div style={{ flex: 1, padding: '8px', background: '#fff', borderRadius: '6px', fontSize: '0.75rem', border: '1px solid #1e293b', textAlign: 'center', fontWeight: '600' }}>
                        {f.target.substring(0, 20)}
                    </div>
                    <div style={{ minWidth: '40px', textAlign: 'right', fontWeight: '700', color: '#3182ce', fontSize: '0.85rem' }}>
                        {f.value}
                    </div>
                </div>
            ))}
        </div>
    );
};

const HeatmapOverlay = ({ data, onClose }) => {
    const { points, max_intensity, url } = data || { points: [], max_intensity: 0, url: '/' };
    
    return (
        <div style={{ position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', zIndex: 9999, background: 'rgba(26, 32, 44, 0.9)', display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '40px' }}>
            <div style={{ background: '#fff', width: '100%', maxWidth: '1000px', height: '100%', borderRadius: '20px', overflow: 'hidden', display: 'flex', flexDirection: 'column' }}>
                <div style={{ padding: '20px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid #edf2f7' }}>
                    <div>
                        <h2 style={{ margin: 0, fontSize: '1.25rem' }}>Interaction Heatmap</h2>
                        <span style={{ fontSize: '0.85rem', color: '#718096' }}>Context: {url}</span>
                    </div>
                    <button onClick={onClose} style={{ border: 'none', background: 'none', fontSize: '1.5rem', cursor: 'pointer' }}>×</button>
                </div>
                <div style={{ flex: 1, position: 'relative', background: '#f7fafc', overflow: 'auto' }}>
                    {/* Simulated Page Layout */}
                    <div style={{ minHeight: '1200px', padding: '20px' }}>
                        <div style={{ height: '80px', background: '#fff', border: '1px solid #1e293b', marginBottom: '20px' }} />
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '20px', marginBottom: '20px' }}>
                            <div style={{ height: '300px', background: '#fff', border: '1px solid #1e293b' }} />
                            <div style={{ height: '300px', background: '#fff', border: '1px solid #1e293b' }} />
                            <div style={{ height: '300px', background: '#fff', border: '1px solid #1e293b' }} />
                            <div style={{ height: '300px', background: '#fff', border: '1px solid #1e293b' }} />
                        </div>
                        <div style={{ height: '500px', background: '#fff', border: '1px solid #1e293b' }} />
                    </div>

                    {/* Heatmap Dots */}
                    <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', pointerEvents: 'none' }}>
                        {points.map((p, i) => (
                            <div key={i} style={{ 
                                position: 'absolute', 
                                left: p.x, 
                                top: p.y, 
                                width: '40px', 
                                height: '40px', 
                                transform: 'translate(-50%, -50%)',
                                borderRadius: '50%',
                                background: `radial-gradient(circle, rgba(255, 69, 0, ${Math.min(0.8, (p.intensity / max_intensity) + 0.2)}) 0%, rgba(255, 255, 0, 0) 70%)`
                            }} />
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

const WorldMap = ({ data }) => {
    const locations = safeArr(data);
    
    const dots = [];
    for(let i=0; i<25; i++) {
        for(let j=0; j<12; j++) {
            dots.push({ x: i * 24 + 12, y: j * 20 + 10 });
        }
    }
    
    return (
        <div style={{ position: 'relative', height: '280px', background: 'linear-gradient(180deg, #0f172a 0%, #ffffff 100%)', borderRadius: '16px', overflow: 'hidden', border: '1px solid #f1f3f5' }}>
            <svg viewBox="0 0 600 240" style={{ width: '100%', height: '100%', opacity: 0.15 }}>
                {dots.map((d, i) => <circle key={i} cx={d.x} cy={d.y} r="1.2" fill="#94a3b8" />)}
            </svg>
            
            {/* Live pulsing markers */}
            <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%' }}>
                {locations.map((loc, i) => {
                    // Primitive random mapping since we don't have a real geo-poly map
                    const x = (hashString(loc.country_code || 'US') % 560) + 20;
                    const y = (hashString(loc.city || 'NY') % 200) + 20;
                    return (
                        <div key={i} style={{ 
                            position: 'absolute', 
                            left: `${(x/600)*100}%`, 
                            top: `${(y/240)*100}%`,
                            width: '8px', height: '8px', background: '#3182ce', borderRadius: '50%',
                            boxShadow: '0 0 10px #3182ce',
                            animation: 'pulse 2s infinite'
                        }}>
                             <style>{`@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(3); opacity: 0; } }`}</style>
                        </div>
                    );
                })}
            </div>

            <div style={{ position: 'absolute', bottom: '15px', right: '15px', padding: '10px 15px', background: 'rgba(255,255,255,0.9)', borderRadius: '10px', backdropFilter: 'blur(4px)', border: '1px solid #1e293b' }}>
                <div style={{ fontSize: '0.75rem', fontWeight: '700', color: '#1a202c' }}>LIVE TRAFFIC</div>
                <div style={{ fontSize: '0.65rem', color: '#718096' }}>Real-time Geolocation</div>
            </div>
        </div>
    );
};

const hashString = (str) => {
    let hash = 0;
    for (let i = 0; i < str.length; i++) hash = (hash << 5) - hash + str.charCodeAt(i);
    return Math.abs(hash);
};

const FadeIn = ({ children, delay = 0 }) => (
    <div style={{
        animation: `slide-in 0.5s ease ${delay}s both`,
    }}>{children}</div>
);

const VendorsTab = ({ data, currencyConfig, dateRange, handleDateRange }) => {
    const stats = data?.kpis || {};
    const [filter, setFilter] = useState('All');
    
    const filteredDirectory = (data?.directory || []).filter(v => {
        if (filter === 'All') return true;
        return v.status === filter;
    });

    return (
        <FadeIn>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
                <div>
                    <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: "-0.03em", color: "#f1f5f9" }}>Vendor Logistics Hub</h1>
                    <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 4 }}>
                        <span style={{ width: 6, height: 6, borderRadius: "50%", background: "#6ee7b7", animation: "pulse 2s infinite", display: "inline-block" }} />
                        <span style={{ fontSize: 12, color: "#64748b" }}>Real-time vendor tracking · Performance metrics</span>
                    </div>
                </div>
                <DateRangePicker dateRange={dateRange} handleDateRange={handleDateRange} />
            </div>

            <div className="responsive-grid grid-kpi" style={{ marginBottom: 24 }}>
                <KpiCard label="Active Vendors" value={stats.active || 0} delta={stats.active_delta} positive={true} sub="vs last month" icon="🏪" />
                <KpiCard label="New This Month" value={stats.new || 0} delta={stats.new_delta} positive={true} sub="registration peak" icon="✨" />
                <KpiCard label="Avg Rating" value={`${stats.rating || 0}★`} delta={stats.rating_delta} positive={true} sub="customer trust" icon="⭐" />
                <KpiCard label="Total GMV" value={fmtCurrency(stats.gmv || 0, currencyConfig.symbol)} delta={stats.gmv_delta} positive={true} sub="revenue flow" icon="💰" />
                <KpiCard label="Dispute Rate" value={`${stats.dispute_rate || 0}%`} delta={stats.dispute_delta} positive={true} sub="low risk" icon="⚖️" />
            </div>

            <div className="responsive-grid grid-2" style={{ marginBottom: 16 }}>
                <Card>
                    <SectionHeader title="Vendor Growth" sub="Monthly acquisition & retention" />
                    <ResponsiveContainer width="100%" height={240}>
                        <BarChart data={data?.growth_chart || []}>
                            <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" vertical={false} />
                            <XAxis dataKey="month" tick={{ fill: "#64748b", fontSize: 10 }} axisLine={false} />
                            <YAxis tick={{ fill: "#64748b", fontSize: 10 }} axisLine={false} />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend wrapperStyle={{ fontSize: 10, paddingTop: 10 }} />
                            <Bar dataKey="active" fill="#6366f1" name="Active" radius={[4, 4, 0, 0]} />
                            <Bar dataKey="new" fill="#10b981" name="New" radius={[4, 4, 0, 0]} />
                            <Bar dataKey="churned" fill="#ef4444" name="Churned" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </Card>
                <Card>
                    <SectionHeader title="Sales by Category" sub="Revenue distribution" />
                    <ResponsiveContainer width="100%" height={240}>
                        <PieChart>
                            <Pie data={data?.category_pie || []} innerRadius={60} outerRadius={80} paddingAngle={5} dataKey="value">
                                {(data?.category_pie || []).map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={["#6366f1", "#10b981", "#f59e0b", "#8b5cf6", "#ec4899", "#06b6d4"][index % 6]} />
                                ))}
                            </Pie>
                            <Tooltip content={<CustomTooltip />} />
                            <Legend verticalAlign="middle" align="right" layout="vertical" wrapperStyle={{ fontSize: 11, color: "#e2e8f0" }} />
                        </PieChart>
                    </ResponsiveContainer>
                </Card>
            </div>

            <Card style={{ marginBottom: 16 }}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
                    <SectionHeader title="Vendor Directory" sub="Real-time performance ranking" />
                    <div style={{ display: "flex", gap: 8 }}>
                        {['All', 'Top', 'Active', 'Rising', 'Warn'].map(btn => (
                            <button key={btn} onClick={() => setFilter(btn)} style={{
                                background: filter === btn ? "rgba(99,102,241,0.2)" : "rgba(99,102,241,0.05)",
                                border: `1px solid ${filter === btn ? "#6366f1" : "rgba(99,102,241,0.1)"}`,
                                borderRadius: 6, color: filter === btn ? "#818cf8" : "#64748b",
                                padding: "4px 12px", fontSize: 11, fontWeight: 600, cursor: "pointer"
                            }}>{btn}</button>
                        ))}
                    </div>
                </div>
                <div className="table-wrapper">
                    <table style={{ width: "100%", borderCollapse: "collapse" }}>
                        <thead>
                            <tr>
                                {["Vendor", "Category", "Revenue", "Orders", "Rating", "MoM", "Disputes", "Status", "Action"].map(h => (
                                    <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 10, letterSpacing: "0.06em", textTransform: "uppercase", borderBottom: "1px dotted rgba(99,102,241,0.2)" }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {filteredDirectory.map((v, i) => (
                                <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(99,102,241,0.05)" }}>
                                    <td style={{ padding: "12px 0" }}>
                                        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                                            <div style={{ width: 32, height: 32, borderRadius: 10, background: "rgba(99,102,241,0.15)", color: "#818cf8", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 12, fontWeight: 800 }}>{v.initials}</div>
                                            <div>
                                                <div style={{ fontSize: 13, fontWeight: 700, color: "#f1f5f9" }}>{v.name}</div>
                                                <div style={{ fontSize: 10, color: "#64748b" }}>Joined {v.joined}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style={{ fontSize: 12, color: "#94a3b8" }}>{v.category}</td>
                                    <td style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9" }}>{fmtCurrency(v.revenue, currencyConfig.symbol)}</td>
                                    <td style={{ fontSize: 12, color: "#94a3b8" }}>{v.orders}</td>
                                    <td style={{ fontSize: 12, color: "#f59e0b" }}>{"★".repeat(Math.floor(v.rating))}</td>
                                    <td><span style={{ fontSize: 11, fontWeight: 700, color: (v.trend && v.trend.startsWith('+')) ? '#10b981' : '#f87171' }}>{v.trend}</span></td>
                                    <td><span style={{ fontSize: 10, padding: "2px 6px", borderRadius: 4, background: v.disputes > 3 ? "rgba(239,68,68,0.15)" : "rgba(245,158,11,0.15)", color: v.disputes > 3 ? "#f87171" : "#f59e0b" }}>{v.disputes} active</span></td>
                                    <td><span style={{ fontSize: 10, fontWeight: 700, padding: "2px 6px", borderRadius: 4, background: "rgba(99,102,241,0.15)", color: "#818cf8" }}>{v.status}</span></td>
                                    <td>
                                        <div style={{ display: "flex", gap: 5 }}>
                                            <button style={{ background: "transparent", border: "1px solid #6366f1", color: "#6366f1", fontSize: 10, padding: "2px 6px", borderRadius: 4, cursor: "pointer" }}>View</button>
                                            <button style={{ background: "transparent", border: "1px solid #f87171", color: "#f87171", fontSize: 10, padding: "2px 6px", borderRadius: 4, cursor: "pointer" }}>Flag</button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </Card>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
                <Card>
                    <SectionHeader title="Dispute Trend" sub="Risk assessment timeline" />
                    <ResponsiveContainer width="100%" height={200}>
                        <AreaChart data={data?.dispute_trend || []}>
                            <defs>
                                <linearGradient id="colorDispute" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="5%" stopColor="#f59e0b" stopOpacity={0.4} />
                                    <stop offset="95%" stopColor="#f59e0b" stopOpacity={0} />
                                </linearGradient>
                            </defs>
                            <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" vertical={false} />
                            <XAxis dataKey="month" tick={{ fill: "#64748b", fontSize: 10 }} />
                            <Tooltip content={<CustomTooltip />} />
                            <Area type="monotone" dataKey="count" stroke="#f59e0b" fillOpacity={1} fill="url(#colorDispute)" />
                        </AreaChart>
                    </ResponsiveContainer>
                </Card>
                <Card>
                    <SectionHeader title="Vendor Alerts" sub="Actionable logistics signals" />
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 }}>
                        {[
                            { t: "Dispute Warning", m: "3 vendors exceeding 5% refund", l: "critical", i: "⚠" },
                            { t: "Declining Revenue", m: "Nike Store down 12% week/week", l: "warning", i: "📉" },
                            { t: "New Applications", m: "12 pending certifications", l: "info", i: "📄" },
                            { t: "Rising Star", m: "Alpha Gear reached Pro status", l: "success", i: "⭐" }
                        ].map((a, i) => (
                            <div key={i} style={{ background: "rgba(255,255,255,0.03)", borderRadius: 12, padding: "12px", border: "1px solid rgba(255,255,255,0.05)" }}>
                                <div style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9", display: "flex", justifyContent: "space-between" }}>
                                    {a.t} <span style={{ opacity: 0.6 }}>{a.i}</span>
                                </div>
                                <div style={{ fontSize: 11, color: "#64748b", marginTop: 4 }}>{a.m}</div>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>
        </FadeIn>
    );
};

const FinanceTab = ({ data, currencyConfig, dateRange, handleDateRange }) => {
    const stats = data?.kpis || {};
    return (
        <FadeIn>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
                <div>
                    <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: "-0.03em", color: "#f1f5f9" }}>Financial Performance</h1>
                    <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 4 }}>
                        <span style={{ width: 6, height: 6, borderRadius: "50%", background: "#6366f1", animation: "pulse 2s infinite", display: "inline-block" }} />
                        <span style={{ fontSize: 12, color: "#64748b" }}>Revenue flow monitoring · Reconciliation logs</span>
                    </div>
                </div>
                <DateRangePicker dateRange={dateRange} handleDateRange={handleDateRange} />
            </div>

            <div className="responsive-grid grid-kpi" style={{ marginBottom: 24 }}>
                <KpiCard label="Gross GMV" value={fmtCurrency(stats.gross_gmv || 0, currencyConfig.symbol)} delta={stats.gross_gmv_delta} positive={!(stats.gross_gmv_delta || "").includes("-")} sub="total throughput" icon="📊" />
                <KpiCard label="Net Revenue" value={fmtCurrency(stats.net_revenue || 0, currencyConfig.symbol)} delta={stats.net_revenue_delta} positive={!(stats.net_revenue_delta || "").includes("-")} sub="platform edge" icon="💰" />
                <KpiCard label="Commission" value={fmtCurrency(stats.commission || 0, currencyConfig.symbol)} delta={stats.commission_delta} positive={!(stats.commission_delta || "").includes("-")} sub="admin earnings" icon="💎" />
                <KpiCard label="Refund Rate" value={`${stats.refund_rate || 0}%`} delta={stats.refund_delta} positive={(stats.refund_delta || "").includes("-")} sub="quality score" icon="↩️" />
                <KpiCard label="Pending Payouts" value={fmtCurrency(stats.pending_payouts || 0, currencyConfig.symbol)} delta={`${stats.pending_vendors || 0} vendors`} positive={false} sub="awaiting action" icon="⏳" />
            </div>

            <div className="responsive-grid grid-2" style={{ marginBottom: 16 }}>
                <Card>
                    <SectionHeader title="Earnings & Outflows" sub="Commission vs Refunds" />
                    <ResponsiveContainer width="100%" height={260}>
                        <BarChart data={data?.chart || []}>
                            <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" vertical={false} />
                            <XAxis dataKey="month" tick={{ fill: "#64748b", fontSize: 10 }} />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend wrapperStyle={{ fontSize: 10 }} />
                            <Bar dataKey="commission" fill="#6366f1" radius={[4, 4, 0, 0]} name="Commission" />
                            <Bar dataKey="fees" fill="#8b5cf6" radius={[4, 4, 0, 0]} name="Fees" />
                            <Bar dataKey="refunds" fill="#f87171" radius={[4, 4, 0, 0]} name="Refunds" />
                        </BarChart>
                    </ResponsiveContainer>
                </Card>

                <Card>
                    <SectionHeader title="Profitability Pulse" sub="Transaction health metrics" />
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
                        <div style={{ background: "rgba(99,102,241,0.05)", borderRadius: 14, padding: "16px", border: "1px solid rgba(99,102,241,0.1)" }}>
                            <div style={{ fontSize: 11, color: "#64748b", textTransform: "uppercase", letterSpacing: "0.05em" }}>Refund Trend</div>
                            <ResponsiveContainer width="100%" height={100}>
                                <AreaChart data={data?.refund_trend || []}>
                                    <Area type="monotone" dataKey="rate" stroke="#f87171" fill="#f8717133" />
                                </AreaChart>
                            </ResponsiveContainer>
                        </div>
                        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 8 }}>
                            {[
                                { l: "Avg Order Val", v: fmtCurrency(data?.aov || 0, currencyConfig.symbol), c: "#6366f1" },
                                { l: "Items/Order", v: data?.items_per_order || '0', c: "#10b981" },
                                { l: "Payment Fail", v: "0.9%", c: "#f59e0b" },
                                { l: "Chargeback", v: "0.3%", c: "#ef4444" }
                            ].map(m => (
                                <div key={m.l} style={{ background: "rgba(255,255,255,0.03)", borderRadius: 10, padding: "10px", border: "1px solid rgba(255,255,255,0.05)" }}>
                                    <div style={{ fontSize: 9, color: "#64748b" }}>{m.l}</div>
                                    <div style={{ fontSize: 14, fontWeight: 800, color: m.c }}>{m.v}</div>
                                </div>
                            ))}
                        </div>
                    </div>
                </Card>
            </div>

            <div className="responsive-grid grid-sidebar-left" style={{ marginBottom: 16 }}>
                <Card>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
                        <SectionHeader title="Vendor Payouts" sub="Awaiting reconciliation" />
                        <button style={{ background: "#10b981", color: "#fff", border: "none", borderRadius: 6, padding: "5px 12px", fontSize: 11, fontWeight: 700, cursor: "pointer" }}>Run Payouts</button>
                    </div>
                    <div className="table-wrapper">
                        <table style={{ width: "100%", borderCollapse: "collapse" }}>
                            <thead>
                                <tr>
                                    {["Vendor", "Amount", "Status", "Date"].map(h => (
                                        <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 10, textTransform: "uppercase" }}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {(data?.payouts || []).map((p, i) => (
                                    <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(255,255,255,0.05)" }}>
                                        <td style={{ padding: "10px 0", fontSize: 12, fontWeight: 600, color: "#f1f5f9" }}>{p.vendor}</td>
                                        <td style={{ fontSize: 12, fontWeight: 700, color: "#818cf8" }}>{fmtCurrency(p.amount, currencyConfig.symbol)}</td>
                                        <td><span style={{ fontSize: 10, padding: "2px 6px", borderRadius: 4, background: p.status === 'Paid' ? "#10b98122" : (p.status === 'Pending' ? "#f59e0b22" : "#6366f122"), color: p.status === 'Paid' ? "#10b981" : (p.status === 'Pending' ? "#f59e0b" : "#6366f1") }}>{p.status}</span></td>
                                        <td style={{ fontSize: 11, color: "#64748b" }}>{p.date}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>

                <Card>
                    <SectionHeader title="Tax Collection" sub="Global compliance logs" />
                    <div className="table-wrapper">
                        <table style={{ width: "100%", borderCollapse: "collapse" }}>
                            <thead>
                                <tr>
                                    {["Region", "Collected", "Rate", "Status"].map(h => (
                                        <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 10, textTransform: "uppercase" }}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {(data?.tax_data || []).map((t, i) => (
                                    <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(255,255,255,0.05)" }}>
                                        <td style={{ padding: "10px 0", fontSize: 12, color: "#f1f5f9" }}>{t.region}</td>
                                        <td style={{ fontSize: 12, fontWeight: 700, color: "#e2e8f0" }}>{fmtCurrency(t.collected, currencyConfig.symbol)}</td>
                                        <td style={{ fontSize: 11, color: "#64748b" }}>{t.rate}</td>
                                        <td><span style={{ fontSize: 10, color: t.status === 'Compliant' ? '#10b981' : '#f59e0b' }}>{t.status}</span></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div style={{ marginTop: 15, padding: "10px", borderRadius: 8, background: "rgba(16,185,129,0.1)", border: "1px solid rgba(16,185,129,0.2)", textAlign: "center" }}>
                        <span style={{ fontSize: 11, color: "#64748b" }}>Total Tax Tracked:</span>{" "}
                        <span style={{ fontSize: 14, fontWeight: 800, color: "#10b981" }}>{fmtCurrency((data?.tax_data || []).reduce((acc, t) => acc + t.collected, 0), currencyConfig.symbol)}</span>
                    </div>
                </Card>
            </div>
        </FadeIn>
    );
};

const DateRangePicker = ({ dateRange, handleDateRange }) => (
    <div style={{
        display: "flex", gap: 8,
        background: "rgba(99,102,241,0.08)", border: "1px solid rgba(99,102,241,0.2)",
        borderRadius: 10, padding: "4px 6px",
    }}>
        {[
            { l: 'Today', d: 0 },
            { l: '7D', d: 7 },
            { l: '30D', d: 30 },
            { l: '90D', d: 90 }
        ].map(opt => (
            <button key={opt.l} 
                onClick={() => handleDateRange(opt.d, opt.l === '7D' ? 'Last 7 Days' : (opt.l === '30D' ? 'Last 30 Days' : (opt.l === '90D' ? 'Last 90 Days' : 'Today')))}
                style={{
                    background: dateRange.label.includes(opt.l) || (opt.l === 'Today' && dateRange.label === 'Today') ? "rgba(99,102,241,0.3)" : "transparent",
                    border: "none", borderRadius: 7, color: (dateRange.label.includes(opt.l) || (opt.l === 'Today' && dateRange.label === 'Today')) ? "#818cf8" : "#64748b",
                    padding: "5px 12px", fontSize: 11, fontWeight: 700, cursor: "pointer"
                }}
            >{opt.l}</button>
        ))}
    </div>
);

const OverviewTab = ({ visitorStats, forecasting, cartStats, healthStats, topVendors, pagePerformance, systemHealth, insights, currencyConfig, fetchHeatmap, heatmapData, setHeatmapData, dateRange, handleDateRange }) => {
    return (
        <FadeIn>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
                <div>
                    <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: "-0.03em", color: "#f1f5f9" }}>Operations Dashboard</h1>
                    <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 4 }}>
                        <span style={{ width: 6, height: 6, borderRadius: "50%", background: "#6ee7b7", animation: "pulse 2s infinite", display: "inline-block" }} />
                        <span style={{ fontSize: 12, color: "#64748b" }}>System Live · Real-time streaming · Updated just now</span>
                    </div>
                </div>
                <DateRangePicker dateRange={dateRange} handleDateRange={handleDateRange} />
            </div>
            <div className="responsive-grid grid-kpi" style={{ marginBottom: 24 }}>
                <KpiCard 
                    label="Total Revenue" 
                    value={fmtCurrency(forecasting.history?.reduce((acc, curr) => acc + curr.total, 0) || 0, currencyConfig.symbol)} 
                    delta={`${forecasting.growth_rate > 0 ? '+' : ''}${forecasting.growth_rate}%`} 
                    positive={forecasting.growth_rate > 0} 
                    sub="vs previous period" 
                    icon="💰"
                    sparkData={(forecasting.history || []).map(h => h.total)}
                />
                <KpiCard 
                    label="Total Visits" 
                    value={fmt(visitorStats.total_visits || 0)} 
                    delta={visitorStats.total_visits_delta} 
                    positive={!(visitorStats.total_visits_delta || "").includes("-")} 
                    sub="real-time traffic" 
                    icon="👥"
                    sparkData={visitorStats.visit_trend || []}
                />
                <KpiCard 
                    label="Avg Session" 
                    value={`${Math.round(visitorStats.avg_duration_sec || 0)}s`} 
                    delta={visitorStats.avg_duration_delta} 
                    positive={!(visitorStats.avg_duration_delta || "").includes("-")} 
                    sub="engagement depth" 
                    icon="⏱️"
                    sparkData={visitorStats.duration_trend || []}
                />
                <KpiCard 
                    label="Conversion Rate" 
                    value={`${((visitorStats.funnel_stats?.purchased || 0) / (visitorStats.total_visits || 1) * 100).toFixed(1)}%`} 
                    delta={null} 
                    positive={true} 
                    sub="cart to order" 
                    icon="🎯"
                    sparkData={cartStats.abandoned_trend || []}
                />
                <KpiCard 
                    label="Bounce Rate" 
                    value={`${Math.round(visitorStats.bounce_rate || 0)}%`} 
                    delta={visitorStats.bounce_rate_delta} 
                    positive={(visitorStats.bounce_rate_delta || "").includes("-")} 
                    sub="lower is better" 
                    icon="↩️"
                    sparkData={visitorStats.bounce_trend || []}
                />
            </div>

            <div className="responsive-grid grid-sidebar-right" style={{ marginBottom: 16 }}>
                <Card>
                    <SectionHeader title="Revenue Analytics & Forecast" sub="Historical sales with predictive modeling" />
                    <div style={{ height: 320 }}>
                        <ResponsiveContainer width="100%" height="100%">
                            <AreaChart data={[...(forecasting.history || []), ...(forecasting.forecast || [])]}>
                                <defs>
                                    <linearGradient id="colorRev" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%" stopColor="#6366f1" stopOpacity={0.3} />
                                        <stop offset="95%" stopColor="#6366f1" stopOpacity={0} />
                                    </linearGradient>
                                    <linearGradient id="colorFore" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="5%" stopColor="#8b5cf6" stopOpacity={0.1} />
                                        <stop offset="95%" stopColor="#8b5cf6" stopOpacity={0} />
                                    </linearGradient>
                                </defs>
                                <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" vertical={false} />
                                <XAxis dataKey="date" axisLine={false} tickLine={false} tick={{ fill: "#64748b", fontSize: 10 }} minTickGap={30} />
                                <YAxis axisLine={false} tickLine={false} tick={{ fill: "#64748b", fontSize: 10 }} tickFormatter={fmtCurrency} />
                                <Tooltip content={<CustomTooltip />} />
                                <Area type="monotone" dataKey="total" stroke="#6366f1" strokeWidth={3} fillOpacity={1} fill="url(#colorRev)" name="Actual Revenue" />
                                <Area type="monotone" dataKey="forecast" stroke="#8b5cf6" strokeWidth={2} strokeDasharray="5 5" fillOpacity={1} fill="url(#colorFore)" name="Forecasted" />
                            </AreaChart>
                        </ResponsiveContainer>
                    </div>
                </Card>

                <Card>
                    <SectionHeader title="Traffic Composition" sub="Session distribution by source" />
                    <ResponsiveContainer width="100%" height={220}>
                        <PieChart>
                            <Pie data={safeArr(visitorStats.countries).slice(0, 5)} innerRadius={60} outerRadius={80} paddingAngle={5} dataKey="count">
                                {safeArr(visitorStats.countries).map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'][index % 5]} />
                                ))}
                            </Pie>
                            <Tooltip content={<CustomTooltip />} />
                        </PieChart>
                    </ResponsiveContainer>
                    <div style={{ display: "flex", flexWrap: "wrap", gap: "6px 16px", marginTop: 10 }}>
                        {safeArr(visitorStats.countries).slice(0, 5).map((e, i) => (
                            <div key={e.name} style={{ display: "flex", alignItems: "center", gap: 6 }}>
                                <div style={{ width: 8, height: 8, borderRadius: "50%", background: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981'][i % 5] }} />
                                <span style={{ fontSize: 11, color: "#94a3b8" }}>{e.name}</span>
                                <span style={{ fontSize: 11, fontWeight: 700, color: "#f1f5f9" }}>{e.count}</span>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 16, marginBottom: 16 }}>
                <Card>
                    <SectionHeader title="Hourly Traffic" sub="Visits per hour today" />
                    <ResponsiveContainer width="100%" height={160}>
                        <BarChart data={visitorStats.hourly_traffic || []}>
                            <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" vertical={false} />
                            <XAxis dataKey="h" tick={{ fill: "#64748b", fontSize: 10 }} axisLine={false} tickLine={false} />
                            <YAxis tick={{ fill: "#64748b", fontSize: 10 }} axisLine={false} tickLine={false} tickFormatter={fmt} />
                            <Tooltip content={<CustomTooltip />} />
                            <Bar dataKey="v" fill="#6366f1" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </Card>

                <Card>
                    <SectionHeader title="Conversion Funnel" sub="User journey breakdown" />
                    <div style={{ display: "flex", flexDirection: "column", gap: 8, marginTop: 4 }}>
                        {[
                            { label: "Visits", value: visitorStats.funnel_stats?.visits || 0, pct: 100 },
                            { label: "Product View", value: visitorStats.funnel_stats?.product_views || 0, pct: visitorStats.funnel_stats?.visits > 0 ? Math.round(((visitorStats.funnel_stats?.product_views || 0) / visitorStats.funnel_stats.visits) * 100) : 0 },
                            { label: "Add to Cart", value: visitorStats.funnel_stats?.add_to_cart || 0, pct: visitorStats.funnel_stats?.visits > 0 ? Math.round(((visitorStats.funnel_stats?.add_to_cart || 0) / visitorStats.funnel_stats.visits) * 100) : 0 },
                            { label: "Checkout", value: visitorStats.funnel_stats?.checkout || 0, pct: visitorStats.funnel_stats?.visits > 0 ? Math.round(((visitorStats.funnel_stats?.checkout || 0) / visitorStats.funnel_stats.visits) * 100) : 0 },
                            { label: "Purchased", value: visitorStats.funnel_stats?.purchased || 0, pct: visitorStats.funnel_stats?.visits > 0 ? Math.round(((visitorStats.funnel_stats?.purchased || 0) / visitorStats.funnel_stats.visits) * 100) : 0 },
                        ].map((s, i) => (
                            <div key={i}>
                                <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 4 }}>
                                    <span style={{ fontSize: 11, color: "#94a3b8" }}>{s.label}</span>
                                    <span style={{ fontSize: 11, fontWeight: 700, color: "#f1f5f9", fontFamily: "'DM Mono',monospace" }}>{(s.value || 0).toLocaleString()} <span style={{ color: "#64748b" }}>({s.pct}%)</span></span>
                                </div>
                                <div style={{ height: 6, background: "rgba(99,102,241,0.1)", borderRadius: 4 }}>
                                    <div style={{ height: "100%", width: `${s.pct}%`, background: `linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%)`, borderRadius: 4, opacity: 1 - i * 0.12 }} />
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>

                <Card>
                    <SectionHeader title="Growth Velocity" sub="Month-over-month trajectory" />
                    <div style={{ textAlign: "center", padding: "12px 0 8px" }}>
                        <div style={{ fontSize: 48, fontWeight: 800, color: "#6ee7b7", letterSpacing: "-0.04em", lineHeight: 1 }}>{forecasting.growth_rate > 0 ? '+' : ''}{forecasting.growth_rate}%</div>
                        <div style={{ fontSize: 11, color: "#6ee7b7", fontWeight: 700, marginTop: 4, letterSpacing: "0.08em" }}>{forecasting.growth_rate >= 18 ? 'OUTPERFORMING TARGETS' : 'GROWTH MOMENTUM'}</div>
                        <div style={{ fontSize: 11, color: "#64748b", marginTop: 2 }}>Target: 18.0% growth</div>
                    </div>
                    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 8, marginTop: 8 }}>
                        {[
                            { l: "Abandoned", v: cartStats.abandoned_count, c: "#818cf8" },
                            { l: "Recovery Val", v: fmtCurrency(cartStats.total_value, currencyConfig.symbol), c: "#6ee7b7" },
                            { l: "Exits", v: Math.round(visitorStats.total_visits * (visitorStats.bounce_rate / 100) || 0), c: "#fbbf24" },
                            { l: "Health Score", v: healthStats.errors_24h > 10 ? "Fair" : "Good", c: "#f472b6" },
                        ].map(m => (
                            <div key={m.l} style={{ background: "rgba(99,102,241,0.07)", borderRadius: 10, padding: "10px 12px" }}>
                                <div style={{ fontSize: 10, color: "#64748b", marginBottom: 2 }}>{m.l}</div>
                                <div style={{ fontSize: 18, fontWeight: 800, color: m.c }}>{m.v}</div>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>

            <div className="responsive-grid grid-sidebar-left" style={{ marginBottom: 16 }}>
                <Card>
                    <SectionHeader title="Top Vendors" sub="By revenue this period" action="View All" />
                    <div className="table-wrapper">
                        <table style={{ width: "100%", borderCollapse: "collapse" }}>
                        <thead>
                            <tr>
                                {["Vendor", "Revenue", "Orders", "Rating", "MoM"].map(h => (
                                    <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 8, letterSpacing: "0.06em", textTransform: "uppercase", borderBottom: "1px solid rgba(99,102,241,0.1)" }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {(topVendors || []).length > 0 ? (topVendors || []).map((v, i) => (
                                <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(99,102,241,0.06)", cursor: "pointer" }}>
                                    <td style={{ padding: "10px 0" }}>
                                        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                                            <div style={{ width: 28, height: 28, borderRadius: 8, background: `linear-gradient(135deg, hsl(${i * 55 + 220},70%,55%), hsl(${i * 55 + 260},70%,45%))`, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 11, fontWeight: 800, color: "#fff" }}>{v.name[0]}</div>
                                            <span style={{ fontSize: 12, fontWeight: 600, color: "#e2e8f0" }}>{v.name}</span>
                                        </div>
                                    </td>
                                    <td style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9", fontFamily: "'DM Mono',monospace" }}>{fmtCurrency(v.revenue, currencyConfig.symbol)}</td>
                                    <td style={{ fontSize: 12, color: "#94a3b8" }}>{v.orders}</td>
                                    <td style={{ fontSize: 12, color: "#fbbf24", fontWeight: 700 }}>★ {v.rating}</td>
                                    <td><span style={{ fontSize: 11, fontWeight: 700, padding: "2px 7px", borderRadius: 5, background: (v.trend && v.trend.startsWith("+")) ? "rgba(110,231,183,0.12)" : "rgba(248,113,113,0.12)", color: (v.trend && v.trend.startsWith("+")) ? "#6ee7b7" : "#f87171" }}>{v.trend}</span></td>
                                </tr>
                            )) : (
                                <tr><td colSpan="5" style={{ textAlign: 'center', padding: '20px', color: '#64748b', fontSize: '11px' }}>No vendor data recorded this period</td></tr>
                            )}
                        </tbody>
                    </table></div>
                </Card>

                <Card>
                    <SectionHeader title="Page Performance & Behavior" sub="Interaction depth per page" />
                    <table style={{ width: "100%", borderCollapse: "collapse" }}>
                        <thead>
                            <tr>
                                {["Page Path", "Views", "Unique", "Avg Time", "Action"].map(h => (
                                    <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 8, letterSpacing: "0.06em", textTransform: "uppercase", borderBottom: "1px solid rgba(99,102,241,0.1)" }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {(pagePerformance || []).slice(0, 5).map((p, i) => (
                                <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(99,102,241,0.06)" }}>
                                    <td style={{ padding: "9px 0", fontSize: 11, color: "#818cf8", fontFamily: "'DM Mono',monospace" }}>{p.url.substring(0, 20)}</td>
                                    <td style={{ fontSize: 12, color: "#e2e8f0", fontWeight: 600 }}>{p.views}</td>
                                    <td style={{ fontSize: 12, color: "#94a3b8" }}>{p.unique_views}</td>
                                    <td style={{ fontSize: 11, color: "#e2e8f0", fontFamily: "'DM Mono',monospace" }}>{Math.round(p.avg_time)}s</td>
                                    <td><button onClick={() => fetchHeatmap(p.url)} style={{ background: "transparent", border: "1px solid #3182ce", borderRadius: 4, color: "#3182ce", fontSize: 10, padding: "2px 6px", cursor: "pointer" }}>Heatmap</button></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </Card>
            </div>

            <div className="responsive-grid grid-sidebar-left" style={{ marginBottom: 16 }}>
                <Card>
                    <SectionHeader title="System Integration Health" sub="Real-time API & service status" />
                    <div className="table-wrapper">
                    <table style={{ width: "100%", borderCollapse: "collapse" }}>
                        <thead>
                            <tr>
                                {["Service", "Source", "Uptime", "Latency", "Status"].map(h => (
                                    <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 8, letterSpacing: "0.06em", textTransform: "uppercase", borderBottom: "1px solid rgba(99,102,241,0.1)" }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {systemHealth.length > 0 ? systemHealth.map((s, i) => (
                                <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(99,102,241,0.06)" }}>
                                    <td style={{ padding: "9px 0", fontSize: 12, fontWeight: 600, color: "#e2e8f0" }}>{s.name}</td>
                                    <td style={{ fontSize: 11, color: "#64748b" }}>{s.source}</td>
                                    <td style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9", fontFamily: "'DM Mono',monospace" }}>{s.rate}</td>
                                    <td style={{ fontSize: 11, color: "#64748b", fontFamily: "'DM Mono',monospace" }}>{s.latency}ms</td>
                                    <td><StatusDot status={s.status} /></td>
                                </tr>
                            )) : (
                                <tr><td colSpan="5" style={{ textAlign: 'center', padding: '15px', color: '#64748b', fontSize: '11px' }}>Monitoring systems initializing...</td></tr>
                            )}
                        </tbody>
                    </table></div>
                </Card>

                <Card>
                    <SectionHeader title="Automated Insights" sub="AI-driven performance signals" />
                    <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                        {(insights || []).length > 0 ? (insights || []).map((a, i) => (
                            <div key={i} style={{ background: "rgba(99,102,241,0.05)", border: `1px solid ${a.level === 'critical' ? '#f87171' : (a.level === 'warning' ? '#fbbf24' : '#818cf8')}22`, borderLeft: `3px solid ${a.level === 'critical' ? '#f87171' : (a.level === 'warning' ? '#fbbf24' : '#818cf8')}`, borderRadius: 10, padding: "10px 14px", display: "flex", alignItems: "flex-start", gap: 10 }}>
                                <span style={{ fontSize: 16, flexShrink: 0, marginTop: 1 }}>{a.level === 'critical' ? '⚡' : (a.level === 'warning' ? '⚠️' : '📈')}</span>
                                <div style={{ display: "flex", flexDirection: "column" }}>
                                    <span style={{ fontSize: 13, fontWeight: 700, color: "#e2e8f0", marginBottom: 2 }}>{a.title}</span>
                                    <span style={{ fontSize: 12, color: "#94a3b8", lineHeight: 1.5 }}>{a.message}</span>
                                </div>
                            </div>
                        )) : (
                            <div style={{ textAlign: "center", padding: "40px 0" }}>
                                <div style={{ fontSize: "2rem", marginBottom: "10px" }}>🧘</div>
                                <p style={{ color: "#718096", fontSize: "0.9rem" }}>Scanning for anomalies...</p>
                            </div>
                        )}
                    </div>
                </Card>
            </div>
        </FadeIn>
    );
};

const MarketingTab = ({ data, currencyConfig, dateRange, handleDateRange }) => {
    const stats = data?.kpis || {};
    return (
        <FadeIn>
            <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
                <div>
                    <h1 style={{ fontSize: 24, fontWeight: 800, letterSpacing: "-0.03em", color: "#f1f5f9" }}>Marketing & Growth ROI</h1>
                    <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 4 }}>
                        <span style={{ width: 6, height: 6, borderRadius: "50%", background: "#8b5cf6", animation: "pulse 2s infinite", display: "inline-block" }} />
                        <span style={{ fontSize: 12, color: "#64748b" }}>Campaign attribution · Loyalty segments</span>
                    </div>
                </div>
                <DateRangePicker dateRange={dateRange} handleDateRange={handleDateRange} />
            </div>

            <div className="responsive-grid grid-kpi" style={{ marginBottom: 24 }}>
                <KpiCard label="Campaign Revenue" value={fmtCurrency(stats.campaign_revenue || 0, currencyConfig.symbol)} delta={stats.revenue_delta} positive={!(stats.revenue_delta || "").includes("-")} sub="attribution flow" icon="📣" />
                <KpiCard label="Email Open Rate" value={stats.email_open_rate ?? 'N/A'} delta={null} positive={null} sub="engagement depth" icon="📧" />
                <KpiCard label="Avg ROI" value={stats.avg_roi ?? 'N/A'} delta={null} positive={null} sub="marketing efficiency" icon="📈" />
                <KpiCard label="Active Coupons" value={stats.active_coupons || 0} delta={null} positive={true} sub="promo velocity" icon="🎫" />
                <KpiCard label="Customer LTV" value={fmtCurrency(stats.customer_ltv || 0, currencyConfig.symbol)} delta={stats.ltv_delta} positive={!(stats.ltv_delta || "").includes("-")} sub="lifetime value" icon="👤" />
            </div>

            <div className="responsive-grid grid-sidebar-right" style={{ marginBottom: 16 }}>
                <Card>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
                        <SectionHeader title="Campaign Performance" sub="Multi-channel reach & conversion" />
                        <button style={{ background: "#6366f1", color: "#fff", border: "none", borderRadius: 8, padding: "6px 14px", fontSize: 11, fontWeight: 700, cursor: "pointer" }}>+ New Campaign</button>
                    </div>
                    <div className="table-wrapper">
                        <table style={{ width: "100%", borderCollapse: "collapse" }}>
                            <thead>
                                <tr>
                                    {["Campaign Name", "Channel", "Sent", "Opens", "Clicks", "Revenue", "ROI", "Status"].map(h => (
                                        <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 10, textTransform: "uppercase" }}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {(data?.campaigns || []).map((c, i) => (
                                    <tr key={i} className="row-hover" style={{ borderBottom: "1px solid rgba(255,255,255,0.05)" }}>
                                        <td style={{ padding: "12px 0", fontSize: 13, fontWeight: 600, color: "#f1f5f9" }}>{c.name}</td>
                                        <td><span style={{ fontSize: 10, padding: "2px 7px", borderRadius: 5, background: c.channel === 'Email' ? "#0ea5e922" : (c.channel === 'Social' ? "#ec489922" : (c.channel === 'Paid' ? "#f59e0b22" : "#8b5cf622")), color: c.channel === 'Email' ? "#0ea5e9" : (c.channel === 'Social' ? "#ec4899" : (c.channel === 'Paid' ? "#f59e0b" : "#8b5cf6")) }}>{c.channel}</span></td>
                                        <td style={{ fontSize: 12, color: "#94a3b8" }}>{c.sent != null ? c.sent.toLocaleString() : '—'}</td>
                                        <td style={{ fontSize: 12, color: "#e2e8f0" }}>{c.opens != null ? c.opens.toLocaleString() : '—'}</td>
                                        <td style={{ fontSize: 12, color: "#e2e8f0" }}>{c.clicks != null ? c.clicks.toLocaleString() : '—'}</td>
                                        <td style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9" }}>{fmtCurrency(c.revenue, currencyConfig.symbol)}</td>
                                        <td style={{ fontSize: 12, fontWeight: 700, color: "#10b981" }}>{c.roi ?? '—'}</td>
                                        <td><span style={{ fontSize: 10, padding: "2px 6px", borderRadius: 4, background: c.status === 'Live' ? "#10b98122" : "#64748b22", color: c.status === 'Live' ? "#10b981" : "#64748b" }}>{c.status}</span></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
                <Card>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 16 }}>
                        <SectionHeader title="Coupon Tracker" sub="Discount impact logs" />
                        <button style={{ background: "transparent", border: "1px solid #6366f1", color: "#6366f1", borderRadius: 6, padding: "4px 10px", fontSize: 10, fontWeight: 700, cursor: "pointer" }}>+ Create Coupon</button>
                    </div>
                    <div className="table-wrapper">
                        <table style={{ width: "100%", borderCollapse: "collapse" }}>
                            <thead>
                                <tr style={{ borderBottom: "1px solid rgba(255,255,255,0.05)" }}>
                                    {["Code", "Discount", "Uses", "Revenue", "Expires"].map(h => (
                                        <th key={h} style={{ fontSize: 10, fontWeight: 700, color: "#475569", textAlign: "left", paddingBottom: 10, textTransform: "uppercase" }}>{h}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {(data?.coupons || []).map((c, i) => (
                                    <tr key={i} className="row-hover">
                                        <td style={{ padding: "10px 0", fontSize: 11, fontFamily: "'DM Mono', monospace", color: "#818cf8" }}>{c.code}</td>
                                        <td style={{ fontSize: 12, fontWeight: 800, color: "#10b981" }}>{c.discount}</td>
                                        <td style={{ fontSize: 12, color: "#e2e8f0" }}>{c.uses}</td>
                                        <td style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9" }}>{fmtCurrency(c.revenue, currencyConfig.symbol)}</td>
                                        <td style={{ fontSize: 10, color: "#64748b" }}>{c.expires}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </Card>
                <Card>
                    <SectionHeader title="Marketing Insights" sub="AI-powered growth recommendations" />
                    <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                        {[
                            { t: "Best Send Time", m: "Thursdays at 6:45 PM for highest CTR", i: "⏰", c: "#6366f1" },
                            { t: "Retargeting ROI", m: "Abandoned cart emails up 400%", i: "♻️", c: "#10b981" },
                            { t: "Budget Advice", m: "Shift 12% from Search to Social", i: "💡", c: "#f59e0b" },
                            { t: "Cohort Strategy", m: "Jan cohort shows peak LTV at M4", i: "📊", c: "#8b5cf6" }
                        ].map((s, i) => (
                            <div key={i} style={{ display: "flex", gap: 12, padding: "12px", background: "rgba(255,255,255,0.03)", borderRadius: 12, borderLeft: `3px solid ${s.c}` }}>
                                <span style={{ fontSize: 18 }}>{s.i}</span>
                                <div>
                                    <div style={{ fontSize: 12, fontWeight: 700, color: "#f1f5f9" }}>{s.t}</div>
                                    <div style={{ fontSize: 11, color: "#64748b", marginTop: 2 }}>{s.m}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>
            </div>
        </FadeIn>
    );
};

// --- Main Application ---

const App = () => {
    // ── STATE ────────────────────────────────────────────────────────────────
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState("overview");
    const [time, setTime] = useState(new Date());
    const [dateRange, setDateRange] = useState({
        start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end: new Date().toISOString().split('T')[0],
        label: "Last 30 Days"
    });

    const [visitorStats, setVisitorStats] = useState({ total_visits: 0, unique_visitors: 0, bounce_rate: 0, avg_duration_sec: 0, countries: [] });
    const [healthStats, setHealthStats] = useState({ errors_24h: 0, avg_latency_ms: 0, recent_errors: [], memory_usage: '0 MB' });
    const [cartStats, setCartStats] = useState({ abandoned_count: 0, total_value: 0, recent_abandonments: [] });
    const [forecasting, setForecasting] = useState({ history: [], forecast: [], growth_rate: 0 });
    const [insights, setInsights] = useState([]);
    const [liveVisitors, setLiveVisitors] = useState(0); 
    const [liveLocations, setLiveLocations] = useState([]);
    const [trafficSources, setTrafficSources] = useState([]);
    const [pagePerformance, setPagePerformance] = useState([]);
    const [behaviorFlow, setBehaviorFlow] = useState([]);
    const [heatmapData, setHeatmapData] = useState(null);
    const [topVendors, setTopVendors] = useState([]);
    const [systemHealth, setSystemHealth] = useState([]);
    const [currencyConfig, setCurrencyConfig] = useState({ symbol: '$', code: 'USD' });
    const [vendorData, setVendorData] = useState(null);
    const [financeData, setFinanceData] = useState(null);
    const [marketingData, setMarketingData] = useState(null);
// ── HELPER: safeFetch (Updated for session auth) ───────────────────────────
const safeFetch = async (endpoint, options = {}) => {
  try {
    // Analytics endpoints have been moved to /admin/analytics/ for session auth
    const url = endpoint.startsWith('http') 
      ? endpoint 
      : `${window.APP_BASE_URL}/admin/analytics/${endpoint}`;
      
    const response = await fetch(url, {
      ...options,
      credentials: 'include', // Crucial for session-based auth
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest', // Helps Laravel identify AJAX
        ...options.headers
      }
    });
    
    if (response.status === 401) {
      console.warn('Dashboard: Unauthorized access. Please ensure you are logged in as admin.');
      return null;
    }
    
    if (!response.ok) throw new Error(`Fetch failed: ${response.status}`);
    return await response.json();
  } catch (err) {
    console.error(`Dashboard Fetch Error [${endpoint}]:`, err);
    return null;
  }
};

    const fetchMetrics = async (endpoints) => {
        const query = `?start_date=${dateRange.start}&end_date=${dateRange.end}`;
        const promises = endpoints.map(e => {
            const endpointWithQuery = ['health-stats', 'live-locations', 'automated-insights', 'system-health', 'currency-config', 'top-vendors'].includes(e) 
                ? e 
                : `${e}${query}`;
            return safeFetch(endpointWithQuery);
        });

        const results = await Promise.all(promises);
        
        endpoints.forEach((e, i) => {
            const data = results[i];
            if (!data) return;

            switch(e) {
                case 'visitor-stats': setVisitorStats(data); break;
                case 'health-stats': setHealthStats({ 
                    errors_24h: data.errors_24h || 0, 
                    avg_latency_ms: data.avg_latency_ms || 0, 
                    recent_errors: safeArr(data.recent_errors),
                    memory_usage: data.memory_usage || '0 MB'
                }); break;
                case 'cart-stats': setCartStats(data); break;
                case 'forecasting': setForecasting(data); break;
                case 'automated-insights': setInsights(safeArr(data)); break;
                case 'live-locations': 
                    setLiveLocations(safeArr(data));
                    setLiveVisitors(safeArr(data).length);
                    break;
                case 'traffic-sources': setTrafficSources(safeArr(data)); break;
                case 'page-performance': setPagePerformance(safeArr(data)); break;
                case 'behavior-flow': setBehaviorFlow(safeArr(data)); break;
                case 'top-vendors': setTopVendors(safeArr(data)); break;
                case 'system-health': setSystemHealth(safeArr(data)); break;
                case 'currency-config': setCurrencyConfig(data); break;
                case 'vendor-analytics': setVendorData(data); break;
                case 'finance-analytics': setFinanceData(data); break;
                case 'marketing-analytics': setMarketingData(data); break;
            }
        });

        if (loading) setLoading(false);
    };

    // Initial and periodic fetch
    useEffect(() => {
        // High-frequency metrics (Real-time)
        const realtimeEndpoints = ['live-locations', 'health-stats', 'automated-insights', 'system-health'];
        // Low-frequency metrics (Historical/Summary)
        const historicalEndpoints = [
            'visitor-stats', 'cart-stats', 'forecasting', 'traffic-sources', 
            'page-performance', 'behavior-flow', 'top-vendors', 'currency-config',
            'vendor-analytics', 'finance-analytics', 'marketing-analytics'
        ];

        // Fetch everything initially
        fetchMetrics([...realtimeEndpoints, ...historicalEndpoints]);

        const rtInterval = setInterval(() => fetchMetrics(realtimeEndpoints), 15000); // 15s
        const histInterval = setInterval(() => fetchMetrics(historicalEndpoints), 120000); // 2m
        const clockInterval = setInterval(() => setTime(new Date()), 1000);

        return () => {
            clearInterval(rtInterval);
            clearInterval(histInterval);
            clearInterval(clockInterval);
        };
    }, [dateRange]);

    const fetchHeatmap = async (url) => {
        const query = `?url=${encodeURIComponent(url)}&start_date=${dateRange.start}&end_date=${dateRange.end}`;
        const data = await safeFetch(`interaction-heatmap${query}`);
        if (data) setHeatmapData(data);
    };

    const handleDateRange = (days, label) => {
        setDateRange({
            start: new Date(Date.now() - days * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            end: new Date().toISOString().split('T')[0],
            label
        });
        setLoading(true);
    };

    if (loading) return (
        <div style={{ height: '90vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#fcfcfd' }}>
            <div style={{ textAlign: 'center' }}>
                <div style={{ 
                    width: '50px', 
                    height: '50px', 
                    border: '4px solid #f1f3f5', 
                    borderTopColor: '#3182ce', 
                    borderRadius: '50%', 
                    animation: 'spin 1s linear infinite',
                    margin: '0 auto' 
                }} />
                <p style={{ marginTop: '20px', color: '#718096', fontWeight: '600', fontSize: '0.9rem' }}>LOADING ANALYTICS ENGINE...</p>
                <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
            </div>
        </div>
    );

    return (
        <div id="marketops-dashboard-wrapper" style={{
            background: "#f8fafc",
            minHeight: "100vh",
            fontFamily: "'DM Sans', sans-serif",
            color: "#1e293b",
            position: "relative",
            zIndex: 1
        }}>
            <style dangerouslySetInnerHTML={{__html: `
                .responsive-grid { display: grid; gap: 16px; }
                .responsive-grid > * { min-width: 0; }
                .grid-kpi { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
                .grid-2 { grid-template-columns: 1fr 1fr; }
                .grid-sidebar-right { grid-template-columns: 2.2fr 1fr; }
                .grid-sidebar-left { grid-template-columns: 1.1fr 0.9fr; }
                .table-wrapper { overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch; }
                
                @media (max-width: 1400px) {
                    .grid-sidebar-right, .grid-sidebar-left { grid-template-columns: 1fr; }
                    .grid-2 { grid-template-columns: 1fr; }
                }
                
                @media (max-width: 900px) {
                    .top-nav { padding: 10px 16px !important; flex-wrap: wrap; height: auto !important; gap: 10px; }
                    .main-content { padding: 16px !important; }
                }

                @media (max-width: 768px) {
                    .grid-kpi { grid-template-columns: repeat(2, 1fr); }
                }

                @media (max-width: 480px) {
                    .grid-kpi { grid-template-columns: 1fr; }
                }
            `}} />
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');
                #marketops-dashboard-wrapper { box-sizing: border-box; }
                #marketops-dashboard-wrapper * { box-sizing: border-box; }
                @keyframes pulse { 0%,100% { opacity:1 } 50% { opacity:.4 } }
                @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
                @keyframes slide-in { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
                .mo-tab-btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); border: none !important; outline: none !important; }
                .mo-tab-btn:hover { background: rgba(99,102,241,0.08) !important; }
                .row-hover:hover { background: rgba(99,102,241,0.06) !important; transition: background 0.2s; }
            `}</style>

            {/* ── TOP NAV ── */}
            <div className="top-nav" style={{
                position: "sticky", top: 0, zIndex: 100,
                background: "rgba(255,255,255,0.95)", backdropFilter: "blur(12px)",
                borderBottom: "1px solid #1e293b",
                padding: "0 28px", display: "flex", alignItems: "center",
                justifyContent: "space-between", height: 58,
                boxShadow: "0 1px 2px 0 rgba(0, 0, 0, 0.05)"
            }}>
                <div style={{ display: "flex", alignItems: "center", gap: 28 }}>
                    <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                        <div style={{
                            width: 32, height: 32, background: "linear-gradient(135deg,#6366f1,#8b5cf6)",
                            borderRadius: 9, display: "flex", alignItems: "center", justifyContent: "center",
                            fontSize: 16
                        }}>⚡</div>
                        <span style={{ fontWeight: 800, fontSize: 15, letterSpacing: "-0.02em", color: "#0f172a" }}>MarketOps</span>
                    </div>

                    <div style={{ display: "flex", gap: 6, background: "#f1f5f9", padding: "4px", borderRadius: 12, border: "1px solid #e2e8f0" }}>
                        {["overview", "vendors", "finance", "marketing"].map(tab => {
                            const isActive = activeTab === tab;
                            return (
                                <button key={tab} className="mo-tab-btn"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        console.log(`DEBUG: Tab Clicked -> ${tab}`);
                                        setActiveTab(tab);
                                    }}
                                    style={{
                                        background: isActive ? "#6366f1" : "transparent",
                                        borderRadius: 9, 
                                        color: isActive ? "#fff" : "#64748b",
                                        padding: "7px 18px", 
                                        fontSize: 13, 
                                        fontWeight: 700,
                                        cursor: "pointer", 
                                        textTransform: "capitalize", 
                                        boxShadow: isActive ? "0 4px 12px rgba(99,102,241,0.25)" : "none",
                                        pointerEvents: "auto",
                                        zIndex: 10
                                    }}>
                                    {tab}
                                </button>
                            );
                        })}
                    </div>
                </div>

                <div style={{ display: "flex", alignItems: "center", gap: 16 }}>
                    <div style={{
                        display: "flex", alignItems: "center", gap: 7,
                        background: "rgba(110,231,183,0.08)", border: "1px solid rgba(110,231,183,0.2)",
                        borderRadius: 8, padding: "5px 12px",
                    }}>
                        <span style={{
                            width: 7, height: 7, borderRadius: "50%", background: "#6ee7b7",
                            boxShadow: "0 0 8px #6ee7b7", animation: "pulse 1.5s infinite"
                        }} />
                        <span style={{ fontSize: 12, fontWeight: 700, color: "#6ee7b7" }}>{liveVisitors}</span>
                        <span style={{ fontSize: 11, color: "#64748b" }}>live now</span>
                    </div>

                    <div style={{ fontSize: 11, color: "#475569", fontFamily: "'DM Mono', monospace" }}>
                        {(time || new Date()).toLocaleTimeString()}
                    </div>

                    <button 
                        onClick={() => window.open(API('/api/v2/analytics/generate-report'))}
                        style={{
                            background: "linear-gradient(135deg,#6366f1,#8b5cf6)",
                            border: "none", borderRadius: 9, color: "#fff",
                            padding: "7px 16px", fontSize: 12, fontWeight: 700,
                            cursor: "pointer", letterSpacing: "0.02em"
                        }}
                    >
                        ⬇ Export Report
                    </button>
                </div>
            </div>

            {/* ── MAIN CONTENT ── */}
            <div className="main-content" style={{ padding: "24px 28px", maxWidth: 1400, margin: "0 auto" }}>
                {/* ── CONDITIONAL RENDER BY TAB ── */}
                <div style={{ position: "relative", minHeight: "600px" }}>
                    {activeTab === 'overview' && (
                        <OverviewTab 
                            visitorStats={visitorStats} 
                            forecasting={forecasting} 
                            cartStats={cartStats} 
                            healthStats={healthStats} 
                            topVendors={topVendors} 
                            pagePerformance={pagePerformance} 
                            systemHealth={systemHealth} 
                            insights={insights} 
                            currencyConfig={currencyConfig} 
                            fetchHeatmap={fetchHeatmap} 
                            heatmapData={heatmapData} 
                            setHeatmapData={setHeatmapData} 
                            dateRange={dateRange}
                            handleDateRange={handleDateRange}
                        />
                    )}

                    {activeTab === 'vendors' && <VendorsTab data={vendorData} currencyConfig={currencyConfig} dateRange={dateRange} handleDateRange={handleDateRange} />}
                    {activeTab === 'finance' && <FinanceTab data={financeData} currencyConfig={currencyConfig} dateRange={dateRange} handleDateRange={handleDateRange} />}
                    {activeTab === 'marketing' && <MarketingTab data={marketingData} currencyConfig={currencyConfig} dateRange={dateRange} handleDateRange={handleDateRange} />}
                </div>
            </div>
            {heatmapData && <HeatmapOverlay data={heatmapData} onClose={() => setHeatmapData(null)} />}
        </div>
    );
};

export default App;
