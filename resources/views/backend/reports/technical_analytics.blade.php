<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Technical Analytics Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #3498db; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #2c3e50; margin: 0; }
        .subtitle { font-size: 14px; color: #7f8c8d; margin-top: 5px; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: bold; color: #3498db; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; }
        .grid { display: block; width: 100%; }
        .card { width: 30%; display: inline-block; background: #f9f9f9; padding: 15px; border-radius: 8px; margin-right: 2%; vertical-align: top; }
        .card:last-child { margin-right: 0; }
        .metric-label { font-size: 11px; color: #95a5a6; text-transform: uppercase; }
        .metric-value { font-size: 20px; font-weight: bold; color: #2c3e50; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; font-size: 12px; color: #7f8c8d; border-bottom: 1px solid #eee; padding: 8px 0; }
        td { font-size: 13px; padding: 8px 0; border-bottom: 1px solid #f9f9f9; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #bdc3c7; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">System Performance & Commerce Report</h1>
        <div class="subtitle">Generated on {{ $report_date }} | Period: {{ $period }}</div>
    </div>

    <div class="section">
        <h2 class="section-title">Commerce Overview</h2>
        <div class="grid">
            <div class="card">
                <div class="metric-label">Total Revenue</div>
                <div class="metric-value">${{ number_format($commerce['total_revenue'], 2) }}</div>
            </div>
            <div class="card">
                <div class="metric-label">Total Orders</div>
                <div class="metric-value">{{ $commerce['order_count'] }}</div>
            </div>
            <div class="card">
                <div class="metric-label">Avg Order Value</div>
                <div class="metric-value">${{ number_format($commerce['avg_order_value'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Technical Health</h2>
        <div class="grid">
            <div class="card">
                <div class="metric-label">Total Visits</div>
                <div class="metric-value">{{ number_format($technical['total_visits']) }}</div>
            </div>
            <div class="card">
                <div class="metric-label">System Errors</div>
                <div class="metric-value" style="color: {{ $technical['error_count'] > 0 ? '#e74c3c' : '#27ae60' }}">{{ $technical['error_count'] }}</div>
            </div>
            <div class="card">
                <div class="metric-label">Avg Latency</div>
                <div class="metric-value">{{ round($technical['avg_latency'], 2) }}ms</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Traffic Ingress Highlights</h2>
        <table>
            <thead>
                <tr>
                    <th>Page URL</th>
                    <th>Visits</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_pages as $page)
                <tr>
                    <td>{{ $page->url }}</td>
                    <td style="font-weight: bold;">{{ number_format($page->count) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Technical Audit - Mayush Platform Intelligence Engine
    </div>
</body>
</html>
