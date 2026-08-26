@extends('layouts.app')

@section('content')

<style>
:root {
    --primary: #2563eb;
    --primary-hover: #1d4ed8;
    --primary-light: #eff6ff;
    --primary-glow: rgba(37, 99, 235, 0.18);
    --navy-dark: #0f172a;
    --navy-card: #1e293b;
    --surface: #ffffff;
    --surface-alt: #f8fafc;
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --text-subtle: #94a3b8;
    --success: #10b981;
    --success-light: #ecfdf5;
    --warning: #f59e0b;
    --warning-light: #fffbeb;
    --danger: #ef4444;
    --danger-light: #fef2f2;
    --info: #06b6d4;
    --info-light: #ecfeff;
    --sidebar-w: 260px;
    --header-h: 70px;
    --radius-lg: 20px;
    --radius-md: 14px;
    --radius-sm: 10px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
    --shadow-md: 0 4px 20px -2px rgba(15,23,42,0.06), 0 2px 6px -1px rgba(15,23,42,0.04);
    --shadow-lg: 0 12px 36px -4px rgba(15,23,42,0.09), 0 4px 12px -2px rgba(15,23,42,0.05);
    --shadow-glow: 0 12px 32px rgba(37,99,235,0.22);
}

* { box-sizing: border-box; }
html, body {
    margin: 0;
    padding: 0;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    background: #f1f5f9;
    color: var(--text-main);
    -webkit-font-smoothing: antialiased;
}

/* Layout container */
.app-container {
    display: flex;
    min-height: 100vh;
    background: 
        radial-gradient(at 100% 0%, rgba(37, 99, 235, 0.04) 0px, transparent 50%),
        radial-gradient(at 0% 100%, rgba(6, 182, 212, 0.04) 0px, transparent 50%),
        #f8fafc;
}

/* ================= SIDEBAR ================= */
.app-sidebar {
    width: var(--sidebar-w);
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    background: #ffffff;
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    z-index: 50;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-brand {
    height: var(--header-h);
    padding: 0 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid var(--border-light);
}

.brand-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.25);
    color: #ffffff;
    font-weight: 900;
    font-size: 13px;
    overflow: hidden;
}

.brand-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.brand-text h2 {
    font-size: 13px;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0;
    color: var(--navy-dark);
}

.brand-text span {
    font-size: 9px;
    font-weight: 700;
    color: var(--primary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    display: block;
    margin-top: 2px;
}

.sidebar-nav {
    flex: 1;
    padding: 20px 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-category {
    font-size: 10px;
    font-weight: 800;
    color: var(--text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 12px 12px 6px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.nav-link svg {
    width: 18px;
    height: 18px;
    color: var(--text-subtle);
    transition: color 0.2s ease;
}

.nav-link:hover {
    background: var(--primary-light);
    color: var(--primary);
    transform: translateX(3px);
}

.nav-link:hover svg {
    color: var(--primary);
}

.nav-link.active {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
}

.nav-link.active svg {
    color: #ffffff;
}

.sidebar-user {
    padding: 16px 20px;
    border-top: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fcfdfe;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
}

.user-details strong {
    font-size: 12px;
    font-weight: 700;
    color: var(--navy-dark);
    display: block;
}

.user-details span {
    font-size: 10px;
    color: var(--text-muted);
    display: block;
}

/* ================= MAIN CONTENT ================= */
.app-main {
    margin-left: var(--sidebar-w);
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Topbar */
.app-header {
    height: var(--header-h);
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    z-index: 40;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.mobile-toggle {
    display: none;
    background: transparent;
    border: none;
    color: var(--navy-dark);
    cursor: pointer;
    padding: 6px;
}

.page-title h1 {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin: 0;
    color: var(--navy-dark);
}

.page-title p {
    font-size: 11px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.live-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 14px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--success);
    position: relative;
}

.pulse-dot::after {
    content: '';
    position: absolute;
    top: -3px;
    left: -3px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.4);
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.6); opacity: 0; }
}

.clock-text {
    font-size: 12px;
    font-weight: 800;
    color: var(--navy-dark);
    font-variant-numeric: tabular-nums;
}

.date-text {
    font-size: 11px;
    color: var(--text-muted);
    border-left: 1px solid var(--border);
    padding-left: 10px;
}

/* Content Area */
.app-content {
    padding: 28px 32px 48px;
    flex: 1;
    max-width: 1600px;
    width: 100%;
    margin: 0 auto;
}

/* HERO SECTION */
.hero-banner {
    position: relative;
    border-radius: var(--radius-lg);
    background: linear-gradient(125deg, #0f172a 0%, #1e293b 50%, #1e3a8a 100%);
    padding: 32px 36px;
    color: #ffffff;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.3);
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 32px;
    align-items: center;
}

.hero-banner::before {
    content: '';
    position: absolute;
    top: -120px;
    right: -80px;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(37, 99, 235, 0) 70%);
    pointer-events: none;
}

.hero-banner::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: 20%;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, rgba(6, 182, 212, 0) 70%);
    pointer-events: none;
}

.hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.hero-title {
    font-size: 26px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.03em;
    margin: 0 0 10px;
}

.hero-desc {
    font-size: 13px;
    line-height: 1.6;
    color: #cbd5e1;
    max-width: 620px;
    margin: 0 0 20px;
}

.hero-pills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.hero-pill-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 12px;
    font-weight: 600;
}

.hero-clock-box {
    position: relative;
    background: rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: var(--radius-md);
    padding: 24px 28px;
    text-align: center;
    min-width: 240px;
}

.hero-clock-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #93c5fd;
}

.hero-clock-time {
    font-size: 36px;
    font-weight: 900;
    letter-spacing: -0.04em;
    margin: 6px 0;
    font-variant-numeric: tabular-nums;
    background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-clock-date {
    font-size: 11px;
    color: #cbd5e1;
    font-weight: 500;
}

/* STATS GRID */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: #cbd5e1;
}

