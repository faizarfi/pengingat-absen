@extends('layouts.app')

@section('content')

<style>
/* ==========================================================================
   MODERN DESIGN SYSTEM - PENGINGAT ABSEN BPS KARANGANYAR
   ========================================================================== */

:root {
    --primary: #2563eb;
    --primary-hover: #1d4ed8;
    --primary-light: #eff6ff;
    --primary-glow: rgba(37, 99, 235, 0.22);
    
    --navy-dark: #0f172a;
    --navy-card: #1e293b;
    --surface: #ffffff;
    --surface-alt: #f8fafc;
    --surface-glass: rgba(255, 255, 255, 0.92);
    
    --border: #e2e8f0;
    --border-light: #f1f5f9;
    --border-glass: rgba(226, 232, 240, 0.8);
    
    --text-main: #0f172a;
    --text-muted: #64748b;
    --text-subtle: #94a3b8;
    
    --success: #10b981;
    --success-light: #ecfdf5;
    --success-border: #a7f3d0;
    --success-text: #065f46;
    
    --warning: #f59e0b;
    --warning-light: #fffbeb;
    --warning-border: #fde68a;
    --warning-text: #92400e;
    
    --danger: #ef4444;
    --danger-light: #fef2f2;
    --danger-border: #fecaca;
    --danger-text: #991b1b;
    
    --info: #06b6d4;
    --info-light: #ecfeff;
    --info-border: #a5f3fc;
    --info-text: #155e75;

    --sidebar-w: 270px;
    --header-h: 72px;
    --radius-xl: 24px;
    --radius-lg: 18px;
    --radius-md: 12px;
    --radius-sm: 8px;
    
    --shadow-subtle: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
    --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
    --shadow-hover: 0 16px 36px -4px rgba(15, 23, 42, 0.09), 0 4px 12px -2px rgba(15, 23, 42, 0.05);
    --shadow-glow-blue: 0 10px 25px -3px rgba(37, 99, 235, 0.28);
}

/* Base resets & layout */
* { box-sizing: border-box; }
body {
    background: #f8fafc;
    color: var(--text-main);
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.app-container {
    display: flex;
    min-height: 100vh;
    background: 
        radial-gradient(at 100% 0%, rgba(37, 99, 235, 0.04) 0px, transparent 45%),
        radial-gradient(at 0% 100%, rgba(6, 182, 212, 0.03) 0px, transparent 45%),
        #f8fafc;
}

/* ==========================================================================
   SIDEBAR NAVIGATION
   ========================================================================== */
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
    box-shadow: 2px 0 12px rgba(15, 23, 42, 0.02);
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
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.22);
    overflow: hidden;
    flex-shrink: 0;
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
    font-size: 10px;
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
    padding: 12px 14px 6px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: var(--radius-md);
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-link svg {
    width: 18px;
    height: 18px;
    color: var(--text-subtle);
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.nav-link:hover {
    background: var(--primary-light);
    color: var(--primary);
    transform: translateX(4px);
}

.nav-link:hover svg {
    color: var(--primary);
}

.nav-link.active {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.26);
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
    background: #fafcff;
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
    box-shadow: 0 4px 10px rgba(14, 165, 233, 0.25);
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

/* ==========================================================================
   MAIN LAYOUT & HEADER
   ========================================================================== */
.app-main {
    margin-left: var(--sidebar-w);
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.app-header {
    height: var(--header-h);
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    z-index: 40;
    box-shadow: 0 1px 6px rgba(15, 23, 42, 0.02);
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
    padding: 8px;
    border-radius: 8px;
}

.mobile-toggle:hover {
    background: var(--surface-alt);
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
    font-weight: 500;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 14px;
}

.live-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 16px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-subtle);
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
    letter-spacing: 0.02em;
}

.date-text {
    font-size: 11px;
    color: var(--text-muted);
    border-left: 1px solid var(--border);
    padding-left: 10px;
    font-weight: 600;
}

/* Main Content Wrapper */
.app-content {
    padding: 28px 32px 48px;
    flex: 1;
    max-width: 1600px;
    width: 100%;
    margin: 0 auto;
}

/* ==========================================================================
   HERO BANNER - MODERN AMBIENT GLOW
   ========================================================================== */
.hero-banner {
    position: relative;
    border-radius: var(--radius-xl);
    background: linear-gradient(130deg, #091223 0%, #0f244a 45%, #183870 80%, #1e40af 100%);
    padding: 34px 40px;
    color: #ffffff;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 20px 45px -10px rgba(15, 36, 74, 0.35);
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 32px;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.hero-banner::before {
    content: '';
    position: absolute;
    top: -140px;
    right: -60px;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.35) 0%, rgba(37, 99, 235, 0) 70%);
    pointer-events: none;
}

.hero-banner::after {
    content: '';
    position: absolute;
    bottom: -120px;
    left: 25%;
    width: 340px;
    height: 340px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.22) 0%, rgba(6, 182, 212, 0) 70%);
    pointer-events: none;
}

.hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.hero-title {
    font-size: 26px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -0.03em;
    margin: 0 0 10px;
    background: linear-gradient(180deg, #ffffff 0%, #e2e8f0 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.status-online { color: var(--success); }
.status-offline { color: var(--danger); }

.emp-avatar.avatar-bg-0 { background: #2563eb; }
.emp-avatar.avatar-bg-1 { background: #10b981; }
.emp-avatar.avatar-bg-2 { background: #f59e0b; }
.emp-avatar.avatar-bg-3 { background: #8b5cf6; }
.emp-avatar.avatar-bg-4 { background: #ec4899; }
.emp-avatar.avatar-bg-5 { background: #06b6d4; }
.emp-avatar.avatar-bg-6 { background: #0284c7; }

.hero-desc {
    font-size: 13px;
    line-height: 1.6;
    color: #cbd5e1;
    max-width: 640px;
    margin: 0 0 22px;
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
    border-radius: var(--radius-md);
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.14);
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.hero-pill-item:hover {
    background: rgba(255, 255, 255, 0.14);
    transform: translateY(-1px);
}

.hero-clock-box {
    position: relative;
    background: rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    padding: 24px 30px;
    text-align: center;
    min-width: 250px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.hero-clock-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #93c5fd;
    margin-bottom: 6px;
}

.hero-clock-time {
    font-size: 34px;
    font-weight: 800;
    letter-spacing: -0.02em;
    font-variant-numeric: tabular-nums;
    color: #ffffff;
    text-shadow: 0 0 20px rgba(147, 197, 253, 0.4);
}

.hero-clock-date {
    font-size: 11px;
    font-weight: 600;
    color: #cbd5e1;
    margin-top: 6px;
}

/* ==========================================================================
   STAT CARDS - MODERN GLASSMORPHIC WITH TOP ACCENT GLOW
   ========================================================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 22px 24px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    box-shadow: var(--shadow-card);
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
    transition: all 0.25s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.stat-card.card-blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
.stat-card.card-orange::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.stat-card.card-cyan::before { background: linear-gradient(90deg, #06b6d4, #38bdf8); }
.stat-card.card-green::before { background: linear-gradient(90deg, #10b981, #34d399); }

.stat-info span {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: block;
    margin-bottom: 6px;
}

.stat-info h3 {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin: 0 0 6px;
    color: var(--navy-dark);
    display: flex;
    align-items: baseline;
    gap: 6px;
}

.stat-info small {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 500;
    display: block;
}

.stat-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}

.stat-card:hover .stat-icon-wrap {
    transform: scale(1.08);
}

.icon-blue { background: #eff6ff; color: #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15); }
.icon-orange { background: #fffbeb; color: #d97706; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15); }
.icon-cyan { background: #ecfeff; color: #0891b2; box-shadow: 0 4px 12px rgba(8, 145, 178, 0.15); }
.icon-green { background: #ecfdf5; color: #059669; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15); }

/* ==========================================================================
   HOLIDAY & QUEUE CONTROL BAR
   ========================================================================== */
.control-bar {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 16px;
    align-items: center;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 22px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-card);
}

.status-tag-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.status-tag-workday {
    background: var(--success-light);
    color: var(--success-text);
    border: 1px solid var(--success-border);
}

.status-tag-holiday {
    background: var(--warning-light);
    color: var(--warning-text);
    border: 1px solid var(--warning-border);
}

/* ==========================================================================
   ACTION BANNER (MANUAL BROADCAST HUB)
   ========================================================================== */
.action-banner {
    position: relative;
    border-radius: var(--radius-lg);
    background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
    border: 1px solid rgba(37, 99, 235, 0.25);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 24px;
    box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.08);
}

.action-banner-text h3 {
    font-size: 16px;
    font-weight: 800;
    margin: 0 0 4px;
    color: var(--navy-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.action-banner-text p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

.action-buttons-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ==========================================================================
   BUTTON SYSTEM
   ========================================================================== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: var(--radius-md);
    font-size: 13px;
    font-weight: 700;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 11px;
    border-radius: var(--radius-sm);
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
}

.btn-secondary {
    background: #ffffff;
    color: var(--navy-dark);
    border-color: var(--border);
    box-shadow: var(--shadow-subtle);
}

.btn-secondary:hover {
    background: var(--surface-alt);
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.btn-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
}

.btn-success:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-1px);
}

.btn-primary-soft {
    background: var(--primary-light);
    color: var(--primary);
    border-color: rgba(37, 99, 235, 0.15);
}

.btn-primary-soft:hover {
    background: #dbeafe;
    color: var(--primary-hover);
}

.btn-danger-soft {
    background: var(--danger-light);
    color: var(--danger);
    border-color: var(--danger-border);
}

.btn-danger-soft:hover {
    background: #fee2e2;
    color: var(--danger-text);
}

/* ==========================================================================
   APP CARDS & SECTION CONTAINERS
   ========================================================================== */
.app-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}

.app-card:hover {
    box-shadow: var(--shadow-hover);
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.card-title-group h3 {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    color: var(--navy-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-title-group p {
    font-size: 12px;
    color: var(--text-muted);
    margin: 3px 0 0;
}

.card-body {
    padding: 24px;
}

.section-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 24px;
    margin-bottom: 24px;
}

/* ==========================================================================
   DATA TABLES (OUTBOX & LOGS)
   ========================================================================== */
.table-responsive {
    overflow-x: auto;
    border-radius: var(--radius-md);
}

.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12px;
    text-align: left;
}

.modern-table thead th {
    background: #f8fafc;
    color: var(--text-muted);
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 12px 14px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.modern-table tbody tr {
    transition: background 0.15s ease;
}

.modern-table tbody tr:hover {
    background: #f8faff;
}

.modern-table tbody td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--border-light);
    color: var(--navy-dark);
    vertical-align: middle;
}

.modern-table tbody tr:last-child td {
    border-bottom: none;
}

/* Status Badges */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.02em;
    white-space: nowrap;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.badge-success { background: var(--success-light); color: var(--success-text); border: 1px solid var(--success-border); }
.badge-success .badge-dot { background: var(--success); }

.badge-warning { background: var(--warning-light); color: var(--warning-text); border: 1px solid var(--warning-border); }
.badge-warning .badge-dot { background: var(--warning); }

.badge-danger { background: var(--danger-light); color: var(--danger-text); border: 1px solid var(--danger-border); }
.badge-danger .badge-dot { background: var(--danger); }

.badge-info { background: var(--info-light); color: var(--info-text); border: 1px solid var(--info-border); }
.badge-info .badge-dot { background: var(--info); }

.badge-neutral { background: #f1f5f9; color: var(--text-muted); border: 1px solid #e2e8f0; }

/* ==========================================================================
   CALENDAR COMPONENT
   ========================================================================== */
.calendar-wrapper {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.cal-header-day {
    padding: 8px 4px;
    text-align: center;
    font-size: 11px;
    font-weight: 800;
    color: var(--text-muted);
    text-transform: uppercase;
}

.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

.cal-cell {
    padding: 8px 4px;
    text-align: center;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    min-height: 52px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
}

.cal-cell:hover {
    transform: scale(1.06);
    z-index: 2;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.cal-legend {
    display: flex;
    gap: 16px;
    margin-top: 18px;
    font-size: 11px;
    color: var(--text-muted);
    flex-wrap: wrap;
}

.cal-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}

.upcoming-holiday-card {
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: 10px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: transform 0.15s ease;
}

.upcoming-holiday-card:hover {
    transform: translateX(3px);
    border-color: #cbd5e1;
}

/* ==========================================================================
   WEBHOOK & CHEATSHEET CARD
   ========================================================================== */
.webhook-box {
    background: linear-gradient(135deg, #091223 0%, #112240 50%, #1e3a8a 100%);
    border-radius: var(--radius-lg);
    padding: 24px 28px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 12px 30px -4px rgba(15, 36, 74, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.webhook-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}

.webhook-header h4 {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ffffff;
}

.code-pill {
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: var(--radius-md);
    padding: 12px 16px;
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-size: 12px;
    color: #38bdf8;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    gap: 12px;
}

.command-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.command-item {
    background: rgba(255, 255, 255, 0.07);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: var(--radius-md);
    padding: 12px 14px;
    transition: background 0.2s ease;
}

.command-item:hover {
    background: rgba(255, 255, 255, 0.12);
}

.command-item code {
    color: #4ade80;
    font-weight: 800;
    font-size: 12px;
    display: block;
    margin-bottom: 4px;
}

.command-item p {
    font-size: 11px;
    color: #cbd5e1;
    margin: 0;
    line-height: 1.4;
}

/* ==========================================================================
   EMPLOYEE LIST & FILTER
   ========================================================================== */
.search-filter-box {
    margin-bottom: 16px;
}

.search-input-wrap {
    position: relative;
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
    padding-left: 40px;
}

.employee-table-wrap {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 520px;
    overflow-y: auto;
    padding-right: 4px;
}

.employee-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.employee-row:hover {
    background: #f8faff;
    border-color: #cbd5e1;
    transform: translateX(2px);
}

.employee-row.is-selected {
    background: #eff6ff !important;
    border-color: #93c5fd !important;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12) !important;
}

.emp-checkbox-wrap {
    display: flex;
    align-items: center;
    margin-right: 2px;
}

.emp-checkbox {
    width: 18px;
    height: 18px;
    border-radius: 5px;
    accent-color: var(--primary);
    cursor: pointer;
    flex-shrink: 0;
}

/* Floating Bulk Action Dock */
.bulk-action-bar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(140%);
    z-index: 9999;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 999px;
    padding: 8px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 20px 45px -8px rgba(15, 23, 42, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.1);
    color: #ffffff;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
    opacity: 0;
    pointer-events: none;
    max-width: 95vw;
}

.bulk-action-bar.is-active {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
    pointer-events: auto;
}

.bulk-pill {
    background: rgba(37, 99, 235, 0.35);
    border: 1px solid rgba(96, 165, 250, 0.4);
    color: #93c5fd;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.bulk-pill-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #60a5fa;
    box-shadow: 0 0 8px #60a5fa;
}

.btn-bulk {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-bulk-masuk {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
}

.btn-bulk-masuk:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

.btn-bulk-pulang {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #ffffff;
}

.btn-bulk-pulang:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
}

.btn-bulk-kustom {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
}

.btn-bulk-kustom:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}

.btn-bulk-cancel {
    background: rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
}

.btn-bulk-cancel:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.emp-main {
    display: flex;
    align-items: center;
    gap: 12px;
}

.emp-avatar {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    color: #ffffff;
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.emp-info h4 {
    font-size: 13px;
    font-weight: 700;
    margin: 0;
    color: var(--navy-dark);
}

.emp-info p {
    font-size: 11px;
    color: var(--text-muted);
    margin: 3px 0 0;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ==========================================================================
   FORM CONTROLS & DROPZONE
   ========================================================================== */
.form-group {
    margin-bottom: 18px;
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
    padding: 10px 14px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    background: #ffffff;
    color: var(--navy-dark);
    font-size: 13px;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-glow);
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

/* Tab Controls for Forms */
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

/* Dropzone */
.file-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: var(--radius-md);
    padding: 28px 20px;
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
    width: 36px;
    height: 36px;
    color: var(--text-subtle);
    margin-bottom: 8px;
    transition: color 0.2s ease;
}

.file-dropzone:hover svg {
    color: var(--primary);
}

/* Variable Tag Helper — Interactive Click-to-Insert */
.var-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    font-weight: 700;
    color: #1d4ed8;
    margin: 2px 3px 2px 0;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}

.var-pill:hover {
    background: #2563eb;
    color: #ffffff;
    border-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
}

.var-pill:active {
    transform: translateY(0);
}

/* Sticky Topbar Agent Status Pill */
.agent-topbar-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 13px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.02em;
    user-select: none;
    transition: all 0.2s ease;
}

.agent-pill-online {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.agent-pill-offline {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.agent-ping-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    position: relative;
}

.dot-online {
    background: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    animation: ping-pulse 2s infinite;
}

.dot-offline {
    background: #ef4444;
}

@keyframes ping-pulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5); }
    70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Hero Activity Summary (Replacing Double Clock) */
.hero-metric-box {
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    min-width: 270px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.hero-metric-label {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: #93c5fd;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.hero-metric-main {
    display: flex;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 12px;
}

.hero-metric-number {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.hero-metric-total {
    font-size: 13px;
    font-weight: 600;
    color: #cbd5e1;
}

.hero-progress-bar {
    width: 100%;
    height: 7px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 6px;
}

.hero-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #38bdf8, #4ade80);
    border-radius: 999px;
    transition: width 0.6s ease;
}

.hero-progress-desc {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 600;
    color: #cbd5e1;
}

/* Employee Row Action Dropdown */
.emp-actions-group {
    display: flex;
    align-items: center;
    gap: 6px;
    position: relative;
}

.btn-icon-only {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
}

.btn-icon-only:hover {
    color: var(--navy-dark);
    background: var(--surface-alt);
}

.action-dropdown-wrap {
    position: relative;
}

.action-dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
    min-width: 175px;
    padding: 6px;
    z-index: 100;
}

.action-dropdown-wrap.open .action-dropdown-menu {
    display: block;
    animation: dropdownFadeIn 0.15s ease-out;
}

@keyframes dropdownFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 10px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    background: transparent;
    border: none;
    border-radius: 6px;
    text-align: left;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}

.dropdown-item:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.dropdown-item.text-danger {
    color: #dc2626;
}

.dropdown-item.text-danger:hover {
    background: #fef2f2;
    color: #b91c1c;
}

.dropdown-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 4px 0;
}

/* Template Grid Layout & Live WhatsApp Chat Preview */
.template-grid-layout {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 24px;
    align-items: start;
}

@media (max-width: 1024px) {
    .template-grid-layout {
        grid-template-columns: 1fr;
    }
}

.wa-preview-container {
    background: #efeae2;
    background-image: radial-gradient(#d1d7db 0.75px, transparent 0.75px);
    background-size: 16px 16px;
    border-radius: 16px;
    border: 1px solid #d1d7db;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

.wa-preview-header {
    background: #005c4b;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ffffff;
}

.wa-preview-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #128c7e;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    color: #ffffff;
    letter-spacing: -0.02em;
}

.wa-preview-user {
    line-height: 1.25;
}

.wa-preview-user strong {
    font-size: 13px;
    display: block;
    color: #ffffff;
}

.wa-preview-user span {
    font-size: 11px;
    color: #e0f2fe;
    opacity: 0.9;
}

.wa-preview-body {
    padding: 20px 14px;
    min-height: 260px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}

.wa-chat-bubble {
    background: #d9fdd3;
    border-radius: 8px 8px 2px 8px;
    padding: 10px 12px 6px;
    max-width: 92%;
    margin-left: auto;
    box-shadow: 0 1px 1px rgba(11, 20, 26, 0.13);
    position: relative;
    font-size: 12.5px;
    line-height: 1.5;
    color: #111b21;
    word-break: break-word;
    white-space: pre-wrap;
}

.wa-chat-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
    font-size: 10px;
    color: #667781;
}

.wa-preview-switcher {
    display: flex;
    gap: 4px;
    padding: 8px 10px;
    background: #f0f2f5;
    border-bottom: 1px solid #e9edef;
}

.wa-switcher-btn {
    flex: 1;
    padding: 6px 8px;
    font-size: 11px;
    font-weight: 700;
    border: none;
    background: transparent;
    color: #54656f;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.wa-switcher-btn.active {
    background: #ffffff;
    color: #008069;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* ==========================================================================
   FOOTER & RESPONSIVENESS
   ========================================================================== */
.app-footer {
    padding: 22px 32px;
    background: #ffffff;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: var(--text-muted);
}

@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .section-grid { grid-template-columns: 1fr; }
    .calendar-wrapper { grid-template-columns: 1fr; }
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
    .control-bar {
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
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="app-container">

    {{-- ================= SIDEBAR NAVIGATION ================= --}}
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

            <span class="nav-category">Export & Laporan</span>
            <a href="{{ url('/admin/employees/export') }}" class="nav-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Export Pegawai (.csv)
            </a>
            <a href="{{ route('admin.outbox.export') }}" class="nav-link" title="Unduh rekap seluruh riwayat antrean & log pengiriman WhatsApp">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Rekap Log Outbox (.csv)
            </a>
        </nav>

        <div class="sidebar-user">
            <div class="user-profile" style="cursor: pointer;" onclick="openChangePasswordModal()" title="Klik untuk ganti password">
                <div class="user-avatar">A</div>
                <div class="user-details">
                    <strong>Admin BPS</strong>
                    <span>Super Admin 🔑</span>
                </div>
            </div>
            <div style="display: flex; gap: 4px; align-items: center;">
                <button type="button" onclick="openChangePasswordModal()" class="btn btn-sm btn-primary-soft" title="Ganti Password Akun Admin">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </button>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger-soft" title="Keluar dari sistem">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="app-main">

        {{-- TOPBAR --}}
        <header class="app-header">
            <div class="header-left">
                <button class="mobile-toggle" onclick="toggleSidebar()" aria-label="Toggle Menu">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="page-title">
                    <h1>Dashboard Pengingat Absensi</h1>
                    <p>{{ $organization }} • Pusat Kontrol Notifikasi Otomatis</p>
                </div>
            </div>

            <div class="header-right">
                <div class="agent-topbar-pill {{ ($agentStatus ?? 'offline') === 'online' ? 'agent-pill-online' : 'agent-pill-offline' }}" title="Status koneksi WhatsApp Desktop Agent ke Server">
                    <span class="agent-ping-dot {{ ($agentStatus ?? 'offline') === 'online' ? 'dot-online' : 'dot-offline' }}"></span>
                    <span class="agent-pill-text">{{ ($agentStatus ?? 'offline') === 'online' ? 'Agent Online' : 'Agent Offline' }}</span>
                </div>

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

        {{-- CONTENT BODY --}}
        <main class="app-content" id="dashboard">

            {{-- HERO BANNER --}}
            <div class="hero-banner">
                <div>
                    <div class="hero-tag">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #4ade80; display: inline-block;"></span>
                        SISTEM MONITORING KEHADIRAN AKTIF
                    </div>
                    <h2 class="hero-title">Otomatisasi Pengingat Absen Pegawai</h2>
                    <p class="hero-desc">
                        Kelola jadwal kehadiran pegawai, kirimkan notifikasi WA tepat waktu dengan pantun & kata mutiara motivasi, dan kendalikan pengiriman pesan otomatis secara tersinkronisasi.
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

                {{-- STATISTIK RINGKAS HARI INI (PENGGANTI DOUBLE CLOCK) --}}
                <div class="hero-metric-box">
                    <div class="hero-metric-label">PROGRES PENGIRIMAN HARI INI</div>
                    <div class="hero-metric-main">
                        <span class="hero-metric-number">{{ ($outboxStats['sent_today'] ?? 0) + ($totalSentToday ?? 0) }}</span>
                        <span class="hero-metric-total">/ {{ count($employees) }} Pegawai</span>
                    </div>
                    @php
                        $totalEmp = max(count($employees), 1);
                        $sentCount = ($outboxStats['sent_today'] ?? 0) + ($totalSentToday ?? 0);
                        $pct = min(round(($sentCount / $totalEmp) * 100), 100);
                    @endphp
                    <div class="hero-progress-bar">
                        <div class="hero-progress-fill" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="hero-progress-desc">
                        <span>{{ $pct }}% Selesai Terkirim</span>
                        <span>{{ $outboxStats['pending'] ?? 0 }} Antrean Pending</span>
                    </div>
                </div>
            </div>

            {{-- STATS GRID --}}
            <div class="stats-grid">
                <div class="stat-card card-blue">
                    <div class="stat-info">
                        <span>Jadwal Absen Masuk</span>
                        <h3>{{ $checkIn }} <small style="font-size:13px; font-weight:700; color:var(--text-muted)">WIB</small></h3>
                        <small>Reminder: {{ $preReminderMinutes }} mnt sebelumnya</small>
                    </div>
                    <div class="stat-icon-wrap icon-blue">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>

                <div class="stat-card card-orange">
                    <div class="stat-info">
                        <span>Jadwal Pulang {{ now()->isFriday() ? '(Hari Ini: Jumat)' : '(Senin - Kamis)' }}</span>
                        <h3>{{ $todayCheckOut ?? $checkOut }} <small style="font-size:13px; font-weight:700; color:var(--text-muted)">WIB</small></h3>
                        <small>Sen-Kam: {{ $checkOut }} | Jum: {{ $checkOutFriday ?? '16:30' }} WIB</small>
                    </div>
                    <div class="stat-icon-wrap icon-orange">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                </div>

                <div class="stat-card card-cyan">
                    <div class="stat-info">
                        <span>Antrean Outbox</span>
                        <h3>{{ $outboxStats['pending'] ?? 0 }} <small style="font-size:13px; font-weight:700; color:var(--text-muted)">Pending</small></h3>
                        <small>Diproses: {{ $outboxStats['processing'] ?? 0 }} | Retry: {{ $outboxStats['retry'] ?? 0 }}</small>
                    </div>
                    <div class="stat-icon-wrap icon-cyan">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>

                <div class="stat-card card-green">
                    <div class="stat-info">
                        <span>Terkirim Hari Ini</span>
                        <h3>{{ ($outboxStats['sent_today'] ?? 0) + ($totalSentToday ?? 0) }} <small style="font-size:13px; font-weight:700; color:var(--text-muted)">Pesan</small></h3>
                        <small>Gagal: <strong style="color:var(--danger)">{{ $outboxStats['failed'] ?? 0 }}</strong> | Agent: <strong class="{{ ($agentStatus ?? '') === 'online' ? 'status-online' : 'status-offline' }}">{{ strtoupper($agentStatus ?? 'OFFLINE') }}</strong></small>
                    </div>
                    <div class="stat-icon-wrap icon-green">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            {{-- HOLIDAY & QUEUE CONTROL BAR --}}
            <div class="control-bar">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    @if($isTodayHoliday ?? false)
                        <span class="status-tag-pill status-tag-holiday">
                            🏖️ {{ $todayHolidayName ?? 'Hari Libur' }} (Pengingat Otomatis Libur)
                        </span>
                    @else
                        <span class="status-tag-pill status-tag-workday">
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
                            <button type="submit" class="btn btn-sm btn-danger-soft">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Retry Semua Gagal ({{ $outboxStats['failed'] }})
                            </button>
                        </form>
                    @endif

                    @if(($outboxStats['pending'] ?? 0) > 0)
                        <form method="POST" action="{{ route('admin.outbox.cancel-pending') }}" style="margin: 0;" onsubmit="return confirm('Batalkan semua antrean yang belum terkirim?')">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background: var(--warning-light); color: var(--warning-text); border: 1px solid var(--warning-border);">
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

            {{-- LIVE OUTBOX MONITORING TABLE WITH PAGINATION --}}
            <div class="app-card" id="outbox-section" style="margin-bottom: 24px;">
                <div class="card-header">
                    <div class="card-title-group">
                        <h3>
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Antrean & Log Outbox WhatsApp Desktop
                            @if(isset($outboxMessages) && $outboxMessages->total() > 0)
                                <span style="font-size: 11px; font-weight: 700; background: var(--primary-light); color: var(--primary); padding: 2px 8px; border-radius: 999px;">
                                    Total: {{ $outboxMessages->total() }}
                                </span>
                            @endif
                            @if(($outboxStats['pending'] ?? 0) > 0 || ($outboxStats['processing'] ?? 0) > 0)
                                <span id="liveQueueBadge" style="font-size: 11px; font-weight: 700; background: #ecfeff; color: #0891b2; border: 1px solid #a5f3fc; padding: 2px 10px; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px;">
                                    <span class="pulse-dot" style="background: #06b6d4; width: 7px; height: 7px; border-radius: 50%; box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.25);"></span>
                                    Mengirim {{ ($outboxStats['pending'] ?? 0) + ($outboxStats['processing'] ?? 0) }} pesan...
                                </span>
                            @endif
                        </h3>
                        <p>Menampilkan riwayat dan status proses pengiriman pesan terbaru (5 pesan per halaman).</p>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        {{-- Tombol Hapus Terpilih (Muncul dinamis saat checkbox tercentang) --}}
                        <button type="button" id="btnDeleteSelectedOutbox" onclick="triggerDeleteSelectedOutbox()" class="btn btn-sm btn-danger" style="display: none; align-items: center; gap: 5px; background: #ef4444; border-color: #ef4444;" title="Hapus log yang dicentang">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus Terpilih (<span id="outboxSelectedCount">0</span>)
                        </button>

                        {{-- Tombol Bersihkan Semua Log --}}
                        @if(isset($outboxMessages) && $outboxMessages->total() > 0)
                            <button type="button" data-total="{{ $outboxMessages->total() }}" onclick="confirmClearAllOutbox(this.dataset.total)" class="btn btn-sm" style="display: inline-flex; align-items: center; gap: 5px; color: #dc2626; border: 1px solid #fca5a5; background: #fff5f5; font-weight: 600;" title="Bersihkan seluruh log dan reset cache">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Bersihkan Semua Log
                            </button>
                        @endif

                        <a href="{{ route('admin.outbox.export') }}" class="btn btn-sm btn-primary-soft" style="display: inline-flex; align-items: center; gap: 5px;" title="Unduh seluruh rekap riwayat pengiriman ke file CSV / Excel">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh Rekap (.csv)
                        </a>

                        <button type="button" onclick="location.reload()" class="btn btn-sm btn-secondary" title="Refresh data">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Refresh Log
                        </button>
                    </div>
                </div>

                @if(isset($outboxMessages) && $outboxMessages->isNotEmpty())
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th style="width: 36px; padding-left: 20px; text-align: center;">
                                        <input type="checkbox" id="outbox-select-all" title="Pilih Semua di Halaman Ini" onchange="toggleSelectAllOutbox(this)" style="cursor: pointer; transform: scale(1.15);">
                                    </th>
                                    <th>ID</th>
                                    <th>Penerima</th>
                                    <th>Nomor WA</th>
                                    <th>Tipe Pesan</th>
                                    <th>Status</th>
                                    <th>Waktu Pengiriman</th>
                                    <th style="text-align: right; padding-right: 24px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outboxMessages as $msg)
                                    <tr>
                                        <td style="padding-left: 20px; text-align: center;">
                                            <input type="checkbox" class="outbox-checkbox" value="{{ $msg->id }}" onchange="handleOutboxCheckChange()" style="cursor: pointer; transform: scale(1.15);">
                                        </td>
                                        <td style="font-weight: 700; color: var(--text-muted);">#{{ $msg->id }}</td>
                                        <td style="font-weight: 700; color: var(--navy-dark);">
                                            {{ $msg->employee->name ?? 'Pegawai' }}
                                        </td>
                                        <td style="font-family: monospace; color: var(--text-muted);">
                                            {{ $msg->phone_number }}
                                        </td>
                                        <td>
                                            <span style="font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: #f1f5f9; color: var(--text-muted); text-transform: uppercase;">
                                                {{ $msg->type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($msg->status === 'sent')
                                                <span class="badge badge-success">
                                                    <span class="badge-dot"></span>
                                                    Terkirim
                                                </span>
                                            @elseif($msg->status === 'processing')
                                                <span class="badge badge-info">
                                                    <span class="badge-dot"></span>
                                                    Diproses Agent
                                                </span>
                                            @elseif($msg->status === 'pending')
                                                <span class="badge badge-warning">
                                                    <span class="badge-dot"></span>
                                                    Pending
                                                </span>
                                            @elseif($msg->status === 'failed')
                                                <span class="badge badge-danger" title="{{ $msg->last_error }}">
                                                    <span class="badge-dot"></span>
                                                    Gagal
                                                </span>
                                            @elseif($msg->status === 'cancelled')
                                                <span class="badge badge-neutral">
                                                    Dibatalkan
                                                </span>
                                            @else
                                                <span class="badge badge-neutral">
                                                    {{ $msg->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="color: var(--text-muted); font-size: 11px;">
                                            @if($msg->sent_at)
                                                {{ \Carbon\Carbon::parse($msg->sent_at)->format('H:i:s d/m/Y') }}
                                            @elseif($msg->scheduled_at)
                                                Jadwal: {{ \Carbon\Carbon::parse($msg->scheduled_at)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td style="text-align: right; padding-right: 24px; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                @if(in_array($msg->status, ['failed', 'cancelled']))
                                                    <form method="POST" action="{{ route('admin.outbox.retry-single', $msg->id) }}" style="display: inline; margin: 0;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary" style="padding: 4px 8px; font-size: 10px;" title="Kirim ulang pesan ini">
                                                            Retry
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="btn btn-sm" style="padding: 4px 7px; font-size: 10px; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px;" title="Hapus log ini" data-id="{{ $msg->id }}" onclick="confirmDeleteSingleOutbox(this.dataset.id)">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION CONTROLS --}}
                    @if($outboxMessages->hasPages())
                        <div style="padding: 14px 24px; border-top: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #fafcff;">
                            <div style="font-size: 12px; color: var(--text-muted); font-weight: 500;">
                                Menampilkan <strong>{{ $outboxMessages->firstItem() ?? 0 }}</strong> - <strong>{{ $outboxMessages->lastItem() ?? 0 }}</strong> dari <strong>{{ $outboxMessages->total() }}</strong> pesan
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                {{-- Previous Button --}}
                                @if ($outboxMessages->onFirstPage())
                                    <span class="btn btn-sm btn-secondary" style="opacity: 0.45; cursor: not-allowed; padding: 5px 12px;">
                                        &laquo; Sebelumnya
                                    </span>
                                @else
                                    <a href="{{ $outboxMessages->previousPageUrl() }}" class="btn btn-sm btn-secondary" style="padding: 5px 12px;">
                                        &laquo; Sebelumnya
                                    </a>
                                @endif

                                <span style="font-size: 11px; font-weight: 800; color: var(--navy-dark); padding: 5px 10px; background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                                    {{ $outboxMessages->currentPage() }} / {{ $outboxMessages->lastPage() }}
                                </span>

                                {{-- Next Button --}}
                                @if ($outboxMessages->hasMorePages())
                                    <a href="{{ $outboxMessages->nextPageUrl() }}" class="btn btn-sm btn-secondary" style="padding: 5px 12px;">
                                        Berikutnya &raquo;
                                    </a>
                                @else
                                    <span class="btn btn-sm btn-secondary" style="opacity: 0.45; cursor: not-allowed; padding: 5px 12px;">
                                        Berikutnya &raquo;
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div style="padding: 36px 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 10px; color: var(--text-subtle); display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p style="margin: 0;">Belum ada riwayat antrean outbox hari ini.</p>
                    </div>
                @endif
            </div>

            {{-- KALENDER KERJA & HARI LIBUR NASIONAL --}}
            <div id="kalender" class="app-card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <div class="card-title-group">
                        <h3>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Kalender Kerja & Hari Libur Nasional
                        </h3>
                        <p>Pantau tanggal merah, hari libur resmi, dan jadwal kerja aktif yang terhubung dengan otomatisasi pengingat.</p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeMonth(-1)" title="Bulan Sebelumnya" style="padding: 6px 10px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span id="calendarMonthYear" style="font-size: 13px; font-weight: 800; color: var(--navy-dark); min-width: 140px; text-align: center;">...</span>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="changeMonth(1)" title="Bulan Berikutnya" style="padding: 6px 10px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary-soft" onclick="goToToday()">
                            Bulan Ini
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="calendar-wrapper">
                        <!-- Calendar Grid -->
                        <div>
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; text-align: center; margin-bottom: 8px;">
                                <div class="cal-header-day" style="color: #ef4444;">Min</div>
                                <div class="cal-header-day">Sen</div>
                                <div class="cal-header-day">Sel</div>
                                <div class="cal-header-day">Rab</div>
                                <div class="cal-header-day">Kam</div>
                                <div class="cal-header-day">Jum</div>
                                <div class="cal-header-day" style="color: #ef4444;">Sab</div>
                            </div>
                            <div id="calendarGrid" class="cal-grid">
                                <!-- Generated by Javascript -->
                            </div>

                            <div class="cal-legend">
                                <div class="cal-legend-item">
                                    <span style="width: 10px; height: 10px; border-radius: 3px; background: #2563eb;"></span> Hari Ini
                                </div>
                                <div class="cal-legend-item">
                                    <span style="width: 10px; height: 10px; border-radius: 3px; background: #fee2e2; border: 1px solid #fca5a5;"></span> Tanggal Merah / Libur
                                </div>
                                <div class="cal-legend-item">
                                    <span style="width: 10px; height: 10px; border-radius: 3px; background: #ffffff; border: 1px solid #e2e8f0;"></span> Hari Kerja Aktif
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Holidays List -->
                        <div style="background: var(--surface-alt); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 18px;">
                            <h4 style="font-size: 13px; font-weight: 800; margin: 0 0 14px; color: var(--navy-dark); display: flex; align-items: center; justify-content: space-between;">
                                <span>📅 Libur Nasional Mendatang</span>
                                <span style="font-size: 10px; color: var(--primary); font-weight: 700;">Tahun {{ date('Y') }}</span>
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @if(isset($upcomingHolidays) && $upcomingHolidays->isNotEmpty())
                                    @foreach($upcomingHolidays as $h)
                                        <div class="upcoming-holiday-card">
                                            <div>
                                                <strong style="font-size: 12px; color: var(--navy-dark); display: block;">{{ $h->name }}</strong>
                                                <span style="font-size: 10px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($h->date)->translatedFormat('l, d F Y') }}</span>
                                            </div>
                                            <span style="font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: #fee2e2; color: #dc2626; white-space: nowrap;">
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
                                Export CSV
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="search-filter-box" style="display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                            <div class="search-input-wrap" style="flex: 1; min-width: 200px;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" id="searchEmployee" class="form-control" placeholder="Cari nama atau nomor WhatsApp...">
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                <label style="display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 700; color: var(--navy-dark); cursor: pointer; user-select: none; background: var(--surface-alt); padding: 9px 12px; border-radius: var(--radius-md); border: 1px solid var(--border); transition: all 0.2s ease;">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllEmployees(this)" style="width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer;">
                                    <span id="selectAllLabel">Pilih Semua</span>
                                </label>
                                <button type="button" id="clearSelectionBtn" onclick="clearSelectedEmployees()" class="btn btn-sm btn-secondary" style="display: none; padding: 7px 11px;">
                                    Batal
                                </button>
                            </div>
                        </div>

                        <div class="employee-table-wrap">
                            @forelse($employees as $emp)
                                @php
                                    $status = $employeeStatuses[$emp->id] ?? null;
                                    $initials = strtoupper(substr($emp->name, 0, 1));
                                    $colorIdx = abs(crc32($emp->name)) % 7;
                                @endphp
                                <div class="employee-row" data-id="{{ $emp->id }}" data-name="{{ strtolower($emp->name) }}" data-phone="{{ $emp->phone_number }}">
                                    <div class="emp-main">
                                        <div class="emp-checkbox-wrap">
                                            <input type="checkbox" class="emp-checkbox" value="{{ $emp->id }}" data-name="{{ $emp->name }}" data-phone="{{ $emp->phone_number }}" data-panggilan="{{ $emp->panggilan ?? 'Yth.' }}" onchange="handleEmpCheckChange()">
                                        </div>
                                        <div class="emp-avatar avatar-bg-{{ $colorIdx }}">{{ $initials }}</div>
                                        <div class="emp-info">
                                            <h4>
                                                <span style="color: var(--primary); font-weight: 800; font-size: 11px; background: var(--primary-light); padding: 2px 6px; border-radius: 4px; margin-right: 4px;">{{ $emp->panggilan ?? 'Yth.' }}</span> 
                                                {{ $emp->name }}
                                            </h4>
                                            <p>
                                                <span style="font-family: monospace;">{{ $emp->phone_number }}</span>
                                                @if($status)
                                                    @php
                                                        $badgeClass = match($status['variant']) {
                                                            'success' => 'badge-success',
                                                            'warning' => 'badge-warning',
                                                            'info'    => 'badge-info',
                                                            'danger'  => 'badge-danger',
                                                            default   => 'badge-neutral',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}" title="{{ $status['detail'] ?? '' }}">
                                                        <span class="badge-dot"></span>
                                                        {{ $status['label'] }}
                                                        @if(!empty($status['detail']) && $status['variant'] === 'success')
                                                            <small style="opacity: 0.8; font-size: 10px; margin-left: 3px;">({{ $status['detail'] }})</small>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="badge badge-neutral">Siap</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="emp-actions-group">
                                        <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            style="padding: 5px 11px; font-size: 11px; font-weight: 700;" 
                                            title="Kirim Notifikasi Langsung ke Pegawai ini"
                                            data-id="{{ $emp->id }}" 
                                            data-name="{{ $emp->name }}" 
                                            data-phone="{{ $emp->phone_number }}" 
                                            data-panggilan="{{ $emp->panggilan ?? 'Yth.' }}"
                                            onclick="openSendSingleModal(this)">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            Kirim
                                        </button>

                                        <div class="action-dropdown-wrap">
                                            <button type="button" class="btn btn-sm btn-secondary btn-icon-only" onclick="toggleEmpDropdown(this, event)" title="Opsi lainnya">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                            </button>
                                            <div class="action-dropdown-menu">
                                                <a href="https://wa.me/62{{ ltrim($emp->phone_number, '0') }}" target="_blank" class="dropdown-item">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    Chat WhatsApp
                                                </a>
                                                <button type="button" class="dropdown-item" 
                                                    data-id="{{ $emp->id }}" 
                                                    data-name="{{ $emp->name }}" 
                                                    data-phone="{{ $emp->phone_number }}" 
                                                    data-panggilan="{{ $emp->panggilan ?? 'Yth.' }}"
                                                    onclick="editEmployee(this)">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Edit Data
                                                </button>
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-danger" 
                                                    data-id="{{ $emp->id }}" 
                                                    data-name="{{ $emp->name }}"
                                                    onclick="deleteEmployee(this)">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Hapus Pegawai
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                                    <p>Belum ada data pegawai. Silakan tambah atau import data baru.</p>
                                </div>
                            @endforelse

                            {{-- Empty State Pencarian Realtime --}}
                            <div id="emptySearchState" style="display: none; text-align: center; padding: 36px 16px; color: var(--text-muted);">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 8px; opacity: 0.4;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <p style="font-weight: 700; font-size: 13px; margin: 0 0 4px; color: var(--navy-dark);">Pegawai Tidak Ditemukan</p>
                                <span style="font-size: 11px;">Tidak ada kontak yang cocok dengan kata kunci pencarian.</span>
                            </div>
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
                    <form method="POST" action="{{ route('admin.settings.update') }}" id="settingsForm">
                        @csrf

                        <div class="template-grid-layout">
                            {{-- KOLOM KIRI: FORM PENGATURAN & TEMPLATE --}}
                            <div class="template-form-col">
                                <div class="form-row" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 20px;">
                                    <div class="form-group">
                                        <label class="form-label">Jam Masuk Kerja (Pagi)</label>
                                        <input type="time" name="check_in_time" class="form-control" value="{{ $checkIn }}" required onchange="updateLivePreview()">
                                        <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:3px;">Senin s/d Jumat</small>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Jam Pulang (Senin - Kamis)</label>
                                        <input type="time" name="check_out_time" class="form-control" value="{{ $checkOut }}" required onchange="updateLivePreview()">
                                        <small style="font-size:10px; color:var(--text-subtle); display:block; margin-top:3px;">Hari kerja reguler</small>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Jam Pulang (Khusus Jumat)</label>
                                        <input type="time" name="check_out_time_friday" class="form-control" value="{{ $checkOutFriday ?? '16:30' }}" required onchange="updateLivePreview()">
                                        <small style="font-size:10px; color:var(--primary); font-weight:700; display:block; margin-top:3px;">Khusus hari Jumat</small>
                                    </div>
                                </div>

                                <div class="form-row" style="margin-bottom: 20px;">
                                    <div class="form-group">
                                        <label class="form-label">Nama Instansi / Kantor</label>
                                        <input type="text" name="organization_name" class="form-control" value="{{ $organization ?? 'BPS Kabupaten Karanganyar' }}" oninput="updateLivePreview()">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Pengingat Lebih Awal (Menit)</label>
                                        <input type="number" name="pre_reminder_minutes" class="form-control" value="{{ $preReminderMinutes ?? 30 }}" min="1" max="120" oninput="updateLivePreview()">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                        <label class="form-label" style="margin: 0;">Template Pengingat Absen Masuk</label>
                                        <small style="font-size: 10px; color: var(--primary); font-weight: 700;">Klik tag untuk menyisipkan variabel</small>
                                    </div>
                                    <textarea name="template_pre_checkin" class="form-control" rows="4" oninput="updateLivePreview()">{{ $templatePreCheckin }}</textarea>
                                    <div style="margin-top: 6px;">
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkin', '{name}')" title="Klik untuk menyisipkan variabel {name}">+ {name}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkin', '{target_time}')" title="Klik untuk menyisipkan variabel {target_time}">+ {target_time}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkin', '{minutes_left}')" title="Klik untuk menyisipkan variabel {minutes_left}">+ {minutes_left}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkin', '{organization}')" title="Klik untuk menyisipkan variabel {organization}">+ {organization}</button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                        <label class="form-label" style="margin: 0;">Template Pengingat Absen Pulang</label>
                                        <small style="font-size: 10px; color: var(--primary); font-weight: 700;">Klik tag untuk menyisipkan variabel</small>
                                    </div>
                                    <textarea name="template_pre_checkout" class="form-control" rows="4" oninput="updateLivePreview()">{{ $templatePreCheckout }}</textarea>
                                    <div style="margin-top: 6px;">
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkout', '{name}')" title="Klik untuk menyisipkan variabel {name}">+ {name}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkout', '{target_time}')" title="Klik untuk menyisipkan variabel {target_time}">+ {target_time}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkout', '{minutes_left}')" title="Klik untuk menyisipkan variabel {minutes_left}">+ {minutes_left}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_pre_checkout', '{organization}')" title="Klik untuk menyisipkan variabel {organization}">+ {organization}</button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                        <label class="form-label" style="margin: 0;">Template Broadcast Cepat</label>
                                        <small style="font-size: 10px; color: var(--primary); font-weight: 700;">Klik tag untuk menyisipkan variabel</small>
                                    </div>
                                    <textarea name="template_broadcast" class="form-control" rows="3" oninput="updateLivePreview()">{{ $templateBroadcast ?? "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}" }}</textarea>
                                    <div style="margin-top: 6px;">
                                        <button type="button" class="var-pill" onclick="insertVariable('template_broadcast', '{name}')" title="Klik untuk menyisipkan variabel {name}">+ {name}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_broadcast', '{organization}')" title="Klik untuk menyisipkan variabel {organization}">+ {organization}</button>
                                        <button type="button" class="var-pill" onclick="insertVariable('template_broadcast', '{kata}')" title="Klik untuk menyisipkan variabel {kata}">+ {kata}</button>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Kalimat Penutup / Salam</label>
                                    <input type="text" name="closing_word" class="form-control" value="{{ $kata ?? 'Semangat kerja!' }}" oninput="updateLivePreview()">
                                </div>

                                <div style="margin-top: 24px;">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Simpan Semua Pengaturan
                                    </button>
                                </div>
                            </div>

                            {{-- KOLOM KANAN: LIVE PRATINJAU WHATSAPP (MOCKUP REALTIME) --}}
                            <div class="template-preview-col" style="position: sticky; top: 90px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <label class="form-label" style="margin: 0; font-weight: 800; color: #0f172a;">
                                        Pratinjau Pesan WhatsApp
                                    </label>
                                    <span style="font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 4px;">Live Preview</span>
                                </div>

                                <div class="wa-preview-container">
                                    <div class="wa-preview-switcher">
                                        <button type="button" class="wa-switcher-btn active" data-type="pre_checkin" onclick="switchPreviewTab('pre_checkin')">Absen Masuk</button>
                                        <button type="button" class="wa-switcher-btn" data-type="pre_checkout" onclick="switchPreviewTab('pre_checkout')">Absen Pulang</button>
                                        <button type="button" class="wa-switcher-btn" data-type="broadcast" onclick="switchPreviewTab('broadcast')">Broadcast</button>
                                    </div>
                                    <div class="wa-preview-header">
                                        <div class="wa-preview-avatar">WA</div>
                                        <div class="wa-preview-user">
                                            <strong>BPS Notifikasi</strong>
                                            <span>online</span>
                                        </div>
                                    </div>
                                    <div class="wa-preview-body">
                                        <div class="wa-chat-bubble">
                                            <div id="waPreviewText">Memuat pratinjau pesan...</div>
                                            <div class="wa-chat-meta">
                                                <span id="waPreviewTime">07:00</span>
                                                <svg width="15" height="11" viewBox="0 0 16 11" fill="none"><path d="M11.07 0.93L4.93 7.07L2.43 4.57L1.5 5.5L4.93 8.93L12 1.86L11.07 0.93ZM15.07 0.93L8.93 7.07L7.97 6.11L7.03 7.05L8.93 8.93L16 1.86L15.07 0.93Z" fill="#53bdeb"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small style="display: block; font-size: 11px; color: var(--text-muted); margin-top: 8px; line-height: 1.4;">
                                    Tampilan di atas merefleksikan format pesan yang diterima oleh pegawai di aplikasi WhatsApp secara realtime.
                                </small>
                            </div>
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

{{-- FLOATING BULK ACTION DOCK --}}
<div id="bulkActionBar" class="bulk-action-bar">
    <div class="bulk-pill">
        <span class="bulk-pill-dot"></span>
        <span id="bulkSelectedCount">0</span> Pegawai Dipilih
    </div>
    <button type="button" onclick="triggerBulkSend('pre_checkin')" class="btn-bulk btn-bulk-masuk" title="Kirim Pengingat Masuk ke Pegawai Terpilih">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        Kirim Masuk
    </button>
    <button type="button" onclick="triggerBulkSend('pre_checkout')" class="btn-bulk btn-bulk-pulang" title="Kirim Pengingat Pulang ke Pegawai Terpilih">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        Kirim Pulang
    </button>
    <button type="button" onclick="triggerBulkSend('custom')" class="btn-bulk btn-bulk-kustom" title="Kirim Pesan Kustom ke Pegawai Terpilih">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        Pesan Kustom
    </button>
    <button type="button" onclick="triggerBulkDelete()" class="btn-bulk" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white;" title="Hapus Pegawai Terpilih">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        Hapus Terpilih
    </button>
    <button type="button" onclick="clearSelectedEmployees()" class="btn-bulk btn-bulk-cancel" title="Batalkan Semua Pilihan">
        ✕ Batal
    </button>
</div>

{{-- EMBEDDED DATA FOR JAVASCRIPT --}}
<script id="holidaysData" type="application/json">{!! $holidaysJson ?? '{}' !!}</script>
<template id="defaultBroadcastTemplate">{{ $templateBroadcast ?? "Halo {name},\n\nPengumuman: mohon perhatian untuk seluruh pegawai.\n\n{kata}" }}</template>

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
const holidaysEl = document.getElementById('holidaysData');
const holidaysMap = holidaysEl ? JSON.parse(holidaysEl.textContent || '{}') : {};
let currentCalDate = new Date();

const monthNamesIndo = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

function renderCalendar() {
    const year = currentCalDate.getFullYear();
    const month = currentCalDate.getMonth();

    const titleEl = document.getElementById('calendarMonthYear');
    if (titleEl) {
        titleEl.textContent = monthNamesIndo[month] + ' ' + year;
    }

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const grid = document.getElementById('calendarGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const today = new Date();
    const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

    // Previous month filler cells
    for (let i = firstDay - 1; i >= 0; i--) {
        const d = daysInPrevMonth - i;
        const cell = document.createElement('div');
        cell.className = 'cal-cell';
        cell.style.cssText = 'color: #cbd5e1; background: #f8fafc; cursor: default; border: 1px solid #f1f5f9;';
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
        cell.className = 'cal-cell';
        
        let bg = '#ffffff';
        let border = '1px solid #e2e8f0';
        let textColor = isWeekend ? '#ef4444' : '#0f172a';
        let badgeHtml = '';

        if (isToday) {
            bg = 'linear-gradient(135deg, #2563eb, #1d4ed8)';
            border = '1px solid #1e40af';
            textColor = '#ffffff';
            badgeHtml = '<span style="font-size: 8px; font-weight: 800; text-transform: uppercase; background: rgba(255,255,255,0.25); padding: 2px 6px; border-radius: 4px; margin-top: 3px;">Hari Ini</span>';
        } else if (holiday) {
            bg = '#fef2f2';
            border = '1px solid #fca5a5';
            textColor = '#dc2626';
            badgeHtml = `<span style="font-size: 8px; font-weight: 700; color: #dc2626; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; margin-top: 3px;" title="${holiday.name}">🏖️ ${holiday.name.substring(0, 8)}...</span>`;
        }

        cell.style.background = bg;
        cell.style.border = border;
        cell.style.color = textColor;
        cell.innerHTML = `<span>${day}</span>${badgeHtml}`;

        cell.addEventListener('click', () => {
            let info = holiday ? `🏖️ <strong>${holiday.name}</strong><br><span style="color:#dc2626; font-size:12px; margin-top:4px; display:block;">Hari Libur Nasional (Scheduler Otomatis Libur)</span>` : (isWeekend ? '<span style="color:#d97706; font-size:12px;">Akhir Pekan (Weekend)</span>' : '<span style="color:#059669; font-size:12px;">💼 Hari Kerja Aktif (Scheduler Berjalan)</span>');
            Swal.fire({
                title: `${day} ${monthNamesIndo[month]} ${year}`,
                html: `<div style="text-align: center; padding: 12px 0; font-size: 14px;">${info}</div>`,
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
const searchInput = document.getElementById('searchEmployee');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.employee-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const phone = row.getAttribute('data-phone') || '';

            if (name.includes(query) || phone.includes(query)) {
                row.style.display = 'flex';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptySearch = document.getElementById('emptySearchState');
        if (emptySearch) {
            emptySearch.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
        }

        handleEmpCheckChange();
    });
}

/* ==========================================================================
   MULTI-SELECT EMPLOYEE SYSTEM
   ========================================================================== */
function getSelectedEmployees() {
    const checkedBoxes = document.querySelectorAll('.emp-checkbox:checked');
    const selected = [];
    checkedBoxes.forEach(cb => {
        selected.push({
            id: cb.value,
            name: cb.getAttribute('data-name') || '',
            phone: cb.getAttribute('data-phone') || '',
            panggilan: cb.getAttribute('data-panggilan') || 'Yth.'
        });
    });
    return selected;
}

function handleEmpCheckChange() {
    const allCheckboxes = document.querySelectorAll('.emp-checkbox');
    const checkedBoxes = document.querySelectorAll('.emp-checkbox:checked');
    const count = checkedBoxes.length;

    // Highlight row
    allCheckboxes.forEach(cb => {
        const row = cb.closest('.employee-row');
        if (row) {
            if (cb.checked) {
                row.classList.add('is-selected');
            } else {
                row.classList.remove('is-selected');
            }
        }
    });

    // Update floating dock & header controls
    const bulkDock = document.getElementById('bulkActionBar');
    const countEl = document.getElementById('bulkSelectedCount');
    const clearBtn = document.getElementById('clearSelectionBtn');
    const selectAllCb = document.getElementById('selectAllCheckbox');
    const selectAllLabel = document.getElementById('selectAllLabel');

    if (countEl) countEl.textContent = count;

    if (count > 0) {
        if (bulkDock) bulkDock.classList.add('is-active');
        if (clearBtn) clearBtn.style.display = 'inline-flex';
        if (selectAllLabel) selectAllLabel.textContent = `${count} Terpilih`;
    } else {
        if (bulkDock) bulkDock.classList.remove('is-active');
        if (clearBtn) clearBtn.style.display = 'none';
        if (selectAllLabel) selectAllLabel.textContent = 'Pilih Semua';
    }

    // Sync select all checkbox state
    if (selectAllCb) {
        const visibleRows = Array.from(document.querySelectorAll('.employee-row')).filter(r => r.style.display !== 'none');
        const visibleCheckboxes = visibleRows.map(r => r.querySelector('.emp-checkbox')).filter(Boolean);
        const visibleChecked = visibleCheckboxes.filter(cb => cb.checked);

        if (visibleCheckboxes.length > 0 && visibleChecked.length === visibleCheckboxes.length) {
            selectAllCb.checked = true;
            selectAllCb.indeterminate = false;
        } else if (visibleChecked.length > 0) {
            selectAllCb.checked = false;
            selectAllCb.indeterminate = true;
        } else {
            selectAllCb.checked = false;
            selectAllCb.indeterminate = false;
        }
    }
}

function toggleSelectAllEmployees(masterCheckbox) {
    const shouldCheck = masterCheckbox.checked;
    const visibleRows = Array.from(document.querySelectorAll('.employee-row')).filter(r => r.style.display !== 'none');
    
    visibleRows.forEach(row => {
        const cb = row.querySelector('.emp-checkbox');
        if (cb) {
            cb.checked = shouldCheck;
        }
    });

    handleEmpCheckChange();
}

function clearSelectedEmployees() {
    document.querySelectorAll('.emp-checkbox').forEach(cb => {
        cb.checked = false;
    });
    const selectAllCb = document.getElementById('selectAllCheckbox');
    if (selectAllCb) {
        selectAllCb.checked = false;
        selectAllCb.indeterminate = false;
    }
    handleEmpCheckChange();
}

function triggerBulkSend(type) {
    const selected = getSelectedEmployees();
    if (selected.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Pilih Pegawai Terlebih Dahulu',
            text: 'Centang kotak pada nama pegawai yang ingin Anda kirimi pesan.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    const count = selected.length;
    const previewNames = selected.slice(0, 5).map(e => e.name).join(', ') + (count > 5 ? ` dan ${count - 5} lainnya` : '');

    if (type === 'pre_checkin') {
        Swal.fire({
            title: `Kirim Pengingat Masuk?`,
            html: `
                <div style="text-align: left; padding: 4px 0;">
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px;">
                        <strong style="color: #065f46; font-size: 13px; display: block;">🌅 ${count} Pegawai Terpilih:</strong>
                        <span style="font-size: 12px; color: #047857;">${previewNames}</span>
                    </div>
                    <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.5;">
                        Pesan pengingat absen masuk akan dikirimkan ke <strong>${count} pegawai</strong> terpilih secara personal lengkap dengan nama sapaan, jam masuk kerja, sisa waktu, dan pantun pagi.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Ya, Kirim ke ${count} Orang`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitBulkSendForm('pre_checkin', selected.map(e => e.id));
            }
        });
    } else if (type === 'pre_checkout') {
        Swal.fire({
            title: `Kirim Pengingat Pulang?`,
            html: `
                <div style="text-align: left; padding: 4px 0;">
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px;">
                        <strong style="color: #92400e; font-size: 13px; display: block;">🌇 ${count} Pegawai Terpilih:</strong>
                        <span style="font-size: 12px; color: #b45309;">${previewNames}</span>
                    </div>
                    <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.5;">
                        Pesan pengingat absen pulang akan dikirimkan ke <strong>${count} pegawai</strong> terpilih sebelum jam operasional selesai.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Ya, Kirim ke ${count} Orang`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitBulkSendForm('pre_checkout', selected.map(e => e.id));
            }
        });
    } else if (type === 'custom') {
        const defaultMsg = document.getElementById('defaultBroadcastTemplate')?.innerHTML || '';
        
        Swal.fire({
            title: `Kirim Pesan Kustom`,
            html: `
                <div style="text-align: left; padding: 4px 0;">
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 10px 14px; margin-bottom: 12px;">
                        <strong style="color: #1e40af; font-size: 13px; display: block;">💬 ${count} Penerima Terpilih:</strong>
                        <span style="font-size: 12px; color: #1d4ed8;">${previewNames}</span>
                    </div>
                    <label style="font-size: 12px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Isi Pesan Khusus:</label>
                    <textarea id="bulkCustomMessage" class="form-control" rows="5" style="width: 100%; font-family: inherit; font-size: 12px; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; resize: vertical;" placeholder="Tulis pesan...">${defaultMsg}</textarea>
                    <div style="margin-top: 10px; font-size: 11px; color: #475569; background: #f8fafc; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; line-height: 1.5;">
                        💡 <strong>Variabel Otomatis:</strong> <code>{name}</code> = Nama pegawai, <code>{kata}</code> = Salam penutup, <code>{organization}</code> = Instansi.
                    </div>
                </div>
            `,
            width: '560px',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: `Kirim ke ${count} Orang`,
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const msg = document.getElementById('bulkCustomMessage')?.value || '';
                if (!msg.trim()) {
                    Swal.showValidationMessage('Isi pesan tidak boleh kosong!');
                    return false;
                }
                return msg;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitBulkSendForm('custom', selected.map(e => e.id), result.value);
            }
        });
    }
}

function submitBulkSendForm(type, employeeIds, message = '') {
    Swal.fire({
        title: 'Memasukkan ke antrean...',
        text: `Menyiapkan pengiriman pesan untuk ${employeeIds.length} pegawai...`,
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.send-selected") }}';

    let html = `@csrf<input type="hidden" name="type" value="${type}">`;
    employeeIds.forEach(id => {
        html += `<input type="hidden" name="employee_ids[]" value="${id}">`;
    });
    if (message) {
        html += `<textarea name="message" style="display:none;"></textarea>`;
    }
    form.innerHTML = html;
    if (message) {
        form.querySelector('textarea').value = message;
    }
    document.body.appendChild(form);
    form.submit();
}

/* Bulk Delete — Hapus Banyak Pegawai Sekaligus */
function triggerBulkDelete() {
    const selected = getSelectedEmployees();
    if (selected.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Pilih Pegawai Terlebih Dahulu',
            text: 'Centang kotak pada nama pegawai yang ingin dihapus.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    const count = selected.length;
    const nameList = selected.slice(0, 10).map(e => `• ${e.name}`).join('\n') + (count > 10 ? `\n• ...dan ${count - 10} lainnya` : '');

    Swal.fire({
        title: `Hapus ${count} Pegawai?`,
        html: `<p style="margin-bottom: 8px;">Data berikut akan <b>dihapus permanen</b>:</p>
               <div style="text-align: left; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; max-height: 200px; overflow-y: auto; font-size: 13px;">
                   <pre style="margin: 0; font-family: inherit; white-space: pre-wrap;">${nameList}</pre>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Ya, Hapus ${count} Pegawai`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                text: `Menghapus ${count} data pegawai...`,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.employees.delete-selected") }}';

            let html = `@csrf`;
            selected.forEach(e => {
                html += `<input type="hidden" name="employee_ids[]" value="${e.id}">`;
            });
            form.innerHTML = html;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* ==========================================================================
   OUTBOX LOG MANAGEMENT (PILIH SEMUA, HAPUS TERPILIH, BERSIHKAN SEMUA)
   ========================================================================== */

function getSelectedOutboxIds() {
    const checkboxes = document.querySelectorAll('.outbox-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function handleOutboxCheckChange() {
    const selectedIds = getSelectedOutboxIds();
    const count = selectedIds.length;
    const btnDelete = document.getElementById('btnDeleteSelectedOutbox');
    const badgeCount = document.getElementById('outboxSelectedCount');
    const masterCheckbox = document.getElementById('outbox-select-all');

    if (badgeCount) badgeCount.textContent = count;

    if (btnDelete) {
        btnDelete.style.display = count > 0 ? 'inline-flex' : 'none';
    }

    if (masterCheckbox) {
        const allCheckboxes = document.querySelectorAll('.outbox-checkbox');
        if (allCheckboxes.length > 0) {
            masterCheckbox.checked = count === allCheckboxes.length;
            masterCheckbox.indeterminate = count > 0 && count < allCheckboxes.length;
        }
    }
}

function toggleSelectAllOutbox(master) {
    const checkboxes = document.querySelectorAll('.outbox-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = master.checked;
    });
    handleOutboxCheckChange();
}

function triggerDeleteSelectedOutbox() {
    const ids = getSelectedOutboxIds();
    if (ids.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Pilih Log Terlebih Dahulu',
            text: 'Centang kotak pada baris log yang ingin dihapus.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    const count = ids.length;
    Swal.fire({
        title: `Hapus ${count} Log Terpilih?`,
        text: `${count} riwayat antrean & log pengiriman ini akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Ya, Hapus ${count} Log`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                text: `Menghapus ${count} log antrean...`,
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.outbox.delete-selected") }}';

            let html = `@csrf`;
            ids.forEach(id => {
                html += `<input type="hidden" name="outbox_ids[]" value="${id}">`;
            });
            form.innerHTML = html;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmClearAllOutbox(total) {
    Swal.fire({
        title: 'Bersihkan Seluruh Log & Cache?',
        html: `<p style="margin-bottom: 8px;">Semua <b>${total} data riwayat antrean & log</b> pengiriman akan dihapus permanen dan cache antrean akan di-reset.</p>
               <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; font-size: 12px; color: #991b1b; text-align: left;">
                   ⚠️ <b>Perhatian:</b> Tindakan ini tidak dapat dibatalkan. Log riwayat pengiriman sebelumnya tidak akan bisa dilihat lagi.
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Bersihkan Semua Log',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Membersihkan...',
                text: 'Menghapus seluruh log & mereset cache...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.outbox.clear-all") }}';
            form.innerHTML = `@csrf`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmDeleteSingleOutbox(id) {
    Swal.fire({
        title: `Hapus Log #${id}?`,
        text: 'Baris log pengiriman ini akan dihapus permanen.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/admin/outbox') }}/${id}`;
            form.innerHTML = `@csrf<input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* File Selection Handler */
function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        document.getElementById('fileChosenText').textContent = 'File Terpilih: ' + input.files[0].name;
    }
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
                text: 'Mohon tunggu proses broadcast selesai...',
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
    const defaultMsg = document.getElementById('defaultBroadcastTemplate')?.innerHTML || '';
    
    Swal.fire({
        title: 'Kirim Broadcast Cepat',
        html: `
            <div style="text-align: left; padding: 6px 0;">
                <p style="font-size: 12px; color: #64748b; margin: 0 0 10px;">
                    Tulis atau sesuaikan pesan pengumuman yang ingin dikirimkan ke seluruh pegawai aktif:
                </p>
                <textarea id="broadcastCustomMessage" class="form-control" rows="6" style="width: 100%; font-family: inherit; font-size: 13px; padding: 12px; border-radius: 10px; border: 1px solid #cbd5e1; box-sizing: border-box; resize: vertical;">${defaultMsg}</textarea>
                <div style="margin-top: 12px; font-size: 11px; color: #475569; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; line-height: 1.6;">
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

/* Modal Kirim Pesan / Pengingat ke Satu Orang Pegawai */
function openSendSingleModal(idOrEl, name, phone, panggilan) {
    let id = idOrEl;
    if (typeof idOrEl === 'object' && idOrEl !== null && idOrEl.dataset) {
        id = idOrEl.dataset.id;
        name = idOrEl.dataset.name;
        phone = idOrEl.dataset.phone;
        panggilan = idOrEl.dataset.panggilan;
    }
    const sapaanLengkap = (panggilan ? panggilan + ' ' : '') + name;
    
    Swal.fire({
        title: 'Kirim Notifikasi',
        html: `
            <div style="text-align: left; padding: 4px 0;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <strong style="font-size: 13px; color: #0f172a; display: block;">${sapaanLengkap}</strong>
                        <span style="font-size: 11px; font-family: monospace; color: #64748b;">${phone}</span>
                    </div>
                    <span style="font-size: 10px; font-weight: 700; background: #eff6ff; color: #2563eb; padding: 3px 8px; border-radius: 6px;">Penerima Tunggal</span>
                </div>

                <p style="font-size: 12px; font-weight: 700; color: #0f172a; margin: 0 0 8px;">Pilih Jenis Pesan:</p>
                
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="radio" name="send_single_type" value="pre_checkin" checked onchange="toggleCustomSingleBox(this.value)">
                        <div>
                            <strong style="font-size: 12px; color: #0f172a; display: block;">🌅 Pengingat Absen Masuk</strong>
                            <span style="font-size: 11px; color: #64748b;">Format otomatis: jadwal masuk, sisa menit, dan pantun pagi</span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="radio" name="send_single_type" value="pre_checkout" onchange="toggleCustomSingleBox(this.value)">
                        <div>
                            <strong style="font-size: 12px; color: #0f172a; display: block;">🌇 Pengingat Absen Pulang</strong>
                            <span style="font-size: 11px; color: #64748b;">Format otomatis: jadwal pulang dan ucapan terima kasih</span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="radio" name="send_single_type" value="custom" onchange="toggleCustomSingleBox(this.value)">
                        <div>
                            <strong style="font-size: 12px; color: #0f172a; display: block;">💬 Pesan Kustom / Khusus</strong>
                            <span style="font-size: 11px; color: #64748b;">Tulis pesan langsung untuk pegawai ini</span>
                        </div>
                    </label>
                </div>

                <div id="customSingleMessageBox" style="display: none;">
                    <label style="font-size: 12px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Tulis Isi Pesan:</label>
                    <textarea id="singleCustomText" class="form-control" rows="4" style="width: 100%; font-family: inherit; font-size: 12px; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box; resize: vertical;" placeholder="Halo {name}, ...">Halo {name},&#10;&#10;Mohon perhatian terkait informasi berikut.&#10;&#10;{kata}</textarea>
                </div>
            </div>
        `,
        width: '540px',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Kirim Sekarang',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const selectedType = document.querySelector('input[name="send_single_type"]:checked')?.value || 'pre_checkin';
            const customText = document.getElementById('singleCustomText')?.value || '';
            if (selectedType === 'custom' && !customText.trim()) {
                Swal.showValidationMessage('Isi pesan kustom tidak boleh kosong!');
                return false;
            }
            return { type: selectedType, message: customText };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mengirim pesan...',
                text: 'Memasukkan notifikasi ke antrean outbox...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/employees/${id}/send`;
            form.innerHTML = `
                @csrf
                <input type="hidden" name="type" value="${result.value.type}">
                <textarea name="message" style="display:none;"></textarea>
            `;
            form.querySelector('textarea').value = result.value.message;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function toggleCustomSingleBox(type) {
    const box = document.getElementById('customSingleMessageBox');
    if (box) {
        box.style.display = type === 'custom' ? 'block' : 'none';
    }
}

/* Edit Karyawan Modal */
function editEmployee(idOrEl, name, phone, panggilan) {
    let id = idOrEl;
    if (typeof idOrEl === 'object' && idOrEl !== null && idOrEl.dataset) {
        id = idOrEl.dataset.id;
        name = idOrEl.dataset.name;
        phone = idOrEl.dataset.phone;
        panggilan = idOrEl.dataset.panggilan;
    }
    const panggilanOptions = ['Yth.', 'Bapak', 'Ibu', 'Pak', 'Bu', 'Mas', 'Mbak', 'Sdr.', 'Sdri.'];
    const optionsHtml = panggilanOptions.map(opt => 
        `<option value="${opt}" ${opt === panggilan ? 'selected' : ''}>${opt}</option>`
    ).join('');

    Swal.fire({
        title: 'Edit Data Pegawai',
        html: `
            <div style="text-align: left; padding: 10px 0;">
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display:block; margin-bottom:4px;">Nama Lengkap</label>
                <input type="text" id="editName" value="${name}" class="swal2-input" style="width: 100%; margin: 0 0 14px; font-size:13px; height:42px; border-radius:8px;">
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display:block; margin-bottom:4px;">Panggilan / Sapaan</label>
                <select id="editPanggilan" class="swal2-input" style="width: 100%; margin: 0 0 14px; font-size:13px; height:42px; border-radius:8px;">
                    ${optionsHtml}
                </select>
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display:block; margin-bottom:4px;">Nomor WhatsApp</label>
                <input type="text" id="editPhone" value="${phone}" class="swal2-input" style="width: 100%; margin: 0; font-size:13px; height:42px; border-radius:8px;">
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
function deleteEmployee(idOrEl, name) {
    let id = idOrEl;
    if (typeof idOrEl === 'object' && idOrEl !== null && idOrEl.dataset) {
        id = idOrEl.dataset.id;
        name = idOrEl.dataset.name;
    }

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
const setDefaultsBtn = document.getElementById('setDefaultsBtn');
if (setDefaultsBtn) {
    setDefaultsBtn.addEventListener('click', function() {
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
                fetch("{{ route('admin.set-default-times') }}", {
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
}
/* Modal Ganti Password Admin */
function openChangePasswordModal() {
    Swal.fire({
        title: 'Ganti Password Admin',
        html: `
            <div style="text-align: left; padding: 6px 0;">
                <p style="font-size: 12px; color: #64748b; margin: 0 0 14px;">
                    Demi keamanan akun sistem, gunakan kombinasi minimal 8 karakter dengan huruf dan angka.
                </p>
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Password Saat Ini:</label>
                <input type="password" id="currentPasswordInput" class="form-control" style="width: 100%; margin: 0 0 12px; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box;" placeholder="Masukkan password saat ini...">
                
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Password Baru (Min. 8 Karakter):</label>
                <input type="password" id="newPasswordInput" class="form-control" style="width: 100%; margin: 0 0 12px; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box;" placeholder="Masukkan password baru...">
                
                <label style="font-size: 12px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Konfirmasi Password Baru:</label>
                <input type="password" id="confirmPasswordInput" class="form-control" style="width: 100%; margin: 0; padding: 10px 12px; font-size: 13px; border-radius: 8px; border: 1px solid #cbd5e1; box-sizing: border-box;" placeholder="Ulangi password baru...">
            </div>
        `,
        width: '460px',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Simpan Password Baru',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const currentPass = document.getElementById('currentPasswordInput').value;
            const newPass = document.getElementById('newPasswordInput').value;
            const confirmPass = document.getElementById('confirmPasswordInput').value;

            if (!currentPass) {
                Swal.showValidationMessage('Password saat ini wajib diisi!');
                return false;
            }
            if (!newPass || newPass.length < 8) {
                Swal.showValidationMessage('Password baru minimal harus 8 karakter!');
                return false;
            }
            if (newPass !== confirmPass) {
                Swal.showValidationMessage('Konfirmasi password baru tidak cocok!');
                return false;
            }

            return {
                current_password: currentPass,
                new_password: newPass,
                new_password_confirmation: confirmPass
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan password...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.profile.password") }}';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="current_password" value="${result.value.current_password}">
                <input type="hidden" name="new_password" value="${result.value.new_password}">
                <input type="hidden" name="new_password_confirmation" value="${result.value.new_password_confirmation}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* Auto-refresh outbox secara otomatis jika sedang ada pengiriman aktif */
(function initOutboxAutoRefresh() {
    const activeQueue = {{ ($outboxStats['pending'] ?? 0) + ($outboxStats['processing'] ?? 0) }};
    if (activeQueue > 0) {
        console.log('[Outbox Monitor] Ada ' + activeQueue + ' pesan dalam proses. Memperbarui status dalam 8 detik...');
        setTimeout(function() {
            location.reload();
        }, 8000);
    }
})();

/* ==========================================================================
   UI/UX IMPROVEMENTS: ACTION DROPDOWN, VARIABLE INSERTION, & LIVE PREVIEW
   ========================================================================== */

/* Toggle Dropdown Menu [ ⋮ ] pada Baris Pegawai */
function toggleEmpDropdown(button, event) {
    event.stopPropagation();
    const wrap = button.closest('.action-dropdown-wrap');
    if (!wrap) return;
    const wasOpen = wrap.classList.contains('open');

    // Tutup semua dropdown lain yang sedang terbuka
    document.querySelectorAll('.action-dropdown-wrap.open').forEach(el => el.classList.remove('open'));

    if (!wasOpen) {
        wrap.classList.add('open');
    }
}

// Tutup dropdown otomatis jika user mengklik di luar menu
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown-wrap')) {
        document.querySelectorAll('.action-dropdown-wrap.open').forEach(el => el.classList.remove('open'));
    }
});

/* Click-to-Insert Template Variable Pill */
function insertVariable(textareaName, variableTag) {
    const textarea = document.querySelector(`textarea[name="${textareaName}"]`);
    if (!textarea) return;

    const startPos = textarea.selectionStart;
    const endPos = textarea.selectionEnd;
    const value = textarea.value;

    textarea.value = value.substring(0, startPos) + variableTag + value.substring(endPos, value.length);
    textarea.focus();
    textarea.selectionStart = startPos + variableTag.length;
    textarea.selectionEnd = startPos + variableTag.length;

    updateLivePreview();
}

/* WhatsApp Live Chat Preview System */
let activePreviewType = 'pre_checkin';

function switchPreviewTab(type) {
    activePreviewType = type;
    document.querySelectorAll('.wa-switcher-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-type') === type);
    });
    updateLivePreview();
}

function updateLivePreview() {
    let textareaName = 'template_pre_checkin';
    let targetTime = document.querySelector('input[name="check_in_time"]')?.value || '07:30';
    if (activePreviewType === 'pre_checkout') {
        textareaName = 'template_pre_checkout';
        targetTime = document.querySelector('input[name="check_out_time"]')?.value || '16:00';
    } else if (activePreviewType === 'broadcast') {
        textareaName = 'template_broadcast';
    }

    const textareaEl = document.querySelector(`textarea[name="${textareaName}"]`);
    const templateVal = textareaEl ? textareaEl.value : '';
    const org = document.querySelector('input[name="organization_name"]')?.value || 'BPS Kabupaten Karanganyar';
    const minutes = document.querySelector('input[name="pre_reminder_minutes"]')?.value || '30';
    const closing = document.querySelector('input[name="closing_word"]')?.value || 'Semangat kerja!';

    let rendered = templateVal
        .replace(/{name}/g, 'Budi Santoso, S.Stat.')
        .replace(/{target_time}/g, targetTime)
        .replace(/{minutes_left}/g, minutes)
        .replace(/{organization}/g, org)
        .replace(/{kata}/g, closing);

    // Tambahkan contoh pantun ramah jika preview pengingat absensi
    if (activePreviewType !== 'broadcast' && !rendered.includes('Pantun')) {
        rendered += '\n\nPergi ke pasar membeli durian,\nJangan lupa beli buah manggis.\nAyo kawan kita presensi harian,\nBekerja giat dengan senyum manis.';
    }

    const previewEl = document.getElementById('waPreviewText');
    if (previewEl) {
        previewEl.textContent = rendered;
    }

    const timeEl = document.getElementById('waPreviewTime');
    if (timeEl) {
        const now = new Date();
        timeEl.textContent = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }
}

// Inisialisasi live preview saat halaman siap
document.addEventListener('DOMContentLoaded', function() {
    updateLivePreview();
});
</script>

@endsection