.stat-info span {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    display: block;
}

.stat-info h3 {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin: 6px 0 2px;
    color: var(--navy-dark);
}

.stat-info small {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-subtle);
}

.stat-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icon-blue { background: #eff6ff; color: #2563eb; }
.icon-orange { background: #fff7ed; color: #ea580c; }
.icon-green { background: #ecfdf5; color: #059669; }
.icon-cyan { background: #ecfeff; color: #0891b2; }

/* ACTION BAR BANNER */
.action-banner {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    box-shadow: var(--shadow-sm);
}

.action-banner-text h3 {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    color: var(--navy-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-banner-text p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 4px 0 0;
}

.action-buttons-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* BUTTON SYSTEM */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    transform: translateY(-1px);
    color: #ffffff;
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
    color: #ffffff;
}

.btn-secondary {
    background: #ffffff;
    border-color: var(--border);
    color: var(--navy-dark);
}

.btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: var(--primary);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 11px;
    border-radius: 8px;
}

.btn-danger-soft {
    background: var(--danger-light);
    color: var(--danger);
}
.btn-danger-soft:hover {
    background: #fee2e2;
}

.btn-primary-soft {
    background: var(--primary-light);
    color: var(--primary);
}
.btn-primary-soft:hover {
    background: #dbeafe;
}

/* SECTION GRID (TWO COLUMNS) */
.section-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 24px;
    margin-bottom: 24px;
}

/* CARD COMPONENT */
.app-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-title-group h3 {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    color: var(--navy-dark);
}

.card-title-group p {
    font-size: 11px;
    color: var(--text-muted);
    margin: 3px 0 0;
}

.card-body {
    padding: 24px;
    flex: 1;
}

/* FORM ELEMENTS */
.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: var(--navy-dark);
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    height: 42px;
    padding: 0 14px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: #ffffff;
    font-size: 13px;
    color: var(--navy-dark);
    transition: all 0.2s ease;
    outline: none;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

textarea.form-control {
    height: auto;
    min-height: 90px;
    padding: 10px 14px;
    resize: vertical;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

/* EMPLOYEE LIST & SEARCH */
.search-filter-box {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}

.search-input-wrap {
    position: relative;
    flex: 1;
}

.search-input-wrap svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: var(--text-subtle);
}

.search-input-wrap input {
    padding-left: 38px;
}

.employee-table-wrap {
    max-height: 380px;
    overflow-y: auto;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-light);
}

.employee-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-light);
    transition: background 0.15s ease;
}

.employee-row:last-child {
    border-bottom: none;
}

.employee-row:hover {
    background: #f8fafc;
}

.emp-main {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.emp-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    color: #ffffff;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.emp-info h4 {
    font-size: 13px;
    font-weight: 700;
    margin: 0;
    color: var(--navy-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.emp-info p {
    font-size: 11px;
    color: var(--text-muted);
    margin: 2px 0 0;
    display: flex;
    align-items: center;
    gap: 4px;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
}

.badge-success { background: var(--success-light); color: var(--success); }
.badge-warning { background: var(--warning-light); color: var(--warning); }
.badge-danger { background: var(--danger-light); color: var(--danger); }
.badge-neutral { background: #f1f5f9; color: var(--text-muted); }

/* TAB SYSTEM FOR SETTINGS */
.tab-nav {
    display: flex;
    gap: 6px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
    padding-bottom: 8px;
}

.tab-btn {
    padding: 8px 16px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tab-btn:hover {
    color: var(--primary);
    background: var(--primary-light);
}

.tab-btn.active {
    color: var(--primary);
    background: var(--primary-light);
}

.tab-pane {
    display: none;
}
.tab-pane.active {
    display: block;
}

/* WEBHOOK CHEATSHEET CARD */
.webhook-box {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border-radius: var(--radius-md);
    padding: 20px 24px;
    color: #ffffff;
    margin-bottom: 24px;
}

.webhook-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.webhook-header h4 {
    font-size: 14px;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.code-pill {
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    padding: 10px 14px;
    font-family: monospace;
    font-size: 12px;
    color: #38bdf8;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.command-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.command-item {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 10px 12px;
}

.command-item code {
    color: #4ade80;
    font-weight: 700;
    font-size: 12px;
}

.command-item p {
    font-size: 10px;
    color: #94a3b8;
    margin: 4px 0 0;
}

/* DROPZONE */
.file-dropzone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-sm);
    padding: 24px;
    text-align: center;
    background: var(--surface-alt);
    cursor: pointer;
    transition: all 0.2s ease;
}

.file-dropzone:hover {
    border-color: var(--primary);
    background: var(--primary-light);
}

.file-dropzone svg {
    width: 32px;
    height: 32px;
    color: var(--text-subtle);
    margin-bottom: 8px;
}

/* FOOTER */
.app-footer {
    padding: 20px 32px;
    background: #ffffff;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-muted);
}

/* RESPONSIVE */
@media (max-width: 1180px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .section-grid { grid-template-columns: 1fr; }
    .command-grid { grid-template-columns: 1fr; }
}

@media (max-width: 820px) {
    .app-sidebar {
        transform: translateX(-100%);
    }
    .app-sidebar.open {
        transform: translateX(0);
    }
    .app-main {
        margin-left: 0;
    }
    .mobile-toggle {
        display: block;
    }
    .hero-banner {
        grid-template-columns: 1fr;
        padding: 24px;
    }
    .action-banner {
        flex-direction: column;
        align-items: flex-start;
    }
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .app-header {
        padding: 0 16px;
    }
    .app-content {
        padding: 16px;
    }
    .date-text {
        display: none;
    }
}
</style>

<div class="app-container">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="app-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf1IUEkGN16g9RhT8qFdfmIZ4dhUxQJBcvHlhlK8KrG2KrCmp0-o5Wx12J&s=10" alt="BPS Logo">
            </div>
            <div class="brand-text">
                <h2>BPS KARANGANYAR</h2>
                <span>Pengingat Absensi</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-category">Navigasi Utama</span>
            <a href="#dashboard" class="nav-link active">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="#broadcast" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Kirim Broadcast
            </a>
            <a href="#karyawan" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Data Pegawai
            </a>
            <a href="#pengaturan" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Jadwal & Pesan
            </a>

            <span class="nav-category">Jadwal & Kalender</span>
            <a href="#kalender" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Kalender Libur
            </a>

            <span class="nav-category">Integrasi & Export</span>
            <a href="#webhook" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Bot WhatsApp
            </a>
            <a href="{{ url('/admin/employees/export') }}" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Excel (.csv)
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-profile">
                <div class="user-avatar">A</div>
                <div class="user-details">
                    <strong>Admin BPS</strong>
                    <span>Super Administrator</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger-soft" title="Keluar dari sistem">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ================= MAIN ================= --}}
    <div class="app-main">

        {{-- TOPBAR --}}
        <header class="app-header">
            <div class="header-left">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="page-title">
                    <h1>Dashboard Pengingat Absensi</h1>
                    <p>{{ $organization }} • Pusat Kontrol Notifikasi WhatsApp</p>
                </div>
            </div>

            <div class="header-right">
                <div class="live-badge">
                    <span class="pulse-dot"></span>
                    <span class="clock-text" id="topClock">00:00:00</span>
                    <span class="date-text" id="topDate">...</span>
                </div>

                <a href="#broadcast" class="btn btn-primary btn-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Kirim Pesan
                </a>
            </div>
        </header>

        {{-- CONTENT --}}
        <main class="app-content" id="dashboard">

            {{-- HERO BANNER --}}
            <div class="hero-banner">
                <div>
                    <div class="hero-tag">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #4ade80;"></span>
                        SISTEM MONITORING KEHADIRAN AKTIF
                    </div>
                    <h2 class="hero-title">Otomatisasi Pengingat Absen Pegawai</h2>
                    <p class="hero-desc">
                        Kelola jadwal kehadiran pegawai, kirimkan notifikasi WA tepat waktu dengan pantun motivasi, dan kendalikan pengiriman broadcast langsung via WhatsApp Admin.
                    </p>
                    <div class="hero-pills">
                        <div class="hero-pill-item">
                            @if(($agentStatus ?? 'offline') === 'online')
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                Agent Online (WA Desktop {{ ($whatsappReady ?? false) ? 'Ready' : 'Standby' }})
                            @else
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                Agent Offline
                            @endif
                        </div>
                        <div class="hero-pill-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            {{ count($employees) }} Pegawai Terdaftar
                        </div>
                        <div class="hero-pill-item">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Driver: <strong>{{ strtoupper($waDriver ?? 'DESKTOP') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="hero-clock-box">
                    <div class="hero-clock-label">WAKTU INDONESIA BARAT (WIB)</div>
                    <div class="hero-clock-time" id="heroClock">00:00:00</div>
                    <div class="hero-clock-date" id="heroDate">Memuat tanggal...</div>
                </div>
            </div>

            {{-- STATS GRID --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <span>Jadwal Absen Masuk</span>
                        <h3>{{ $checkIn }} <small style="font-size:13px; font-weight:600; color:var(--text-muted)">WIB</small></h3>
                        <small>Reminder: {{ $preReminderMinutes }} mnt sebelumnya</small>
                    </div>
                    <div class="stat-icon-wrap icon-blue">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span>Jadwal Pulang {{ now()->isFriday() ? '(Hari Ini: Jumat)' : '(Senin - Kamis)' }}</span>
                        <h3>{{ $todayCheckOut ?? $checkOut }} <small style="font-size:13px; font-weight:600; color:var(--text-muted)">WIB</small></h3>
                        <small>Sen-Kam: {{ $checkOut }} | Jum: {{ $checkOutFriday ?? '16:30' }} WIB</small>
                    </div>
                    <div class="stat-icon-wrap icon-orange">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span>Antrean Outbox (WA Desktop)</span>
                        <h3>{{ $outboxStats['pending'] ?? 0 }} <small style="font-size:13px; font-weight:600; color:var(--text-muted)">Pending</small></h3>
                        <small>Diproses: {{ $outboxStats['processing'] ?? 0 }} | Retry: {{ $outboxStats['retry'] ?? 0 }}</small>
                    </div>
                    <div class="stat-icon-wrap icon-cyan">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <span>Terkirim Hari Ini</span>
                        <h3>{{ ($outboxStats['sent_today'] ?? 0) + ($totalSentToday ?? 0) }} <small style="font-size:13px; font-weight:600; color:var(--text-muted)">Pesan</small></h3>
                        <small>Gagal: <strong style="color:var(--danger)">{{ $outboxStats['failed'] ?? 0 }}</strong> | Agent: <strong style="color:{{ ($agentStatus ?? '') === 'online' ? 'var(--success)' : 'var(--danger)' }}">{{ strtoupper($agentStatus ?? 'OFFLINE') }}</strong></small>
                    </div>
                    <div class="stat-icon-wrap icon-green">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            {{-- HOLIDAY & QUEUE CONTROL BAR --}}
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: center; background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 16px 20px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    @if($isTodayHoliday ?? false)
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: #fef3c7; color: #b45309; font-size: 12px; font-weight: 700;">
                            🏖️ {{ $todayHolidayName ?? 'Hari Libur' }} (Pengingat Otomatis Libur)
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 700;">
                            💼 Hari Kerja Aktif (Pengingat Otomatis Berjalan)
                        </span>
                    @endif

                    <form method="POST" action="{{ route('admin.holidays.sync') }}" style="display: inline; margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" title="Sinkronkan kalender tanggal merah">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Sync Tanggal Merah
                        </button>
                    </form>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @if(($outboxStats['failed'] ?? 0) > 0)
                        <form method="POST" action="{{ route('admin.outbox.retry-failed') }}" style="margin: 0;" onsubmit="return confirm('Kirim ulang semua pesan yang gagal?')">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Retry Semua Gagal ({{ $outboxStats['failed'] }})
                            </button>
                        </form>
                    @endif

                    @if(($outboxStats['pending'] ?? 0) > 0)
                        <form method="POST" action="{{ route('admin.outbox.cancel-pending') }}" style="margin: 0;" onsubmit="return confirm('Batalkan semua antrean yang belum terkirim?')">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Batalkan Pending ({{ $outboxStats['pending'] }})
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ACTION BANNER (MANUAL BROADCAST) --}}
            <div class="action-banner" id="broadcast">
                <div class="action-banner-text">
                    <h3>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Pusat Pengiriman Pesan Instan
                    </h3>
                    <p>Trigger pengiriman notifikasi absensi langsung ke seluruh pegawai aktif sekarang.</p>
                </div>
                <div class="action-buttons-group">
                    <form method="POST" action="{{ route('admin.send-pre-checkin') }}" onsubmit="return confirmBroadcast(event, 'Pengingat Absen Masuk')">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            Kirim Pengingat Masuk
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.send-pre-checkout') }}" onsubmit="return confirmBroadcast(event, 'Pengingat Absen Pulang')">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Kirim Pengingat Pulang
                        </button>
                    </form>

                    <button type="button" onclick="openQuickBroadcastModal()" class="btn btn-secondary">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Kirim Broadcast Cepat
                    </button>
                </div>
            </div>

            {{-- LIVE OUTBOX MONITORING TABLE --}}
            <div style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 20px 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: var(--navy-dark); display: flex; align-items: center; gap: 8px;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Antrean & Log Outbox WhatsApp Desktop
                        </h3>
                        <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0 0;">Menampilkan riwayat dan status proses pengiriman pesan terbaru oleh Agent.</p>
                    </div>

                    <div style="display: flex; gap: 6px;">
                        <button type="button" onclick="location.reload()" class="btn btn-sm btn-secondary" title="Refresh data">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh
                        </button>
                    </div>
                </div>

                @if(isset($outboxMessages) && $outboxMessages->isNotEmpty())
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                                    <th style="padding: 10px 12px;">ID</th>
                                    <th style="padding: 10px 12px;">Penerima</th>
                                    <th style="padding: 10px 12px;">Nomor WA</th>
                                    <th style="padding: 10px 12px;">Tipe</th>
                                    <th style="padding: 10px 12px;">Status</th>
                                    <th style="padding: 10px 12px;">Waktu Terkirim</th>
                                    <th style="padding: 10px 12px; text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outboxMessages as $msg)
                                    <tr style="border-bottom: 1px solid var(--border-light);">
                                        <td style="padding: 10px 12px; font-weight: 700; color: var(--text-muted);">#{{ $msg->id }}</td>
                                        <td style="padding: 10px 12px; font-weight: 700; color: var(--navy-dark);">
                                            {{ $msg->employee->name ?? 'Pegawai' }}
                                        </td>
                                        <td style="padding: 10px 12px; font-family: monospace; color: var(--text-muted);">
                                            {{ $msg->phone_number }}
                                        </td>
                                        <td style="padding: 10px 12px;">
                                            <span style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 6px; background: #f1f5f9; color: var(--text-muted);">
                                                {{ $msg->type }}
                                            </span>
                                        </td>
                                        <td style="padding: 10px 12px;">
                                            @if($msg->status === 'sent')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; background: #ecfdf5; color: #059669; font-size: 11px; font-weight: 700;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                                    Terkirim
                                                </span>
                                            @elseif($msg->status === 'processing')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; background: #eff6ff; color: #2563eb; font-size: 11px; font-weight: 700;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6;"></span>
                                                    Diproses Agent
                                                </span>
                                            @elseif($msg->status === 'pending')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 700;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                                                    Pending
                                                </span>
                                            @elseif($msg->status === 'failed')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; background: #fef2f2; color: #dc2626; font-size: 11px; font-weight: 700;" title="{{ $msg->last_error }}">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                                    Gagal
                                                </span>
                                            @elseif($msg->status === 'cancelled')
                                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; background: #f1f5f9; color: var(--text-muted); font-size: 11px; font-weight: 700;">
                                                    Dibatalkan
                                                </span>
                                            @else
                                                <span style="padding: 3px 8px; border-radius: 999px; background: #f1f5f9; color: var(--text-muted); font-size: 11px; font-weight: 700;">
                                                    {{ $msg->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 10px 12px; color: var(--text-muted); font-size: 11px;">
                                            @if($msg->sent_at)
                                                {{ \Carbon\Carbon::parse($msg->sent_at)->format('H:i:s d/m/Y') }}
                                            @elseif($msg->scheduled_at)
                                                Jadwal: {{ \Carbon\Carbon::parse($msg->scheduled_at)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td style="padding: 10px 12px; text-align: right;">
                                            @if(in_array($msg->status, ['failed', 'cancelled']))
                                                <form method="POST" action="{{ route('admin.outbox.retry-single', $msg->id) }}" style="display: inline; margin: 0;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 10px;" title="Kirim ulang pesan ini">
                                                        Retry
                                                    </button>
                                                </form>
                                            @else
                                                <span style="color: var(--text-subtle); font-size: 11px;">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                        <p style="margin: 0;">Belum ada riwayat antrean outbox hari ini.</p>
                    </div>
                @endif
            </div>

            {{-- KALENDER KERJA & HARI LIBUR NASIONAL --}}
            <div id="kalender" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; margin: 0; color: var(--navy-dark); display: flex; align-items: center; gap: 8px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Kalender Kerja & Hari Libur Nasional
                        </h3>
                        <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0 0;">Pantau tanggal merah, hari libur resmi, dan jadwal kerja aktif yang terhubung dengan otomatisasi pengingat.</p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeMonth(-1)" title="Bulan Sebelumnya" style="padding: 6px 12px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span id="calendarMonthYear" style="font-size: 14px; font-weight: 800; color: var(--navy-dark); min-width: 150px; text-align: center;">...</span>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeMonth(1)" title="Bulan Berikutnya" style="padding: 6px 12px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary-soft" onclick="goToToday()" style="background: var(--primary-light); color: var(--primary); border: none; font-weight: 700; padding: 6px 12px; border-radius: 8px;">
                            Bulan Ini
                        </button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                    <!-- Calendar Grid -->
                    <div>
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px;">
                            <span style="color: #ef4444;">Min</span>
                            <span>Sen</span>
                            <span>Sel</span>
                            <span>Rab</span>
                            <span>Kam</span>
                            <span>Jum</span>
                            <span style="color: #ef4444;">Sab</span>
                        </div>
                        <div id="calendarGrid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;">
                            <!-- Generated by Javascript -->
                        </div>

                        <div style="display: flex; gap: 16px; margin-top: 16px; font-size: 11px; color: var(--text-muted); flex-wrap: wrap;">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 10px; height: 10px; border-radius: 3px; background: #2563eb;"></span> Hari Ini
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 10px; height: 10px; border-radius: 3px; background: #fee2e2; border: 1px solid #fca5a5;"></span> Tanggal Merah / Libur
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 10px; height: 10px; border-radius: 3px; background: #f8fafc; border: 1px solid #e2e8f0;"></span> Hari Kerja Aktif
                            </span>
                        </div>
                    </div>

                    <!-- Upcoming Holidays List -->
                    <div style="background: var(--surface-alt); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 16px;">
                        <h4 style="font-size: 13px; font-weight: 800; margin: 0 0 12px; color: var(--navy-dark); display: flex; align-items: center; justify-content: space-between;">
                            <span>📅 Libur Nasional Mendatang</span>
                            <span style="font-size: 10px; color: var(--primary); font-weight: 700;">Tahun {{ date('Y') }}</span>
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @if(isset($upcomingHolidays) && $upcomingHolidays->isNotEmpty())
                                @foreach($upcomingHolidays as $h)
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 8px 10px; background: #ffffff; border: 1px solid var(--border-light); border-radius: 8px;">
                                        <div>
                                            <strong style="font-size: 12px; color: var(--navy-dark); display: block;">{{ $h->name }}</strong>
                                            <span style="font-size: 10px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($h->date)->translatedFormat('l, d F Y') }}</span>
                                        </div>
                                        <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #fee2e2; color: #dc2626; white-space: nowrap;">
                                            {{ \Carbon\Carbon::parse($h->date)->isToday() ? 'Hari Ini' : \Carbon\Carbon::parse($h->date)->diffForHumans() }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p style="font-size: 11px; color: var(--text-muted); margin: 0;">Tidak ada jadwal libur terdekat.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- WEBHOOK & BOT WA CHEATSHEET --}}
            <div class="webhook-box" id="webhook">
                <div class="webhook-header">
                    <h4>
                        <svg width="18" height="18" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Integrasi Webhook Bot WhatsApp Fonnte
                    </h4>
                    <span style="font-size: 11px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 4px 10px; border-radius: 999px; font-weight: 700;">
                        Nomor Admin: {{ config('whatsapp.admin_number') ?? '082327867328' }}
                    </span>
                </div>

                <div class="code-pill">
                    <span>URL Webhook: <strong>{{ url('/webhook/fonnte') }}</strong></span>
                    <button type="button" onclick="copyWebhookUrl('{{ url('/webhook/fonnte') }}')" class="btn btn-sm btn-primary-soft" style="background: rgba(255,255,255,0.15); color:#fff; border:none;">
                        Salin URL
                    </button>
                </div>

                <div class="command-grid">
                    <div class="command-item">
                        <code>masuk</code>
                        <p>Kirim pengingat absen masuk pagi ke semua pegawai aktif.</p>
                    </div>
                    <div class="command-item">
                        <code>pulang</code>
                        <p>Kirim pengingat absen pulang sore ke semua pegawai aktif.</p>
                    </div>
                    <div class="command-item">
                        <code>tambah [Nama] [Nomor]</code>
                        <p>Daftarkan 1 atau banyak pegawai langsung via chat WA.</p>
                    </div>
                </div>
            </div>

            {{-- TWO COLUMN WORK AREA --}}
            <div class="section-grid" id="karyawan">

                {{-- KOLOM KIRI: DAFTAR PEGAWAI DENGAN PENCARIAN REALTIME --}}
                <div class="app-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <h3>Data Pegawai ({{ count($employees) }})</h3>
                            <p>Daftar kontak penerima notifikasi pengingat absen</p>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <a href="{{ url('/admin/employees/export') }}" class="btn btn-sm btn-secondary">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Export
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="search-filter-box">
                            <div class="search-input-wrap">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" id="searchEmployee" class="form-control" placeholder="Cari nama atau nomor WhatsApp...">
                            </div>
                        </div>

                        <div class="employee-table-wrap">
                            @forelse($employees as $emp)
                                @php
                                    $status = $employeeStatuses[$emp->id] ?? null;
                                    $initials = strtoupper(substr($emp->name, 0, 1));
                                    $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];
                                    $bg = $colors[abs(crc32($emp->name)) % count($colors)];
                                @endphp
                                <div class="employee-row" data-name="{{ strtolower($emp->name) }}" data-phone="{{ $emp->phone_number }}">
                                    <div class="emp-main">
                                        <div class="emp-avatar" style="background: {{ $bg }}">{{ $initials }}</div>
                                        <div class="emp-info">
                                            <h4><span style="color: var(--primary); font-weight: 800; font-size: 11px; background: var(--primary-light); padding: 2px 6px; border-radius: 4px; margin-right: 4px;">{{ $emp->panggilan ?? 'Yth.' }}</span> {{ $emp->name }}</h4>
                                            <p>
                                                <span>{{ $emp->phone_number }}</span>
                                                @if($status)
                                                    <span class="badge {{ $status['variant'] === 'success' ? 'badge-success' : ($status['variant'] === 'warning' ? 'badge-warning' : 'badge-danger') }}">
                                                        {{ $status['label'] }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-neutral">Siap</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div style="display:flex; gap:6px;">
                                        <a href="https://wa.me/62{{ ltrim($emp->phone_number, '0') }}" target="_blank" class="btn btn-sm btn-primary-soft" title="Chat via WhatsApp">
                                            WA
                                        </a>
                                        <button onclick="editEmployee({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ $emp->phone_number }}', '{{ addslashes($emp->panggilan ?? 'Yth.') }}')" class="btn btn-sm btn-secondary">
                                            Edit
                                        </button>
                                        <button onclick="deleteEmployee({{ $emp->id }}, '{{ addslashes($emp->name) }}')" class="btn btn-sm btn-danger-soft">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                                    <p>Belum ada data pegawai. Silakan tambah atau import data baru.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: TAMBAH & IMPORT PEGAWAI --}}
                <div class="app-card">
                    <div class="card-header">
                        <div class="card-title-group">
                            <h3>Kelola Kontak Pegawai</h3>
                            <p>Tambah data satuan atau unggah file Excel/CSV</p>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Tabs Tambah vs Import --}}
                        <div class="tab-nav">
                            <button type="button" class="tab-btn active" onclick="switchEmpTab('tabTambah')">Tambah Satuan</button>
                            <button type="button" class="tab-btn" onclick="switchEmpTab('tabImport')">Import Excel / CSV</button>
                        </div>

                        {{-- Tab 1: Tambah Satuan --}}
                        <div id="tabTambah" class="tab-pane active">
                            <form method="POST" action="{{ route('admin.employees.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap Pegawai</label>
                                    <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso, S.Stat." required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Panggilan / Sapaan</label>
                                    <select name="panggilan" class="form-control" style="height: 42px;">
                                        <option value="Yth.">Yth.</option>
                                        <option value="Bapak">Bapak</option>
                                        <option value="Ibu">Ibu</option>
                                        <option value="Pak">Pak</option>
                                        <option value="Bu">Bu</option>
                                        <option value="Mas">Mas</option>
                                        <option value="Mbak">Mbak</option>
                                        <option value="Sdr.">Sdr.</option>
                                        <option value="Sdri.">Sdri.</option>
                                    </select>
                                    <small style="font-size:10px; color:var(--text-subtle); margin-top:4px; display:block">
                                        Sapaan ini digunakan dalam pesan pengingat (misal: "Bapak Budi Santoso").
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nomor WhatsApp Aktif</label>
                                    <input type="text" name="phone_number" class="form-control" placeholder="Contoh: 081234567890" required>
                                    <small style="font-size:10px; color:var(--text-subtle); margin-top:4px; display:block">
                                        Format nomor: 08xxx atau 628xxx (otomatis dinormalisasi oleh sistem).
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Simpan Pegawai Baru
                                </button>
                            </form>
                        </div>

                        {{-- Tab 2: Import Excel --}}
                        <div id="tabImport" class="tab-pane">
                            <form id="importForm" method="POST" action="{{ route('admin.employees.import') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="file-dropzone" onclick="document.getElementById('employeeFile').click()">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p style="font-size: 13px; font-weight: 700; color: var(--navy-dark); margin: 0 0 4px;" id="fileChosenText">Klik untuk Memilih File Excel / CSV</p>
                                    <span style="font-size: 11px; color: var(--text-muted)">Mendukung format .xlsx, .xls, .csv</span>
                                    <input id="employeeFile" type="file" name="employee_file" accept=".csv,.xlsx,.xls" style="display:none" onchange="handleFileSelected(this)">
                                </div>
                                <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 14px;">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Unggah & Import Data
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            {{-- PENGATURAN WAKTU & TEMPLATE PESAN --}}
            <div class="app-card" id="pengaturan" style="margin-bottom: 32px;">
                <div class="card-header">
                    <div class="card-title-group">
                        <h3>Konfigurasi Jadwal & Format Pesan</h3>
                        <p>Sesuaikan waktu kerja instansi dan template notifikasi WhatsApp</p>
                    </div>
                    <button type="button" id="setDefaultsBtn" class="btn btn-sm btn-secondary">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Set Default (07:30 / 16:00 / 16:30)
                    </button>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf

                        <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label">Jam Masuk Kerja (Pagi)</label>
                                <input type="time" name="check_in_time" class="form-control" value="{{ $checkIn }}" required>
                                <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:3px;">Senin s/d Jumat</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jam Pulang (Senin - Kamis)</label>
                                <input type="time" name="check_out_time" class="form-control" value="{{ $checkOut }}" required>
                                <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:3px;">Hari kerja reguler</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jam Pulang (Khusus Jumat)</label>
                                <input type="time" name="check_out_time_friday" class="form-control" value="{{ $checkOutFriday ?? '16:30' }}" required>
                                <small style="font-size:10px; color:var(--primary); font-weight:600; display:block; margin-top:3px;">Khusus hari Jumat</small>
                            </div>
                        </div>

                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="form-group">
                                <label class="form-label">Nama Instansi / Kantor</label>
                                <input type="text" name="organization_name" class="form-control" value="{{ $organization ?? 'BPS Kabupaten Karanganyar' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pengingat Lebih Awal (Menit Sebelum Jam Masuk/Pulang)</label>
                                <input type="number" name="pre_reminder_minutes" class="form-control" value="{{ $preReminderMinutes ?? 30 }}" min="1" max="120">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Template Pengingat Absen Masuk</label>
                            <textarea name="template_pre_checkin" class="form-control" rows="4">{{ $templatePreCheckin }}</textarea>
                            <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:4px;">
                                Tag variabel yang tersedia: <code>{name}</code>, <code>{target_time}</code>, <code>{minutes_left}</code>, <code>{organization}</code>
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Template Pengingat Absen Pulang</label>
                            <textarea name="template_pre_checkout" class="form-control" rows="4">{{ $templatePreCheckout }}</textarea>
                            <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:4px;">
                                Tag variabel yang tersedia: <code>{name}</code>, <code>{target_time}</code>, <code>{minutes_left}</code>, <code>{organization}</code>
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Template Broadcast Cepat (Default)</label>
                            <textarea name="template_broadcast" class="form-control" rows="3">{{ $templateBroadcast ?? "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}" }}</textarea>
                            <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:4px;">
                                Tag variabel yang tersedia: <code>{name}</code>, <code>{organization}</code>, <code>{kata}</code>
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kalimat Penutup / Salam</label>
                            <input type="text" name="closing_word" class="form-control" value="{{ $kata ?? 'Semangat kerja!' }}">
                        </div>

                        <div style="text-align: right; margin-top: 24px;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Semua Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>

        {{-- FOOTER --}}
        <footer class="app-footer">
            <span>© 2026 Badan Pusat Statistik (BPS) Kabupaten Karanganyar. All rights reserved.</span>
            <span>Aplikasi Pengingat Absen Modern v2.5</span>
        </footer>

    </div>

</div>

<script>
/* Realtime WIB Clock */
function updateClock() {
    const now = new Date();
    const timeOptions = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    const dateOptions = { timeZone: 'Asia/Jakarta', weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };

    const timeStr = new Intl.DateTimeFormat('id-ID', timeOptions).format(now).replace(/\./g, ':');
    const dateStr = new Intl.DateTimeFormat('id-ID', dateOptions).format(now);

    const topClock = document.getElementById('topClock');
    const topDate = document.getElementById('topDate');
    const heroClock = document.getElementById('heroClock');
    const heroDate = document.getElementById('heroDate');

    if (topClock) topClock.textContent = timeStr;
    if (topDate) topDate.textContent = dateStr;
    if (heroClock) heroClock.textContent = timeStr;
    if (heroDate) heroDate.textContent = dateStr;
}
updateClock();
setInterval(updateClock, 1000);

/* Mobile Sidebar Toggle */
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

/* Close sidebar on item click for mobile */
document.querySelectorAll('.nav-link').forEach(function(item) {
    item.addEventListener('click', function() {
        if (window.innerWidth <= 820) {
            document.getElementById('sidebar').classList.remove('open');
        }
    });
});

/* Dynamic Interactive Calendar Engine */
const holidaysMap = {!! $holidaysJson ?? '{}' !!};
let currentCalDate = new Date();

const monthNamesIndo = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

function renderCalendar() {
    const year = currentCalDate.getFullYear();
    const month = currentCalDate.getMonth();

    document.getElementById('calendarMonthYear').textContent = monthNamesIndo[month] + ' ' + year;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    const today = new Date();
    const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

    // Previous month filler cells
    for (let i = firstDay - 1; i >= 0; i--) {
        const d = daysInPrevMonth - i;
        const cell = document.createElement('div');
        cell.style.cssText = 'padding: 10px 6px; text-align: center; border-radius: 8px; font-size: 12px; color: #cbd5e1; background: #f8fafc; min-height: 48px; display: flex; flex-direction: column; align-items: center; justify-content: center;';
        cell.innerHTML = `<span>${d}</span>`;
        grid.appendChild(cell);
    }

    // Current month cells
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const dayOfWeek = new Date(year, month, day).getDay();
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
        const isToday = isCurrentMonth && today.getDate() === day;
        const holiday = holidaysMap[dateStr];

        const cell = document.createElement('div');
        let bg = '#ffffff';
        let border = '1px solid #e2e8f0';
        let textColor = isWeekend ? '#ef4444' : '#0f172a';
        let badgeHtml = '';

        if (isToday) {
            bg = '#2563eb';
            border = '1px solid #1d4ed8';
            textColor = '#ffffff';
            badgeHtml = '<span style="font-size: 8px; font-weight: 800; text-transform: uppercase; background: rgba(255,255,255,0.25); padding: 1px 4px; border-radius: 4px; margin-top: 2px;">Hari Ini</span>';
        } else if (holiday) {
            bg = '#fef2f2';
            border = '1px solid #fca5a5';
            textColor = '#dc2626';
            badgeHtml = `<span style="font-size: 8px; font-weight: 700; color: #dc2626; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; margin-top: 2px;" title="${holiday.name}">🏖️ ${holiday.name.substring(0, 8)}...</span>`;
        }

        cell.style.cssText = `padding: 8px 4px; text-align: center; border-radius: 8px; font-size: 12px; font-weight: 700; color: ${textColor}; background: ${bg}; border: ${border}; min-height: 48px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s ease;`;
        cell.innerHTML = `<span>${day}</span>${badgeHtml}`;

        cell.addEventListener('mouseenter', () => {
            cell.style.transform = 'scale(1.05)';
        });
        cell.addEventListener('mouseleave', () => {
            cell.style.transform = 'scale(1)';
        });

        cell.addEventListener('click', () => {
            let info = holiday ? `🏖️ <strong>${holiday.name}</strong><br><span style="color:#dc2626; font-size:12px;">Hari Libur Nasional (Scheduler Otomatis Libur)</span>` : (isWeekend ? '<span style="color:#d97706; font-size:12px;">Akhir Pekan (Weekend)</span>' : '<span style="color:#059669; font-size:12px;">💼 Hari Kerja Aktif (Scheduler Berjalan)</span>');
            Swal.fire({
                title: `${day} ${monthNamesIndo[month]} ${year}`,
                html: `<div style="text-align: center; padding: 10px 0; font-size: 14px;">${info}</div>`,
                icon: holiday ? 'info' : (isWeekend ? 'warning' : 'success'),
                confirmButtonColor: '#2563eb'
            });
        });

        grid.appendChild(cell);
    }
}

function changeMonth(delta) {
    currentCalDate.setMonth(currentCalDate.getMonth() + delta);
    renderCalendar();
}

function goToToday() {
    currentCalDate = new Date();
    renderCalendar();
}

// Inisialisasi Kalender saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
});

/* Tab Switcher for Employee Card */
function switchEmpTab(tabId) {
    document.querySelectorAll('#karyawan .tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('#karyawan .tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

/* Live Instant Employee Search */
document.getElementById('searchEmployee').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.employee-row');

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const phone = row.getAttribute('data-phone');

        if (name.includes(query) || phone.includes(query)) {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    });
});

/* File Selection Handler */
function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileChosenText').textContent = 'File Terpilih: ' + input.files[0].name;
    }
}

/* Copy Webhook URL to clipboard */
function copyWebhookUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'URL Disalin!',
            text: url,
            timer: 2000,
            showConfirmButton: false,
            confirmButtonColor: '#2563eb'
        });
    });
}

/* Confirmation before manual broadcast */
function confirmBroadcast(event, typeName) {
    event.preventDefault();
    const form = event.target;

    Swal.fire({
        title: 'Kirim ' + typeName + '?',
        text: 'Notifikasi akan langsung dikirim ke seluruh pegawai yang berstatus aktif.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Kirim Sekarang',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengirim notifikasi...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            form.submit();
        }
    });
    return false;
}

/* Open Quick Broadcast Modal with live editable textarea */
function openQuickBroadcastModal() {
    const defaultMsg = @json($templateBroadcast ?? "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}");
    
    Swal.fire({
        title: 'Kirim Broadcast Cepat',
        html: `
            <div style="text-align: left; padding: 6px 0;">
                <p style="font-size: 12px; color: #64748b; margin: 0 0 10px;">
                    Tulis atau sesuaikan pesan pengumuman yang ingin dikirimkan ke seluruh pegawai aktif:
                </p>
                <textarea id="broadcastCustomMessage" class="form-control" rows="6" style="width: 100%; font-family: inherit; font-size: 13px; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; resize: vertical;">${defaultMsg}</textarea>
                <div style="margin-top: 10px; font-size: 11px; color: #475569; background: #f1f5f9; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0; line-height: 1.5;">
                    💡 <strong>Variabel Otomatis:</strong><br>
                    • <code>{name}</code> = Panggilan & Nama (misal: <em>Mas faiz i</em>)<br>
                    • <code>{kata}</code> = Kalimat penutup / salam<br>
                    • <code>{organization}</code> = Nama instansi
                </div>
            </div>
        `,
        width: '560px',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Kirim Sekarang',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const msg = document.getElementById('broadcastCustomMessage').value;
            if (!msg.trim()) {
                Swal.showValidationMessage('Isi pesan tidak boleh kosong!');
                return false;
            }
            return msg;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengirim broadcast...',
                text: 'Mohon tunggu sebentar...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.send-now") }}';
            form.innerHTML = `
                @csrf
                <textarea name="message" style="display:none;"></textarea>
            `;
            form.querySelector('textarea').value = result.value;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* Edit Karyawan Modal */
function editEmployee(id, name, phone, panggilan) {
    const panggilanOptions = ['Yth.', 'Bapak', 'Ibu', 'Pak', 'Bu', 'Mas', 'Mbak', 'Sdr.', 'Sdri.'];
    const optionsHtml = panggilanOptions.map(opt => 
        `<option value="${opt}" ${opt === panggilan ? 'selected' : ''}>${opt}</option>`
    ).join('');

    Swal.fire({
        title: 'Edit Data Pegawai',
        html: `
            <div style="text-align: left; padding: 10px 0;">
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display:block; margin-bottom:4px;">Nama Lengkap</label>
                <input type="text" id="editName" value="${name}" class="swal2-input" style="width: 100%; margin: 0 0 14px; font-size:13px; height:42px;">
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display:block; margin-bottom:4px;">Panggilan / Sapaan</label>
                <select id="editPanggilan" class="swal2-input" style="width: 100%; margin: 0 0 14px; font-size:13px; height:42px;">
                    ${optionsHtml}
                </select>
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display:block; margin-bottom:4px;">Nomor WhatsApp</label>
                <input type="text" id="editPhone" value="${phone}" class="swal2-input" style="width: 100%; margin: 0; font-size:13px; height:42px;">
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Simpan Perubahan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${id}`;
            form.innerHTML = `
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="${document.getElementById('editName').value}">
                <input type="hidden" name="panggilan" value="${document.getElementById('editPanggilan').value}">
                <input type="hidden" name="phone_number" value="${document.getElementById('editPhone').value}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* Hapus Karyawan Modal */
function deleteEmployee(id, name) {
    Swal.fire({
        title: 'Hapus Pegawai?',
        text: `Apakah Anda yakin ingin menghapus data "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* Set Default Times Button */
document.getElementById('setDefaultsBtn').addEventListener('click', function() {
    Swal.fire({
        title: 'Reset Waktu Default?',
        text: 'Waktu masuk diatur ke 07:30 WIB, pulang Senin-Kamis ke 16:00 WIB, dan pulang Jumat ke 16:30 WIB.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Terapkan'
    }).then((res) => {
        if (res.isConfirmed) {
            fetch('{{ route('admin.set-default-times') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            }).then(() => location.reload()).catch(e => alert('Gagal: ' + e.message));
        }
    });
});
</script>

@endsection