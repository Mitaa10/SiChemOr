<?php
$conn = mysqli_connect("localhost", "root", "", "database chemor");
$files_guru = [];
$files_siswa = [];
if ($conn) {
    $r1 = mysqli_query($conn, "SELECT * FROM uploads WHERE tipe_user='guru' ORDER BY tanggal DESC");
    while ($row = mysqli_fetch_assoc($r1)) $files_guru[] = $row;
    $r2 = mysqli_query($conn, "SELECT * FROM uploads WHERE tipe_user='siswa' ORDER BY tanggal DESC");
    while ($row = mysqli_fetch_assoc($r2)) $files_siswa[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SiChemOr — Senyawa Turunan Alkana</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Source+Sans+3:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
  :root {
    --ink: #f4f6fb;
    --ink-2: #eaeff8;
    --ink-3: #dde4f0;
    --ink-4: #e8edf8;
    --border: rgba(30,58,138,0.14);
    --border-strong: rgba(30,58,138,0.28);
    --text-primary: #1a2340;
    --text-secondary: #4a5a80;
    --text-dim: #8fa3c8;
    --accent: #1e3a8a;
    --accent-2: #1e40af;
    --accent-dim: rgba(30,58,138,0.08);
    --accent-glow: 0 0 24px rgba(30,58,138,0.22);
    --emerald: #059669;
    --emerald-dim: rgba(5,150,105,0.10);
    --blue: #2563eb;
    --blue-dim: rgba(37,99,235,0.10);
    --red: #dc2626;
    --surface: #ffffff;
    --surface-2: #f8faff;
    --surface-3: #f0f4fc;
    --surface-hover: #e6edf8;
    --shadow: 0 4px 20px rgba(30,58,138,0.10);
    --shadow-lg: 0 8px 40px rgba(30,58,138,0.16);
    --radius: 10px;
    --radius-lg: 14px;
  }

  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: 'Source Sans 3', sans-serif;
    background: var(--ink);
    color: var(--text-primary);
    min-height: 100vh;
    font-size: 15px;
  }

  /* ===== NAVY LOGIN BACKGROUND ===== */
  #login-screen {
    background: linear-gradient(135deg, #0f1e45 0%, #1e3a8a 40%, #1e40af 70%, #1d4ed8 100%) !important;
  }

  /* Decorative login blobs */
  #login-screen::before {
    content: '';
    position: absolute; inset: 0;
    background:
      radial-gradient(circle 400px at 75% 50%, rgba(255,255,255,0.05) 0%, transparent 70%),
      radial-gradient(circle 200px at 20% 80%, rgba(147,197,253,0.12) 0%, transparent 60%),
      radial-gradient(circle 150px at 85% 15%, rgba(147,197,253,0.08) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
  }

  /* ===== CANVAS 3D ===== */
  #mol-canvas {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 1;
  }

  .screen { display:none; position:relative; z-index:1; min-height:100vh; }
  .screen.active { display:flex; }

  /* ===== LOGIN ===== */
  #login-screen {
    align-items:center; justify-content:center; flex-direction:column;
    min-height:100vh; padding:2rem; position:relative;
  }

  .login-veil {
    position:absolute; inset:0;
    background: radial-gradient(ellipse 900px 700px at 50% 50%, rgba(255,255,255,0.06) 0%, transparent 70%);
    pointer-events:none;
  }

  .login-brand {
    text-align:center; margin-bottom:2.5rem;
    animation: fadeInDown 0.8s cubic-bezier(0.16,1,0.3,1);
    position:relative;
  }

  .brand-logo-ring {
    width:90px; height:90px; margin:0 auto 1.4rem;
    border:2px solid rgba(255,255,255,0.70);
    border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    position:relative;
    box-shadow: 0 0 30px rgba(255,255,255,0.18), inset 0 0 30px rgba(255,255,255,0.06);
    background: rgba(255,255,255,0.08);
  }
  .brand-logo-ring::before {
    content:'';
    position:absolute; inset:-6px;
    border:1px solid rgba(255,255,255,0.22);
    border-radius:50%;
    animation: spin-slow 20s linear infinite;
  }
  .brand-logo-ring::after {
    content:'';
    position:absolute; inset:-12px;
    border:1px dashed rgba(255,255,255,0.12);
    border-radius:50%;
    animation: spin-slow 35s linear infinite reverse;
  }
  .brand-logo-inner { font-size:2rem; filter:drop-shadow(0 0 8px rgba(255,255,255,0.7)); }

  .brand-wordmark {
    font-family:'Playfair Display', serif;
    font-size:3rem; font-weight:900;
    letter-spacing:0.05em;
    color: #ffffff;
    text-shadow: 0 2px 30px rgba(255,255,255,0.25);
    line-height:1;
  }
  .brand-wordmark span { color: #93c5fd; }

  .brand-tagline {
    font-size:0.72rem; letter-spacing:0.22em; text-transform:uppercase;
    color: rgba(255,255,255,0.55); margin-top:0.6rem;
    font-family:'Source Sans 3',sans-serif; font-weight:600;
  }

  /* === LOGIN UNIFIED CARD === */
  .login-unified {
    width:420px; max-width:96vw;
    background: #ffffff;
    border:1px solid rgba(30,58,138,0.10);
    border-radius:16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.30), 0 4px 20px rgba(0,0,0,0.20);
    overflow:hidden;
    animation: fadeInUp 0.8s cubic-bezier(0.16,1,0.3,1) 0.15s both;
  }

  .login-unified-header {
    padding:1.6rem 1.8rem 1.2rem;
    border-bottom:1px solid rgba(30,58,138,0.10);
    background: #f8faff;
  }
  .login-unified-header h2 {
    font-family:'Playfair Display',serif;
    font-size:1.1rem; font-weight:700; color:#1a2340;
    margin-bottom:0.25rem;
  }
  .login-unified-header p { font-size:0.78rem; color:#4a5a80; }

  .login-body { padding:1.6rem 1.8rem; }

  /* Role selector */
  .role-selector { display:grid; grid-template-columns:1fr 1fr; gap:0.7rem; margin-bottom:1.4rem; }
  .role-btn {
    padding:1rem 0.8rem; border-radius:var(--radius);
    border:1.5px solid rgba(30,58,138,0.18);
    background:#f8faff;
    cursor:pointer; transition:all 0.2s; text-align:center;
    position:relative; overflow:hidden;
  }
  .role-btn::before {
    content:''; position:absolute; inset:0;
    background: linear-gradient(135deg, rgba(30,58,138,0.06), transparent);
    opacity:0; transition:opacity 0.2s;
  }
  .role-btn:hover::before { opacity:1; }
  .role-btn:hover { border-color:rgba(30,58,138,0.45); }
  .role-btn.selected { border-color:#1e3a8a; background:rgba(30,58,138,0.07); box-shadow:0 0 0 3px rgba(30,58,138,0.12); }
  .role-btn.selected::before { opacity:1; }
  .role-btn-icon { font-size:1.6rem; margin-bottom:0.4rem; display:block; }
  .role-btn-label { font-size:0.8rem; font-weight:700; color:#1a2340; font-family:'Source Sans 3',sans-serif; }
  .role-btn-sub { font-size:0.65rem; color:#4a5a80; margin-top:0.15rem; }

  /* Password field */
  .pw-section { margin-bottom:1.2rem; transition:all 0.3s; }
  .pw-section.hidden { display:none; }
  .field-label { font-size:0.72rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#4a5a80; display:block; margin-bottom:0.4rem; }
  .field-wrap { position:relative; }
  .field-input {
    width:100%; padding:0.72rem 2.5rem 0.72rem 0.9rem;
    background: #f4f6fb;
    border:1.5px solid rgba(30,58,138,0.18);
    border-radius:var(--radius);
    color:#1a2340;
    font-size:0.88rem; font-family:'Source Sans 3',sans-serif;
    outline:none; transition:border-color 0.2s;
    letter-spacing:0.05em;
  }
  .field-input:focus { border-color:rgba(30,58,138,0.55); box-shadow:0 0 0 3px rgba(30,58,138,0.08); }
  .field-toggle {
    position:absolute; right:0.8rem; top:50%; transform:translateY(-50%);
    background:none; border:none; color:#8fa3c8; cursor:pointer; font-size:0.82rem;
    padding:0.2rem;
  }
  .field-toggle:hover { color:#4a5a80; }
  .field-err { font-size:0.7rem; color:var(--red); margin-top:0.3rem; display:none; }

  .btn-enter {
    width:100%; padding:0.78rem 1rem;
    background: linear-gradient(135deg, #1e3a8a, #1e40af);
    border:none; border-radius:var(--radius);
    color:#ffffff; font-size:0.88rem; font-weight:700;
    cursor:pointer; transition:all 0.2s;
    font-family:'Source Sans 3',sans-serif;
    letter-spacing:0.05em; text-transform:uppercase;
    box-shadow: 0 4px 14px rgba(30,58,138,0.30);
  }
  .btn-enter:hover { box-shadow:0 6px 22px rgba(30,58,138,0.45); transform:translateY(-1px); }
  .btn-enter:disabled { opacity:0.45; cursor:not-allowed; transform:none; }

  .login-hint { display:none; }
    /* ===== COLLAPSIBLE SIDEBAR ===== */
    .sidebar {
    transition: width 0.25s ease, transform 0.25s ease;
    width: 256px;
    overflow-x: hidden;
    position: relative;
    }
    .sidebar.collapsed {
    width: 72px;
    }
    .sidebar.collapsed .sidebar-brand-text,
    .sidebar.collapsed .user-badge .u-name,
    .sidebar.collapsed .user-badge .u-role,
    .sidebar.collapsed .role-badge,
    .sidebar.collapsed .nav-item span:not(.nav-icon),
    .sidebar.collapsed .nav-section,
    .sidebar.collapsed .sidebar-footer span {
    display: none;
    }
    .sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 0.6rem 0;
    }
    .sidebar.collapsed .nav-icon {
    font-size: 1.2rem;
    margin: 0;
    }
    .sidebar.collapsed .user-badge {
    justify-content: center;
    padding: 0.7rem 0;
    }
    .sidebar.collapsed .avatar {
    margin: 0 auto;
    }
    .sidebar.collapsed .sidebar-brand {
    justify-content: center;
    padding: 1.2rem 0;
    }
    .sidebar.collapsed .sidebar-brand-icon {
    margin: 0 auto;
    }
    .sidebar.collapsed .sidebar-footer button {
    padding: 0.55rem 0;
    text-align: center;
    }
    .sidebar.collapsed .sidebar-footer button::before {
    content: '🚪';
    font-size: 1.1rem;
    }
    .sidebar.collapsed .sidebar-footer button span {
    display: none;
    }

    .sidebar-toggle {
    position: absolute;
    top: 1rem;
    right: -12px;
    width: 24px;
    height: 24px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 100;
    font-size: 0.8rem;
    color: var(--text-secondary);
    transition: all 0.2s;
    box-shadow: var(--shadow);
    }
    .sidebar-toggle:hover {
    background: var(--accent-dim);
    color: var(--accent);
    transform: scale(1.1);
    }
    .sidebar.collapsed .sidebar-toggle {
    transform: rotate(180deg);
    }

    /* Responsive untuk mobile */
    @media (max-width: 768px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 1000;
        transform: translateX(0);
        box-shadow: var(--shadow-lg);
    }
    .sidebar.collapsed {
        transform: translateX(-100%);
        width: 0;
    }
    .sidebar:not(.collapsed) {
        width: 256px;
    }
    .sidebar-toggle {
        right: -12px;
    }
    .sidebar.collapsed .sidebar-toggle {
        right: -36px;
        background: var(--accent);
        color: white;
    }
    }
  /* ===== PROFILE SCREEN ===== */
  #profile-screen {
    align-items:center; justify-content:center; flex-direction:column;
    position:fixed; inset:0; z-index:500;
    background: rgba(10,20,50,0.55); backdrop-filter:blur(12px);
  }

  .profile-card {
    width:460px; max-width:96vw;
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:16px;
    box-shadow: var(--shadow-lg);
    overflow:hidden;
    animation: fadeInUp 0.4s cubic-bezier(0.16,1,0.3,1);
  }
  .profile-card-header {
    padding:1.4rem 1.6rem;
    border-bottom:1px solid var(--border);
    background:var(--surface-2);
    display:flex; align-items:center; gap:0.8rem;
  }
  .profile-card-icon { font-size:1.6rem; }
  .profile-card-header h2 { font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; }
  .guru-card h2 { color: var(--accent); }
  .siswa-card h2 { color: var(--emerald); }
  .profile-card-header p { font-size:0.72rem; color:var(--text-secondary); margin-top:0.15rem; }
  .profile-card-body { padding:1.6rem; }
  .profile-card-footer { padding:1rem 1.6rem; border-top:1px solid var(--border); display:flex; gap:0.7rem; background:var(--surface-2); }

  .form-group { margin-bottom:1rem; }
  .form-label { font-size:0.68rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-secondary); display:block; margin-bottom:0.35rem; }
  .form-input {
    width:100%; background:var(--ink-4); border:1.5px solid var(--border);
    border-radius:var(--radius); padding:0.65rem 0.9rem; color:var(--text-primary);
    font-size:0.85rem; font-family:'Source Sans 3',sans-serif; outline:none; transition:all 0.2s;
  }
  .form-input:focus { border-color:rgba(30,58,138,0.50); }
  .form-select {
    width:100%; background:var(--ink-4); border:1.5px solid var(--border);
    border-radius:var(--radius); padding:0.65rem 0.9rem; color:var(--text-primary);
    font-size:0.85rem; font-family:'Source Sans 3',sans-serif; outline:none;
  }
  .form-textarea {
    width:100%; background:var(--ink-4); border:1.5px solid var(--border);
    border-radius:var(--radius); padding:0.65rem 0.9rem; color:var(--text-primary);
    font-size:0.85rem; font-family:'Source Sans 3',sans-serif; outline:none;
    resize:vertical; min-height:78px; transition:all 0.2s;
  }
  .form-textarea:focus { border-color:rgba(30,58,138,0.50); }

  /* ===== APP SCREEN ===== */
  #app-screen { flex-direction:row; align-items:stretch; }

  /* SIDEBAR */
  .sidebar {
    width:256px; min-height:100vh;
    background:var(--surface);
    border-right:1px solid var(--border);
    display:flex; flex-direction:column;
    position:sticky; top:0; height:100vh; overflow-y:auto; flex-shrink:0;
  }

  .sidebar-brand {
    padding:1.4rem 1.2rem 1.2rem;
    border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:0.75rem;
  }
  .sidebar-brand-icon {
    width:36px; height:36px; border-radius:8px;
    background:var(--accent-dim); border:1px solid rgba(30,58,138,0.25);
    display:flex; align-items:center; justify-content:center; font-size:1rem;
    flex-shrink:0;
  }
  .sidebar-brand-text { }
  .sidebar-brand-name {
    font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:900;
    color:var(--text-primary); letter-spacing:0.02em; line-height:1;
  }
  .sidebar-brand-name span { color:var(--accent); }
  .sidebar-brand-sub { font-size:0.6rem; color:var(--text-dim); letter-spacing:0.12em; text-transform:uppercase; margin-top:0.15rem; }

  .user-badge {
    margin:0.9rem 0.9rem;
    padding:0.7rem 0.85rem;
    background:var(--surface-3);
    border:1px solid var(--border);
    border-radius:var(--radius);
    display:flex; align-items:center; gap:0.6rem;
  }
  .avatar {
    width:34px; height:34px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:0.88rem; flex-shrink:0;
  }
  .avatar.guru-av { background:var(--accent-dim); border:1px solid rgba(30,58,138,0.25); }
  .avatar.siswa-av { background:var(--emerald-dim); border:1px solid rgba(63,185,80,0.25); }
  .u-name { font-size:0.8rem; font-weight:700; color:var(--text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:110px; }
  .u-role { font-size:0.64rem; color:var(--text-secondary); }
  .role-badge {
    font-size:0.56rem; padding:0.12rem 0.45rem; border-radius:4px;
    font-weight:700; letter-spacing:0.1em; text-transform:uppercase; margin-left:auto; flex-shrink:0;
  }
  .role-badge.guru { background:var(--accent-dim); color:var(--accent); border:1px solid rgba(30,58,138,0.22); }
  .role-badge.siswa { background:var(--emerald-dim); color:var(--emerald); border:1px solid rgba(63,185,80,0.22); }

  .nav-section { padding:1rem 1.2rem 0.3rem; font-size:0.58rem; color:var(--text-dim); letter-spacing:0.18em; text-transform:uppercase; font-weight:700; }

  .nav-item {
    display:flex; align-items:center; gap:0.65rem;
    padding:0.6rem 1.2rem; cursor:pointer; transition:all 0.15s;
    border-left:2px solid transparent; font-size:0.82rem; color:var(--text-secondary);
    position:relative; user-select:none;
  }
  .nav-item:hover { color:var(--text-primary); background:var(--surface-3); }
  .nav-item.active { color:var(--accent); border-left-color:var(--accent); background:var(--accent-dim); font-weight:600; }
  .nav-icon { font-size:0.9rem; width:18px; text-align:center; }

  .nav-badge {
    margin-left:auto; background:var(--red); color:#fff;
    font-size:0.58rem; font-weight:700; padding:0.06rem 0.4rem;
    border-radius:20px; min-width:18px; text-align:center; line-height:1.6;
  }
  .nav-badge.emerald { background:var(--emerald); }

  .sidebar-footer { margin-top:auto; padding:0.9rem 1rem; border-top:1px solid var(--border); }
  .btn-logout {
    width:100%; padding:0.55rem; background:transparent;
    border:1px solid var(--border); border-radius:var(--radius);
    color:var(--text-secondary); font-size:0.76rem; cursor:pointer; transition:all 0.2s;
    font-family:'Source Sans 3',sans-serif; font-weight:600;
  }
  .btn-logout:hover { border-color:rgba(248,81,73,0.40); color:var(--red); background:rgba(248,81,73,0.06); }

  /* MAIN */
  .main-content { flex:1; overflow-y:auto; overflow-x:hidden; background:var(--ink); }

  .top-bar {
    display:flex; align-items:center; padding:1rem 1.8rem;
    border-bottom:1px solid var(--border); background:var(--surface);
    position:sticky; top:0; z-index:10; gap:1rem;
  }
  .page-title { font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:700; color:var(--text-primary); }
  .page-subtitle { font-size:0.68rem; color:var(--text-secondary); margin-top:0.06rem; letter-spacing:0.05em; }
  .top-actions { margin-left:auto; display:flex; gap:0.6rem; align-items:center; }

  .btn-primary {
    padding:0.52rem 1.1rem;
    background:linear-gradient(135deg, var(--accent), var(--accent-2));
    border:none; border-radius:var(--radius); color:var(--ink);
    font-size:0.76rem; font-weight:700; cursor:pointer; transition:all 0.2s;
    font-family:'Source Sans 3',sans-serif;
    letter-spacing:0.05em;
    display:flex; align-items:center; gap:0.38rem;
  }
  .btn-primary:hover { box-shadow:var(--accent-glow); transform:translateY(-1px); }

  .btn-secondary {
    padding:0.52rem 1.1rem;
    background:var(--surface-3); border:1px solid var(--border);
    border-radius:var(--radius); color:var(--text-primary);
    font-size:0.76rem; font-weight:600; cursor:pointer; transition:all 0.2s;
    font-family:'Source Sans 3',sans-serif;
    display:flex; align-items:center; gap:0.38rem;
  }
  .btn-secondary:hover { border-color:rgba(30,58,138,0.40); color:var(--accent); }

  .content-area { padding:1.6rem 1.8rem; }

  /* STATS */
  .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:0.9rem; margin-bottom:1.8rem; }
  .stat-card {
    padding:1.1rem 1.2rem; background:var(--surface);
    border:1px solid var(--border); border-radius:var(--radius-lg);
    position:relative; overflow:hidden; transition:all 0.2s;
  }
  .stat-card::after { content:''; position:absolute; top:0; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,var(--accent),transparent); opacity:0.7; }
  .stat-card:hover { transform:translateY(-2px); border-color:var(--border-strong); }
  .stat-icon { font-size:1.2rem; margin-bottom:0.5rem; }
  .stat-value { font-family:'JetBrains Mono',monospace; font-size:1.7rem; font-weight:600; color:var(--accent); }
  .stat-label { font-size:0.68rem; color:var(--text-secondary); margin-top:0.15rem; letter-spacing:0.05em; text-transform:uppercase; }
  .stat-change { font-size:0.64rem; color:var(--emerald); margin-top:0.35rem; }

  /* SECTION HEADER */
  .section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.9rem; }
  .section-title {
    font-family:'Playfair Display',serif; font-size:0.9rem; font-weight:700;
    color:var(--accent); display:flex; align-items:center; gap:0.5rem;
  }
  .section-title::before { content:''; width:3px; height:14px; background:var(--accent); border-radius:2px; flex-shrink:0; }

  /* MODULE */
  .module-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:0.8rem; margin-bottom:1.8rem; }
  .module-card {
    padding:1.3rem; background:var(--surface);
    border:1px solid var(--border); border-radius:var(--radius-lg);
    cursor:pointer; transition:all 0.2s; position:relative; overflow:hidden;
  }
  .module-card:hover { border-color:rgba(30,58,138,0.35); transform:translateY(-3px); box-shadow:var(--shadow); }
  .module-icon { width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; margin-bottom:0.8rem; background:var(--accent-dim); border:1px solid rgba(30,58,138,0.18); }
  .module-type { font-size:0.6rem; color:var(--text-dim); letter-spacing:0.12em; text-transform:uppercase; margin-bottom:0.28rem; }
  .module-name { font-size:0.85rem; font-weight:700; margin-bottom:0.25rem; color:var(--text-primary); }
  .module-desc { font-size:0.7rem; color:var(--text-secondary); line-height:1.55; }
  .module-status { position:absolute; top:0.8rem; right:0.8rem; font-size:0.58rem; padding:0.14rem 0.5rem; border-radius:4px; font-weight:700; letter-spacing:0.06em; }
  .status-filled { background:var(--emerald-dim); color:var(--emerald); border:1px solid rgba(63,185,80,0.25); }
  .status-empty { background:rgba(100,100,120,0.12); color:var(--text-dim); border:1px solid rgba(100,100,120,0.18); }
  .module-actions { display:flex; gap:0.4rem; margin-top:0.8rem; padding-top:0.8rem; border-top:1px solid var(--border); }
  .btn-icon {
    width:28px; height:28px; border-radius:6px; border:1px solid var(--border);
    background:var(--surface-3); color:var(--text-secondary); cursor:pointer;
    display:flex; align-items:center; justify-content:center; font-size:0.75rem; transition:all 0.15s;
  }
  .btn-icon:hover { border-color:rgba(30,58,138,0.40); color:var(--accent); }
  .btn-icon.danger:hover { border-color:rgba(248,81,73,0.40); color:var(--red); background:rgba(248,81,73,0.06); }

  /* VIDEO */
  .video-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:0.8rem; margin-bottom:1.8rem; }
  .video-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; transition:all 0.2s; }
  .video-card:hover { border-color:rgba(88,166,255,0.35); transform:translateY(-2px); box-shadow:var(--shadow); }
  .video-thumb { width:100%; height:130px; background:linear-gradient(135deg,#dce6f5,#eaeff8); display:flex; align-items:center; justify-content:center; position:relative; border-bottom:1px solid var(--border); }
  .video-placeholder { display:flex; flex-direction:column; align-items:center; gap:0.3rem; color:var(--text-dim); font-size:0.68rem; }
  .yt-icon { font-size:2rem; }
  .play-btn { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:42px; height:42px; border-radius:50%; background:rgba(88,166,255,0.15); border:1.5px solid rgba(88,166,255,0.50); display:flex; align-items:center; justify-content:center; font-size:1rem; cursor:pointer; transition:all 0.2s; }
  .play-btn:hover { background:rgba(88,166,255,0.30); box-shadow:0 0 16px rgba(88,166,255,0.25); transform:translate(-50%,-50%) scale(1.1); }
  .video-info { padding:0.85rem; }
  .video-title { font-size:0.82rem; font-weight:700; margin-bottom:0.2rem; }
  .video-sub { font-size:0.68rem; color:var(--text-secondary); }

  /* ZOOM */
  .zoom-card { padding:1.4rem; background:var(--surface-2); border:1px solid rgba(88,166,255,0.18); border-radius:var(--radius-lg); display:flex; align-items:center; gap:1.2rem; margin-bottom:1.8rem; transition:all 0.2s; }
  .zoom-card:hover { border-color:rgba(88,166,255,0.35); }
  .zoom-icon { font-size:2.2rem; flex-shrink:0; }
  .zoom-info { flex:1; }
  .zoom-title { font-size:0.92rem; font-weight:700; margin-bottom:0.2rem; }
  .zoom-meta { font-size:0.7rem; color:var(--text-secondary); }
  .zoom-status { font-size:0.7rem; color:var(--emerald); margin-top:0.2rem; display:flex; align-items:center; gap:0.3rem; }
  .dot-live { width:6px; height:6px; border-radius:50%; background:var(--emerald); animation:pulse 2s infinite; flex-shrink:0; }
  .btn-join { padding:0.65rem 1.5rem; background:linear-gradient(135deg,#79baff,var(--blue)); border:none; border-radius:var(--radius); color:var(--ink); font-size:0.8rem; font-weight:700; cursor:pointer; transition:all 0.2s; font-family:'Source Sans 3',sans-serif; display:flex; align-items:center; gap:0.4rem; }
  .btn-join:hover { box-shadow:0 0 18px rgba(88,166,255,0.30); transform:translateY(-1px); }

  /* LAB */
  .lab-grid { display:grid; gap:0.8rem; margin-bottom:1.8rem; }
  .lab-card { padding:1.4rem; background:var(--surface-2); border:1px solid rgba(63,185,80,0.18); border-radius:var(--radius-lg); display:flex; align-items:center; gap:1rem; cursor:pointer; transition:all 0.2s; text-decoration:none; }
  .lab-card:hover { border-color:rgba(63,185,80,0.45); box-shadow:0 0 18px rgba(63,185,80,0.12); transform:translateY(-2px); }
  .lab-icon { font-size:1.8rem; }
  .lab-name { font-size:0.88rem; font-weight:700; color:var(--emerald); }
  .lab-desc { font-size:0.68rem; color:var(--text-secondary); margin-top:0.15rem; }

  /* ASSIGNMENT */
  .assignment-card { padding:1.4rem; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); transition:all 0.2s; margin-bottom:0.8rem; }
  .assignment-card:hover { border-color:var(--border-strong); }
  .assign-header { display:flex; align-items:flex-start; gap:0.8rem; margin-bottom:0.8rem; }
  .assign-icon { font-size:1.3rem; }
  .assign-title { font-size:0.85rem; font-weight:700; }
  .assign-sub { font-size:0.68rem; color:var(--text-secondary); margin-top:0.15rem; }
  .assign-deadline { padding:0.4rem 0.65rem; background:rgba(30,58,138,0.08); border:1px solid rgba(30,58,138,0.15); border-radius:6px; font-size:0.68rem; color:var(--accent); margin-bottom:0.8rem; }
  .upload-area { border:1.5px dashed rgba(30,58,138,0.20); border-radius:var(--radius); padding:1.3rem; text-align:center; cursor:pointer; transition:all 0.2s; background:var(--surface-3); }
  .upload-area:hover { border-color:rgba(30,58,138,0.45); background:var(--accent-dim); }
  .upload-icon { font-size:1.3rem; margin-bottom:0.35rem; }
  .upload-text { font-size:0.72rem; color:var(--text-secondary); }
  .upload-types { font-size:0.62rem; color:var(--text-dim); margin-top:0.18rem; }

  /* PROGRESS */
  .progress-bar { height:4px; background:rgba(30,58,138,0.10); border-radius:3px; margin-top:0.6rem; overflow:hidden; }
  .progress-fill { height:100%; border-radius:3px; background:linear-gradient(90deg,var(--emerald),#2ea043); transition:width 0.5s ease; }
  .progress-text { font-size:0.62rem; color:var(--emerald); margin-top:0.2rem; font-family:'JetBrains Mono',monospace; }

  /* ===== DISCUSSION ===== */
  .discussion-container { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; margin-bottom:1.8rem; }
  .disc-header { padding:1rem 1.2rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.6rem; background:var(--surface-2); }
  .disc-title { font-family:'Playfair Display',serif; font-size:0.85rem; color:var(--accent); font-weight:700; }
  .disc-count { font-size:0.65rem; color:var(--text-secondary); margin-left:auto; }
  .messages { padding:1.2rem; display:flex; flex-direction:column; gap:0.9rem; max-height:360px; overflow-y:auto; }
  .message { display:flex; gap:0.6rem; animation:fadeInUp 0.3s ease; }
  .msg-avatar { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:0.78rem; flex-shrink:0; border:1px solid var(--border); }
  .msg-body { flex:1; }
  .msg-header { display:flex; align-items:center; gap:0.4rem; margin-bottom:0.22rem; }
  .msg-name { font-size:0.78rem; font-weight:700; }
  .msg-name.guru { color:var(--accent); }
  .msg-name.siswa { color:var(--emerald); }
  .msg-role-tag { font-size:0.54rem; padding:0.06rem 0.35rem; border-radius:3px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; }
  .msg-role-tag.guru { background:var(--accent-dim); color:var(--accent); border:1px solid rgba(30,58,138,0.20); }
  .msg-role-tag.siswa { background:var(--emerald-dim); color:var(--emerald); border:1px solid rgba(63,185,80,0.20); }
  .msg-time { font-size:0.6rem; color:var(--text-dim); margin-left:auto; font-family:'JetBrains Mono',monospace; }
  .msg-text { font-size:0.78rem; color:var(--text-secondary); line-height:1.6; padding:0.65rem 0.85rem; background:var(--surface-3); border-radius:0 8px 8px 8px; border:1px solid var(--border); }
  .msg-actions { display:flex; gap:0.4rem; margin-top:0.28rem; }
  .msg-action-btn { font-size:0.64rem; color:var(--text-dim); cursor:pointer; background:none; border:none; font-family:'Source Sans 3',sans-serif; transition:color 0.15s; }
  .msg-action-btn:hover { color:var(--accent); }
  .disc-input { padding:0.85rem 1.2rem; border-top:1px solid var(--border); display:flex; gap:0.6rem; align-items:flex-end; background:var(--surface-2); }
  .input-field { flex:1; background:var(--ink-4); border:1.5px solid var(--border); border-radius:8px; padding:0.62rem 0.85rem; color:var(--text-primary); font-size:0.78rem; font-family:'Source Sans 3',sans-serif; resize:none; outline:none; transition:border-color 0.2s; min-height:38px; }
  .input-field:focus { border-color:rgba(30,58,138,0.45); }
  .input-field::placeholder { color:var(--text-dim); }
  .btn-send { width:38px; height:38px; border-radius:8px; background:linear-gradient(135deg,var(--accent),var(--accent-2)); border:none; color:var(--ink); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.88rem; transition:all 0.2s; flex-shrink:0; }
  .btn-send:hover { box-shadow:var(--accent-glow); transform:scale(1.05); }
  .disc-empty { text-align:center; padding:2rem 1rem; color:var(--text-dim); }
  .disc-empty-icon { font-size:1.8rem; margin-bottom:0.4rem; opacity:0.35; }
  .disc-empty-text { font-size:0.75rem; }

  .guru-banner { padding:0.6rem 0.9rem; background:rgba(30,58,138,0.07); border:1px solid rgba(30,58,138,0.18); border-radius:6px; font-size:0.68rem; color:var(--accent); display:flex; align-items:center; gap:0.4rem; margin-bottom:0.9rem; }

  /* TABS */
  .tab-nav { display:flex; gap:0.18rem; background:var(--surface-3); border:1px solid var(--border); border-radius:8px; padding:0.2rem; width:fit-content; margin-bottom:1.4rem; }
  .tab-btn { padding:0.42rem 1rem; border:none; border-radius:6px; background:transparent; color:var(--text-secondary); font-size:0.78rem; font-weight:600; cursor:pointer; transition:all 0.15s; font-family:'Source Sans 3',sans-serif; }
  .tab-btn.active { background:var(--surface); color:var(--accent); border:1px solid rgba(30,58,138,0.20); }

  /* PRESENSI */
  .presensi-card { background:var(--surface); border:1px solid rgba(63,185,80,0.18); border-radius:var(--radius-lg); overflow:hidden; margin-bottom:1.8rem; }
  .presensi-header { padding:1rem 1.2rem; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:0.6rem; background:var(--surface-2); }
  .presensi-title { font-family:'Playfair Display',serif; font-size:0.85rem; color:var(--emerald); font-weight:700; }
  .presensi-body { padding:1.2rem; }
  .presensi-btn { padding:0.62rem 1.5rem; background:linear-gradient(135deg,#4fc75a,var(--emerald)); border:none; border-radius:var(--radius); color:var(--ink); font-size:0.82rem; font-weight:700; cursor:pointer; transition:all 0.2s; font-family:'Source Sans 3',sans-serif; display:flex; align-items:center; gap:0.4rem; }
  .presensi-btn:hover { box-shadow:0 0 16px rgba(63,185,80,0.25); transform:translateY(-1px); }
  .presensi-btn:disabled { opacity:0.45; cursor:not-allowed; transform:none; }
  .presensi-status-row { display:flex; align-items:center; gap:0.8rem; margin-bottom:0.8rem; padding:0.65rem 0.85rem; background:rgba(63,185,80,0.06); border-radius:8px; border:1px solid rgba(63,185,80,0.14); }
  .presensi-table { width:100%; border-collapse:collapse; font-size:0.78rem; }
  .presensi-table th { padding:0.52rem 0.85rem; text-align:left; color:var(--text-secondary); font-weight:700; border-bottom:1px solid var(--border); background:var(--surface-2); font-size:0.68rem; letter-spacing:0.06em; text-transform:uppercase; }
  .presensi-table td { padding:0.52rem 0.85rem; border-bottom:1px solid var(--border); }
  .presensi-table tr:last-child td { border-bottom:none; }
  .badge-hadir { background:var(--emerald-dim); color:var(--emerald); border:1px solid rgba(63,185,80,0.25); font-size:0.6rem; padding:0.1rem 0.42rem; border-radius:3px; font-weight:700; }
  .badge-alpha { background:rgba(248,81,73,0.10); color:var(--red); border:1px solid rgba(248,81,73,0.22); font-size:0.6rem; padding:0.1rem 0.42rem; border-radius:3px; font-weight:700; }
  .badge-izin { background:var(--accent-dim); color:var(--accent); border:1px solid rgba(30,58,138,0.22); font-size:0.6rem; padding:0.1rem 0.42rem; border-radius:3px; font-weight:700; }

  /* KUIS */
  .kuis-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:0.8rem; margin-bottom:1.8rem; }
  .kuis-card { padding:1.3rem; background:var(--surface); border:1px solid rgba(88,166,255,0.16); border-radius:var(--radius-lg); transition:all 0.2s; }
  .kuis-card:hover { border-color:rgba(88,166,255,0.35); transform:translateY(-2px); }
  .kuis-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:0.6rem; }
  .kuis-title { font-size:0.85rem; font-weight:700; }
  .kuis-meta { font-size:0.68rem; color:var(--text-secondary); margin-top:0.15rem; }
  .badge-kuis { font-size:0.58rem; padding:0.14rem 0.48rem; border-radius:3px; font-weight:700; letter-spacing:0.06em; background:var(--blue-dim); color:var(--blue); border:1px solid rgba(88,166,255,0.20); white-space:nowrap; }
  .btn-kuis { width:100%; margin-top:0.8rem; padding:0.55rem 0.85rem; background:rgba(88,166,255,0.07); border:1px solid rgba(88,166,255,0.22); border-radius:var(--radius); color:var(--blue); font-size:0.76rem; font-weight:700; cursor:pointer; transition:all 0.2s; font-family:'Source Sans 3',sans-serif; }
  .btn-kuis:hover { background:rgba(88,166,255,0.14); box-shadow:0 0 14px rgba(88,166,255,0.15); }
  .kuis-question { padding:0.85rem; background:var(--surface-2); border:1px solid var(--border); border-radius:8px; margin-bottom:0.65rem; }
  .kuis-question-text { font-size:0.82rem; font-weight:700; margin-bottom:0.65rem; line-height:1.5; }
  .kuis-option { display:flex; align-items:center; gap:0.5rem; padding:0.44rem 0.65rem; border-radius:6px; border:1px solid var(--border); margin-bottom:0.3rem; cursor:pointer; transition:all 0.15s; font-size:0.78rem; background:var(--surface); }
  .kuis-option:hover { border-color:rgba(88,166,255,0.35); background:rgba(88,166,255,0.06); }
  .kuis-option.selected { border-color:rgba(63,185,80,0.45); background:rgba(63,185,80,0.08); color:var(--emerald); }
  .kuis-option input[type=radio] { accent-color:var(--emerald); }
/* Tombol Hapus Kuis */
.btn-hapus-kuis {
  background: rgba(220, 38, 38, 0.07) !important;
  color: #dc2626 !important;
  border-color: rgba(220, 38, 38, 0.22) !important;
}

.btn-hapus-kuis:hover {
  background: rgba(220, 38, 38, 0.15) !important;
  box-shadow: 0 0 14px rgba(220, 38, 38, 0.15) !important;
  transform: translateY(-1px);
}
  /* RESULT TABLE */
  .result-table { width:100%; border-collapse:collapse; font-size:0.78rem; }
  .result-table th { padding:0.6rem 0.85rem; text-align:left; color:var(--text-secondary); font-weight:700; border-bottom:1px solid var(--border); background:var(--surface-2); font-size:0.65rem; letter-spacing:0.06em; text-transform:uppercase; }
  .result-table td { padding:0.6rem 0.85rem; border-bottom:1px solid var(--border); }
  .result-table tr:last-child td { border-bottom:none; }
  .result-table tr:hover td { background:var(--surface-hover); }
  .score-badge { font-family:'JetBrains Mono',monospace; font-size:0.76rem; padding:0.14rem 0.5rem; border-radius:3px; font-weight:700; }
  .score-good { background:var(--emerald-dim); color:var(--emerald); border:1px solid rgba(63,185,80,0.25); }
  .score-mid { background:var(--accent-dim); color:var(--accent); border:1px solid rgba(30,58,138,0.22); }
  .score-low { background:rgba(248,81,73,0.09); color:var(--red); border:1px solid rgba(248,81,73,0.20); }

  /* MODAL */
  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(10px); z-index:200; align-items:center; justify-content:center; }
  .modal-overlay.open { display:flex; }
  .modal { width:520px; max-width:96vw; background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; animation:fadeInUp 0.28s ease; max-height:90vh; overflow-y:auto; box-shadow:var(--shadow-lg); }
  .modal-header { padding:1.2rem 1.4rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:var(--surface-2); }
  .modal-title { font-family:'Playfair Display',serif; font-size:0.92rem; color:var(--accent); font-weight:700; }
  .modal-close { background:none; border:none; color:var(--text-secondary); cursor:pointer; font-size:1rem; }
  .modal-close:hover { color:var(--text-primary); }
  .modal-body { padding:1.3rem; }
  .modal-footer { padding:0.85rem 1.3rem; border-top:1px solid var(--border); display:flex; gap:0.6rem; justify-content:flex-end; background:var(--surface-2); }

  /* EMPTY STATE */
  .empty-state { text-align:center; padding:2.5rem 1.2rem; color:var(--text-dim); border:1.5px dashed rgba(30,58,138,0.16); border-radius:var(--radius-lg); background:var(--surface-2); }
  .empty-icon { font-size:2rem; margin-bottom:0.7rem; opacity:0.4; }
  .empty-text { font-size:0.8rem; margin-bottom:0.38rem; color:var(--text-secondary); }
  .empty-sub { font-size:0.68rem; }

  /* ROLE VISIBILITY */
  .guru-only { display:none; }
  .siswa-only { display:none; }
  body.role-guru .guru-only { display:flex; }
  body.role-guru .guru-only.block { display:block; }
  body.role-siswa .siswa-only { display:flex; }
  body.role-siswa .siswa-only.block { display:block; }

  /* MODAL UPLOAD */
  .modal-upload-zone { border:1.5px dashed rgba(30,58,138,0.20); border-radius:8px; padding:1.3rem; text-align:center; cursor:pointer; transition:all 0.2s; background:var(--surface-3); position:relative; }
  .modal-upload-zone:hover { border-color:rgba(30,58,138,0.50); background:var(--accent-dim); }
  .modal-upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
  .modal-upload-zone-label { font-size:0.78rem; color:var(--text-secondary); margin-top:0.35rem; }
  .modal-upload-zone-types { font-size:0.64rem; color:var(--text-dim); margin-top:0.2rem; }
  .modal-file-preview { display:flex; align-items:center; gap:0.6rem; padding:0.6rem 0.85rem; background:rgba(63,185,80,0.06); border:1px solid rgba(63,185,80,0.20); border-radius:7px; margin-top:0.45rem; }
  .modal-file-preview span { font-size:0.75rem; color:var(--emerald); font-weight:700; flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .modal-file-remove { background:none; border:none; color:var(--text-dim); cursor:pointer; font-size:0.7rem; font-family:'Source Sans 3',sans-serif; }
  .modal-file-remove:hover { color:var(--red); }

  /* FILTER */
  .filter-bar { display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap; margin-bottom:1rem; }
  .filter-bar label { font-size:0.65rem; font-weight:700; color:var(--text-dim); letter-spacing:0.1em; text-transform:uppercase; white-space:nowrap; }
  .filter-btn { padding:0.25rem 0.65rem; border-radius:4px; border:1px solid var(--border); background:var(--surface-3); color:var(--text-secondary); font-size:0.68rem; font-weight:700; cursor:pointer; transition:all 0.15s; font-family:'Source Sans 3',sans-serif; white-space:nowrap; letter-spacing:0.04em; }
  .filter-btn:hover { border-color:rgba(30,58,138,0.35); color:var(--accent); }
  .filter-btn.active { background:var(--accent-dim); color:var(--accent); border-color:rgba(30,58,138,0.40); }

  /* SOAL EDITOR */
  .soal-block { background:var(--surface-2); border:1px solid var(--border); border-radius:8px; padding:0.85rem 0.95rem; margin-bottom:0.7rem; position:relative; transition:border-color 0.2s; }
  .soal-block:hover { border-color:rgba(88,166,255,0.25); }
  .soal-block-num { font-size:0.6rem; font-weight:700; color:var(--blue); text-transform:uppercase; letter-spacing:0.12em; margin-bottom:0.5rem; }
  .soal-opsi-row { display:flex; align-items:center; gap:0.45rem; margin-bottom:0.28rem; }
  .soal-opsi-key { width:22px; height:22px; border-radius:50%; flex-shrink:0; border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:0.62rem; font-weight:700; cursor:pointer; transition:all 0.15s; background:var(--surface-3); color:var(--text-secondary); }
  .soal-opsi-key.benar { background:var(--emerald-dim); border-color:rgba(63,185,80,0.50); color:var(--emerald); }
  .soal-opsi-key:hover:not(.benar) { border-color:rgba(63,185,80,0.35); color:var(--emerald); }
  .soal-del-btn { position:absolute; top:0.45rem; right:0.55rem; background:none; border:none; color:var(--text-dim); cursor:pointer; font-size:0.75rem; padding:0.12rem 0.32rem; border-radius:4px; transition:all 0.15s; font-family:'Source Sans 3',sans-serif; }
  .soal-del-btn:hover { background:rgba(248,81,73,0.08); color:var(--red); }
  .soal-hint { font-size:0.6rem; color:var(--text-dim); margin-top:0.45rem; }
  .add-soal-btn { width:100%; padding:0.58rem; border:1.5px dashed rgba(30,58,138,0.18); border-radius:8px; background:transparent; color:var(--text-dim); font-size:0.76rem; font-weight:700; cursor:pointer; transition:all 0.2s; font-family:'Source Sans 3',sans-serif; margin-top:0.22rem; }
  .add-soal-btn:hover { border-color:rgba(30,58,138,0.40); color:var(--accent); background:var(--accent-dim); }

  /* MODUL TIPE */
  .modul-tipe-tabs { display:flex; gap:0.4rem; margin-bottom:0.7rem; }
  .modul-tipe-tab { flex:1; padding:0.45rem 0.45rem; border-radius:6px; border:1.5px solid var(--border); background:var(--surface-3); color:var(--text-secondary); font-size:0.72rem; font-weight:700; cursor:pointer; text-align:center; transition:all 0.15s; font-family:'Source Sans 3',sans-serif; }
  .modul-tipe-tab.active { background:var(--accent-dim); color:var(--accent); border-color:rgba(30,58,138,0.40); }

  /* REKAP PERTEMUAN */
  .rekap-pertemuan-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:0.8rem; overflow:hidden; transition:all 0.2s; }
  .rekap-pertemuan-header { padding:0.85rem 1.1rem; background:var(--surface-2); display:flex; align-items:center; justify-content:space-between; cursor:pointer; user-select:none; border-bottom:1px solid var(--border); }
  .rekap-pertemuan-header:hover { background:var(--surface-3); }
  .rekap-pertemuan-title { font-size:0.82rem; font-weight:700; display:flex; align-items:center; gap:0.5rem; }
  .rekap-pertemuan-meta { font-size:0.68rem; color:var(--text-secondary); display:flex; gap:0.9rem; align-items:center; }
  .rekap-pertemuan-body { padding:0.95rem 1.1rem; display:none; }
  .rekap-pertemuan-body.open { display:block; }

  /* SCROLLBAR */
  ::-webkit-scrollbar { width:4px; height:4px; }
  ::-webkit-scrollbar-track { background:#eaeff8; }
  ::-webkit-scrollbar-thumb { background:rgba(30,58,138,0.22); border-radius:10px; }
  ::-webkit-scrollbar-thumb:hover { background:rgba(30,58,138,0.45); }

  @keyframes fadeInDown { from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)} }
  @keyframes fadeInUp { from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)} }
  @keyframes spin-slow { from{transform:rotate(0deg)}to{transform:rotate(360deg)} }
  @keyframes pulse { 0%,100%{opacity:1}50%{opacity:0.3} }

  /* ===== 3D MOLECULE VIEWER PANEL ===== */
  .mol3d-panel {
    background: linear-gradient(145deg, #0f1e45 0%, #1e3a8a 55%, #1e40af 100%);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(30,58,138,0.25);
    box-shadow: 0 8px 32px rgba(30,58,138,0.22), inset 0 1px 0 rgba(255,255,255,0.08);
    overflow: hidden;
    margin-bottom: 1.8rem;
    position: relative;
  }
  .mol3d-panel-header {
    padding: 1rem 1.4rem;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    background: rgba(0,0,0,0.12);
  }
  .mol3d-panel-title {
    font-family:'Playfair Display',serif; font-size:0.9rem; font-weight:700;
    color:#ffffff; display:flex; align-items:center; gap:0.5rem;
  }
  .mol3d-panel-title::before { content:''; width:3px; height:14px; background:rgba(147,197,253,0.8); border-radius:2px; flex-shrink:0; }
  .mol3d-panel-sub { font-size:0.65rem; color:rgba(147,197,253,0.75); letter-spacing:0.08em; }
  .mol3d-panel-body {
    display: grid; grid-template-columns: 1fr 280px;
    min-height: 320px;
  }
  .mol3d-canvas-wrap {
    position: relative; overflow: hidden;
    background: radial-gradient(ellipse 80% 80% at 50% 50%, rgba(37,99,235,0.25) 0%, transparent 70%);
  }
  #dash-mol-canvas {
    display: block; width: 100%; height: 320px;
    cursor: grab;
  }
  #dash-mol-canvas:active { cursor: grabbing; }
  .mol3d-sidebar {
    border-left: 1px solid rgba(255,255,255,0.08);
    padding: 1.1rem;
    background: rgba(0,0,0,0.15);
    display: flex; flex-direction: column; gap: 0.7rem;
  }
  .mol3d-sidebar-label {
    font-size:0.58rem; color:rgba(147,197,253,0.60); letter-spacing:0.16em; text-transform:uppercase; font-weight:700;
    margin-bottom:0.1rem;
  }
  .mol3d-molecule-list { display:flex; flex-direction:column; gap:0.4rem; }
  .mol3d-mol-btn {
    padding: 0.65rem 0.85rem;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.10);
    background: rgba(255,255,255,0.05);
    cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 0.6rem;
  }
  .mol3d-mol-btn:hover { background:rgba(255,255,255,0.12); border-color:rgba(147,197,253,0.40); }
  .mol3d-mol-btn.active { background:rgba(147,197,253,0.15); border-color:rgba(147,197,253,0.60); box-shadow:0 0 12px rgba(147,197,253,0.15); }
  .mol3d-mol-dot {
    width:10px; height:10px; border-radius:50%; flex-shrink:0;
  }
  .mol3d-mol-name { font-size:0.75rem; font-weight:700; color:#fff; }
  .mol3d-mol-formula { font-family:'JetBrains Mono',monospace; font-size:0.62rem; color:rgba(147,197,253,0.75); margin-top:0.06rem; }
  .mol3d-info-card {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 8px; padding: 0.85rem;
  }
  .mol3d-info-mol-name { font-family:'Playfair Display',serif; font-size:0.88rem; font-weight:700; color:#fff; margin-bottom:0.2rem; }
  .mol3d-info-formula {
    font-family:'JetBrains Mono',monospace; font-size:0.72rem;
    color:rgba(147,197,253,0.90); margin-bottom:0.6rem;
  }
  .mol3d-info-row { display:flex; justify-content:space-between; font-size:0.65rem; margin-bottom:0.28rem; }
  .mol3d-info-key { color:rgba(255,255,255,0.50); }
  .mol3d-info-val { color:#fff; font-weight:600; font-family:'JetBrains Mono',monospace; }
  .mol3d-legend { display:flex; flex-wrap:wrap; gap:0.45rem; }
  .mol3d-legend-item { display:flex; align-items:center; gap:0.3rem; font-size:0.62rem; color:rgba(255,255,255,0.65); }
  .mol3d-legend-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
  .mol3d-controls {
    position:absolute; bottom:0.75rem; left:0.9rem;
    display:flex; gap:0.4rem;
  }
  .mol3d-ctrl-btn {
    width:28px; height:28px; border-radius:6px;
    background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.15);
    color:rgba(255,255,255,0.75); font-size:0.8rem; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all 0.15s;
  }
  .mol3d-ctrl-btn:hover { background:rgba(255,255,255,0.15); color:#fff; }
  .mol3d-spin-indicator {
    position:absolute; top:0.75rem; left:0.9rem;
    font-size:0.6rem; color:rgba(147,197,253,0.60); letter-spacing:0.08em;
    display:flex; align-items:center; gap:0.3rem;
  }
  .mol3d-spin-dot { width:5px; height:5px; border-radius:50%; background:rgba(147,197,253,0.70); animation:pulse 2s infinite; }

.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 1000;
  transition: width 0.25s ease, transform 0.25s ease;
  width: 256px;
  overflow-x: hidden;
  overflow-y: auto;
  background: var(--surface);
  border-right: 1px solid var(--border);
}

.main-content {
  margin-left: 256px;
  transition: margin-left 0.25s ease;
  width: calc(100% - 256px);
}

/* Toggle button styling */
.sidebar-toggle {
  position: absolute;
  top: 1rem;
  right: -12px;
  width: 24px;
  height: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 100;
  font-size: 0.8rem;
  color: var(--text-secondary);
  transition: all 0.2s;
  box-shadow: var(--shadow);
}

.sidebar-toggle:hover {
  background: var(--accent-dim);
  color: var(--accent);
  transform: scale(1.1);
}

/* Collapsed state */
.sidebar.collapsed {
  width: 72px;
}

.sidebar.collapsed .sidebar-brand-text,
.sidebar.collapsed .user-badge .u-name,
.sidebar.collapsed .user-badge .u-role,
.sidebar.collapsed .role-badge,
.sidebar.collapsed .nav-item span:not(.nav-icon),
.sidebar.collapsed .nav-section,
.sidebar.collapsed .sidebar-footer span {
  display: none;
}

.sidebar.collapsed .nav-item {
  justify-content: center;
  padding: 0.6rem 0;
}

.sidebar.collapsed .nav-icon {
  font-size: 1.2rem;
  margin: 0;
}

.sidebar.collapsed .user-badge {
  justify-content: center;
  padding: 0.7rem 0;
}

.sidebar.collapsed .avatar {
  margin: 0 auto;
}

.sidebar.collapsed .sidebar-brand {
  justify-content: center;
  padding: 1.2rem 0;
}

.sidebar.collapsed .sidebar-brand-icon {
  margin: 0 auto;
}

.sidebar.collapsed .sidebar-footer button {
  padding: 0.55rem 0;
  text-align: center;
}

.sidebar.collapsed .sidebar-footer button::before {
  content: '🚪';
  font-size: 1.1rem;
}

.sidebar.collapsed .sidebar-footer button span {
  display: none;
}

.sidebar.collapsed .sidebar-toggle {
  transform: rotate(180deg);
}

/* Main content adjustment */
.sidebar.collapsed + .main-content,
.sidebar.collapsed ~ .main-content {
  margin-left: 72px;
  width: calc(100% - 72px);
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(0);
    box-shadow: var(--shadow-lg);
  }
  
  .sidebar.collapsed {
    transform: translateX(-100%);
    width: 0;
  }
  
  .sidebar:not(.collapsed) {
    width: 256px;
  }
  
  .sidebar-toggle {
    right: -12px;
  }
  
  .sidebar.collapsed .sidebar-toggle {
    right: -36px;
    background: var(--accent);
    color: white;
    transform: rotate(0deg);
  }
  
  .sidebar.collapsed + .main-content,
  .sidebar.collapsed ~ .main-content {
    margin-left: 0;
    width: 100%;
  }
  
  .main-content {
    margin-left: 0;
    width: 100%;
  }
}
</style>
</head>
<body>

<!-- 3D CANVAS -->
<canvas id="mol-canvas"></canvas>

<!-- ===== LOGIN SCREEN ===== -->
<div id="login-screen" class="screen active">
  <div class="login-veil"></div>
  <div class="login-brand">
    <div class="brand-logo-ring">
      <div class="brand-logo-inner">⬡</div>
    </div>
    <div class="brand-wordmark">Si<span>Chem</span>Or</div>
    <div class="brand-tagline">Sistem Kimia Organik · Senyawa Turunan Alkana</div>
  </div>

  <div class="login-unified">
    <div class="login-unified-header">
      <h2>Masuk ke Platform</h2>
      <p>Pilih peran Anda untuk melanjutkan</p>
    </div>
    <div class="login-body">
      <div class="role-selector">
        <div class="role-btn" id="role-btn-guru" onclick="pilihRole('guru')">
          <span class="role-btn-icon">🎓</span>
          <div class="role-btn-label">Guru</div>
          <div class="role-btn-sub">Akses penuh manajemen</div>
        </div>
        <div class="role-btn" id="role-btn-siswa" onclick="pilihRole('siswa')">
          <span class="role-btn-icon">🧪</span>
          <div class="role-btn-label">Siswa</div>
          <div class="role-btn-sub">Akses belajar & aktivitas</div>
        </div>
      </div>

      <div class="pw-section hidden" id="pw-section-guru">
        <label class="field-label">Password</label>
        <div class="field-wrap">
          <input type="password" id="pw-guru" class="field-input" placeholder="••••••••"
            onkeydown="if(event.key==='Enter')aksesLogin('guru')">
          <button class="field-toggle" type="button" onclick="togglePw('pw-guru',this)">👁</button>
        </div>
        <div class="field-err" id="err-guru">Password salah. Coba lagi.</div>
      </div>

      <div class="pw-section hidden" id="pw-section-siswa"></div>

      <button class="btn-enter" id="btn-masuk" onclick="aksesLogin(selectedRole)" disabled>
        → Masuk
      </button>
    </div>
  </div>
</div>

<!-- ===== PROFILE SCREEN ===== -->
<div id="profile-screen" class="screen" style="display:none; position:fixed; background:var(--ink);">
  <div class="profile-card" id="profile-card">
    <div class="profile-card-header">
      <div class="profile-card-icon" id="profile-icon">🎓</div>
      <div>
        <h2 id="profile-heading">Lengkapi Profil Guru</h2>
        <p id="profile-sub">Masukkan informasi akun untuk melanjutkan</p>
      </div>
    </div>
    <div class="profile-card-body">
      <div id="guru-fields">
        <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" class="form-input" id="guru-nama" placeholder="Contoh: Ibu Ratna Sari"></div>
        <div class="form-group"><label class="form-label">Username / NIP</label><input type="text" class="form-input" id="guru-username" placeholder="Contoh: ratna.sari"></div>
        <div class="form-group"><label class="form-label">Email Sekolah</label><input type="email" class="form-input" id="guru-email" placeholder="nama@sekolah.sch.id"></div>
        <div class="form-group"><label class="form-label">Mata Pelajaran</label><input type="text" class="form-input" id="guru-mapel" value="Kimia Organik — Senyawa Turunan Alkana" readonly style="opacity:0.55"></div>
      </div>
      <div id="siswa-fields" style="display:none">
        <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" class="form-input" id="siswa-nama" placeholder="Contoh: Arya Pratama"></div>
        <div class="form-group"><label class="form-label">Nomor Absen</label><input type="text" class="form-input" id="siswa-absen" placeholder="Contoh: 15"></div>
        <div class="form-group"><label class="form-label">Kelas</label>
          <select class="form-select" id="siswa-kelas">
            <option value="">-- Pilih Kelas --</option>
            <option>XII MIPA 1</option><option>XII MIPA 2</option><option>XII MIPA 3</option><option>XII MIPA 4</option>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" id="siswa-email" placeholder="nama@gmail.com"></div>
      </div>
    </div>
    <div class="profile-card-footer">
      <button class="btn-secondary" style="flex:1" onclick="kembaliLogin()">← Kembali</button>
      <button class="btn-primary" style="flex:2" onclick="submitProfil()">Masuk ke Platform →</button>
    </div>
  </div>
</div>

<!-- ===== APP SCREEN ===== -->
<div id="app-screen" class="screen">
  <aside class="sidebar" id="main-sidebar">
  <!-- Tombol toggle - letakkan di bagian atas sidebar -->
  <div class="sidebar-toggle" onclick="toggleSidebar()" id="sidebar-toggle-btn">◀</div>
  
  <div class="sidebar-brand">
    <div class="sidebar-brand-icon">⬡</div>
    <div class="sidebar-brand-text">
      <div class="sidebar-brand-name">Si<span>Chem</span>Or</div>
      <div class="sidebar-brand-sub">Kimia Organik</div>
    </div>
  </div>

    <div class="user-badge">
      <div class="avatar guru-av" id="user-avatar">🎓</div>
      <div style="min-width:0">
        <div class="u-name" id="user-name">Pengguna</div>
        <div class="u-role" id="user-role">—</div>
      </div>
      <div class="role-badge guru" id="role-badge">GURU</div>
    </div>

    <div class="nav-section">Navigasi</div>
    <div class="nav-item active" onclick="setActive(this,'dashboard')"><span class="nav-icon">🏠</span> Dashboard</div>
    <div class="nav-item" onclick="setActive(this,'materi')"><span class="nav-icon">📚</span> Modul Materi</div>
    <div class="nav-item" onclick="setActive(this,'video')"><span class="nav-icon">▶️</span> Media Gallery</div>
    <div class="nav-item" onclick="setActive(this,'zoom')"><span class="nav-icon">📹</span> Virtual Room</div>
    <div class="nav-item" onclick="setActive(this,'lab')"><span class="nav-icon">🔬</span> Lab Simulasi</div>

    <div class="nav-section">Aktivitas</div>
    <div class="nav-item" onclick="setActive(this,'presensi')"><span class="nav-icon">✅</span> Presensi</div>
    <div class="nav-item" onclick="setActive(this,'tugas')"><span class="nav-icon">📝</span> Tugas / LKPD</div>
    <div class="nav-item" onclick="setActive(this,'kuis')"><span class="nav-icon">🧠</span> Kuis &amp; Evaluasi</div>
    <div class="nav-item" onclick="setActive(this,'diskusi');tandaiDiskusiDibaca()"><span class="nav-icon">💬</span> Diskusi Bersama<span class="nav-badge" id="badge-nav-diskusi" style="display:none">0</span></div>

    <div class="nav-section guru-only block">Manajemen</div>
    <div class="nav-item guru-only" onclick="setActive(this,'kelola')"><span class="nav-icon">⚙️</span> Kelola Konten</div>

    <div class="sidebar-footer">
  <button class="btn-logout" onclick="logout()">
    <span>🚪 Keluar</span>
  </button>
</div>
  </aside>

  <main class="main-content">
    <div class="top-bar">
      <div>
        <div class="page-title" id="page-title">Dashboard</div>
        <div class="page-subtitle">Kimia Organik — Senyawa Turunan Alkana</div>
      </div>
      <div class="top-actions">
        <button class="btn-primary guru-only" onclick="openModal('tambah-konten')">＋ Tambah Konten</button>
      </div>
    </div>

    <div class="content-area">

      <!-- DASHBOARD -->
      <section id="sec-dashboard">
        <div class="stats-row">
          <div class="stat-card"><div class="stat-icon">📚</div><div class="stat-value" id="stat-modul">0</div><div class="stat-label">Modul Tersedia</div><div class="stat-change" id="stat-modul-sub">Belum ada modul</div></div>
          <div class="stat-card"><div class="stat-icon">▶️</div><div class="stat-value" id="stat-video">0</div><div class="stat-label">Video Tutorial</div><div class="stat-change" id="stat-video-sub">Belum ada video</div></div>
          <div class="stat-card"><div class="stat-icon">📝</div><div class="stat-value">3</div><div class="stat-label">Tugas Aktif</div><div class="stat-change">⏰ 1 hampir deadline</div></div>
          <div class="stat-card"><div class="stat-icon">🧠</div><div class="stat-value">2</div><div class="stat-label">Kuis Aktif</div><div class="stat-change">🟢 1 berlangsung</div></div>
        </div>

        <!-- ===== 3D MOLECULE VIEWER ===== -->
        <div class="mol3d-panel">
          <div class="mol3d-panel-header">
            <div>
              <div class="mol3d-panel-title">Visualisasi 3D Molekul</div>
              <div class="mol3d-panel-sub">Senyawa Turunan Alkana · Klik & Drag untuk memutar</div>
            </div>
          </div>
          <div class="mol3d-panel-body">
            <div class="mol3d-canvas-wrap">
              <canvas id="dash-mol-canvas"></canvas>
              <div class="mol3d-spin-indicator"><div class="mol3d-spin-dot"></div> AUTO ROTATE</div>
              <div class="mol3d-controls">
                <button class="mol3d-ctrl-btn" title="Perbesar" onclick="dashMolZoom(1.15)">＋</button>
                <button class="mol3d-ctrl-btn" title="Perkecil" onclick="dashMolZoom(0.87)">－</button>
                <button class="mol3d-ctrl-btn" title="Reset" onclick="dashMolReset()">⟳</button>
              </div>
            </div>
            <div class="mol3d-sidebar">
              <div>
                <div class="mol3d-sidebar-label">Pilih Molekul</div>
                <div class="mol3d-molecule-list" id="mol3d-list"></div>
              </div>
              <div class="mol3d-info-card" id="mol3d-info"></div>
              <div>
                <div class="mol3d-sidebar-label">Legenda Atom</div>
                <div class="mol3d-legend">
                  <div class="mol3d-legend-item"><div class="mol3d-legend-dot" style="background:#a5b4fc"></div>C — Karbon</div>
                  <div class="mol3d-legend-item"><div class="mol3d-legend-dot" style="background:#93c5fd"></div>H — Hidrogen</div>
                  <div class="mol3d-legend-item"><div class="mol3d-legend-dot" style="background:#fca5a5"></div>O — Oksigen</div>
                  <div class="mol3d-legend-item"><div class="mol3d-legend-dot" style="background:#6ee7b7"></div>Cl — Klorin</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ===== END 3D MOLECULE VIEWER ===== -->
        <div class="siswa-only block" style="margin-bottom:1.8rem;">
          <div class="section-header"><div class="section-title">Progress Belajar</div></div>
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.3rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;">
              <span style="font-size:0.82rem;font-weight:700;">Senyawa Turunan Alkana</span>
              <span style="font-family:'JetBrains Mono';font-size:0.75rem;color:var(--accent);">0 / 7 bab</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width:0%"></div></div>
            <div class="progress-text">0% selesai — menunggu modul dari guru</div>
          </div>
        </div>
        <div class="guru-only block" style="margin-bottom:1.8rem;">
          <div class="guru-banner">🔑 Mode Guru Aktif — Anda memiliki akses penuh untuk mengelola seluruh konten platform.</div>
        </div>
        <div class="section-header"><div class="section-title">Materi Terkini</div></div>
        <div id="dashboard-materi-preview" style="margin-bottom:1.8rem;">
          <div class="empty-state"><div class="empty-icon">📚</div><div class="empty-text">Belum ada modul materi</div><div class="empty-sub">Guru belum mengunggah modul apapun.</div></div>
        </div>
      </section>

      <!-- MATERI -->
      <section id="sec-materi" style="display:none">
        <div class="guru-only block" style="margin-bottom:0.8rem;"><div class="guru-banner">🔑 Klik ikon edit/hapus di setiap kartu untuk mengelola modul.</div></div>
        <div class="section-header" style="margin-bottom:0.8rem;"><div class="section-title">Modul Materi — Senyawa Turunan Alkana</div></div>
        <div id="materi-grid" class="module-grid" style="display:none"></div>
        <div id="materi-empty" class="empty-state">
          <div class="empty-icon">📚</div>
          <div class="empty-text">Belum ada modul materi</div>
          <div class="empty-sub guru-only">Klik "+ Tambah Konten" di atas untuk mengunggah modul pertama.</div>
          <div class="empty-sub siswa-only">Modul materi akan tersedia setelah guru mengunggahnya.</div>
        </div>
        <div class="section-header" style="margin-top:1.4rem;"><div class="section-title">Diskusi Bab — Materi</div></div>
        <div id="disc-materi"></div>
      </section>

      <!-- VIDEO -->
      <section id="sec-video" style="display:none">
        <div class="section-header" style="margin-bottom:0.8rem;"><div class="section-title">Media Gallery — Tutorial YouTube</div></div>
        <div id="video-grid" class="video-grid" style="display:none"></div>
        <div id="video-empty" class="empty-state">
          <div class="empty-icon">▶️</div>
          <div class="empty-text">Belum ada video tutorial</div>
          <div class="empty-sub guru-only">Klik "+ Tambah Konten" dan pilih "Media Gallery".</div>
          <div class="empty-sub siswa-only">Video tutorial akan tersedia setelah guru menambahkannya.</div>
        </div>
        <div class="section-header" style="margin-top:1.4rem;"><div class="section-title">Diskusi Bab — Video</div></div>
        <div id="disc-video"></div>
      </section>

      <!-- ZOOM -->
      <section id="sec-zoom" style="display:none">
        <div class="section-header" style="margin-bottom:0.8rem;"><div class="section-title">Virtual Room — Pertemuan Zoom</div></div>
        <div id="zoom-active-card" style="display:none"></div>
        <div id="zoom-empty-card" class="zoom-card">
          <div class="zoom-icon">📹</div>
          <div class="zoom-info">
            <div class="zoom-title">Kimia Organik — Sesi Live Pertemuan</div>
            <div class="zoom-meta">Jadwal: <span style="color:var(--accent)">Menunggu konfirmasi dari Guru</span></div>
            <div class="zoom-status"><div class="dot-live"></div> Link Zoom akan aktif saat Guru mengatur sesi</div>
          </div>
          <button class="btn-join" disabled style="opacity:0.4;cursor:not-allowed;">📹 Join Meeting</button>
        </div>
        <div class="guru-only block" style="margin-bottom:1.8rem;">
          <div class="section-header"><div class="section-title">Jadwal Sesi Mendatang</div></div>
          <div id="zoom-jadwal-list">
            <div class="empty-state"><div class="empty-icon">📅</div><div class="empty-text">Belum ada sesi terjadwal</div><div class="empty-sub">Klik "+ Tambah Konten" dan pilih "Virtual Room".</div></div>
          </div>
        </div>
        <div class="section-header"><div class="section-title">Diskusi Bab — Virtual Room</div></div>
        <div id="disc-zoom"></div>
      </section>

      <!-- LAB -->
      <section id="sec-lab" style="display:none">
        <div class="section-header" style="margin-bottom:0.8rem;"><div class="section-title">Laboratory Area — Simulasi Molekul 3D</div></div>
        <div class="lab-grid" style="grid-template-columns:1fr;max-width:480px;">
          <a class="lab-card" href="https://molview.org" target="_blank">
            <div class="lab-icon">🧬</div>
            <div>
              <div class="lab-name">MolView</div>
              <div class="lab-desc">Visualisasi struktur 3D molekul senyawa organik secara interaktif dan real-time</div>
            </div>
          </a>
        </div>
        <div class="section-header"><div class="section-title">Diskusi Bab — Lab Simulasi</div></div>
        <div id="disc-lab"></div>
      </section>

      <!-- PRESENSI -->
      <section id="sec-presensi" style="display:none">
        <div class="section-header" style="margin-bottom:0.8rem;">
          <div class="section-title">Presensi Kehadiran</div>
          <button class="btn-primary guru-only" onclick="openModal('presensi')">📋 Buat Sesi Presensi</button>
        </div>
        <div class="siswa-only block">
          <div class="presensi-card">
            <div class="presensi-header">
              <span style="font-size:1rem">✅</span>
              <div class="presensi-title">Presensi Hari Ini</div>
              <div style="margin-left:auto;font-size:0.68rem;color:var(--text-secondary);" id="presensi-tanggal"></div>
            </div>
            <div class="presensi-body">
              <div class="presensi-status-row" id="siswa-presensi-status" style="display:none">
                <span style="font-size:1rem">✅</span>
                <div>
                  <div style="font-size:0.8rem;font-weight:700;color:var(--emerald);">Presensi Berhasil!</div>
                  <div style="font-size:0.68rem;color:var(--text-secondary);" id="presensi-waktu-siswa"></div>
                </div>
              </div>
              <div style="margin-bottom:0.8rem;">
                <div style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:0.4rem;">Pertemuan: <span style="color:var(--accent);font-weight:700;">Kimia Organik — Senyawa Turunan Alkana</span></div>
                <div style="font-size:0.72rem;color:var(--text-dim);">Pastikan kamu sudah membuka halaman ini untuk melakukan presensi digital.</div>
              </div>
              <div style="display:flex;gap:0.6rem;flex-wrap:wrap;">
                <button class="presensi-btn" id="btn-hadir" onclick="isiPresensi('Hadir')">✅ Hadir</button>
                <button class="presensi-btn" style="background:linear-gradient(135deg,var(--accent),var(--accent-2));color:var(--ink);" onclick="isiPresensi('Izin')">📋 Izin</button>
                <button class="presensi-btn" style="background:linear-gradient(135deg,#f06060,var(--red));color:#fff;" onclick="isiPresensi('Sakit')">🤒 Sakit</button>
              </div>
            </div>
          </div>
          <div class="section-header"><div class="section-title">Riwayat Presensi Saya</div></div>
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">
            <table class="presensi-table">
              <thead><tr><th>Pertemuan</th><th>Tanggal</th><th>Status</th></tr></thead>
              <tbody id="riwayat-presensi-siswa">
                <tr><td>Pertemuan 1</td><td>20 Feb 2026</td><td><span class="badge-hadir">Hadir</span></td></tr>
                <tr><td>Pertemuan 2</td><td>24 Feb 2026</td><td><span class="badge-hadir">Hadir</span></td></tr>
                <tr><td>Pertemuan 3</td><td>27 Feb 2026</td><td><span class="badge-izin">Izin</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="guru-only block">
          <div class="guru-banner">🔑 Rekap presensi real-time semua siswa. Data tersimpan otomatis.</div>
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.8rem;margin-bottom:1.8rem;">
            <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-value" style="font-size:1.4rem">24</div><div class="stat-label">Total Siswa</div></div>
            <div class="stat-card" style="border-color:rgba(63,185,80,0.20)"><div class="stat-icon">✅</div><div class="stat-value" style="font-size:1.4rem;color:var(--emerald)" id="stat-hadir-hari-ini">18</div><div class="stat-label">Hadir Hari Ini</div></div>
            <div class="stat-card" style="border-color:rgba(30,58,138,0.20)"><div class="stat-icon">📋</div><div class="stat-value" style="font-size:1.4rem;color:var(--accent)" id="stat-izin-hari-ini">4</div><div class="stat-label">Izin / Sakit</div></div>
            <div class="stat-card" style="border-color:rgba(248,81,73,0.20)"><div class="stat-icon">❌</div><div class="stat-value" style="font-size:1.4rem;color:var(--red)" id="stat-alpha-hari-ini">2</div><div class="stat-label">Alpha</div></div>
          </div>
          <div class="tab-nav" style="margin-bottom:1.2rem;">
            <button class="tab-btn active" onclick="switchPresensiTab(this,'tab-presensi-hari-ini')">📋 Presensi Hari Ini</button>
            <button class="tab-btn" onclick="switchPresensiTab(this,'tab-presensi-riwayat')">📅 Riwayat Pertemuan</button>
          </div>
          <div id="tab-presensi-hari-ini">
            <div class="section-header">
              <div class="section-title">Rekap Presensi — Pertemuan ke-4</div>
              <button class="btn-secondary" onclick="exportPresensi()">📥 Export Excel</button>
            </div>
            <div class="filter-bar">
              <label>Kelas:</label>
              <button class="filter-btn active" onclick="filterPresensiKelas(this,'semua')">Semua</button>
              <button class="filter-btn" onclick="filterPresensiKelas(this,'XII MIPA 1')">XII MIPA 1</button>
              <button class="filter-btn" onclick="filterPresensiKelas(this,'XII MIPA 2')">XII MIPA 2</button>
              <button class="filter-btn" onclick="filterPresensiKelas(this,'XII MIPA 3')">XII MIPA 3</button>
              <button class="filter-btn" onclick="filterPresensiKelas(this,'XII MIPA 4')">XII MIPA 4</button>
            </div>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.8rem;">
              <table class="presensi-table" id="tabel-presensi-guru">
                <thead><tr><th>No</th><th>Nama Siswa</th><th>Kelas</th><th>Waktu</th><th>Status</th><th>Ubah Status</th></tr></thead>
                <tbody id="presensi-guru-body"></tbody>
              </table>
            </div>
          </div>
          <div id="tab-presensi-riwayat" style="display:none">
            <div class="section-header">
              <div class="section-title">Riwayat Presensi Semua Pertemuan</div>
              <button class="btn-secondary" onclick="exportSemuaPresensi()">📥 Export Semua</button>
            </div>
            <div id="rekap-pertemuan-container"></div>
          </div>
        </div>
      </section>

      <!-- TUGAS -->
<section id="sec-tugas" style="display:none">
  <div class="section-header" style="margin-bottom:0.8rem;">
    <div class="section-title">Tugas / LKPD</div>
    <button class="btn-primary guru-only" onclick="openModal('tugas')">＋ Buat Tugas Baru</button>
  </div>
  
  <!-- Tampilan SISWA -->
  <div class="siswa-only block">
    <div id="tugas-siswa-container"></div>
  </div>

  <!-- Tampilan GURU -->
  <div class="guru-only block">
    <div id="tugas-guru-container"></div>
  </div>
</section>

      <!-- KUIS -->
      <section id="sec-kuis" style="display:none">
        <div class="section-header" style="margin-bottom:0.8rem;">
          <div class="section-title">Kuis &amp; Evaluasi — Form Digital</div>
          <button class="btn-primary guru-only" onclick="openModal('buat-kuis')">＋ Buat Kuis Baru</button>
        </div>
        <div class="guru-only block">
          <div class="guru-banner">🔑 Jawaban dan nilai siswa masuk otomatis ke database. Klik "Lihat Hasil" untuk detail per kuis.</div>
          <div class="tab-nav">
            <button class="tab-btn active" onclick="switchKuisTab(this,'daftar-kuis')">📋 Daftar Kuis</button>
            <button class="tab-btn" onclick="switchKuisTab(this,'hasil-kuis')">📊 Hasil &amp; Nilai</button>
          </div>
          <div id="daftar-kuis">
            <div class="kuis-grid">
              <div class="kuis-card">
                <div class="kuis-header"><div><div class="kuis-title">Kuis 1 — Identifikasi Gugus Fungsi</div><div class="kuis-meta">5 soal pilihan ganda · 10 menit · 25 Feb 2026</div></div><span class="badge-kuis">AKTIF</span></div>
                <div style="display:flex;gap:0.4rem;margin-top:0.6rem;"><span style="font-size:0.68rem;color:var(--emerald);">✓ 18 sudah</span><span style="font-size:0.68rem;color:var(--text-dim);">·</span><span style="font-size:0.68rem;color:var(--accent);">6 belum</span></div>
                <div class="progress-bar"><div class="progress-fill" style="width:75%"></div></div>
                <div style="display:flex;gap:0.4rem;margin-top:0.6rem;">
                  <button class="btn-kuis" style="flex:1" onclick="guruLihatHasil(1)">📊 Lihat Hasil</button>
                  <button class="btn-kuis" style="flex:1" onclick="openEditSoal(1)">✏️ Edit Soal</button>
                </div>
              </div>
              <div class="kuis-card">
                <div class="kuis-header"><div><div class="kuis-title">Kuis 2 — Tata Nama IUPAC</div><div class="kuis-meta">8 soal · 15 menit · 26 Feb 2026</div></div><span class="badge-kuis" style="background:var(--accent-dim);color:var(--accent);border-color:rgba(30,58,138,0.22)">BARU</span></div>
                <div style="display:flex;gap:0.4rem;margin-top:0.6rem;"><span style="font-size:0.68rem;color:var(--emerald);">✓ 3 sudah</span><span style="font-size:0.68rem;color:var(--text-dim);">·</span><span style="font-size:0.68rem;color:var(--accent);">21 belum</span></div>
                <div class="progress-bar"><div class="progress-fill" style="width:12%"></div></div>
                <div style="display:flex;gap:0.4rem;margin-top:0.6rem;">
                  <button class="btn-kuis" style="flex:1" onclick="guruLihatHasil(2)">📊 Lihat Hasil</button>
                  <button class="btn-kuis" style="flex:1" onclick="openEditSoal(2)">✏️ Edit Soal</button>
                </div>
              </div>
            </div>
          </div>
          <div id="hasil-kuis" style="display:none;">
            <div class="section-header">
              <div class="section-title" id="hasil-kuis-title">Hasil Kuis</div>
              <button class="btn-secondary" onclick="exportNilai()">📥 Export Excel</button>
            </div>
            <div class="filter-bar">
              <label>Kelas:</label>
              <button class="filter-btn active" onclick="filterHasilKelas(this,'semua')">Semua</button>
              <button class="filter-btn" onclick="filterHasilKelas(this,'XII MIPA 1')">XII MIPA 1</button>
              <button class="filter-btn" onclick="filterHasilKelas(this,'XII MIPA 2')">XII MIPA 2</button>
              <button class="filter-btn" onclick="filterHasilKelas(this,'XII MIPA 3')">XII MIPA 3</button>
              <button class="filter-btn" onclick="filterHasilKelas(this,'XII MIPA 4')">XII MIPA 4</button>
            </div>
            <div id="stat-kelas-nilai" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;"></div>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:1.8rem;">
              <table class="result-table"><thead><tr><th>No</th><th>Nama Siswa</th><th>Kelas</th><th>Waktu Kerjakan</th><th>Durasi</th><th>Nilai</th><th>Detail</th></tr></thead><tbody id="hasil-tbody"></tbody></table>
            </div>
            <button class="btn-secondary" onclick="switchKuisTabByName('daftar-kuis')">← Kembali ke Daftar Kuis</button>
          </div>
        </div>
        <div class="siswa-only block">
          <div class="kuis-grid">
            <div class="kuis-card" style="border-color:rgba(63,185,80,0.20)">
              <div class="kuis-header"><div><div class="kuis-title">Kuis 1 — Identifikasi Gugus Fungsi</div><div class="kuis-meta">5 soal · Estimasi 10 menit</div></div><span class="badge-kuis" style="background:var(--emerald-dim);color:var(--emerald);border-color:rgba(63,185,80,0.22)">BISA DIKERJAKAN</span></div>
              <div style="font-size:0.7rem;color:var(--text-secondary);margin-top:0.4rem;">Deadline: 03 Mar 2026 · 23:59 WIB</div>
              <button class="btn-kuis" onclick="bukaKuis(1)" style="border-color:rgba(63,185,80,0.35);color:var(--emerald)">🚀 Mulai Kerjakan</button>
            </div>
            <div class="kuis-card">
              <div class="kuis-header"><div><div class="kuis-title">Kuis 2 — Tata Nama IUPAC</div><div class="kuis-meta">8 soal · Estimasi 15 menit</div></div><span class="badge-kuis">BARU</span></div>
              <div style="font-size:0.7rem;color:var(--text-secondary);margin-top:0.4rem;">Deadline: 08 Mar 2026 · 23:59 WIB</div>
              <button class="btn-kuis" onclick="bukaKuis(2)">🚀 Mulai Kerjakan</button>
            </div>
          </div>
          <div id="kuis-form-container" style="display:none;">
            <div style="background:var(--surface);border:1px solid rgba(88,166,255,0.20);border-radius:var(--radius-lg);padding:1.3rem;margin-bottom:1.2rem;">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
                <div><div class="kuis-title" id="kuis-form-title">Kuis</div><div class="kuis-meta" id="kuis-form-meta"></div></div>
                <div style="font-family:'JetBrains Mono';font-size:1.1rem;color:var(--accent);" id="kuis-timer">10:00</div>
              </div>
              <div id="kuis-questions-area"></div>
              <div style="display:flex;gap:0.6rem;margin-top:1.2rem;">
                <button class="btn-secondary" onclick="tutupKuis()">← Batal</button>
                <button class="btn-primary" style="flex:1" onclick="submitKuis()">✅ Kumpulkan Jawaban</button>
              </div>
            </div>
          </div>
          <div id="kuis-hasil-siswa" style="display:none;">
            <div style="background:rgba(63,185,80,0.07);border:1px solid rgba(63,185,80,0.22);border-radius:var(--radius-lg);padding:1.8rem;text-align:center;margin-bottom:1.2rem;">
              <div style="font-size:2.5rem;margin-bottom:0.6rem;">🎉</div>
              <div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--emerald);margin-bottom:0.4rem;">Kuis Selesai!</div>
              <div style="font-size:0.8rem;color:var(--text-secondary);margin-bottom:1.2rem;">Jawaban kamu telah berhasil disimpan.</div>
              <div style="font-family:'JetBrains Mono';font-size:2.8rem;font-weight:700;color:var(--accent);" id="nilai-akhir">80</div>
              <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:0.2rem;">Nilai Akhir</div>
              <div style="margin-top:0.8rem;font-size:0.72rem;color:var(--text-secondary);" id="detail-jawaban-siswa"></div>
              <button class="btn-secondary" onclick="kembaliKuis()" style="margin-top:1.2rem;">← Kembali ke Daftar Kuis</button>
            </div>
          </div>
        </div>
      </section>

      <!-- DISKUSI -->
      <section id="sec-diskusi" style="display:none">
        <div class="section-header" style="margin-bottom:0.8rem;"><div class="section-title">Diskusi Bersama — Forum Utama</div></div>
        <div id="disc-main"></div>
      </section>

      <!-- KELOLA -->
      <section id="sec-kelola" style="display:none">
        <div class="guru-banner">🔑 Halaman Manajemen Konten — Hanya bisa diakses oleh Guru.</div>
        <div style="margin-top:1.2rem;">
          <div class="section-header"><div class="section-title">Content Management Panel</div></div>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.8rem;margin-bottom:1.8rem;">
            <div class="stat-card" style="cursor:pointer" onclick="openModal('tambah-konten')"><div class="stat-icon">📄</div><div style="font-size:0.85rem;font-weight:700;color:var(--accent);">Tambah Konten</div><div class="stat-label">Upload modul, video, atau jadwal Zoom</div></div>
            <div class="stat-card" style="cursor:pointer" onclick="openModal('buat-kuis')"><div class="stat-icon">🧠</div><div style="font-size:0.85rem;font-weight:700;color:var(--accent);">Buat Kuis</div><div class="stat-label">Form evaluasi &amp; nilai otomatis</div></div>
            <div class="stat-card" style="cursor:pointer" onclick="exportPresensi()"><div class="stat-icon">✅</div><div style="font-size:0.85rem;font-weight:700;color:var(--accent);">Export Presensi</div><div class="stat-label">Unduh data presensi Excel</div></div>
            <div class="stat-card" style="cursor:pointer" onclick="exportNilai()"><div class="stat-icon">📊</div><div style="font-size:0.85rem;font-weight:700;color:var(--accent);">Export Nilai</div><div class="stat-label">Unduh data nilai kuis Excel</div></div>
            <div class="stat-card" style="cursor:pointer" onclick="exportTugas(1)"><div class="stat-icon">📝</div><div style="font-size:0.85rem;font-weight:700;color:var(--accent);">Export Tugas LKPD 1</div><div class="stat-label">Unduh rekap pengumpulan LKPD 1</div></div>
            <div class="stat-card" style="cursor:pointer" onclick="exportTugas(2)"><div class="stat-icon">📝</div><div style="font-size:0.85rem;font-weight:700;color:var(--accent);">Export Tugas LKPD 2</div><div class="stat-label">Unduh rekap pengumpulan LKPD 2</div></div>
          </div>
          <div class="section-header"><div class="section-title">Moderasi Forum</div></div>
          <div id="disc-mod"></div>
        </div>
      </section>

    </div>
  </main>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-title">Tambah Konten</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body"></div>
    <div class="modal-footer" id="modal-footer">
      <button class="btn-secondary" onclick="closeModal()">Batal</button>
      <button class="btn-primary" id="modal-save-btn" onclick="modalSaveAction()">💾 Simpan</button>
    </div>
  </div>
</div>

<script>
// =====================
// 3D MOLECULE CANVAS
// =====================
(function() {
  var canvas = document.getElementById('mol-canvas');
  var ctx = canvas.getContext('2d');
  var W, H, atoms = [], bonds = [];
  var rot = 0;

  function resize() {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  // Carbon chain structure — simplified 3D points
  var baseAtoms = [
    {x:0, y:0, z:0, type:'C'},
    {x:1.5, y:0, z:0, type:'C'},
    {x:3, y:0, z:0, type:'C'},
    {x:4.5, y:0, z:0, type:'C'},
    {x:0.75, y:1.3, z:0, type:'H'},
    {x:0.75, y:-1.3, z:0, type:'H'},
    {x:2.25, y:1.3, z:0.8, type:'H'},
    {x:2.25, y:-1.3, z:-0.8, type:'H'},
    {x:3.75, y:1.3, z:0, type:'H'},
    {x:3.75, y:-1.3, z:0, type:'H'},
    {x:5.3, y:0.7, z:0.5, type:'O'},
    {x:6.0, y:0.2, z:1.2, type:'H'},
    {x:-0.8, y:0.7, z:-0.5, type:'H'},
    {x:-0.8, y:-0.7, z:0.5, type:'H'},
    {x:5.3, y:-0.7, z:-0.5, type:'H'},
  ];
  var baseBonds = [
    [0,1],[1,2],[2,3],[0,4],[0,5],[1,6],[1,7],[2,8],[2,9],[3,10],[10,11],[0,12],[0,13],[3,14]
  ];

  function project(p, cx, cy, scale) {
    var perspective = 8 / (8 + p.z * 0.5);
    return {
      sx: cx + p.x * scale * perspective,
      sy: cy + p.y * scale * perspective,
      pz: p.z,
      depth: perspective
    };
  }

  function rotateY(p, angle) {
    var cos = Math.cos(angle), sin = Math.sin(angle);
    return {
      x: p.x * cos - p.z * sin,
      y: p.y,
      z: p.x * sin + p.z * cos,
      type: p.type
    };
  }
  function rotateX(p, angle) {
    var cos = Math.cos(angle), sin = Math.sin(angle);
    return {
      x: p.x,
      y: p.y * cos - p.z * sin,
      z: p.y * sin + p.z * cos,
      type: p.type
    };
  }

  var timeOff = 0;
  function draw() {
    ctx.clearRect(0, 0, W, H);
    timeOff += 0.006;

    // Hanya tampil di login screen, sembunyikan di app screen
    var loginScreen = document.getElementById('login-screen');
    if (!loginScreen || !loginScreen.classList.contains('active')) {
      requestAnimationFrame(draw);
      return;
    }

    // Posisi di kanan layar, vertikal tengah
    var cy = H * 0.5;
    var cx = W * 0.75; // Geser ke kanan supaya tidak menimpa login card
    // Di layar kecil, tampilkan di bawah
    if (W < 900) { cx = W * 0.5; cy = H * 0.78; }
    var scale = Math.min(W, H) * 0.075; // Perbesar

    var offsetX = -2.25;
    var transformed = baseAtoms.map(function(a) {
      var p = { x: a.x + offsetX, y: a.y, z: a.z, type: a.type };
      p = rotateY(p, timeOff);
      p = rotateX(p, Math.sin(timeOff * 0.4) * 0.35);
      return p;
    });

    var projected = transformed.map(function(a) {
      var s = project(a, cx, cy, scale);
      return { x: s.sx, y: s.sy, z: a.z, depth: s.depth, type: a.type };
    });

    // Sort by depth
    var sortedBonds = baseBonds.slice().sort(function(a, b) {
      return (projected[a[0]].z + projected[a[1]].z) - (projected[b[0]].z + projected[b[1]].z);
    });

    // Draw bonds — putih/biru muda agar kontras di atas navy
    sortedBonds.forEach(function(b) {
      var p1 = projected[b[0]], p2 = projected[b[1]];
      var avgDepth = (p1.depth + p2.depth) * 0.5;
      ctx.beginPath();
      ctx.moveTo(p1.x, p1.y);
      ctx.lineTo(p2.x, p2.y);
      ctx.strokeStyle = 'rgba(147,197,253,' + (avgDepth * 0.65) + ')'; // biru muda
      ctx.lineWidth = 2.0 * avgDepth;
      ctx.stroke();
    });

    // Draw atoms — warna cerah kontras di atas navy
    projected.forEach(function(p) {
      var r, color, colorInner;
      if (p.type === 'C') {
        r = 13 * p.depth;
        color = 'rgba(255,255,255,'; colorInner = 'rgba(200,220,255,';
      } else if (p.type === 'O') {
        r = 11 * p.depth;
        color = 'rgba(252,165,165,'; colorInner = 'rgba(255,220,220,';
      } else {
        r = 6 * p.depth;
        color = 'rgba(147,197,253,'; colorInner = 'rgba(200,230,255,';
      }

      var grd = ctx.createRadialGradient(p.x - r*0.3, p.y - r*0.3, r*0.05, p.x, p.y, r);
      grd.addColorStop(0, colorInner + '0.95)');
      grd.addColorStop(0.5, color + '0.70)');
      grd.addColorStop(1, color + '0.08)');

      ctx.beginPath();
      ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
      ctx.fillStyle = grd;
      ctx.fill();
      ctx.strokeStyle = color + '0.50)';
      ctx.lineWidth = 0.7;
      ctx.stroke();
    });

    requestAnimationFrame(draw);
  }
  draw();
})();

// =====================
// DASHBOARD 3D MOLECULE VIEWER
// =====================
(function() {
  var MOLECULES = [
    {
      id: 'ethanol', name: 'Etanol', formula: 'C₂H₅OH', group: 'Alkohol',
      bp: '78.4°C', density: '0.789 g/mL', color: '#a5b4fc',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.54,y:0,z:0,type:'C'},
        {x:2.36,y:1.2,z:0.3,type:'O'}, {x:3.28,y:1.1,z:0.1,type:'H'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'},
        {x:-0.52,y:0,z:-1.1,type:'H'},
        {x:1.9,y:-0.9,z:0.7,type:'H'}, {x:1.9,y:-0.2,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[1,2],[2,3],[0,4],[0,5],[0,6],[1,7],[1,8]]
    },
    {
      id: 'acetone', name: 'Aseton', formula: 'CH₃COCH₃', group: 'Keton',
      bp: '56.1°C', density: '0.791 g/mL', color: '#fde68a',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.52,y:0,z:0,type:'C'}, {x:3.04,y:0,z:0,type:'C'},
        {x:1.52,y:1.22,z:0,type:'O'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'}, {x:-0.52,y:0,z:-1.1,type:'H'},
        {x:3.56,y:1.02,z:0.4,type:'H'}, {x:3.56,y:-1.02,z:0.4,type:'H'}, {x:3.56,y:0,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[1,2],[1,3],[0,4],[0,5],[0,6],[2,7],[2,8],[2,9]]
    },
    {
      id: 'acetic', name: 'Asam Asetat', formula: 'CH₃COOH', group: 'Asam Karboksilat',
      bp: '118°C', density: '1.049 g/mL', color: '#fca5a5',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.52,y:0,z:0,type:'C'},
        {x:2.1,y:1.22,z:0,type:'O'}, {x:2.35,y:-0.95,z:0.3,type:'O'}, {x:3.3,y:-0.8,z:0.2,type:'H'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'}, {x:-0.52,y:0,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[1,2],[1,3],[3,4],[0,5],[0,6],[0,7]]
    },
    {
      id: 'methyl_formate', name: 'Metil Asetat', formula: 'CH₃COOCH₃', group: 'Ester',
      bp: '57°C', density: '0.934 g/mL', color: '#6ee7b7',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.52,y:0,z:0,type:'C'},
        {x:2.1,y:1.22,z:0,type:'O'}, {x:2.35,y:-0.95,z:0,type:'O'}, {x:3.85,y:-0.95,z:0,type:'C'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'}, {x:-0.52,y:0,z:-1.1,type:'H'},
        {x:4.3,y:-0.1,z:0.4,type:'H'}, {x:4.3,y:-1.85,z:0.4,type:'H'}, {x:4.3,y:-0.95,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[1,2],[1,3],[3,4],[0,5],[0,6],[0,7],[4,8],[4,9],[4,10]]
    },
    {
      id: 'ethanal', name: 'Etanal', formula: 'CH₃CHO', group: 'Aldehid',
      bp: '20.2°C', density: '0.788 g/mL', color: '#c4b5fd',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.52,y:0,z:0,type:'C'},
        {x:2.1,y:1.22,z:0,type:'O'}, {x:2.1,y:-1.0,z:0,type:'H'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'}, {x:-0.52,y:0,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[1,2],[1,3],[0,4],[0,5],[0,6]]
    },
    {
      id: 'dimetil_eter', name: 'Dimetil Eter', formula: 'CH₃OCH₃', group: 'Eter',
      bp: '-24°C', density: '46.07 g/mL', color: '#fde68a',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.52,y:0,z:0,type:'O'}, {x:3.04,y:0,z:0,type:'C'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'}, {x:-0.52,y:0,z:-1.1,type:'H'},
        {x:3.56,y:1.02,z:0.4,type:'H'}, {x:3.56,y:-1.02,z:0.4,type:'H'}, {x:3.56,y:0,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[1,2],[0,3],[0,4],[0,5],[2,6],[2,7],[2,8]]
    },
    {
      id: 'metil_klorida', name: 'Metil Klorida', formula: 'CH₃Cl', group: 'Alkil Halida',
      bp: '-24.2°C', density: '50.49 g/mL', color: '#b7e0c0',
      atoms: [
        {x:0,y:0,z:0,type:'C'}, {x:1.76,y:0,z:0,type:'Cl'},
        {x:-0.52,y:1.02,z:0.4,type:'H'}, {x:-0.52,y:-1.02,z:0.4,type:'H'}, {x:-0.52,y:0,z:-1.1,type:'H'},
      ],
      bonds: [[0,1],[0,2],[0,3],[0,4]]
    },
  ];

  var activeMolIdx = 0;
  var dashRotY = 0, dashRotX = 0.25;
  var dashZoom = 1.0;
  var autoSpin = true;
  var dragging = false, lastMX = 0, lastMY = 0;

  function renderMolList() {
    var list = document.getElementById('mol3d-list');
    if (!list) return;
    list.innerHTML = '';
    MOLECULES.forEach(function(mol, i) {
      var btn = document.createElement('div');
      btn.className = 'mol3d-mol-btn' + (i===activeMolIdx?' active':'');
      btn.innerHTML = '<div class="mol3d-mol-dot" style="background:'+mol.color+'"></div>'
        +'<div><div class="mol3d-mol-name">'+mol.name+'</div>'
        +'<div class="mol3d-mol-formula">'+mol.formula+'</div></div>';
      btn.onclick = function(){ activeMolIdx = i; dashRotY=0; dashRotX=0.25; renderMolList(); renderMolInfo(); };
      list.appendChild(btn);
    });
  }

  function renderMolInfo() {
    var info = document.getElementById('mol3d-info');
    if (!info) return;
    var mol = MOLECULES[activeMolIdx];
    info.innerHTML = '<div class="mol3d-info-mol-name">'+mol.name+'</div>'
      +'<div class="mol3d-info-formula">'+mol.formula+'</div>'
      +'<div class="mol3d-info-row"><span class="mol3d-info-key">Gugus</span><span class="mol3d-info-val">'+mol.group+'</span></div>'
      +'<div class="mol3d-info-row"><span class="mol3d-info-key">Titik Didih</span><span class="mol3d-info-val">'+mol.bp+'</span></div>'
      +'<div class="mol3d-info-row"><span class="mol3d-info-key">Densitas</span><span class="mol3d-info-val">'+mol.density+'</span></div>';
  }

  // --- Canvas drawing ---
  var canvas, ctx, W, H;

  function initCanvas() {
    canvas = document.getElementById('dash-mol-canvas');
    if (!canvas) return false;
    ctx = canvas.getContext('2d');
    resizeCanvas();
    // Drag to rotate
    canvas.addEventListener('mousedown', function(e){ dragging=true; lastMX=e.clientX; lastMY=e.clientY; autoSpin=false; var ind=document.querySelector('.mol3d-spin-indicator'); if(ind)ind.style.opacity='0.3'; });
    canvas.addEventListener('mousemove', function(e){
      if (!dragging) return;
      dashRotY += (e.clientX - lastMX) * 0.012;
      dashRotX += (e.clientY - lastMY) * 0.012;
      lastMX=e.clientX; lastMY=e.clientY;
    });
    canvas.addEventListener('mouseup', function(){ dragging=false; });
    canvas.addEventListener('mouseleave', function(){ dragging=false; });
    // Touch support
    canvas.addEventListener('touchstart', function(e){ if(e.touches.length===1){ dragging=true; lastMX=e.touches[0].clientX; lastMY=e.touches[0].clientY; autoSpin=false; } },{passive:true});
    canvas.addEventListener('touchmove', function(e){ if(dragging&&e.touches.length===1){ dashRotY+=(e.touches[0].clientX-lastMX)*0.012; dashRotX+=(e.touches[0].clientY-lastMY)*0.012; lastMX=e.touches[0].clientX; lastMY=e.touches[0].clientY; } },{passive:true});
    canvas.addEventListener('touchend', function(){ dragging=false; });
    return true;
  }

  function resizeCanvas() {
    if (!canvas) return;
    W = canvas.width = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }

  function rotY3D(p, a) {
    var c=Math.cos(a),s=Math.sin(a);
    return {x:p.x*c-p.z*s, y:p.y, z:p.x*s+p.z*c, type:p.type};
  }
  function rotX3D(p, a) {
    var c=Math.cos(a),s=Math.sin(a);
    return {x:p.x, y:p.y*c-p.z*s, z:p.y*s+p.z*c, type:p.type};
  }
  function proj3D(p, cx, cy, sc) {
    var fov = 7;
    var persp = fov / (fov + p.z * 0.4);
    return { sx: cx + p.x*sc*persp, sy: cy + p.y*sc*persp, depth: persp };
  }

  // Atom color config
  var ATOM_CFG = {
    'C': { r: 14, fill1: 'rgba(165,180,252,', fill2: 'rgba(199,210,254,', stroke: 'rgba(139,92,246,' },
    'H': { r: 7,  fill1: 'rgba(147,197,253,', fill2: 'rgba(186,230,253,', stroke: 'rgba(56,189,248,' },
    'O': { r: 12, fill1: 'rgba(252,165,165,', fill2: 'rgba(254,202,202,', stroke: 'rgba(248,113,113,' },
    'Cl': { r: 16, fill1: 'rgba(74,222,128,', fill2: 'rgba(134,239,172,', stroke: 'rgba(34,197,94,' },
  };

  var frameCount = 0;
  function drawFrame() {
    requestAnimationFrame(drawFrame);

    var dashSec = document.getElementById('sec-dashboard');
    if (!dashSec || dashSec.style.display === 'none') return;
    if (!canvas) { initCanvas(); renderMolList(); renderMolInfo(); return; }
    if (!W || !H) { resizeCanvas(); return; }

    if (autoSpin && !dragging) dashRotY += 0.007;

    ctx.clearRect(0,0,W,H);

    // Background glow
    var bgGrd = ctx.createRadialGradient(W*0.5, H*0.5, 10, W*0.5, H*0.5, Math.min(W,H)*0.55);
    bgGrd.addColorStop(0, 'rgba(37,99,235,0.18)');
    bgGrd.addColorStop(1, 'transparent');
    ctx.fillStyle = bgGrd;
    ctx.fillRect(0,0,W,H);

    var mol = MOLECULES[activeMolIdx];
    var cx = W * 0.5, cy = H * 0.5;
    var sc = Math.min(W, H) * 0.09 * dashZoom;

    // Compute center offset
    var sumX = 0;
    mol.atoms.forEach(function(a){ sumX += a.x; });
    var ox = sumX / mol.atoms.length;

    // Transform
    var tAtoms = mol.atoms.map(function(a) {
      var p = {x: a.x - ox, y: a.y, z: a.z, type: a.type};
      p = rotY3D(p, dashRotY);
      p = rotX3D(p, dashRotX);
      return p;
    });

    var pAtoms = tAtoms.map(function(a) {
      var pr = proj3D(a, cx, cy, sc);
      return {x:pr.sx, y:pr.sy, depth:pr.depth, z:a.z, type:a.type};
    });

    // Sort bonds by avg z
    var sortedBonds = mol.bonds.slice().sort(function(a,b){
      return (pAtoms[a[0]].z + pAtoms[a[1]].z) - (pAtoms[b[0]].z + pAtoms[b[1]].z);
    });

    // Draw bonds with gradient + glow
    sortedBonds.forEach(function(b) {
      var p1 = pAtoms[b[0]], p2 = pAtoms[b[1]];
      var avgDepth = (p1.depth + p2.depth) * 0.5;
      var alpha = 0.35 + avgDepth * 0.4;
      // Glow pass
      ctx.beginPath();
      ctx.moveTo(p1.x, p1.y);
      ctx.lineTo(p2.x, p2.y);
      ctx.strokeStyle = 'rgba(147,197,253,' + (alpha * 0.4) + ')';
      ctx.lineWidth = 7 * avgDepth;
      ctx.lineCap = 'round';
      ctx.stroke();
      // Main bond
      ctx.beginPath();
      ctx.moveTo(p1.x, p1.y);
      ctx.lineTo(p2.x, p2.y);
      ctx.strokeStyle = 'rgba(147,197,253,' + alpha + ')';
      ctx.lineWidth = 2.5 * avgDepth;
      ctx.stroke();
    });

    // Draw atoms — sorted by depth (back to front)
    var sortedAtoms = pAtoms.slice().sort(function(a,b){ return a.depth - b.depth; });
    sortedAtoms.forEach(function(p) {
      var cfg = ATOM_CFG[p.type] || ATOM_CFG['C'];
      var r = cfg.r * p.depth;
      // Outer glow
      var glowGrd = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, r * 2.2);
      glowGrd.addColorStop(0, cfg.fill1 + '0.20)');
      glowGrd.addColorStop(1, cfg.fill1 + '0)');
      ctx.beginPath(); ctx.arc(p.x, p.y, r*2.2, 0, Math.PI*2);
      ctx.fillStyle = glowGrd; ctx.fill();

      // Atom sphere
      var sphereGrd = ctx.createRadialGradient(p.x - r*0.32, p.y - r*0.32, r*0.06, p.x, p.y, r);
      sphereGrd.addColorStop(0, cfg.fill2 + '1)');
      sphereGrd.addColorStop(0.45, cfg.fill1 + '0.95)');
      sphereGrd.addColorStop(1, cfg.fill1 + '0.15)');
      ctx.beginPath(); ctx.arc(p.x, p.y, r, 0, Math.PI*2);
      ctx.fillStyle = sphereGrd; ctx.fill();

      // Specular highlight
      ctx.beginPath(); ctx.arc(p.x - r*0.28, p.y - r*0.28, r*0.22, 0, Math.PI*2);
      ctx.fillStyle = 'rgba(255,255,255,0.65)'; ctx.fill();

      // Stroke ring
      ctx.beginPath(); ctx.arc(p.x, p.y, r, 0, Math.PI*2);
      ctx.strokeStyle = cfg.stroke + (0.4 + p.depth*0.3) + ')';
      ctx.lineWidth = 1.0; ctx.stroke();
    });

    // Molecule name label at bottom
    ctx.font = '700 11px "JetBrains Mono", monospace';
    ctx.textAlign = 'right';
    ctx.fillStyle = 'rgba(165,180,252,0.60)';
    ctx.fillText(mol.formula, W - 12, H - 12);
  }

  // Init after DOM ready
  window.addEventListener('load', function() {
    if (initCanvas()) {
      renderMolList();
      renderMolInfo();
    }
    drawFrame();
    window.addEventListener('resize', function(){ if(canvas) resizeCanvas(); });
  });

  // Global controls
  window.dashMolZoom = function(f) { dashZoom = Math.max(0.4, Math.min(2.5, dashZoom * f)); };
  window.dashMolReset = function() { dashRotY=0; dashRotX=0.25; dashZoom=1.0; autoSpin=true; var ind=document.querySelector('.mol3d-spin-indicator'); if(ind)ind.style.opacity='1'; };

})();

// =====================
// PASSWORDS & ROLE
// =====================
var PASS = { guru: 'guru2024', siswa: '' };
var selectedRole = '';

function pilihRole(role) {
  selectedRole = role;
  document.getElementById('role-btn-guru').classList.toggle('selected', role==='guru');
  document.getElementById('role-btn-siswa').classList.toggle('selected', role==='siswa');
  document.getElementById('pw-section-guru').classList.toggle('hidden', role!=='guru');
  document.getElementById('pw-section-siswa').classList.toggle('hidden', role!=='siswa');
  var btn = document.getElementById('btn-masuk');
  btn.disabled = false;
  btn.textContent = role==='guru' ? '→ Masuk sebagai Guru' : '→ Masuk sebagai Siswa';
  if (role==='guru') {
    setTimeout(function(){ document.getElementById('pw-guru').focus(); }, 100);
  }
}

function togglePw(inputId, btn) {
  var el = document.getElementById(inputId);
  if (!el) return;
  if (el.type === 'password') {
    el.type = 'text';
    btn.textContent = '🙈';
  } else {
    el.type = 'password';
    btn.textContent = '👁';
  }
}

function aksesLogin(role) {
  if (!role) return;
  var err = document.getElementById('err-guru');
  if (role === 'guru') {
    var pw = document.getElementById('pw-guru').value.trim();
    if (pw !== PASS.guru) {
      if (err) err.style.display = 'block';
      document.getElementById('pw-guru').value = '';
      document.getElementById('pw-guru').focus();
      return;
    }
    if (err) err.style.display = 'none';
  }
  tampilkanFormProfil(role);
}

// =====================
// STATE & DB
// =====================
var currentRole = '';
var currentSection = 'dashboard';
var pendingRole = '';
var currentUserData = {};
var modalTempFile = null;
var modalType = '';
var diskusiLastReadIndex = -1;

// === PERSISTENT CHAT (localStorage) ===
var CHAT_KEY = 'sichemor_messages_v2';

function loadMessages() {
  try {
    var raw = localStorage.getItem(CHAT_KEY);
    if (raw) return JSON.parse(raw);
  } catch(e) {}
  return [];
}
function saveMessages(msgs) {
  try { localStorage.setItem(CHAT_KEY, JSON.stringify(msgs)); } catch(e) {}
}

var LAST_READ_KEY = 'sichemor_lastread_v2';
function loadLastRead() {
  try { var v = localStorage.getItem(LAST_READ_KEY); if (v !== null) return parseInt(v); } catch(e) {}
  return -1;
}
function saveLastRead(idx) {
  try { localStorage.setItem(LAST_READ_KEY, String(idx)); } catch(e) {}
}

// Update struktur DB di bagian awal
var DB = {
  presensi: [],
  presensiRiwayat: [],
  nilaiKuis: [],
  kuis: [],
  tugasFiles: {},  // Menyimpan file jawaban siswa per tugas
  tugasGuru: [],   // Menyimpan data tugas yang dibuat guru
  siswaJawaban: {},
  kontenModul: [],
  kontenVideo: [],
  kontenZoom: [],
  messages: []
};

// Load persistent messages
DB.messages = loadMessages();
diskusiLastReadIndex = loadLastRead();

// Sample tugas data
DB.tugasFiles[1] = [
  {nama:'Arya Pratama',kelas:'XII MIPA 2',waktu:'08:15',filename:'LKPD1_Arya.pdf',filesize:'1.2 MB',fileUrl:null,status:'Tepat Waktu'},
  {nama:'Sari Dewi',kelas:'XII MIPA 2',waktu:'09:30',filename:'LKPD1_Sari.pdf',filesize:'0.8 MB',fileUrl:null,status:'Tepat Waktu'},
  {nama:'Budi Santoso',kelas:'XII MIPA 2',waktu:'21:45',filename:'LKPD1_Budi.jpg',filesize:'2.4 MB',fileUrl:null,status:'Tepat Waktu'},
  {nama:'Fikri Ramadhan',kelas:'XII MIPA 2',waktu:'07:55',filename:'LKPD1_Fikri.pdf',filesize:'1.5 MB',fileUrl:null,status:'Tepat Waktu'},
  {nama:'Dani Kurniawan',kelas:'XII MIPA 2',waktu:'23:10',filename:'LKPD1_Dani.docx',filesize:'0.6 MB',fileUrl:null,status:'Tepat Waktu'},
  {nama:'Mega Pertiwi',kelas:'XII MIPA 2',waktu:'08:40',filename:'LKPD1_Mega.pdf',filesize:'1.9 MB',fileUrl:null,status:'Tepat Waktu'},
];

var activeKuisId = null;
var kuisAnswers = {};
var kuisTimerInterval = null;
var kuisTimeLeft = 0;

// =====================
// NAV BADGES
// =====================
function updateNavBadges() {
  var badge = document.getElementById('badge-nav-diskusi');
  if (badge) {
    var unread = DB.messages.length - (diskusiLastReadIndex + 1);
    if (unread > 0) {
      badge.textContent = unread;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  }
  var statModul = document.getElementById('stat-modul');
  if (statModul) statModul.textContent = DB.kontenModul.length;
  var statModulSub = document.getElementById('stat-modul-sub');
  if (statModulSub) statModulSub.textContent = DB.kontenModul.length > 0 ? '✓ '+DB.kontenModul.length+' modul' : 'Belum ada modul';
  var statVideo = document.getElementById('stat-video');
  if (statVideo) statVideo.textContent = DB.kontenVideo.length;
  var statVideoSub = document.getElementById('stat-video-sub');
  if (statVideoSub) statVideoSub.textContent = DB.kontenVideo.length > 0 ? '+'+DB.kontenVideo.length+' video' : 'Belum ada video';
}

function tandaiDiskusiDibaca() {
  diskusiLastReadIndex = DB.messages.length - 1;
  saveLastRead(diskusiLastReadIndex);
  updateNavBadges();
}

// =====================
// LOGIN FLOW
// =====================
function tampilkanFormProfil(role) {
  pendingRole = role;
  var ps = document.getElementById('profile-screen');
  ps.style.display = 'flex';
  ps.classList.add('active');
  var card = document.getElementById('profile-card');
  if (role === 'guru') {
    card.className = 'profile-card guru-card';
    document.getElementById('profile-icon').textContent = '🎓';
    document.getElementById('profile-heading').textContent = 'Profil Guru';
    document.getElementById('profile-sub').textContent = 'Lengkapi informasi akun guru';
    document.getElementById('guru-fields').style.display = 'block';
    document.getElementById('siswa-fields').style.display = 'none';
  } else {
    card.className = 'profile-card siswa-card';
    document.getElementById('profile-icon').textContent = '🧪';
    document.getElementById('profile-heading').textContent = 'Profil Siswa';
    document.getElementById('profile-sub').textContent = 'Lengkapi nama dan kelas Anda';
    document.getElementById('guru-fields').style.display = 'none';
    document.getElementById('siswa-fields').style.display = 'block';
  }
}

function kembaliLogin() {
  var ps = document.getElementById('profile-screen');
  ps.style.display = 'none';
  ps.classList.remove('active');
}

function submitProfil() {
  var userData = {};
  if (pendingRole === 'guru') {
    var nama = document.getElementById('guru-nama').value.trim();
    var username = document.getElementById('guru-username').value.trim();
    var email = document.getElementById('guru-email').value.trim();
    if (!nama || !username || !email) { alert('Harap isi semua field terlebih dahulu.'); return; }
    userData = {nama:nama, username:username, email:email, mapel:'Kimia Organik'};
  } else {
    var nama = document.getElementById('siswa-nama').value.trim();
    var kelas = document.getElementById('siswa-kelas').value;
    var absen = document.getElementById('siswa-absen').value.trim();
    var email = document.getElementById('siswa-email').value.trim();
    if (!nama || !kelas || !absen) { alert('Harap isi nama, kelas, dan nomor absen.'); return; }
    userData = {nama:nama, kelas:kelas, absen:absen, email:email};
  }
  currentUserData = userData;
  kembaliLogin();
  masukApp(pendingRole, userData);
}

function toggleSidebar() {
  const sidebar = document.getElementById('main-sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle-btn');
  
  if (!sidebar || !toggleBtn) return;
  
  // Toggle class collapsed
  sidebar.classList.toggle('collapsed');
  
  // Ubah arah panah dan simpan status
  if (sidebar.classList.contains('collapsed')) {
    toggleBtn.innerHTML = '▶';
    toggleBtn.setAttribute('aria-label', 'Buka sidebar');
    localStorage.setItem('sichemor_sidebar_collapsed', 'true');
    
    // Optional: Trigger event untuk komponen lain yang perlu menyesuaikan
    window.dispatchEvent(new CustomEvent('sidebarToggle', { detail: { collapsed: true } }));
  } else {
    toggleBtn.innerHTML = '◀';
    toggleBtn.setAttribute('aria-label', 'Tutup sidebar');
    localStorage.setItem('sichemor_sidebar_collapsed', 'false');
    
    window.dispatchEvent(new CustomEvent('sidebarToggle', { detail: { collapsed: false } }));
  }
  
  // Adjust main content jika perlu (untuk mobile)
  adjustMainContentForSidebar();
}

// Fungsi untuk memuat status sidebar dari localStorage
function loadSidebarState() {
  const sidebar = document.getElementById('main-sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle-btn');
  
  if (!sidebar || !toggleBtn) {
    // Jika elemen belum ada, coba lagi setelah 100ms
    if (!sidebarInitialized) {
      sidebarInitialized = true;
      setTimeout(loadSidebarState, 100);
    }
    return;
  }
  
  // Baca status dari localStorage, default: false (terbuka)
  const savedState = localStorage.getItem('sichemor_sidebar_collapsed');
  const isCollapsed = savedState === 'true';
  
  if (isCollapsed) {
    sidebar.classList.add('collapsed');
    toggleBtn.innerHTML = '▶';
    toggleBtn.setAttribute('aria-label', 'Buka sidebar');
  } else {
    sidebar.classList.remove('collapsed');
    toggleBtn.innerHTML = '◀';
    toggleBtn.setAttribute('aria-label', 'Tutup sidebar');
  }
  
  adjustMainContentForSidebar();
  sidebarInitialized = true;
}

// Fungsi untuk menyesuaikan konten utama (terutama untuk mobile)
function adjustMainContentForSidebar() {
  const sidebar = document.getElementById('main-sidebar');
  const mainContent = document.querySelector('.main-content');
  
  if (!sidebar || !mainContent) return;
  
  // Untuk mobile, kita tidak perlu menyesuaikan margin
  if (window.innerWidth <= 768) {
    mainContent.style.marginLeft = '0';
    return;
  }
  
  // Untuk desktop, sesuaikan margin konten utama
  if (sidebar.classList.contains('collapsed')) {
    mainContent.style.marginLeft = '72px';
  } else {
    mainContent.style.marginLeft = '256px';
  }
}

// Fungsi untuk inisialisasi sidebar (dipanggil saat app screen aktif)
// Fungsi sidebar sederhana
var sidebarInitialized = false;

function initSidebar() {
  const sidebar = document.getElementById('main-sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle-btn');
  
  if (!sidebar || !toggleBtn) return;
  if (sidebarInitialized) return;
  sidebarInitialized = true;
  
  // Cek status dari localStorage
  const savedState = localStorage.getItem('sichemor_sidebar_collapsed');
  const isCollapsed = savedState === 'true';
  
  if (isCollapsed) {
    sidebar.classList.add('collapsed');
    toggleBtn.innerHTML = '▶';
  } else {
    sidebar.classList.remove('collapsed');
    toggleBtn.innerHTML = '◀';
  }
  
  // Event toggle
  toggleBtn.onclick = function(e) {
    e.stopPropagation();
    sidebar.classList.toggle('collapsed');
    
    if (sidebar.classList.contains('collapsed')) {
      toggleBtn.innerHTML = '▶';
      localStorage.setItem('sichemor_sidebar_collapsed', 'true');
    } else {
      toggleBtn.innerHTML = '◀';
      localStorage.setItem('sichemor_sidebar_collapsed', 'false');
    }
    
    // Adjust main content
    const mainContent = document.querySelector('.main-content');
    if (mainContent && window.innerWidth > 768) {
      mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '72px' : '256px';
    }
  };
  
  // Adjust margin
  const mainContent = document.querySelector('.main-content');
  if (mainContent && window.innerWidth > 768) {
    mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '72px' : '256px';
  }
}

// Fungsi untuk reset sidebar (opsional, bisa dipanggil saat logout)
function resetSidebar() {
  localStorage.removeItem('sichemor_sidebar_collapsed');
  const sidebar = document.getElementById('main-sidebar');
  const toggleBtn = document.getElementById('sidebar-toggle-btn');
  
  if (sidebar && toggleBtn) {
    sidebar.classList.remove('collapsed');
    toggleBtn.innerHTML = '◀';
    toggleBtn.setAttribute('aria-label', 'Tutup sidebar');
  }
}

function masukApp(role, userData) {
  currentRole = role;
  document.body.className = 'role-' + role;
  document.getElementById('login-screen').classList.remove('active');
  var app = document.getElementById('app-screen');
  app.classList.add('active');
// Auto masuk jika dari halaman upload
if (window.location.search.indexOf('masuk=1') !== -1) {
    masukApp('siswa', {nama:'Siswa', kelas:'', absen:''});
}

  var displayName = userData ? userData.nama : (role==='guru' ? 'Ibu Ratna' : 'Arya Pratama');
  var displayRole = role==='guru'
    ? 'Guru · ' + (userData.username || '')
    : (userData.kelas||'XII MIPA 2') + ' · No. ' + (userData.absen||'-');

  document.getElementById('user-name').textContent = displayName;
  document.getElementById('user-role').textContent = displayRole;
  document.getElementById('user-avatar').textContent = role==='guru' ? '🎓' : '👨‍🎓';
  document.getElementById('user-avatar').className = 'avatar ' + (role==='guru' ? 'guru-av' : 'siswa-av');
  document.getElementById('role-badge').textContent = role.toUpperCase();
  document.getElementById('role-badge').className = 'role-badge ' + role;

  var now = new Date();
  var opts = {weekday:'long',year:'numeric',month:'long',day:'numeric'};
  var tanggalEl = document.getElementById('presensi-tanggal');
  if (tanggalEl) tanggalEl.textContent = now.toLocaleDateString('id-ID', opts);

  initDiscussions();
  renderPresensiGuru();
  renderHasilKuis();
  
  // Muat data dari localStorage
  muatTugasGuruDariLocalStorage();
  muatKuisDariLocalStorage();
  muatSemuaTugasDariLocalStorageDinamis();
  
  // Render komponen - gunakan fungsi dinamis
  if (role === 'guru') {
    renderTugasGuruDariDB();
    renderKuisGuru();
  } else {
    renderTugasSiswaDariDB();
    renderKuisSiswa();
  }
  
  renderRekapPertemuan();
  renderKontenModul();
  renderKontenVideo();
  renderKontenZoom();
  renderDashboardPreview();
  updateNavBadges();
  showSection('dashboard');
  
  // Inisialisasi sidebar
  initSidebar();
}

// =====================
// NAVIGATION
// =====================
function setActive(el, section) {
  document.querySelectorAll('.nav-item').forEach(function(i){ i.classList.remove('active'); });
  el.classList.add('active');
  showSection(section);
  var txt = el.textContent.trim().replace(/[^a-zA-Z\s\u00C0-\u024F\/]/g,'').trim();
  document.getElementById('page-title').textContent = txt || section;
}

function showSection(name) {
  currentSection = name;
  document.querySelectorAll('[id^="sec-"]').forEach(function(s) { 
    s.style.display = 'none'; 
  });
  
  var sec = document.getElementById('sec-' + name);
  if (sec) sec.style.display = 'block';
  
  // Update tampilan tugas saat membuka section tugas
  if (name === 'tugas') {
    if (currentRole === 'guru') {
      renderTugasGuruDariDB();
    } else if (currentRole === 'siswa') {
      renderTugasSiswaDariDB();
    }
  }
  // Load modul dari DB saat buka halaman materi
  if (name === 'materi') {
    renderKontenModulDariDB();
  }
}

// =====================
// DISCUSSION — Persistent via localStorage
// =====================
function renderDiscussion(containerId, showMod) {
  var container = document.getElementById(containerId);
  if (!container) return;

  // Buat struktur dulu, lalu load pesan dari DB
  container.innerHTML = '<div class="discussion-container">'
    + '<div class="disc-header"><span style="font-size:0.95rem">💬</span><div class="disc-title">Forum Diskusi</div><div class="disc-count" id="disc-count-' + containerId + '">...</div></div>'
    + '<div class="messages" id="msg-list-' + containerId + '"><div class="disc-empty"><div class="disc-empty-icon">⏳</div><div class="disc-empty-text">Memuat pesan...</div></div></div>'
    + '<div class="disc-input">'
    + '<textarea class="input-field" placeholder="Tulis pesan diskusi..." rows="1" id="chat-input-' + containerId + '" onkeydown="sendMsg(event,\'' + containerId + '\')"></textarea>'
    + '<button class="btn-send" onclick="sendMsg(null,\'' + containerId + '\')">➤</button>'
    + '</div></div>';

  loadPesanDiskusi(containerId, showMod);
}

function loadPesanDiskusi(containerId, showMod) {
  fetch('api.php?action=get_diskusi')
    .then(function(r) { return r.json(); })
    .then(function(res) {
      var msgList = document.getElementById('msg-list-' + containerId);
      var countEl = document.getElementById('disc-count-' + containerId);
      if (!msgList) return;
      var data = (res.success && res.data) ? res.data : [];
      if (countEl) countEl.textContent = data.length + ' pesan';
      if (data.length === 0) {
        msgList.innerHTML = '<div class="disc-empty"><div class="disc-empty-icon">💬</div><div class="disc-empty-text">Belum ada pesan. Mulai diskusi sekarang!</div></div>';
        return;
      }
      msgList.innerHTML = data.map(function(m) {
        var avatarStyle = m.role==='guru'
          ? 'background:var(--accent-dim);border-color:rgba(30,58,138,0.25);'
          : 'background:var(--emerald-dim);border-color:rgba(63,185,80,0.25);';
        var avatar = m.role==='guru' ? '🎓' : '👤';
        var waktu = m.waktu ? m.waktu.substring(11,16) : '';
        return '<div class="message">'
          + '<div class="msg-avatar" style="' + avatarStyle + '">' + avatar + '</div>'
          + '<div class="msg-body">'
          + '<div class="msg-header"><span class="msg-name ' + m.role + '">' + escapeHtml(m.nama) + '</span>'
          + '<span class="msg-role-tag ' + m.role + '">' + (m.role==='guru'?'Guru':'Siswa') + '</span>'
          + '<span class="msg-time">' + waktu + '</span></div>'
          + '<div class="msg-text">' + escapeHtml(m.pesan) + '</div>'
          + '<div class="msg-actions"><button class="msg-action-btn">👍 Suka</button><button class="msg-action-btn">↩ Balas</button>'
          + (showMod || currentRole==='guru' ? '<button class="msg-action-btn" style="margin-left:auto;color:var(--text-dim)" onclick="hapusPesan(' + m.id + ',\'' + containerId + '\')">🗑 Hapus</button>' : '')
          + '</div></div></div>';
      }).join('');
      msgList.scrollTop = msgList.scrollHeight;
    })
    .catch(function() {
      var msgList = document.getElementById('msg-list-' + containerId);
      if (msgList) msgList.innerHTML = '<div style="color:var(--red);padding:1rem;text-align:center;">❌ Gagal memuat pesan.</div>';
    });
}
// =====================
// FUNGSI HAPUS KUIS
// =====================

function hapusKuis(kuisId) {
  if (!confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus kuis ini?\n\nSemua data nilai siswa untuk kuis ini juga akan dihapus permanen.\n\nTindakan ini tidak dapat dibatalkan!')) {
    return;
  }
  
  var kuis = DB.kuis.find(function(k) { return k.id === kuisId; });
  if (!kuis) {
    alert('Kuis tidak ditemukan!');
    return;
  }
  
  var judulKuis = kuis.judul;
  
  // Hapus dari array kuis
  var kuisIndex = DB.kuis.findIndex(function(k) { return k.id === kuisId; });
  if (kuisIndex >= 0) {
    DB.kuis.splice(kuisIndex, 1);
  }
  
  // Hapus semua nilai kuis yang terkait
  var nilaiTerhapus = DB.nilaiKuis.filter(function(n) { return n.id === kuisId; }).length;
  DB.nilaiKuis = DB.nilaiKuis.filter(function(n) { return n.id !== kuisId; });
  
  // Simpan ke localStorage
  simpanKuisKeLocalStorage();
  
  // Refresh tampilan
  renderKuisGuru();
  renderKuisSiswa();
  
  // Jika sedang menampilkan hasil kuis, kembali ke daftar kuis
  var hasilKuis = document.getElementById('hasil-kuis');
  if (hasilKuis && hasilKuis.style.display === 'block') {
    switchKuisTabByName('daftar-kuis');
  }
  
  showToast('🗑️ Kuis "' + judulKuis + '" berhasil dihapus! (' + nilaiTerhapus + ' data nilai siswa ikut terhapus)');
}

// Perbaiki fungsi renderKuisGuru untuk menambahkan tombol hapus
// Fungsi untuk merefresh tampilan kuis di halaman guru
function renderKuisGuru() {
  var daftarKuis = document.getElementById('daftar-kuis');
  if (!daftarKuis) return;
  
  if (!DB.kuis || DB.kuis.length === 0) {
    daftarKuis.innerHTML = '<div class="empty-state">' +
      '<div class="empty-icon">📝</div>' +
      '<div class="empty-text">Belum ada kuis</div>' +
      '<div class="empty-sub">Klik "+ Buat Kuis Baru" untuk membuat kuis pertama.</div>' +
      '</div>';
    return;
  }
  
  var html = '<div class="kuis-grid">';
  
  DB.kuis.forEach(function(kuis) {
    var totalSiswa = 24; // Total siswa
    var sudahMengerjakan = DB.nilaiKuis.filter(function(n) { return n.id === kuis.id; }).length;
    var persen = totalSiswa > 0 ? Math.round((sudahMengerjakan / totalSiswa) * 100) : 0;
    
    html += '<div class="kuis-card" id="kuis-card-' + kuis.id + '">' +
      '<div class="kuis-header">' +
      '<div>' +
      '<div class="kuis-title">' + escapeHtml(kuis.judul) + '</div>' +
      '<div class="kuis-meta">' + kuis.soal.length + ' soal · ' + kuis.durasi + ' menit</div>' +
      '</div>' +
      '<span class="badge-kuis">AKTIF</span>' +
      '</div>' +
      '<div style="display:flex;gap:0.4rem;margin-top:0.6rem;">' +
      '<span style="font-size:0.68rem;color:var(--emerald);">✓ ' + sudahMengerjakan + ' sudah</span>' +
      '<span style="font-size:0.68rem;color:var(--text-dim);">·</span>' +
      '<span style="font-size:0.68rem;color:var(--accent);">' + (totalSiswa - sudahMengerjakan) + ' belum</span>' +
      '</div>' +
      '<div class="progress-bar"><div class="progress-fill" style="width:' + persen + '%"></div></div>' +
      '<div style="display:flex;gap:0.4rem;margin-top:0.8rem;">' +
      '<button class="btn-kuis" style="flex:1" onclick="guruLihatHasil(' + kuis.id + ')">📊 Lihat Hasil</button>' +
      '<button class="btn-kuis" style="flex:1" onclick="openEditSoal(' + kuis.id + ')">✏️ Edit Soal</button>' +
      '<button class="btn-kuis" style="flex:1;background:rgba(220,38,38,0.07);color:var(--red);border-color:rgba(220,38,38,0.22);" onclick="hapusKuis(' + kuis.id + ')">🗑 Hapus</button>' +
      '</div>' +
      '</div>';
  });
  
  html += '</div>';
  daftarKuis.innerHTML = html;
}
function hapusPesan(id, containerId) {
  if (!confirm('Hapus pesan ini?')) return;
  var fd = new FormData();
  fd.append('action', 'hapus_pesan');
  fd.append('id', id);
  fetch('api.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        initDiscussions();
        updateNavBadges();
      } else {
        alert('Gagal hapus pesan.');
      }
    });
}

function sendMsg(e, containerId) {
  if (e && e.key !== 'Enter') return;
  var input = document.getElementById('chat-input-' + containerId);
  var text = input ? input.value.trim() : '';
  if (!text) return;

  var nama = currentRole==='guru' ? (currentUserData.nama||'Guru') : (currentUserData.nama||'Siswa');
  var fd = new FormData();
  fd.append('action', 'kirim_pesan');
  fd.append('nama', nama);
  fd.append('role', currentRole);
  fd.append('pesan', text);

  input.value = '';

  fetch('api.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        initDiscussions();
        updateNavBadges();
      }
    });
}

function initDiscussions() {
  ['disc-main','disc-materi','disc-video','disc-zoom','disc-lab'].forEach(function(id){
    renderDiscussion(id, currentRole==='guru');
  });
  renderDiscussion('disc-mod', true);
}

// =====================
// MODAL CONTENTS
// =====================
var modalContents = {
  'tambah-konten': {
    title:'＋ Tambah Konten',
    render: function() {
      return '<div class="form-group">'
        + '<label class="form-label">Tujuan Navigasi</label>'
        + '<select class="form-select" id="konten-tujuan" onchange="onTujuanChange()">'
        + '<option value="">-- Pilih Tujuan --</option>'
        + '<option value="modul">📚 Modul Materi</option>'
        + '<option value="video">▶️ Media Gallery (Video YouTube)</option>'
        + '<option value="zoom">📹 Virtual Room (Zoom)</option>'
        + '</select></div>'
        + '<div id="konten-modul-fields" style="display:none">'
        + '<div class="form-group"><label class="form-label">Judul Modul</label><input type="text" class="form-input" id="konten-judul" placeholder="Contoh: Alkohol — Gugus Fungsi OH"></div>'
        + '<div class="form-group"><label class="form-label">Deskripsi Singkat</label><input type="text" class="form-input" id="konten-desc" placeholder="Deskripsi isi modul"></div>'
        + '<div class="form-group"><label class="form-label">Gugus Fungsi / Tipe</label><select class="form-select" id="konten-gugus"><option>Gugus –OH · Alkohol</option><option>Gugus –O– · Eter</option><option>Gugus –CHO · Aldehid</option><option>Gugus C=O · Keton</option><option>Gugus –COOH · Asam Karboksilat</option><option>Gugus –COO– · Ester</option><option>Gugus –X · Alkil Halida</option><option>Lainnya</option></select></div>'
        + '<div class="form-group"><label class="form-label">Jenis Modul</label>'
        + '<div class="modul-tipe-tabs"><button type="button" class="modul-tipe-tab active" id="tab-tipe-file" onclick="pilihTipeModul(\'file\')">📄 Upload File</button><button type="button" class="modul-tipe-tab" id="tab-tipe-link" onclick="pilihTipeModul(\'link\')">🔗 Link / URL</button></div>'
        + '</div>'
        + '<div id="konten-modul-file-section"><div class="form-group"><label class="form-label">Unggah File Modul</label>'
        + '<div class="modal-upload-zone" id="modal-upload-zone-modul" onclick="document.getElementById(\'modal-file-input\').click()">'
        + '<input type="file" id="modal-file-input" style="display:none" onchange="onModalFileSelected(this)">'
        + '<div style="font-size:1.5rem;opacity:0.5;">📄</div>'
        + '<div class="modal-upload-zone-label">Klik untuk pilih file modul</div>'
        + '<div class="modal-upload-zone-types">Semua format diterima · Maks: 100MB</div></div>'
        + '<div id="modal-file-preview-modul" style="display:none" class="modal-file-preview"><span style="font-size:1rem">📄</span><span id="modal-file-name-modul">—</span><button class="modal-file-remove" onclick="clearModalFile()">✕ Hapus</button></div>'
        + '</div></div>'
        + '<div id="konten-modul-link-section" style="display:none"><div class="form-group"><label class="form-label">URL / Link Modul</label><input type="url" class="form-input" id="konten-link-modul" placeholder="https://drive.google.com/..."></div></div>'
        + '</div>'
        + '<div id="konten-video-fields" style="display:none">'
        + '<div class="form-group"><label class="form-label">Judul Video</label><input type="text" class="form-input" id="konten-judul-video" placeholder="Contoh: Tutorial Tata Nama IUPAC Alkohol"></div>'
        + '<div class="form-group"><label class="form-label">Deskripsi</label><input type="text" class="form-input" id="konten-desc-video" placeholder="Deskripsi singkat video"></div>'
        + '<div class="form-group"><label class="form-label">URL YouTube</label><input type="text" class="form-input" id="konten-url-video" placeholder="https://www.youtube.com/watch?v=..."></div>'
        + '</div>'
        + '<div id="konten-zoom-fields" style="display:none">'
        + '<div class="form-group"><label class="form-label">Nama Sesi</label><input type="text" class="form-input" id="konten-judul-zoom" placeholder="Contoh: Pertemuan 5 — Aldehid & Keton"></div>'
        + '<div class="form-group"><label class="form-label">Jadwal</label><input type="datetime-local" class="form-input" id="konten-waktu-zoom"></div>'
        + '<div class="form-group"><label class="form-label">Link Zoom Meeting</label><input type="text" class="form-input" id="konten-link-zoom" placeholder="https://zoom.us/j/..."></div>'
        + '<div class="form-group"><label class="form-label">Kelas</label><select class="form-select" id="konten-kelas-zoom"><option>XII MIPA 1</option><option selected>XII MIPA 2</option><option>XII MIPA 3</option><option>XII MIPA 4</option><option>Semua Kelas</option></select></div>'
        + '</div>';
    }
  },
  'buat-kuis': {  // <-- GANTI BAGIAN INI
     title:'Buat Kuis Baru',
     render: function() {
       return '<div class="form-group">' +
         '<label class="form-label">Judul Kuis</label>' +
         '<input type="text" class="form-input" id="kuis-judul" placeholder="Contoh: Kuis 3 — Reaksi Kimia Alkohol">' +
         '</div>' +
         '<div class="form-group">' +
         '<label class="form-label">Durasi (menit)</label>' +
         '<input type="number" class="form-input" id="kuis-durasi" value="10" min="1" max="120">' +
         '</div>' +
         '<div class="form-group">' +
         '<label class="form-label">Deadline</label>' +
         '<input type="datetime-local" class="form-input" id="kuis-deadline">' +
         '</div>' +
         '<div id="soal-container-buat" style="max-height:400px;overflow-y:auto;padding-right:4px;">' +
         // Soal akan ditambahkan secara dinamis
         '</div>' +
         '<button class="btn-secondary" style="width:100%;margin-bottom:0.4rem;" onclick="tambahSoalBaruBuat()">' +
         '＋ Tambah Soal Baru' +
         '</button>';
     }
   },
  'presensi': {
    title:'Buat Sesi Presensi',
    render: function() {
      return '<div class="form-group"><label class="form-label">Nama Pertemuan</label><input type="text" class="form-input" id="p-nama" placeholder="Contoh: Pertemuan 5 — Aldehid & Keton"></div>'
        + '<div class="form-group"><label class="form-label">Tanggal & Waktu</label><input type="datetime-local" class="form-input" id="p-waktu"></div>'
        + '<div class="form-group"><label class="form-label">Kelas</label><select class="form-select" id="p-kelas"><option>XII MIPA 1</option><option selected>XII MIPA 2</option><option>XII MIPA 3</option><option>XII MIPA 4</option></select></div>'
        + '<div class="form-group"><label class="form-label">Batas Waktu Presensi (menit)</label><input type="number" class="form-input" value="30" min="5" max="120"></div>';
    }
  },
 'tugas': {
  title:'Buat Tugas / LKPD Baru',
  render: function() {
    return '<div class="form-group">' +
      '<label class="form-label">Judul Tugas</label>' +
      '<input type="text" class="form-input" id="modal-tugas-judul" placeholder="Contoh: LKPD 3 — Reaksi Esterifikasi">' +
      '</div>' +
      '<div class="form-group">' +
      '<label class="form-label">Deskripsi / Instruksi</label>' +
      '<textarea class="form-textarea" id="modal-tugas-desc" placeholder="Tulis instruksi pengerjaan tugas..."></textarea>' +
      '</div>' +
      '<div class="form-group">' +
      '<label class="form-label">Deadline</label>' +
      '<input type="datetime-local" class="form-input" id="modal-tugas-deadline">' +
      '</div>' +
      '<div class="form-group">' +
      '<label class="form-label">Upload File Soal Tugas</label>' +
      '<div class="modal-upload-zone" id="modal-upload-zone-tugas" onclick="document.getElementById(\'modal-file-input-tugas\').click()">' +
      '<input type="file" id="modal-file-input-tugas" style="display:none" onchange="onModalTugasFileSelected(this)">' +
      '<div style="font-size:1.5rem;opacity:0.5;">📋</div>' +
      '<div class="modal-upload-zone-label">Klik untuk pilih file soal tugas</div>' +
      '<div class="modal-upload-zone-types">PDF, DOC, JPG, PPT (Maks: 50MB)</div>' +
      '</div>' +
      '<div id="modal-file-preview-tugas" style="display:none" class="modal-file-preview">' +
      '<span style="font-size:1rem">📋</span>' +
      '<span id="modal-file-name-tugas">—</span>' +
      '<button class="modal-file-remove" onclick="clearModalTugasFile()">✕ Hapus</button>' +
      '</div>' +
      '</div>';
    }
  },
  'edit-kuis': {
    title:'✏️ Edit Soal Kuis',
    render: function() { return '<div id="edit-soal-container"><p style="color:var(--text-dim);font-size:0.8rem;text-align:center;padding:1rem;">Memuat soal...</p></div>'; }
  }
};

var modalTugasTempFile = null;

function pilihTipeTugas(tipe) {
  ['file','link','none'].forEach(function(t) {
    var tab = document.getElementById('tab-tugas-' + t);
    var sec = document.getElementById('tugas-' + t + '-section');
    if (tab) tab.classList.toggle('active', t === tipe);
    if (sec) sec.style.display = t === tipe ? 'block' : 'none';
  });
}
function onModalTugasFileSelected(input) {
  if (!input.files || !input.files[0]) return;
  modalTugasTempFile = input.files[0];
  var preview = document.getElementById('modal-file-preview-tugas');
  var nameEl = document.getElementById('modal-file-name-tugas');
  var zone = document.getElementById('modal-upload-zone-tugas');
  if (preview) preview.style.display = 'flex';
  if (zone) zone.style.display = 'none';
  if (nameEl) nameEl.textContent = input.files[0].name + ' · ' + (input.files[0].size < 1048576 ? Math.round(input.files[0].size/1024) + ' KB' : (input.files[0].size/1048576).toFixed(1) + ' MB');
}
function clearModalTugasFile() {
  modalTugasTempFile = null;
  var preview = document.getElementById('modal-file-preview-tugas');
  var zone = document.getElementById('modal-upload-zone-tugas');
  var fi = document.getElementById('modal-file-input-tugas');
  if (preview) preview.style.display = 'none';
  if (zone) zone.style.display = 'block';
  if (fi) fi.value = '';
}

function onTujuanChange() {
  var val = document.getElementById('konten-tujuan').value;
  document.getElementById('konten-modul-fields').style.display = val==='modul' ? 'block' : 'none';
  document.getElementById('konten-video-fields').style.display = val==='video' ? 'block' : 'none';
  document.getElementById('konten-zoom-fields').style.display = val==='zoom' ? 'block' : 'none';
}
function onModalFileSelected(input) {
  if (!input.files || !input.files[0]) return;
  modalTempFile = input.files[0];
  var preview = document.getElementById('modal-file-preview-modul');
  var nameEl = document.getElementById('modal-file-name-modul');
  var zone = document.getElementById('modal-upload-zone-modul');
  if (preview) preview.style.display = 'flex';
  if (zone) zone.style.display = 'none';
  if (nameEl) nameEl.textContent = input.files[0].name + ' · ' + (input.files[0].size/1024 < 1024 ? Math.round(input.files[0].size/1024) + ' KB' : (input.files[0].size/1048576).toFixed(1) + ' MB');
}
function clearModalFile() {
  modalTempFile = null;
  var preview = document.getElementById('modal-file-preview-modul');
  var zone = document.getElementById('modal-upload-zone-modul');
  var fi = document.getElementById('modal-file-input');
  if (preview) preview.style.display = 'none';
  if (zone) zone.style.display = 'block';
  if (fi) fi.value = '';
}

function simpanKonten() {
  var tujuan = document.getElementById('konten-tujuan') ? document.getElementById('konten-tujuan').value : null;
  if (modalType === 'tambah-konten') {
    if (!tujuan) { alert('Pilih tujuan navigasi terlebih dahulu.'); return; }
    if (tujuan === 'modul') {
      var judul = document.getElementById('konten-judul').value.trim();
      var desc = document.getElementById('konten-desc').value.trim();
      var gugus = document.getElementById('konten-gugus').value;
      if (!judul) { alert('Judul modul wajib diisi.'); return; }
      var tipeTab = document.getElementById('tab-tipe-link');
      var isLink = tipeTab && tipeTab.classList.contains('active');
      var fd = new FormData();
      fd.append('action', 'simpan_modul');
      fd.append('judul', judul);
      fd.append('deskripsi', desc);
      if (isLink) {
        var linkVal = document.getElementById('konten-link-modul') ? document.getElementById('konten-link-modul').value.trim() : '';
        if (!linkVal) { alert('Masukkan URL/link modul terlebih dahulu.'); return; }
        fd.append('link', linkVal);
      } else {
        if (!modalTempFile) { alert('Harap unggah file modul terlebih dahulu.'); return; }
        fd.append('file', modalTempFile);
      }
      var btnSimpan = document.querySelector('.modal-footer .btn-primary');
      if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.textContent = '⏳ Mengupload...'; }
      fetch('api.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
          if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan'; }
          if (res.success) {
            closeModal();
            showToast('✅ Modul "' + judul + '" berhasil ditambahkan!');
            renderKontenModulDariDB();
            updateNavBadges();
          } else {
            alert('Gagal simpan modul: ' + (res.message || 'Error'));
          }
        })
        .catch(function() {
          if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan'; }
          alert('Terjadi kesalahan jaringan.');
        });
      modalTempFile = null;
      return;
    } else if (tujuan === 'video') {
      var judul = document.getElementById('konten-judul-video').value.trim();
      var desc = document.getElementById('konten-desc-video').value.trim();
      var url = document.getElementById('konten-url-video').value.trim();
      if (!judul || !url) { alert('Judul dan URL YouTube wajib diisi.'); return; }
      DB.kontenVideo.push({judul:judul,desc:desc,urlYoutube:url,embedId:extractYoutubeId(url),timestamp:new Date().toLocaleString('id-ID')});
      renderKontenVideo(); updateNavBadges(); closeModal();
      showToast('✅ Video "'+judul+'" berhasil ditambahkan!');
    } else if (tujuan === 'zoom') {
      var judul = document.getElementById('konten-judul-zoom').value.trim();
      var link = document.getElementById('konten-link-zoom').value.trim();
      var waktu = document.getElementById('konten-waktu-zoom').value;
      var kelas = document.getElementById('konten-kelas-zoom').value;
      if (!judul || !link) { alert('Nama sesi dan link Zoom wajib diisi.'); return; }
      DB.kontenZoom.push({judul:judul,link:link,waktu:waktu,kelas:kelas,timestamp:new Date().toLocaleString('id-ID')});
      renderKontenZoom(); updateNavBadges(); closeModal();
      showToast('✅ Sesi Zoom "'+judul+'" berhasil ditambahkan!');
    }
  } else if (modalType === 'tugas') {
    // Handle upload tugas oleh guru — kirim ke MySQL via api.php
    var judul = document.getElementById('modal-tugas-judul') ? document.getElementById('modal-tugas-judul').value.trim() : '';
    var desc = document.getElementById('modal-tugas-desc') ? document.getElementById('modal-tugas-desc').value.trim() : '';
    var deadline = document.getElementById('modal-tugas-deadline') ? document.getElementById('modal-tugas-deadline').value : '';

    if (!judul) {
      alert('Judul tugas wajib diisi.');
      return;
    }

    if (!modalTugasTempFile) {
      alert('Harap upload file soal tugas terlebih dahulu.');
      return;
    }

    // Kirim ke server via FormData
    var fd = new FormData();
    fd.append('action', 'upload_tugas_guru');
    fd.append('judul', judul);
    fd.append('deskripsi', desc);
    fd.append('deadline', deadline);
    fd.append('file', modalTugasTempFile);

    // Disable tombol simpan agar tidak double-submit
    var btnSimpan = document.querySelector('.modal-footer .btn-primary');
    if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.textContent = '⏳ Mengupload...'; }

    fetch('api.php', { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan'; }
        if (res.success) {
          closeModal();
          showToast('✅ Tugas "' + judul + '" berhasil diupload!');
          refreshTampilanTugas();
        } else {
          alert('Gagal upload tugas: ' + (res.message || 'Error tidak diketahui'));
        }
      })
      .catch(function(e) {
        if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan'; }
        alert('Terjadi kesalahan jaringan saat upload tugas.');
      });

    modalTempFile = null;
    modalTugasTempFile = null;
    return; // return lebih awal karena proses async
    
  } else {
    closeModal();
  }
  
  modalTempFile = null;
  modalTugasTempFile = null;
}

// =====================
// FUNGSI REFRESH TAMPILAN TUGAS
// =====================
function refreshTampilanTugas() {
  if (currentRole === 'guru') {
    renderTugasGuruDariDB();
  } else if (currentRole === 'siswa') {
    renderTugasSiswaDariDB();
  }
}

function showSection(name) {
  currentSection = name;
  document.querySelectorAll('[id^="sec-"]').forEach(function(s) { 
    s.style.display = 'none'; 
  });
  
  var sec = document.getElementById('sec-' + name);
  if (sec) sec.style.display = 'block';
  
  // Update tampilan tugas saat membuka section tugas
  if (name === 'tugas') {
    if (currentRole === 'guru') {
      renderTugasGuruDariDB();
    } else if (currentRole === 'siswa') {
      renderTugasSiswaDariDB();
    }
  }
}
// Tampilkan tugas di halaman GURU
// =====================
// FUNGSI RENDER TUGAS GURU (DIPERBAIKI)
// =====================

function renderTabelTugasGuru(tugasId) {
  var tbody = document.getElementById('tugas-tbody-' + tugasId);
  if (!tbody) return;
  
  var total = 24;
  var allData = DB.tugasFiles[tugasId] || [];
  var kelas = filterTugasKelasAktif[tugasId] || 'semua';
  var data = kelas === 'semua' ? allData : allData.filter(function(d) { 
    return d.kelas === kelas; 
  });
  
  var collected = allData.length;
  var countEl = document.getElementById('t-count-' + tugasId);
  var remainEl = document.getElementById('t-remain-' + tugasId);
  var barEl = document.getElementById('t-bar-' + tugasId);
  
  if (countEl) countEl.textContent = collected;
  if (remainEl) remainEl.textContent = total - collected;
  if (barEl) barEl.style.width = Math.round(collected / total * 100) + '%';
  
  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:1.8rem;color:var(--text-dim);">' +
      (kelas === 'semua' ? 'Belum ada siswa yang mengumpulkan tugas ini.' : 'Belum ada tugas dari kelas ' + kelas + '.') + 
      '</td></tr>';
    return;
  }
  
  var iconMap = {
    pdf: '📕', jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
    doc: '📝', docx: '📝', ppt: '📊', pptx: '📊', 
    xls: '📊', xlsx: '📊', txt: '📄', zip: '📦', rar: '📦'
  };
  
  tbody.innerHTML = data.map(function(d, i) {
    var ext = d.filename.split('.').pop().toLowerCase();
    var icon = iconMap[ext] || '📄';
    var badgeCls = d.status === 'Tepat Waktu' ? 'hadir' : 'izin';
    var realIdx = allData.findIndex(function(x) { 
      return x.nama === d.nama && x.filename === d.filename; 
    });
    
    var openFileFn = d.fileUrl 
      ? "window.open('" + d.fileUrl + "','_blank')" 
      : "alert('File tidak tersedia untuk dilihat.')";
    
    var downloadFn = d.fileUrl 
      ? "unduhFile('" + d.fileUrl + "','" + d.filename + "')" 
      : "alert('File tidak tersedia untuk diunduh.')";
    
    return '<tr>' +
      '<td style="color:var(--text-dim);font-weight:700">' + (i + 1) + '</td>' +
      '<td style="font-weight:700">' + escapeHtml(d.nama) + '</td>' +
      '<td>' + d.kelas + (d.absen ? ' (No. ' + d.absen + ')' : '') + '</td>' +
      '<td style="font-family:\'JetBrains Mono\';font-size:0.72rem;">' + (d.tanggal || '-') + '</td>' +
      '<td style="font-family:\'JetBrains Mono\';font-size:0.72rem;">' + d.waktu + ' WIB</td>' +
      '<td>' +
      '<div style="display:flex;align-items:center;gap:0.4rem;">' +
      '<span style="font-size:1rem;">' + icon + '</span>' +
      '<span style="font-size:0.75rem;color:var(--blue);text-decoration:underline;cursor:pointer;" ' +
      'onclick="' + openFileFn + '">' + escapeHtml(d.filename) + '</span>' +
      '</div>' +
      '</td>' +
      '<td style="font-size:0.72rem;color:var(--text-secondary)">' + d.filesize + '</td>' +
      '<td><span class="badge-' + badgeCls + '">' + d.status + '</span></td>' +
      '<td>' +
      '<div style="display:flex;gap:0.3rem;">' +
      '<button onclick="' + openFileFn + '" ' +
      'style="padding:0.24rem 0.5rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:4px;color:var(--blue);cursor:pointer;font-size:0.68rem;">👁 Lihat</button>' +
      '<button onclick="' + downloadFn + '" ' +
      'style="padding:0.24rem 0.5rem;background:var(--emerald-dim);border:1px solid rgba(63,185,80,0.25);border-radius:4px;color:var(--emerald);cursor:pointer;font-size:0.68rem;">⬇ Download</button>' +
      '<button onclick="hapusFileTugas(' + tugasId + ',' + realIdx + ')" ' +
      'style="padding:0.24rem 0.5rem;background:rgba(248,81,73,0.07);border:1px solid rgba(248,81,73,0.20);border-radius:4px;color:var(--red);cursor:pointer;font-size:0.68rem;">🗑 Hapus</button>' +
      '</div>' +
      '</td>' +
      '</tr>';
  }).join('');
}

function buatHTMLTugasGuru(tugas) {
  var icon = '📋';
  if (tugas.fileType) {
    if (tugas.fileType === 'application/pdf') icon = '📕';
    else if (tugas.fileType.startsWith('image/')) icon = '🖼️';
    else if (tugas.fileType.includes('word') || tugas.fileType.includes('document')) icon = '📝';
    else if (tugas.fileType.includes('presentation')) icon = '📊';
  } else {
    var ext = tugas.filename ? tugas.filename.split('.').pop().toLowerCase() : '';
    if (ext === 'pdf') icon = '📕';
    else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = '🖼️';
    else if (['doc', 'docx'].includes(ext)) icon = '📝';
    else if (['ppt', 'pptx'].includes(ext)) icon = '📊';
  }
  
  return '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.8rem;flex-wrap:wrap;gap:0.45rem;">' +
    '<div>' +
    '<div style="font-size:0.88rem;font-weight:700;">' + tugas.judul + '</div>' +
    '<div style="font-size:0.68rem;color:var(--text-secondary);margin-top:0.15rem;">' +
    'Deadline: ' + tugas.deadlineFormatted + '</div>' +
    '</div>' +
    '<div style="display:flex;gap:0.45rem;align-items:center;">' +
    '<span style="font-size:0.7rem;color:var(--emerald);">✓ <span id="t-count-' + tugas.id + '">0</span> terkumpul</span>' +
    '<span style="font-size:0.7rem;color:var(--text-dim);">·</span>' +
    '<span style="font-size:0.7rem;color:var(--accent);">⏳ <span id="t-remain-' + tugas.id + '">24</span> belum</span>' +
    '<button class="btn-secondary" style="padding:0.34rem 0.68rem;font-size:0.7rem;" onclick="downloadSemuaTugas(' + tugas.id + ')">📦 Download Semua</button>' +
    '<button class="btn-secondary" style="padding:0.34rem 0.68rem;font-size:0.7rem;" onclick="exportTugas(' + tugas.id + ')">📥 Export Excel</button>' +
    '</div>' +
    '</div>' +
    '<div style="margin-bottom:0.8rem;padding:0.5rem 0.75rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:6px;display:flex;align-items:center;gap:0.6rem;">' +
    '<span style="font-size:1rem;">📎</span>' +
    '<div style="flex:1;">' +
    '<div style="font-size:0.72rem;color:var(--text-secondary);">File Soal Tugas:</div>' +
    '<div style="font-size:0.78rem;font-weight:600;color:var(--blue);text-decoration:underline;cursor:pointer;" ' +
    'onclick="' + (tugas.fileUrl ? "window.open('" + tugas.fileUrl + "','_blank')" : "alert('File tidak tersedia')") + '">' +
    (tugas.filename || 'File tidak tersedia') + ' (' + (tugas.filesize || '0 KB') + ')' +
    '</div>' +
    '</div>' +
    '<button onclick="' + (tugas.fileUrl ? "window.open('" + tugas.fileUrl + "','_blank')" : "alert('File tidak tersedia')") + '" ' +
    'style="padding:0.24rem 0.6rem;background:transparent;border:1px solid var(--border);border-radius:4px;color:var(--text-secondary);cursor:pointer;font-size:0.68rem;">🔍 Lihat</button>' +
    '<button onclick="hapusTugas(' + tugas.id + ')" ' +
    'style="padding:0.24rem 0.6rem;background:rgba(248,81,73,0.07);border:1px solid rgba(248,81,73,0.20);border-radius:4px;color:var(--red);cursor:pointer;font-size:0.68rem;">🗑 Hapus Tugas</button>' +
    '</div>' +
    '<div class="filter-bar">' +
    '<label>Kelas:</label>' +
    '<button class="filter-btn active" onclick="filterTugasKelas(this, ' + tugas.id + ', \'semua\')">Semua</button>' +
    '<button class="filter-btn" onclick="filterTugasKelas(this, ' + tugas.id + ', \'XII MIPA 1\')">XII MIPA 1</button>' +
    '<button class="filter-btn" onclick="filterTugasKelas(this, ' + tugas.id + ', \'XII MIPA 2\')">XII MIPA 2</button>' +
    '<button class="filter-btn" onclick="filterTugasKelas(this, ' + tugas.id + ', \'XII MIPA 3\')">XII MIPA 3</button>' +
    '<button class="filter-btn" onclick="filterTugasKelas(this, ' + tugas.id + ', \'XII MIPA 4\')">XII MIPA 4</button>' +
    '</div>' +
    '<div class="progress-bar" style="margin-bottom:1.2rem;">' +
    '<div class="progress-fill" id="t-bar-' + tugas.id + '" style="width:0%"></div>' +
    '</div>' +
    '<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">' +
    '<table class="result-table">' +
    '<thead><tr><th>No</th><th>Nama Siswa</th><th>Kelas</th><th>Waktu Kumpul</th><th>File Tugas</th><th>Ukuran</th><th>Status</th><th>Aksi</th></tr></thead>' +
    '<tbody id="tugas-tbody-' + tugas.id + '">' +
    '<tr><td colspan="8" style="text-align:center;padding:1.8rem;color:var(--text-dim);">Belum ada siswa yang mengumpulkan tugas ini.</td></tr>' +
    '</tbody>' +
    '</table>' +
    '</div>';
}

function hapusTugas(tugasId) {
  if (!confirm('Hapus tugas ini? Semua file jawaban siswa juga akan dihapus.')) return;
  
  // Hapus semua file URL
  if (DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId].forEach(function(t) {
      if (t.fileUrl) URL.revokeObjectURL(t.fileUrl);
    });
    delete DB.tugasFiles[tugasId];
    localStorage.removeItem('sichemor_tugas_' + tugasId);
  }
  
  // Hapus dari array tugasGuru
  var tugasIndex = DB.tugasGuru.findIndex(function(t) { return t.id === tugasId; });
  if (tugasIndex >= 0) {
    if (DB.tugasGuru[tugasIndex].fileUrl) {
      URL.revokeObjectURL(DB.tugasGuru[tugasIndex].fileUrl);
    }
    DB.tugasGuru.splice(tugasIndex, 1);
    simpanTugasGuruKeLocalStorage();
  }
  
  // Refresh tampilan
  renderTugasGuruDariDB();
  renderTugasSiswaDariDB();
  showToast('🗑️ Tugas berhasil dihapus');
}

function extractYoutubeId(url) {
  var match = url.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
  return match ? match[1] : null;
}

function renderKontenModul() {
  renderKontenModulDariDB();
}

function renderKontenModulDariDB() {
  var grid = document.getElementById('materi-grid');
  var empty = document.getElementById('materi-empty');
  if (!grid) return;
  grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-dim);">⏳ Memuat modul...</div>';

  fetch('api.php?action=get_modul')
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success || !res.data || res.data.length === 0) {
        grid.style.display = 'none';
        if (empty) empty.style.display = 'block';
        if (document.getElementById('stat-modul')) document.getElementById('stat-modul').textContent = '0';
        return;
      }
      if (empty) empty.style.display = 'none';
      grid.style.display = 'grid';
      var iconMap = {pdf:'📄',doc:'📝',docx:'📝',ppt:'📊',pptx:'📊',xls:'📊',xlsx:'📊',jpg:'🖼️',jpeg:'🖼️',png:'🖼️',zip:'📦',rar:'📦'};
      grid.innerHTML = res.data.map(function(m) {
        var fileUrl = m.link ? m.link : m.path_file;
        var isLink = !!m.link && !m.path_file;
        var ext = (m.nama_file || '').split('.').pop().toLowerCase();
        var icon = isLink ? '🔗' : (iconMap[ext] || '📄');
        var tipeBadge = isLink
          ? '<span style="font-size:0.58rem;background:var(--blue-dim);color:var(--blue);border:1px solid rgba(88,166,255,0.22);border-radius:3px;padding:0.08rem 0.38rem;font-weight:700;margin-left:0.3rem;">LINK</span>'
          : '<span style="font-size:0.58rem;background:var(--emerald-dim);color:var(--emerald);border:1px solid rgba(63,185,80,0.20);border-radius:3px;padding:0.08rem 0.38rem;font-weight:700;margin-left:0.3rem;">FILE</span>';
        return '<div class="module-card">'
          + '<div class="module-status status-filled">● Tersedia</div>'
          + '<div class="module-icon">' + icon + '</div>'
          + '<div class="module-type">Modul</div>'
          + '<div class="module-name">' + escapeHtml(m.judul) + tipeBadge + '</div>'
          + '<div class="module-desc">' + escapeHtml(m.deskripsi || 'Modul yang diunggah oleh guru.') + '</div>'
          + '<div class="module-actions">'
          + '<button class="btn-icon" onclick="window.open(\'' + fileUrl + '\',\'_blank\')" title="Buka">📄</button>'
          + (!isLink ? '<button class="btn-icon" onclick="unduhFile(\'' + fileUrl + '\',\'' + escapeHtml(m.nama_file||'') + '\')" title="Download">⬇️</button>' : '')
          + '<button class="btn-icon danger guru-only" onclick="hapusModul(' + m.id + ')" title="Hapus">🗑️</button>'
          + '</div></div>';
      }).join('');
      if (document.getElementById('stat-modul')) document.getElementById('stat-modul').textContent = res.data.length;
      if (document.getElementById('stat-modul-sub')) document.getElementById('stat-modul-sub').textContent = '✓ ' + res.data.length + ' modul';
    })
    .catch(function() {
      grid.innerHTML = '<div style="color:var(--red);padding:1rem;grid-column:1/-1;">❌ Gagal memuat modul.</div>';
    });
}

function hapusModul(id) {
  if (!confirm('Hapus modul ini?')) return;
  var fd = new FormData();
  fd.append('action', 'hapus_modul');
  fd.append('id', id);
  fetch('api.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        showToast('🗑️ Modul berhasil dihapus');
        renderKontenModulDariDB();
        updateNavBadges();
      } else {
        alert('Gagal hapus: ' + (res.message || 'Error'));
      }
    });
}

function renderDashboardPreview() {
  var preview = document.getElementById('dashboard-materi-preview');
  if (!preview) return;
  if (DB.kontenModul.length === 0) {
    preview.innerHTML = '<div class="empty-state"><div class="empty-icon">📚</div><div class="empty-text">Belum ada modul materi</div><div class="empty-sub">Klik "+ Tambah Konten" untuk memulai.</div></div>';
    return;
  }
  var html = '<div class="module-grid" style="grid-template-columns:repeat(2,1fr)">';
  DB.kontenModul.slice(0,4).forEach(function(m) {
    html += '<div class="module-card"><div class="module-status status-filled">● Tersedia</div>'
      + '<div class="module-icon">📄</div><div class="module-type">'+m.gugus+'</div>'
      + '<div class="module-name">'+m.judul+'</div><div class="module-desc">'+(m.desc||'')+'</div></div>';
  });
  html += '</div>';
  preview.innerHTML = html;
}

function renderKontenVideo() {
  var grid = document.getElementById('video-grid');
  var empty = document.getElementById('video-empty');
  if (!grid) return;
  if (DB.kontenVideo.length === 0) { grid.style.display='none'; if(empty) empty.style.display='block'; return; }
  if (empty) empty.style.display='none';
  grid.style.display='grid';
  grid.innerHTML = DB.kontenVideo.map(function(v,i) {
    var thumbUrl = v.embedId ? 'https://img.youtube.com/vi/'+v.embedId+'/mqdefault.jpg' : null;
    var thumbHtml = thumbUrl
      ? '<img src="'+thumbUrl+'" style="width:100%;height:130px;object-fit:cover;" onerror="this.style.display=\'none\'">'
      : '<div class="video-placeholder"><div class="yt-icon">▶</div><span>'+v.judul+'</span></div>';
    var playTarget = v.urlYoutube ? 'onclick="window.open(\''+v.urlYoutube+'\',\'_blank\')"' : '';
    return '<div class="video-card"><div class="video-thumb">'+thumbHtml+'<div class="play-btn" '+playTarget+'>▶</div></div>'
      + '<div class="video-info"><div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.5rem;">'
      + '<div><div class="video-title">'+v.judul+'</div><div class="video-sub">'+(v.desc||'Video YouTube')+'</div></div>'
      + '<button class="btn-icon danger guru-only" onclick="hapusVideo('+i+')" title="Hapus" style="flex-shrink:0">🗑️</button>'
      + '</div></div></div>';
  }).join('');
}

function hapusVideo(idx) {
  if (!confirm('Hapus video ini?')) return;
  DB.kontenVideo.splice(idx,1); renderKontenVideo(); updateNavBadges();
}

function renderKontenZoom() {
  var activeCard = document.getElementById('zoom-active-card');
  var emptyCard = document.getElementById('zoom-empty-card');
  var jadwalList = document.getElementById('zoom-jadwal-list');
  if (!activeCard) return;
  if (DB.kontenZoom.length === 0) {
    activeCard.style.display='none'; if(emptyCard) emptyCard.style.display='flex';
    if(jadwalList) jadwalList.innerHTML='<div class="empty-state"><div class="empty-icon">📅</div><div class="empty-text">Belum ada sesi terjadwal</div></div>';
    return;
  }
  if (emptyCard) emptyCard.style.display='none';
  var latest = DB.kontenZoom[DB.kontenZoom.length-1];
  activeCard.style.display='block';
  activeCard.innerHTML = '<div class="zoom-card"><div class="zoom-icon">📹</div><div class="zoom-info">'
    + '<div class="zoom-title">'+latest.judul+'</div>'
    + '<div class="zoom-meta">Jadwal: <span style="color:var(--accent)">'+(latest.waktu ? new Date(latest.waktu).toLocaleString('id-ID') : 'Segera')+'</span> · '+latest.kelas+'</div>'
    + '<div class="zoom-status"><div class="dot-live"></div> Link tersedia</div></div>'
    + '<button class="btn-join" onclick="window.open(\''+latest.link+'\',\'_blank\')">📹 Join Meeting</button></div>';
  if (jadwalList) {
    jadwalList.innerHTML = DB.kontenZoom.map(function(z,i) {
      return '<div style="background:var(--surface);border:1px solid rgba(88,166,255,0.16);border-radius:10px;padding:0.9rem 1.1rem;margin-bottom:0.55rem;display:flex;align-items:center;gap:0.9rem;">'
        + '<span style="font-size:1.1rem">📹</span>'
        + '<div style="flex:1"><div style="font-size:0.82rem;font-weight:700">'+z.judul+'</div>'
        + '<div style="font-size:0.68rem;color:var(--text-secondary)">'+(z.waktu ? new Date(z.waktu).toLocaleString('id-ID') : 'Jadwal belum ditentukan')+' · '+z.kelas+'</div></div>'
        + '<button onclick="window.open(\''+z.link+'\',\'_blank\')" class="btn-join" style="padding:0.4rem 0.9rem;font-size:0.73rem;">Join</button>'
        + '<button onclick="hapusZoom('+i+')" class="btn-icon danger" title="Hapus">🗑️</button></div>';
    }).join('');
  }
}

function hapusZoom(idx) {
  if (!confirm('Hapus sesi Zoom ini?')) return;
  DB.kontenZoom.splice(idx,1); renderKontenZoom(); updateNavBadges();
}

function openModal(type) {
  modalType = type; 
  modalTempFile = null; 
  modalTugasTempFile = null;
  
  var overlay = document.getElementById('modal');
  overlay.classList.add('open');
  
  if (type && modalContents[type]) {
    document.getElementById('modal-title').textContent = modalContents[type].title;
    document.getElementById('modal-body').innerHTML = modalContents[type].render();
    
    // Inisialisasi untuk modal buat-kuis
    if (type === 'buat-kuis') {
      window.soalBuatKuis = [{
        pertanyaan: '',
        opsi: ['', '', '', ''],
        jawaban: 0
      }];
      setTimeout(renderSoalBuatKuis, 50);
    }
  } else {
    document.getElementById('modal-title').textContent = 'Tambah Konten';
    document.getElementById('modal-body').innerHTML = modalContents['tambah-konten'].render();
    modalType = 'tambah-konten';
  }
}

function closeModal() {
  document.getElementById('modal').classList.remove('open');
  modalTempFile = null; 
  modalTugasTempFile = null;
  window.soalBuatKuis = null; // <-- TAMBAHKAN BARIS INI
}

function modalSaveAction() {
  if (modalType === 'edit-kuis') { 
    simpanEditSoal(); 
  } else if (modalType === 'buat-kuis') {
    simpanKuisBaru(); // Fungsi baru untuk menyimpan kuis
  } else { 
    simpanKonten(); 
  }
}

// Inisialisasi soal saat modal dibuka
function initSoalBuatKuis() {
  var container = document.getElementById('soal-container-buat');
  if (!container) return;
  
  // Mulai dengan 1 soal default
  window.soalBuatKuis = window.soalBuatKuis || [{
    pertanyaan: '',
    opsi: ['', '', '', ''],
    jawaban: 0
  }];
  
  renderSoalBuatKuis();
}

function renderSoalBuatKuis() {
  var container = document.getElementById('soal-container-buat');
  if (!container) return;
  
  var html = '';
  window.soalBuatKuis.forEach(function(soal, index) {
    html += renderSoalBuatBlock(soal, index);
  });
  
  container.innerHTML = html;
}

function renderSoalBuatBlock(soal, index) {
  return '<div class="soal-block" id="soal-buat-' + index + '">' +
    '<div class="soal-block-num">Soal ' + (index + 1) + '</div>' +
    (window.soalBuatKuis.length > 1 ? 
      '<button class="soal-del-btn" onclick="hapusSoalBuat(' + index + ')" title="Hapus soal">✕</button>' : 
      '') +
    '<div class="form-group">' +
    '<label class="form-label">Pertanyaan</label>' +
    '<textarea class="form-textarea" style="min-height:52px;" ' +
    'onchange="updateSoalBuat(' + index + ', \'pertanyaan\', this.value)" ' +
    'placeholder="Tulis pertanyaan soal...">' + (soal.pertanyaan || '') + '</textarea>' +
    '</div>' +
    '<div class="form-group">' +
    '<label class="form-label">Opsi Jawaban</label>' +
    getOpsiHtml(index, soal.opsi, soal.jawaban) +
    '</div>' +
    '</div>';
}

function getOpsiHtml(soalIdx, opsi, jawaban) {
  var huruf = ['A', 'B', 'C', 'D'];
  var html = '';
  
  for (var i = 0; i < 4; i++) {
    html += '<div class="soal-opsi-row">' +
      '<div class="soal-opsi-key ' + (jawaban === i ? 'benar' : '') + '" ' +
      'onclick="setJawabanBenarBuat(' + soalIdx + ', ' + i + ')" ' +
      'title="Tandai jawaban benar">' +
      (jawaban === i ? '✓' : huruf[i]) +
      '</div>' +
      '<input type="text" class="form-input" style="flex:1" ' +
      'placeholder="Opsi ' + huruf[i] + '" value="' + (opsi[i] || '') + '" ' +
      'oninput="updateSoalBuat(' + soalIdx + ', \'opsi\', this.value, ' + i + ')">' +
      '</div>';
  }
  
  return html;
}
// Fungsi untuk menyimpan tugas baru (dipanggil dari modal)
function simpanTugasBaru() {
  var judul = document.getElementById('modal-tugas-judul') ? document.getElementById('modal-tugas-judul').value.trim() : '';
  var desc = document.getElementById('modal-tugas-desc') ? document.getElementById('modal-tugas-desc').value.trim() : '';
  var deadline = document.getElementById('modal-tugas-deadline') ? document.getElementById('modal-tugas-deadline').value : '';
  
  if (!judul) {
    alert('Judul tugas wajib diisi.');
    return;
  }
  
  if (!modalTugasTempFile) {
    alert('Harap upload file soal tugas terlebih dahulu.');
    return;
  }
  
  // Buat URL object untuk file
  var fileUrl = URL.createObjectURL(modalTugasTempFile);
  var sizeKB = modalTugasTempFile.size / 1024;
  var sizeStr = sizeKB > 1024 ? (sizeKB / 1024).toFixed(1) + ' MB' : Math.round(sizeKB) + ' KB';
  
  // Format deadline
  var deadlineFormatted = '';
  if (deadline) {
    var d = new Date(deadline);
    deadlineFormatted = d.toLocaleDateString('id-ID', { 
      weekday: 'short', 
      day: '2-digit', 
      month: 'short', 
      year: 'numeric' 
    }) + ' · ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
  } else {
    deadlineFormatted = 'Tidak ada batas waktu';
  }
  
  // Buat ID tugas baru (auto increment)
  var newId = DB.tugasGuru.length > 0 ? Math.max.apply(null, DB.tugasGuru.map(function(t) { return t.id; })) + 1 : 1;
  
  // Simpan ke database
  DB.tugasGuru.push({
    id: newId,
    judul: judul,
    desc: desc,
    deadline: deadline,
    deadlineFormatted: deadlineFormatted,
    filename: modalTugasTempFile.name,
    filesize: sizeStr,
    fileUrl: fileUrl,
    fileType: modalTugasTempFile.type,
    tanggalUpload: new Date().toISOString()
  });
  
  // Inisialisasi array untuk jawaban siswa tugas ini
  if (!DB.tugasFiles[newId]) {
    DB.tugasFiles[newId] = [];
  }
  
  // Simpan ke localStorage
  simpanTugasGuruKeLocalStorage();
  
  closeModal();
  showToast('✅ Tugas "' + judul + '" berhasil dibuat!');
  
  // Refresh tampilan tugas
  refreshTampilanTugas();
  
  // Reset modalTugasTempFile
  modalTugasTempFile = null;
}
function tambahSoalBaruBuat() {
  window.soalBuatKuis.push({
    pertanyaan: '',
    opsi: ['', '', '', ''],
    jawaban: 0
  });
  renderSoalBuatKuis();
}

function hapusSoalBuat(index) {
  if (window.soalBuatKuis.length <= 1) {
    alert('Kuis harus memiliki minimal 1 soal.');
    return;
  }
  
  if (!confirm('Hapus soal ' + (index + 1) + '?')) return;
  
  window.soalBuatKuis.splice(index, 1);
  renderSoalBuatKuis();
}

function updateSoalBuat(soalIdx, field, value, opsiIdx) {
  if (field === 'pertanyaan') {
    window.soalBuatKuis[soalIdx].pertanyaan = value;
  } else if (field === 'opsi') {
    window.soalBuatKuis[soalIdx].opsi[opsiIdx] = value;
  }
}

function setJawabanBenarBuat(soalIdx, opsiIdx) {
  window.soalBuatKuis[soalIdx].jawaban = opsiIdx;
  renderSoalBuatKuis(); // Re-render untuk update tampilan
}
// =====================
// FUNGSI PENYIMPANAN KUIS KE LOCALSTORAGE
// =====================

function simpanKuisKeLocalStorage() {
  try {
    localStorage.setItem('sichemor_kuis', JSON.stringify(DB.kuis));
    localStorage.setItem('sichemor_nilai_kuis', JSON.stringify(DB.nilaiKuis));
    console.log('Kuis tersimpan, jumlah: ' + DB.kuis.length);
  } catch (e) {
    console.error('Gagal menyimpan kuis:', e);
  }
}

function DariLocalStorage() {
  try {
    var saved = localStorage.getItem('sichemor_kuis');
    if (saved) {
      DB.kuis = JSON.parse(saved);
      console.log('Kuis dimuat, jumlah: ' + DB.kuis.length);
    } else {
      DB.kuis = [];
      simpanKuisKeLocalStorage();
    }
    
    var savedNilai = localStorage.getItem('sichemor_nilai_kuis');
    if (savedNilai) {
      DB.nilaiKuis = JSON.parse(savedNilai);
    } else {
      DB.nilaiKuis = [];
    }
  } catch (e) {
    console.error('Gagal memuat kuis:', e);
  }
}


function simpanKuisKeLocalStorage() {
  try {
    localStorage.setItem('sichemor_kuis', JSON.stringify(DB.kuis));
    localStorage.setItem('sichemor_nilai_kuis', JSON.stringify(DB.nilaiKuis));
    console.log('Kuis tersimpan, jumlah: ' + DB.kuis.length);
  } catch (e) {
    console.error('Gagal menyimpan kuis:', e);
  }
}

function muatKuisDariLocalStorage() {
  try {
    var saved = localStorage.getItem('sichemor_kuis');
    if (saved) {
      DB.kuis = JSON.parse(saved);
      console.log('Kuis dimuat, jumlah: ' + DB.kuis.length);
    } else {
      DB.kuis = [];
      simpanKuisKeLocalStorage();
    }
    
    var savedNilai = localStorage.getItem('sichemor_nilai_kuis');
    if (savedNilai) {
      DB.nilaiKuis = JSON.parse(savedNilai);
    } else {
      DB.nilaiKuis = [];
    }
  } catch (e) {
    console.error('Gagal memuat kuis:', e);
  }
}
// Fungsi untuk menyimpan kuis baru
function simpanKuisBaru() {
  var judul = document.getElementById('kuis-judul')?.value.trim();
  var durasi = parseInt(document.getElementById('kuis-durasi')?.value);
  var deadline = document.getElementById('kuis-deadline')?.value;
  
  if (!judul) {
    alert('Judul kuis wajib diisi!');
    return;
  }
  
  if (!durasi || durasi < 1) {
    alert('Durasi kuis wajib diisi minimal 1 menit!');
    return;
  }
  
  if (!window.soalBuatKuis || window.soalBuatKuis.length === 0) {
    alert('Kuis harus memiliki minimal 1 soal!');
    return;
  }
  
  for (var i = 0; i < window.soalBuatKuis.length; i++) {
    var soal = window.soalBuatKuis[i];
    
    if (!soal.pertanyaan.trim()) {
      alert('Pertanyaan soal ' + (i + 1) + ' belum diisi!');
      return;
    }
    
    var opsiTerisi = soal.opsi.some(function(opsi) { return opsi.trim(); });
    if (!opsiTerisi) {
      alert('Soal ' + (i + 1) + ' harus memiliki minimal satu opsi!');
      return;
    }
  }
  
  var newId = DB.kuis.length > 0 ? Math.max.apply(null, DB.kuis.map(function(k) { return k.id; })) + 1 : 3;
  
  DB.kuis.push({
    id: newId,
    judul: judul,
    durasi: durasi,
    deadline: deadline,
    tanggalBuat: new Date().toISOString(),
    soal: window.soalBuatKuis.map(function(s) {
      return {
        pertanyaan: s.pertanyaan,
        opsi: s.opsi.slice(),
        jawaban: s.jawaban
      };
    })
  });
  
  simpanKuisKeLocalStorage();
  
  window.soalBuatKuis = null;
  closeModal();
  showToast('✅ Kuis "' + judul + '" berhasil dibuat dengan ' + DB.kuis[DB.kuis.length-1].soal.length + ' soal!');
  
  renderKuisGuru();
  renderKuisSiswa();
}
// =====================
// FUNGSI RENDER KUIS SISWA
// =====================

function renderKuisSiswa() {
  var container = document.querySelector('#sec-kuis .siswa-only');
  if (!container) return;
  
  // Cari atau buat grid container
  var kuisGrid = container.querySelector('.kuis-grid');
  if (!kuisGrid) {
    kuisGrid = document.createElement('div');
    kuisGrid.className = 'kuis-grid';
    container.innerHTML = '';
    container.appendChild(kuisGrid);
  } else {
    kuisGrid.innerHTML = '';
  }
  
  if (!DB.kuis || DB.kuis.length === 0) {
    kuisGrid.innerHTML = '<div class="empty-state" style="grid-column:1/-1">' +
      '<div class="empty-icon">📝</div>' +
      '<div class="empty-text">Belum ada kuis</div>' +
      '<div class="empty-sub">Guru belum membuat kuis apapun.</div>' +
      '</div>';
    return;
  }
  
  var nama = currentUserData.nama || 'Siswa';
  var sudahMengerjakan = DB.nilaiKuis.filter(function(n) { return n.nama === nama; }).map(function(n) { return n.id; });
  
  var html = '';
  DB.kuis.forEach(function(kuis) {
    var sudahDikerjakan = sudahMengerjakan.includes(kuis.id);
    var statusText = sudahDikerjakan ? '✓ SUDAH DIKERJAKAN' : 'BISA DIKERJAKAN';
    var statusColor = sudahDikerjakan ? 'background:var(--emerald-dim);color:var(--emerald);' : 'background:var(--blue-dim);color:var(--blue);';
    
    html += '<div class="kuis-card" id="kuis-siswa-' + kuis.id + '">' +
      '<div class="kuis-header">' +
      '<div>' +
      '<div class="kuis-title">' + (kuis.judul || 'Kuis') + '</div>' +
      '<div class="kuis-meta">' + (kuis.soal ? kuis.soal.length : 0) + ' soal · Estimasi ' + (kuis.durasi || 10) + ' menit</div>' +
      '</div>' +
      '<span class="badge-kuis" style="' + statusColor + '">' + statusText + '</span>' +
      '</div>' +
      '<div style="font-size:0.7rem;color:var(--text-secondary);margin-top:0.4rem;">' +
      (kuis.deadline ? 'Deadline: ' + new Date(kuis.deadline).toLocaleDateString('id-ID') + ' · 23:59 WIB' : 'Tidak ada batas waktu') +
      '</div>';
    
    if (!sudahDikerjakan) {
      html += '<button class="btn-kuis" onclick="bukaKuis(' + kuis.id + ')" style="margin-top:0.8rem;width:100%;border-color:rgba(63,185,80,0.35);color:var(--emerald);cursor:pointer;">🚀 Mulai Kerjakan</button>';
    } else {
      var nilaiSiswa = DB.nilaiKuis.find(function(n) { return n.nama === nama && n.id === kuis.id; });
      html += '<div style="margin-top:0.8rem;text-align:center;padding:0.5rem;background:rgba(63,185,80,0.06);border-radius:6px;">' +
        '<span style="font-size:0.7rem;color:var(--emerald);">✓ Telah dikerjakan</span><br>' +
        '<span style="font-size:0.85rem;font-weight:700;color:var(--accent);">Nilai: ' + (nilaiSiswa ? nilaiSiswa.nilai : 0) + '</span>' +
        '</div>';
    }
    
    html += '</div>';
  });
  
  kuisGrid.innerHTML = html;
}
// Fungsi untuk merefresh tampilan kuis di halaman guru
function renderKuisGuru() {
  var daftarKuis = document.getElementById('daftar-kuis');
  if (!daftarKuis) return;
  
  if (!DB.kuis || DB.kuis.length === 0) {
    daftarKuis.innerHTML = '<div class="empty-state">' +
      '<div class="empty-icon">📝</div>' +
      '<div class="empty-text">Belum ada kuis</div>' +
      '<div class="empty-sub">Klik "+ Buat Kuis Baru" untuk membuat kuis pertama.</div>' +
      '</div>';
    return;
  }
  
  var html = '<div class="kuis-grid">';
  
  DB.kuis.forEach(function(kuis) {
    var totalSiswa = 24;
    var sudahMengerjakan = DB.nilaiKuis.filter(function(n) { return n.id === kuis.id; }).length;
    var persen = totalSiswa > 0 ? Math.round((sudahMengerjakan / totalSiswa) * 100) : 0;
    
    html += '<div class="kuis-card" id="kuis-card-' + kuis.id + '">' +
      '<div class="kuis-header">' +
      '<div>' +
      '<div class="kuis-title">' + escapeHtml(kuis.judul) + '</div>' +
      '<div class="kuis-meta">' + kuis.soal.length + ' soal · ' + kuis.durasi + ' menit</div>' +
      '</div>' +
      '<span class="badge-kuis">AKTIF</span>' +
      '</div>' +
      '<div style="display:flex;gap:0.4rem;margin-top:0.6rem;">' +
      '<span style="font-size:0.68rem;color:var(--emerald);">✓ ' + sudahMengerjakan + ' sudah</span>' +
      '<span style="font-size:0.68rem;color:var(--text-dim);">·</span>' +
      '<span style="font-size:0.68rem;color:var(--accent);">' + (totalSiswa - sudahMengerjakan) + ' belum</span>' +
      '</div>' +
      '<div class="progress-bar"><div class="progress-fill" style="width:' + persen + '%"></div></div>' +
      '<div style="display:flex;gap:0.4rem;margin-top:0.8rem;">' +
      '<button class="btn-kuis" style="flex:1" onclick="guruLihatHasil(' + kuis.id + ')">📊 Lihat Hasil</button>' +
      '<button class="btn-kuis" style="flex:1" onclick="openEditSoal(' + kuis.id + ')">✏️ Edit Soal</button>' +
      '<button class="btn-kuis" style="flex:1;background:rgba(220,38,38,0.07);color:#dc2626;border-color:rgba(220,38,38,0.22);" onclick="hapusKuis(' + kuis.id + ')">🗑 Hapus</button>' +
      '</div>' +
      '</div>';
  });
  
  html += '</div>';
  daftarKuis.innerHTML = html;
}
document.getElementById('modal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
function showToast(msg) {
  var t = document.createElement('div');
  t.textContent = msg;
  t.style.cssText = 'position:fixed;bottom:2rem;right:2rem;background:var(--surface);color:var(--text-primary);padding:0.7rem 1.1rem;border-radius:8px;font-size:0.8rem;z-index:9999;box-shadow:var(--shadow-lg);animation:fadeInUp 0.3s ease;max-width:360px;border:1px solid var(--border);';
  document.body.appendChild(t);
  setTimeout(function(){ t.remove(); }, 3200);
}

function pilihTipeModul(tipe) {
  var tabFile = document.getElementById('tab-tipe-file');
  var tabLink = document.getElementById('tab-tipe-link');
  var sFile = document.getElementById('konten-modul-file-section');
  var sLink = document.getElementById('konten-modul-link-section');
  if (!tabFile || !tabLink || !sFile || !sLink) return;
  if (tipe === 'file') { tabFile.classList.add('active'); tabLink.classList.remove('active'); sFile.style.display='block'; sLink.style.display='none'; }
  else { tabLink.classList.add('active'); tabFile.classList.remove('active'); sLink.style.display='block'; sFile.style.display='none'; }
}

// =====================
// PRESENSI
// =====================
function renderPresensiGuru() { renderPresensiGuruFiltered(); }

function ubahStatusPresensi(id, status) {
  var s = DB.presensi.find(function(p){ return p.id===id; });
  if (s) { s.status=status; renderPresensiGuru(); }
}

function isiPresensi(status) {
  var now = new Date();
  var waktu = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
  var nama = currentUserData.nama || 'Siswa';
  var existing = DB.presensi.find(function(p){ return p.nama===nama; });
  if (existing) { existing.status=status; existing.waktu=waktu; }
  else { DB.presensi.push({id:DB.presensi.length+1,nama:nama,kelas:currentUserData.kelas||'XII MIPA 2',waktu:waktu,status:status}); }
  var statusEl = document.getElementById('siswa-presensi-status');
  var waktuEl = document.getElementById('presensi-waktu-siswa');
  if (statusEl) statusEl.style.display = 'flex';
  if (waktuEl) waktuEl.textContent = 'Status: ' + status + ' · Pukul ' + waktu + ' WIB';
  var btn = document.getElementById('btn-hadir');
  if (btn) btn.disabled = true;
  var tbody = document.getElementById('riwayat-presensi-siswa');
  if (tbody) {
    var tgl = now.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
    var badgeCls = status==='Hadir'?'hadir':status==='Alpha'?'alpha':'izin';
    var newRow = document.createElement('tr');
    newRow.innerHTML = '<td>Pertemuan 4</td><td>'+tgl+'</td><td><span class="badge-'+badgeCls+'">'+status+'</span></td>';
    tbody.insertBefore(newRow, tbody.firstChild);
  }
  renderPresensiGuru();
}

function renderRekapPertemuan() {
  var container = document.getElementById('rekap-pertemuan-container');
  if (!container) return;
  var allPertemuan = DB.presensiRiwayat.slice();
  var now = new Date();
  allPertemuan.push({pertemuan:4,judul:'Pertemuan 4 — Asam Karboksilat & Ester',tanggal:now.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}),kelas:'XII MIPA 2',data:DB.presensi});
  container.innerHTML = allPertemuan.slice().reverse().map(function(p) {
    var hadirCount = p.data.filter(function(d){ return d.status==='Hadir'; }).length;
    var izinCount = p.data.filter(function(d){ return d.status==='Izin'||d.status==='Sakit'; }).length;
    var alphaCount = p.data.filter(function(d){ return d.status==='Alpha'; }).length;
    var kelasSet = {};
    p.data.forEach(function(d){ kelasSet[d.kelas]=true; });
    var kelasList = Object.keys(kelasSet).sort();
    var pid = 'rekap-p' + p.pertemuan;
    return '<div class="rekap-pertemuan-card">'
      + '<div class="rekap-pertemuan-header" onclick="toggleRekapAccordion(this)">'
      + '<div class="rekap-pertemuan-title"><span style="background:var(--accent-dim);color:var(--accent);border:1px solid rgba(30,58,138,0.22);border-radius:4px;padding:0.1rem 0.45rem;font-size:0.62rem;font-weight:700">P-'+p.pertemuan+'</span>'+p.judul+'</div>'
      + '<div class="rekap-pertemuan-meta"><span>📅 '+p.tanggal+'</span><span style="color:var(--emerald);">✅ '+hadirCount+'</span><span style="color:var(--accent);">📋 '+izinCount+'</span><span style="color:var(--red);">❌ '+alphaCount+'</span>'
      + '<button onclick="exportRekapPertemuan(event,'+p.pertemuan+')" style="background:var(--surface-2);border:1px solid var(--border);border-radius:4px;padding:0.18rem 0.55rem;font-size:0.64rem;color:var(--text-secondary);cursor:pointer;font-family:\'Source Sans 3\',sans-serif;">📥 Export</button>'
      + '</div></div>'
      + '<div class="rekap-pertemuan-body" id="'+pid+'-body">'
      + '<div class="filter-bar" style="margin-bottom:0.7rem;"><label>Kelas:</label><button class="filter-btn active" onclick="filterRekapKelas(event,\''+pid+'\',\'semua\')">Semua</button>'
      + kelasList.map(function(k){ return '<button class="filter-btn" onclick="filterRekapKelas(event,\''+pid+'\',\''+k+'\')">'+k+'</button>'; }).join('')
      + (['XII MIPA 1','XII MIPA 2','XII MIPA 3','XII MIPA 4'].filter(function(k){ return !kelasSet[k]; }).map(function(k){ return '<button class="filter-btn" onclick="filterRekapKelas(event,\''+pid+'\',\''+k+'\')">'+k+'</button>'; }).join(''))
      + '</div>'
      + '<table class="presensi-table" id="'+pid+'-table"><thead><tr><th>No</th><th>Nama Siswa</th><th>Kelas</th><th>Waktu</th><th>Status</th></tr></thead>'
      + '<tbody id="'+pid+'-tbody">'
      + p.data.map(function(d,i){ var bc=d.status==='Hadir'?'hadir':d.status==='Alpha'?'alpha':'izin'; return '<tr data-kelas="'+d.kelas+'"><td>'+(i+1)+'</td><td style="font-weight:600">'+d.nama+'</td><td>'+d.kelas+'</td><td style="font-family:\'JetBrains Mono\';font-size:0.73rem">'+d.waktu+'</td><td><span class="badge-'+bc+'">'+d.status+'</span></td></tr>'; }).join('')
      + '</tbody></table></div></div>';
  }).join('');
}

function filterRekapKelas(event, pid, kelas) {
  event.stopPropagation();
  var filterBar = document.querySelector('#'+pid+'-body .filter-bar');
  if (filterBar) { filterBar.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); }); event.target.classList.add('active'); }
  var tbody = document.getElementById(pid+'-tbody');
  if (!tbody) return;
  var no = 1;
  tbody.querySelectorAll('tr').forEach(function(row) {
    var rowKelas = row.getAttribute('data-kelas');
    if (kelas==='semua'||rowKelas===kelas) { row.style.display=''; row.cells[0].textContent=no++; } else { row.style.display='none'; }
  });
}

function toggleRekapAccordion(headerEl) {
  var body = headerEl.nextElementSibling;
  if (body) body.classList.toggle('open');
}

function exportRekapPertemuan(event, pertemuanNo) {
  event.stopPropagation();
  var allPertemuan = DB.presensiRiwayat.slice();
  var now = new Date();
  allPertemuan.push({pertemuan:4,judul:'Pertemuan 4',tanggal:now.toLocaleDateString('id-ID'),kelas:'XII MIPA 2',data:DB.presensi});
  var found = allPertemuan.find(function(p){ return p.pertemuan===pertemuanNo; });
  if (!found) return;
  var headers = ['No','Nama Siswa','Kelas','Waktu','Status'];
  var rows = found.data.map(function(d,i){ return [i+1,d.nama,d.kelas,d.waktu,d.status]; });
  downloadExcel(rows,headers,'presensi_p'+pertemuanNo+'_'+found.tanggal.replace(/\s/g,'_')+'.xlsx');
}

function exportSemuaPresensi() {
  var allPertemuan = DB.presensiRiwayat.slice();
  var now = new Date();
  allPertemuan.push({pertemuan:4,judul:'Pertemuan 4',tanggal:now.toLocaleDateString('id-ID'),kelas:'XII MIPA 2',data:DB.presensi});
  var allRows = [];
  var headers = ['Pertemuan','Tanggal','No','Nama Siswa','Kelas','Waktu','Status'];
  allPertemuan.forEach(function(p) {
    p.data.forEach(function(d,i){ allRows.push(['P-'+p.pertemuan+' — '+p.judul,p.tanggal,i+1,d.nama,d.kelas,d.waktu,d.status]); });
  });
  downloadExcel(allRows,headers,'rekap_presensi_semua.xlsx');
}

function exportPresensi() {
  var headers = ['No','Nama Siswa','Kelas','Waktu','Status'];
  var data = DB.presensi.map(function(p,i){ return [i+1,p.nama,p.kelas,p.waktu,p.status]; });
  downloadExcel(data,headers,'presensi_p4.xlsx');
}

function switchPresensiTab(el, tabId) {
  document.querySelectorAll('#sec-presensi .tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  document.getElementById('tab-presensi-hari-ini').style.display='none';
  document.getElementById('tab-presensi-riwayat').style.display='none';
  document.getElementById(tabId).style.display='block';
  if (tabId==='tab-presensi-riwayat') renderRekapPertemuan();
}

// =====================
// FUNGSI UPLOAD JAWABAN SISWA UNTUK TUGAS GURU
// =====================

// =====================
// TUGAS
// =====================
function handleFileUpload(tugasId, input) {
  if (!input.files || !input.files[0]) return;
  
  var file = input.files[0];
  var now = new Date();
  var waktu = String(now.getHours()).padStart(2,'0')+':'+String(now.getMinutes()).padStart(2,'0');
  
  var fileUrl = URL.createObjectURL(file);
  var sizeKB = file.size/1024;
  var sizeStr = sizeKB>1024 ? (sizeKB/1024).toFixed(1)+' MB' : Math.round(sizeKB)+' KB';
  
  // Sembunyikan area upload
  var uploadArea = document.getElementById('upload-area-' + tugasId);
  var uploadDone = document.getElementById('upload-done-' + tugasId);
  var filenameEl = document.getElementById('upload-filename-' + tugasId);
  
  if (uploadArea) uploadArea.style.display = 'none';
  if (uploadDone) uploadDone.style.display = 'block';
  
  var ext = file.name.split('.').pop().toLowerCase();
  var icon = '📄';
  if (ext === 'pdf') icon = '📕';
  else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = '🖼️';
  else if (['doc', 'docx'].includes(ext)) icon = '📝';
  
  if (filenameEl) {
    filenameEl.innerHTML = icon + ' ' + file.name + ' · ' + sizeStr + ' · Pukul ' + waktu + ' WIB';
  }
  
  var nama = currentUserData.nama || 'Siswa';
  var kelas = currentUserData.kelas || 'XII MIPA 2';
  
  if (!DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId] = [];
  }
  
  var entry = {
    nama: nama,
    kelas: kelas,
    waktu: waktu,
    filename: file.name,
    filesize: sizeStr,
    fileUrl: fileUrl,
    fileType: file.type,
    status: 'Tepat Waktu',
    timestamp: now.toISOString()
  };
  
  var idx = DB.tugasFiles[tugasId].findIndex(function(t) { return t.nama === nama; });
  
  if (idx >= 0) {
    if (DB.tugasFiles[tugasId][idx].fileUrl) {
      URL.revokeObjectURL(DB.tugasFiles[tugasId][idx].fileUrl);
    }
    DB.tugasFiles[tugasId][idx] = entry;
  } else {
    DB.tugasFiles[tugasId].push(entry);
  }
  
  simpanTugasKeLocalStorage(tugasId);
  
  if (currentRole === 'guru') {
    renderTabelTugasGuru(tugasId);
  }
  
  showToast('✅ File berhasil diupload: ' + file.name);
}

// Simpan data tugas ke localStorage
// =====================
// FUNGSI PENYIMPANAN TUGAS KE LOCALSTORAGE (DIPERBAIKI)
// =====================

function simpanTugasKeLocalStorage(tugasId) {
  try {
    localStorage.setItem('sichemor_tugas_' + tugasId, JSON.stringify(DB.tugasFiles[tugasId] || []));
    console.log('Tugas ID ' + tugasId + ' tersimpan, jumlah file: ' + (DB.tugasFiles[tugasId] || []).length);
  } catch (e) {
    console.error('Gagal menyimpan ke localStorage:', e);
  }
}

function muatTugasDariLocalStorage(tugasId) {
  try {
    var saved = localStorage.getItem('sichemor_tugas_' + tugasId);
    if (saved) {
      DB.tugasFiles[tugasId] = JSON.parse(saved);
      console.log('Tugas ID ' + tugasId + ' dimuat, jumlah file: ' + DB.tugasFiles[tugasId].length);
    }
  } catch (e) {
    console.error('Gagal memuat dari localStorage:', e);
  }
}

function simpanTugasGuruKeLocalStorage() {
  try {
    localStorage.setItem('sichemor_tugas_guru', JSON.stringify(DB.tugasGuru));
    console.log('Tugas guru tersimpan, jumlah: ' + DB.tugasGuru.length);
  } catch (e) {
    console.error('Gagal menyimpan tugas guru:', e);
  }
}

function muatTugasGuruDariLocalStorage() {
  try {
    var saved = localStorage.getItem('sichemor_tugas_guru');
    if (saved) {
      DB.tugasGuru = JSON.parse(saved);
      console.log('Tugas guru dimuat, jumlah: ' + DB.tugasGuru.length);
    } else {
      // Data dummy untuk demo
      var dummyPdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
      DB.tugasGuru = [
        {
          id: 1,
          judul: 'LKPD 1 — Identifikasi Gugus Fungsi',
          desc: 'Kerjakan soal-soal identifikasi gugus fungsi senyawa turunan alkana',
          deadline: '',
          deadlineFormatted: 'Tidak ada batas waktu',
          filename: 'LKPD_1_Identifikasi_Gugus_Fungsi.pdf',
          filesize: '2.5 MB',
          fileUrl: dummyPdfUrl,
          fileType: 'application/pdf',
          tanggalUpload: new Date().toISOString()
        },
        {
          id: 2,
          judul: 'LKPD 2 — Tata Nama IUPAC',
          desc: 'Latihan soal tata nama senyawa turunan alkana',
          deadline: '',
          deadlineFormatted: 'Tidak ada batas waktu',
          filename: 'LKPD_2_Tata_Nama_IUPAC.pdf',
          filesize: '1.8 MB',
          fileUrl: dummyPdfUrl,
          fileType: 'application/pdf',
          tanggalUpload: new Date().toISOString()
        }
      ];
      simpanTugasGuruKeLocalStorage();
    }
  } catch (e) {
    console.error('Gagal memuat tugas guru:', e);
  }
}

function muatSemuaTugasDariLocalStorage() {
  try {
    if (DB.tugasGuru) {
      DB.tugasGuru.forEach(function(tugas) {
        muatTugasDariLocalStorage(tugas.id);
      });
    }
  } catch (e) {
    console.error('Gagal memuat semua tugas:', e);
  }
}

// Muat data tugas dari localStorage
function muatTugasGuruDariLocalStorage() {
  try {
    var saved = localStorage.getItem('sichemor_tugas_guru');
    if (saved) {
      DB.tugasGuru = JSON.parse(saved);
      console.log('Data tugas guru berhasil dimuat');
    } else {
      // TAMBAHKAN DATA DUMMY
      console.log('Tidak ada data, menambahkan data dummy...');
      
      // Buat URL dummy untuk file PDF
      var dummyPdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
      
      DB.tugasGuru = [
        {
          id: 1,
          judul: 'LKPD 1 — Identifikasi Gugus Fungsi',
          desc: 'Kerjakan soal-soal identifikasi gugus fungsi senyawa turunan alkana',
          deadline: '2026-03-07T22:19',
          deadlineFormatted: 'Sab, 07 Mar 2026 · 22:19 WIB',
          filename: 'LKPD_1_Identifikasi_Gugus_Fungsi.pdf',
          filesize: '2.5 MB',
          fileUrl: dummyPdfUrl,
          fileType: 'application/pdf',
          tanggalUpload: new Date().toISOString()
        },
        {
          id: 2,
          judul: 'LKPD 2 — Tata Nama IUPAC',
          desc: 'Latihan soal tata nama senyawa turunan alkana',
          deadline: '2026-03-14T23:59',
          deadlineFormatted: 'Sab, 14 Mar 2026 · 23:59 WIB',
          filename: 'LKPD_2_Tata_Nama_IUPAC.pdf',
          filesize: '1.8 MB',
          fileUrl: dummyPdfUrl,
          fileType: 'application/pdf',
          tanggalUpload: new Date().toISOString()
        }
      ];
      
      // Simpan ke localStorage
      localStorage.setItem('sichemor_tugas_guru', JSON.stringify(DB.tugasGuru));
    }
  } catch (e) {
    console.error('Gagal memuat tugas guru:', e);
  }
}

// Panggil fungsi ini saat aplikasi dimulai
muatTugasDariLocalStorage();

// =====================
// FUNGSI PENYIMPANAN TUGAS GURU
// =====================

function simpanTugasGuruKeLocalStorage() {
  try {
    localStorage.setItem('sichemor_tugas_guru', JSON.stringify(DB.tugasGuru));
  } catch (e) {
    console.error('Gagal menyimpan tugas guru:', e);
  }
}

function muatTugasGuruDariLocalStorage() {
  try {
    var saved = localStorage.getItem('sichemor_tugas_guru');
    if (saved) {
      DB.tugasGuru = JSON.parse(saved);
      console.log('Data tugas guru dimuat:', DB.tugasGuru);
    } else {
      // TAMBAHKAN DATA DUMMY INI
      console.log('Tidak ada data, menambahkan data dummy...');
      
      // Buat URL dummy untuk file PDF
      var dummyPdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
      
      DB.tugasGuru = [
        {
          id: 1,
          judul: 'LKPD 1 — Identifikasi Gugus Fungsi',
          desc: 'Kerjakan soal-soal identifikasi gugus fungsi senyawa turunan alkana',
          deadline: '2026-03-07T22:19',
          deadlineFormatted: 'Sab, 07 Mar 2026 · 22:19 WIB',
          filename: 'PKIM236011 Week 6 Student Handout (1).pdf',
          filesize: '3.9 MB',
          fileUrl: dummyPdfUrl, // Link ke PDF dummy
          fileType: 'application/pdf',
          tanggalUpload: new Date().toISOString()
        }
      ];
      
      // Simpan ke localStorage
      localStorage.setItem('sichemor_tugas_guru', JSON.stringify(DB.tugasGuru));
    }
  } catch (e) {
    console.error('Gagal memuat tugas guru:', e);
  }
}

// Muat data tugas guru dari localStorage
function muatTugasGuruDariLocalStorage() {
  try {
    var saved = localStorage.getItem('sichemor_tugas_guru');
    if (saved) {
      DB.tugasGuru = JSON.parse(saved);
      console.log('Data tugas guru berhasil dimuat');
      
      // Re-create object URLs untuk file yang tersimpan
      DB.tugasGuru.forEach(function(t) {
        if (t.fileUrl && t.fileUrl.startsWith('blob:')) {
          t.fileUrl = null;
        }
      });
    }
  } catch (e) {
    console.error('Gagal memuat tugas guru dari localStorage:', e);
  }
}

function renderTugasSiswa(tugasId) {
  var nama = currentUserData.nama || 'Siswa';
  var userSubmission = DB.tugasFiles[tugasId] ? 
    DB.tugasFiles[tugasId].find(function(t) { 
      return t.nama === nama; 
    }) : null;
  
  var uploadArea = document.getElementById('upload-area-' + tugasId);
  var uploadDone = document.getElementById('upload-done-' + tugasId);
  var filenameEl = document.getElementById('upload-filename-' + tugasId);
  
  if (!uploadArea || !uploadDone || !filenameEl) return;
  
  if (userSubmission) {
    // Jika sudah ada submission, sembunyikan upload area
    uploadArea.style.display = 'none';
    uploadDone.style.display = 'block';
    
    // Tentukan ikon berdasarkan tipe file
    var icon = '📄';
    if (userSubmission.fileType) {
      if (userSubmission.fileType === 'application/pdf') icon = '📕';
      else if (userSubmission.fileType.startsWith('image/')) icon = '🖼️';
      else if (userSubmission.fileType.includes('word') || 
               userSubmission.fileType.includes('document')) icon = '📝';
    } else {
      // Cek dari ekstensi file
      var ext = userSubmission.filename.split('.').pop().toLowerCase();
      if (ext === 'pdf') icon = '📕';
      else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = '🖼️';
      else if (['doc', 'docx'].includes(ext)) icon = '📝';
    }
    
    filenameEl.innerHTML = icon + ' ' + userSubmission.filename + 
      ' · ' + userSubmission.filesize + ' · Pukul ' + userSubmission.waktu + ' WIB';
    
    // Tambahkan tombol lihat file jika ada URL
    var parentDiv = uploadDone.querySelector('div');
    if (parentDiv && userSubmission.fileUrl) {
      // Cek apakah tombol lihat sudah ada
      if (!document.getElementById('btn-lihat-file-' + tugasId)) {
        var lihatBtn = document.createElement('button');
        lihatBtn.id = 'btn-lihat-file-' + tugasId;
        lihatBtn.innerHTML = '🔍 Lihat File';
        lihatBtn.style.cssText = 'background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:4px;color:var(--blue);cursor:pointer;font-size:0.68rem;padding:0.24rem 0.5rem;margin-left:0.5rem;';
        lihatBtn.onclick = function() { 
          window.open(userSubmission.fileUrl, '_blank'); 
        };
        
        var gantiBtn = parentDiv.querySelector('button:last-child');
        if (gantiBtn) {
          parentDiv.insertBefore(lihatBtn, gantiBtn);
        }
      }
    }
  } else {
    // Jika belum ada submission, tampilkan upload area
    uploadArea.style.display = 'block';
    uploadDone.style.display = 'none';
  }
}

function hapusUpload(tugasId) {
  if (!confirm('Hapus file yang sudah diupload?')) return;
  
  var nama = currentUserData.nama || 'Siswa';
  var userSubmission = DB.tugasFiles[tugasId] ? 
    DB.tugasFiles[tugasId].find(function(t) { return t.nama === nama; }) : null;
  
  // Hapus object URL
  if (userSubmission && userSubmission.fileUrl) {
    URL.revokeObjectURL(userSubmission.fileUrl);
  }
  
  if (DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId] = DB.tugasFiles[tugasId].filter(function(t) { 
      return t.nama !== nama; 
    });
  }
  
  // Simpan perubahan ke localStorage
  simpanTugasKeLocalStorage(tugasId);
  
  // Reset tampilan
  document.getElementById('upload-area-' + tugasId).style.display = 'block';
  document.getElementById('upload-done-' + tugasId).style.display = 'none';
  document.getElementById('file-input-' + tugasId).value = '';
  
  // Refresh tampilan guru jika perlu
  if (currentRole === 'guru') {
    renderTabelTugasGuru(tugasId);
  }
  
  showToast('🗑️ File tugas berhasil dihapus');
}

function renderTugasGuru(tugasId) { renderTugasGuruFiltered(tugasId); }

function downloadSemuaTugas(tugasId) {
  var data = DB.tugasFiles[tugasId];
  if (data.length===0) { alert('Belum ada file tugas yang terkumpul.'); return; }
  var hasRealFile = data.filter(function(d){ return d.fileUrl; });
  if (hasRealFile.length===0) { alert('File contoh tidak dapat diunduh.'); return; }
  hasRealFile.forEach(function(d){ setTimeout(function(){ unduhFile(d.fileUrl,d.filename); },300); });
  showToast('⬇️ Mengunduh '+hasRealFile.length+' file tugas...');
}

function unduhFile(url, filename) { var a=document.createElement('a'); a.href=url; a.download=filename; a.click(); }

function exportTugas(tugasId) {
  var headers=['No','Nama Siswa','Kelas','Waktu Kumpul','Nama File','Ukuran','Status'];
  var rows = DB.tugasFiles[tugasId].map(function(d,i){ return [i+1,d.nama,d.kelas,d.waktu,d.filename,d.filesize,d.status]; });
  downloadExcel(rows,headers,'pengumpulan_lkpd'+tugasId+'.xlsx');
}

function hapusFileTugas(tugasId, namaIdx) {
  if (!confirm('Hapus file tugas siswa ini?')) return;
  DB.tugasFiles[tugasId].splice(namaIdx,1); renderTugasGuru(tugasId);
  showToast('🗑️ File tugas berhasil dihapus.');
}

function switchTugasTab(el, tabId) {
  document.querySelectorAll('#sec-tugas .tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  ['tugas-tab-1','tugas-tab-2'].forEach(function(id){ var e=document.getElementById(id); if(e) e.style.display=id===tabId?'block':'none'; });
  renderTugasGuru(tabId==='tugas-tab-1'?1:2);
}

// =====================
// KUIS
// =====================
function bukaKuis(id) {
  console.log('Membuka kuis ID:', id);
  
  activeKuisId = id;
  kuisAnswers = {};
  
  var kuis = DB.kuis.find(function(k){ return k.id === id; });
  if (!kuis) {
    console.error('Kuis tidak ditemukan!');
    alert('Kuis tidak ditemukan!');
    return;
  }
  
  if (!kuis.soal || kuis.soal.length === 0) {
    alert('Kuis ini tidak memiliki soal!');
    return;
  }
  
  // Sembunyikan grid kuis, tampilkan form
  var kuisGrid = document.querySelector('#sec-kuis .kuis-grid');
  var kuisForm = document.getElementById('kuis-form-container');
  var kuisHasil = document.getElementById('kuis-hasil-siswa');
  
  if (kuisGrid) kuisGrid.style.display = 'none';
  if (kuisForm) kuisForm.style.display = 'block';
  if (kuisHasil) kuisHasil.style.display = 'none';
  
  document.getElementById('kuis-form-title').textContent = kuis.judul;
  document.getElementById('kuis-form-meta').textContent = kuis.soal.length + ' soal · Durasi: ' + kuis.durasi + ' menit';
  
  var area = document.getElementById('kuis-questions-area');
  if (!area) return;
  
  area.innerHTML = '';
  kuis.soal.forEach(function(s, si) {
    var soalHtml = '<div class="kuis-question" id="soal-' + si + '">' +
      '<div class="kuis-question-text">' + (si + 1) + '. ' + (s.pertanyaan || 'Pertanyaan tidak tersedia') + '</div>';
    
    s.opsi.forEach(function(op, oi) {
      var huruf = String.fromCharCode(65 + oi);
      soalHtml += '<div class="kuis-option" id="opt-' + si + '-' + oi + '" onclick="pilihOpsi(' + si + ',' + oi + ')">' +
        '<input type="radio" name="q' + si + '" value="' + oi + '" id="radio-' + si + '-' + oi + '"> ' +
        '<span>' + huruf + '. ' + (op || 'Opsi tidak tersedia') + '</span>' +
        '</div>';
    });
    
    soalHtml += '</div>';
    area.innerHTML += soalHtml;
  });
  
  // Timer
  kuisTimeLeft = kuis.durasi * 60;
  clearInterval(kuisTimerInterval);
  kuisTimerInterval = setInterval(function() {
    if (kuisTimeLeft <= 0) {
      clearInterval(kuisTimerInterval);
      submitKuis();
    } else {
      kuisTimeLeft--;
      var m = Math.floor(kuisTimeLeft / 60);
      var s = kuisTimeLeft % 60;
      var timerEl = document.getElementById('kuis-timer');
      if (timerEl) timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }
  }, 1000);
}
function pilihOpsi(soalIdx, opsiIdx) {
  // Hapus class selected dari semua opsi di soal ini
  for (var i = 0; i < 4; i++) {
    var el = document.getElementById('opt-' + soalIdx + '-' + i);
    if (el) {
      el.classList.remove('selected');
      var radio = el.querySelector('input[type="radio"]');
      if (radio) radio.checked = false;
    }
  }
  
  // Tambahkan class selected ke opsi yang dipilih
  var selected = document.getElementById('opt-' + soalIdx + '-' + opsiIdx);
  if (selected) {
    selected.classList.add('selected');
    var radio = selected.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
  }
  
  // Simpan jawaban
  kuisAnswers[soalIdx] = opsiIdx;
  console.log('Jawaban soal ' + (soalIdx + 1) + ':', opsiIdx);
}

function tutupKuis() { clearInterval(kuisTimerInterval); document.getElementById('kuis-form-container').style.display='none'; document.querySelector('.kuis-grid').style.display='grid'; }

function submitKuis() {
  clearInterval(kuisTimerInterval);
  
  var kuis = DB.kuis.find(function(k) { return k.id === activeKuisId; });
  if (!kuis) {
    alert('Kuis tidak ditemukan!');
    return;
  }
  
  // Hitung jawaban benar
  var benar = 0;
  for (var i = 0; i < kuis.soal.length; i++) {
    if (kuisAnswers[i] === kuis.soal[i].jawaban) {
      benar++;
    }
  }
  
  var nilai = Math.round((benar / kuis.soal.length) * 100);
  var now = new Date();
  var waktu = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
  var tanggal = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
  var nama = currentUserData.nama || 'Siswa';
  var kelas = currentUserData.kelas || 'XII MIPA 2';
  var durasi = (kuis.durasi - Math.ceil(kuisTimeLeft / 60)) + ' menit';
  
  // Simpan nilai
  var existing = DB.nilaiKuis.findIndex(function(n) { return n.nama === nama && n.id === activeKuisId; });
  var nilaiData = {
    id: activeKuisId,
    nama: nama,
    kelas: kelas,
    tanggal: tanggal,
    waktu: waktu,
    durasi: durasi,
    nilai: nilai,
    benar: benar,
    total: kuis.soal.length
  };
  
  if (existing >= 0) {
    DB.nilaiKuis[existing] = nilaiData;
  } else {
    DB.nilaiKuis.push(nilaiData);
  }
  
  // Simpan ke localStorage
  simpanKuisKeLocalStorage();
  
  // Tampilkan hasil
  document.getElementById('kuis-form-container').style.display = 'none';
  document.getElementById('kuis-hasil-siswa').style.display = 'block';
  document.getElementById('nilai-akhir').textContent = nilai;
  document.getElementById('detail-jawaban-siswa').innerHTML = 'Benar ' + benar + ' dari ' + kuis.soal.length + ' soal<br>' +
    'Waktu: ' + tanggal + ' ' + waktu + '<br>' +
    'Durasi: ' + durasi;
  
  // Refresh tampilan kuis siswa
  renderKuisSiswa();
  renderHasilKuis();
  
  showToast('✅ Kuis selesai! Nilai Anda: ' + nilai);
}

function kembaliKuis() { document.getElementById('kuis-hasil-siswa').style.display='none'; document.querySelector('.kuis-grid').style.display='grid'; }
function renderHasilKuis() { filterHasilKelasAktif='semua'; renderHasilKuisFiltered(); }
function guruLihatHasil(kuisId) {
  var kuis=DB.kuis.find(function(k){ return k.id===(kuisId||1); });
  var titleEl=document.getElementById('hasil-kuis-title');
  if(titleEl&&kuis) titleEl.textContent='Hasil '+kuis.judul;
  filterHasilKelas(null,'semua');
  switchKuisTabByName('hasil-kuis');
}
function switchKuisTab(el, tabId) {
  document.querySelectorAll('#sec-kuis .tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  document.getElementById('daftar-kuis').style.display='none';
  document.getElementById('hasil-kuis').style.display='none';
  document.getElementById(tabId).style.display='block';
}
function switchKuisTabByName(tabId) {
  document.querySelectorAll('#sec-kuis .tab-btn').forEach(function(b,i){
    b.classList.remove('active');
    if((i===0&&tabId==='daftar-kuis')||(i===1&&tabId==='hasil-kuis')) b.classList.add('active');
  });
  document.getElementById('daftar-kuis').style.display=tabId==='daftar-kuis'?'block':'none';
  document.getElementById('hasil-kuis').style.display=tabId==='hasil-kuis'?'block':'none';
}

// =====================
// FILTER KELAS
// =====================
var filterPresensiKelasAktif = 'semua';
function filterPresensiKelas(btn, kelas) {
  filterPresensiKelasAktif = kelas;
  document.querySelectorAll('#tab-presensi-hari-ini .filter-btn').forEach(function(b){ b.classList.remove('active'); });
  if (btn) btn.classList.add('active');
  renderPresensiGuruFiltered();
}

function renderPresensiGuruFiltered() {
  var tbody = document.getElementById('presensi-guru-body');
  if (!tbody) return;
  var data = filterPresensiKelasAktif==='semua' ? DB.presensi : DB.presensi.filter(function(p){ return p.kelas===filterPresensiKelasAktif; });
  if (data.length===0) { tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:1.8rem;color:var(--text-dim);">Tidak ada data untuk kelas '+filterPresensiKelasAktif+'.</td></tr>'; return; }
  tbody.innerHTML = data.map(function(p,i) {
    var badgeCls=p.status==='Hadir'?'hadir':p.status==='Alpha'?'alpha':'izin';
    return '<tr><td>'+(i+1)+'</td><td style="font-weight:700">'+p.nama+'</td><td>'+p.kelas+'</td>'
      +'<td style="font-family:\'JetBrains Mono\';font-size:0.73rem">'+p.waktu+'</td>'
      +'<td><span class="badge-'+badgeCls+'">'+p.status+'</span></td>'
      +'<td><select style="background:var(--ink-4);border:1px solid var(--border);border-radius:4px;padding:0.2rem 0.45rem;color:var(--text-primary);font-size:0.68rem;font-family:\'Source Sans 3\',sans-serif;" onchange="ubahStatusPresensi('+p.id+',this.value)">'
      +'<option '+(p.status==='Hadir'?'selected':'')+'>Hadir</option>'
      +'<option '+(p.status==='Izin'?'selected':'')+'>Izin</option>'
      +'<option '+(p.status==='Sakit'?'selected':'')+'>Sakit</option>'
      +'<option '+(p.status==='Alpha'?'selected':'')+'>Alpha</option>'
      +'</select></td></tr>';
  }).join('');
}

var filterTugasKelasAktif = {1:'semua', 2:'semua'};
function filterTugasKelas(btn, tugasId, kelas) {
  filterTugasKelasAktif[tugasId]=kelas;
  var container=tugasId===1?'#tugas-tab-1':'#tugas-tab-2';
  document.querySelectorAll(container+' .filter-btn').forEach(function(b){ b.classList.remove('active'); });
  if(btn) btn.classList.add('active');
  renderTugasGuruFiltered(tugasId);
}

function renderTugasGuruFiltered(tugasId) {
  var tbody = document.getElementById('tugas-tbody-' + tugasId);
  if (!tbody) return;
  
  var total = 24;
  var allData = DB.tugasFiles[tugasId] || [];
  var kelas = filterTugasKelasAktif[tugasId] || 'semua';
  var data = kelas === 'semua' ? allData : allData.filter(function(d) { 
    return d.kelas === kelas; 
  });
  
  var collected = allData.length;
  var countEl = document.getElementById('t' + tugasId + '-count');
  var remainEl = document.getElementById('t' + tugasId + '-remain');
  var barEl = document.getElementById('t' + tugasId + '-bar');
  
  if (countEl) countEl.textContent = collected;
  if (remainEl) remainEl.textContent = total - collected;
  if (barEl) barEl.style.width = Math.round(collected / total * 100) + '%';
  
  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:1.8rem;color:var(--text-dim);">' +
      (kelas === 'semua' ? 'Belum ada siswa yang mengumpulkan tugas ini.' : 'Belum ada tugas dari kelas ' + kelas + '.') + 
      '</td></tr>';
    return;
  }
  
  var iconMap = {
    pdf: '📕', jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
    doc: '📝', docx: '📝', ppt: '📊', pptx: '📊', 
    xls: '📊', xlsx: '📊', txt: '📄', zip: '📦', rar: '📦'
  };
  
  tbody.innerHTML = data.map(function(d, i) {
    var ext = d.filename.split('.').pop().toLowerCase();
    var icon = iconMap[ext] || '📄';
    var badgeCls = d.status === 'Tepat Waktu' ? 'hadir' : 'izin';
    var realIdx = allData.findIndex(function(x) { 
      return x.nama === d.nama && x.filename === d.filename; 
    });
    
    // Buat fungsi untuk membuka file
    var openFileFn = d.fileUrl 
      ? "window.open('" + d.fileUrl + "','_blank')" 
      : "alert('File tidak tersedia untuk dilihat.')";
    
    var downloadFn = d.fileUrl 
      ? "unduhFile('" + d.fileUrl + "','" + d.filename + "')" 
      : "alert('File tidak tersedia untuk diunduh.')";
    
    return '<tr>' +
      '<td style="color:var(--text-dim);font-weight:700">' + (i + 1) + '</td>' +
      '<td style="font-weight:700">' + d.nama + '</td>' +
      '<td>' + d.kelas + '</td>' +
      '<td style="font-family:\'JetBrains Mono\';font-size:0.72rem;color:var(--text-secondary)">' + d.waktu + ' WIB</td>' +
      '<td>' +
      '<div style="display:flex;align-items:center;gap:0.4rem;">' +
      '<span style="font-size:1rem;">' + icon + '</span>' +
      '<span style="font-size:0.75rem;color:var(--blue);text-decoration:underline;cursor:pointer;" ' +
      'onclick="' + openFileFn + '">' + d.filename + '</span>' +
      '</div>' +
      '</td>' +
      '<td style="font-size:0.72rem;color:var(--text-secondary)">' + d.filesize + '</td>' +
      '<td><span class="badge-' + badgeCls + '">' + d.status + '</span></td>' +
      '<td>' +
      '<div style="display:flex;gap:0.3rem;">' +
      '<button onclick="' + openFileFn + '" ' +
      'style="padding:0.24rem 0.5rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:4px;color:var(--blue);cursor:pointer;font-size:0.68rem;font-family:\'Source Sans 3\',sans-serif;">👁 Lihat</button>' +
      '<button onclick="' + downloadFn + '" ' +
      'style="padding:0.24rem 0.5rem;background:var(--emerald-dim);border:1px solid rgba(63,185,80,0.25);border-radius:4px;color:var(--emerald);cursor:pointer;font-size:0.68rem;font-family:\'Source Sans 3\',sans-serif;">⬇ Download</button>' +
      '<button onclick="hapusFileTugas(' + tugasId + ',' + realIdx + ')" ' +
      'style="padding:0.24rem 0.5rem;background:rgba(248,81,73,0.07);border:1px solid rgba(248,81,73,0.20);border-radius:4px;color:var(--red);cursor:pointer;font-size:0.68rem;font-family:\'Source Sans 3\',sans-serif;">🗑 Hapus</button>' +
      '</div>' +
      '</td>' +
      '</tr>';
  }).join('');
}// =====================
// FUNGSI UPLOAD JAWABAN SISWA UNTUK TUGAS GURU
// =====================


// Simpan data tugas ke localStorage
function simpanTugasKeLocalStorage(tugasId) {
  try {
    localStorage.setItem('sichemor_tugas_' + tugasId, JSON.stringify(DB.tugasFiles[tugasId]));
  } catch (e) {
    console.error('Gagal menyimpan ke localStorage:', e);
  }
}

// Muat data tugas dari localStorage
function muatSemuaTugasDariLocalStorage() {
  try {
    // Muat file jawaban untuk setiap tugas yang ada
    if (DB.tugasGuru) {
      DB.tugasGuru.forEach(function(tugas) {
        muatTugasDariLocalStorage(tugas.id);
      });
    }
  } catch (e) {
    console.error('Gagal memuat semua tugas:', e);
  }
}

function hapusUpload(tugasId) {
  if (!confirm('Hapus file yang sudah diupload?')) return;
  
  var nama = currentUserData.nama || 'Siswa';
  var userSubmission = DB.tugasFiles[tugasId] ? 
    DB.tugasFiles[tugasId].find(function(t) { return t.nama === nama; }) : null;
  
  // Hapus object URL
  if (userSubmission && userSubmission.fileUrl) {
    URL.revokeObjectURL(userSubmission.fileUrl);
  }
  
  if (DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId] = DB.tugasFiles[tugasId].filter(function(t) { 
      return t.nama !== nama; 
    });
  }
  
  // Simpan perubahan ke localStorage
  simpanTugasKeLocalStorage(tugasId);
  
  // Reset tampilan
  document.getElementById('upload-area-' + tugasId).style.display = 'block';
  document.getElementById('upload-done-' + tugasId).style.display = 'none';
  document.getElementById('file-input-' + tugasId).value = '';
  
  // Refresh tampilan guru jika perlu
  if (currentRole === 'guru') {
    renderTabelTugasGuru(tugasId);
  }
  
  showToast('🗑️ File tugas berhasil dihapus');
}

var filterHasilKelasAktif = 'semua';
function filterHasilKelas(btn, kelas) {
  filterHasilKelasAktif=kelas;
  if(btn){ document.querySelectorAll('#hasil-kuis .filter-btn').forEach(function(b){ b.classList.remove('active'); }); btn.classList.add('active'); }
  renderHasilKuisFiltered();
}

function renderHasilKuisFiltered() {
  var tbody=document.getElementById('hasil-tbody');
  if(!tbody) return;
  var data=filterHasilKelasAktif==='semua'?DB.nilaiKuis:DB.nilaiKuis.filter(function(n){ return n.kelas===filterHasilKelasAktif; });
  var statEl=document.getElementById('stat-kelas-nilai');
  if(statEl&&data.length>0){
    var avg=Math.round(data.reduce(function(s,n){ return s+n.nilai; },0)/data.length);
    var lulus=data.filter(function(n){ return n.nilai>=75; }).length;
    statEl.innerHTML='<span style="font-size:0.7rem;background:var(--emerald-dim);color:var(--emerald);border:1px solid rgba(63,185,80,0.22);border-radius:3px;padding:0.2rem 0.65rem;font-weight:700;">📊 Rata-rata: '+avg+'</span>'
      +'<span style="font-size:0.7rem;background:var(--accent-dim);color:var(--accent);border:1px solid rgba(30,58,138,0.22);border-radius:3px;padding:0.2rem 0.65rem;font-weight:700;">✅ Lulus (≥75): '+lulus+'/'+data.length+'</span>';
  } else if(statEl) statEl.innerHTML='';
  if(data.length===0){ tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:1.8rem;color:var(--text-dim);">'+(filterHasilKelasAktif==='semua'?'Belum ada siswa yang mengerjakan kuis ini.':'Belum ada hasil untuk kelas '+filterHasilKelasAktif+'.')+'</td></tr>'; return; }
  tbody.innerHTML=data.map(function(n,i){
    var cls=n.nilai>=80?'good':n.nilai>=60?'mid':'low';
    return '<tr><td>'+(i+1)+'</td><td style="font-weight:700">'+n.nama+'</td><td>'+n.kelas+'</td>'
      +'<td style="font-family:\'JetBrains Mono\';font-size:0.73rem">'+n.waktu+'</td>'
      +'<td>'+n.durasi+'</td>'
      +'<td><span class="score-badge score-'+cls+'">'+n.nilai+'</span></td>'
      +'<td style="font-size:0.7rem;color:var(--text-secondary)">'+n.benar+'/'+n.total+' benar</td></tr>';
  }).join('');
}

// =====================
// SOAL EDITOR
// =====================
var editSoalKuisId = null;
var editSoalData = [];

function openEditSoal(kuisId) {
  editSoalKuisId=kuisId;
  var kuis=DB.kuis.find(function(k){ return k.id===kuisId; });
  editSoalData=kuis?kuis.soal.map(function(s){ return {pertanyaan:s.pertanyaan,opsi:s.opsi.slice(),jawaban:s.jawaban}; }):[];
  modalType='edit-kuis'; modalTempFile=null;
  var overlay=document.getElementById('modal');
  overlay.classList.add('open');
  document.getElementById('modal-title').textContent='✏️ Edit Soal — '+(kuis?kuis.judul:'Kuis');
  renderEditSoalModal();
}

function renderEditSoalModal() {
  var kuis=DB.kuis.find(function(k){ return k.id===editSoalKuisId; });
  var html='';
  if(kuis){
    html+='<div style="display:flex;gap:0.8rem;margin-bottom:1.1rem;flex-wrap:wrap;">'
      +'<div class="form-group" style="flex:2;margin:0"><label class="form-label">Judul Kuis</label>'
      +'<input type="text" class="form-input" id="edit-kuis-judul" value="'+kuis.judul+'"></div>'
      +'<div class="form-group" style="flex:1;margin:0"><label class="form-label">Durasi (menit)</label>'
      +'<input type="number" class="form-input" id="edit-kuis-durasi" value="'+kuis.durasi+'" min="1" max="180"></div></div>';
  }
  html+='<div id="soal-list-editor">';
  editSoalData.forEach(function(s,si){ html+=renderSoalBlock(s,si); });
  html+='</div><button class="add-soal-btn" onclick="tambahSoalBaru()">＋ Tambah Soal Baru</button>'
    +'<p class="soal-hint" style="margin-top:0.6rem;">💡 Klik lingkaran A/B/C/D untuk menandai jawaban <strong>benar</strong>. Tanda ✓ hijau = jawaban benar.</p>';
  document.getElementById('modal-body').innerHTML=html;
}

function renderSoalBlock(s,si) {
  var html='<div class="soal-block" id="soal-block-'+si+'">'
    +'<div class="soal-block-num">Soal '+(si+1)+'</div>'
    +'<button class="soal-del-btn" onclick="hapusSoalEdit('+si+')" title="Hapus soal">✕</button>'
    +'<div class="form-group"><label class="form-label">Pertanyaan</label>'
    +'<textarea class="form-textarea" style="min-height:52px;" onchange="editSoalData['+si+'].pertanyaan=this.value" placeholder="Tulis pertanyaan soal...">'+(s.pertanyaan||'')+'</textarea></div>'
    +'<div class="form-group"><label class="form-label">Opsi Jawaban &nbsp;<span style="color:var(--emerald);font-size:0.62rem;">● klik lingkaran untuk tandai jawaban benar</span></label>';
  ['A','B','C','D'].forEach(function(hrf,oi){
    var isBenar=s.jawaban===oi;
    html+='<div class="soal-opsi-row">'
      +'<div class="soal-opsi-key '+(isBenar?'benar':'')+'" onclick="setJawabanBenar('+si+','+oi+')" title="Tandai jawaban benar">'+(isBenar?'✓':hrf)+'</div>'
      +'<input type="text" class="form-input" style="flex:1" placeholder="Opsi '+hrf+'" value="'+(s.opsi[oi]||'')+'" oninput="editSoalData['+si+'].opsi['+oi+']=this.value"></div>';
  });
  html+='</div></div>';
  return html;
}

function setJawabanBenar(soalIdx, opsiIdx) {
  editSoalData[soalIdx].jawaban=opsiIdx;
  var block=document.getElementById('soal-block-'+soalIdx);
  if(block){
    var inputs=block.querySelectorAll('input[type=text]');
    inputs.forEach(function(inp,oi){ editSoalData[soalIdx].opsi[oi]=inp.value; });
    var tarea=block.querySelector('textarea');
    if(tarea) editSoalData[soalIdx].pertanyaan=tarea.value;
    block.outerHTML=renderSoalBlock(editSoalData[soalIdx],soalIdx);
  }
}

function tambahSoalBaru() {
  editSoalData.push({pertanyaan:'',opsi:['','','',''],jawaban:0});
  var list=document.getElementById('soal-list-editor');
  if(list){ var newDiv=document.createElement('div'); newDiv.innerHTML=renderSoalBlock(editSoalData[editSoalData.length-1],editSoalData.length-1); list.appendChild(newDiv.firstChild); }
}

function hapusSoalEdit(si) {
  if(editSoalData.length<=1){ alert('Kuis harus memiliki minimal 1 soal.'); return; }
  if(!confirm('Hapus soal '+(si+1)+'?')) return;
  syncEditSoalFromDOM(); editSoalData.splice(si,1); renderEditSoalModal();
}

function syncEditSoalFromDOM() {
  editSoalData.forEach(function(s,si){
    var block=document.getElementById('soal-block-'+si);
    if(!block) return;
    var tarea=block.querySelector('textarea');
    if(tarea) s.pertanyaan=tarea.value;
    var inputs=block.querySelectorAll('input[type=text]');
    inputs.forEach(function(inp,oi){ s.opsi[oi]=inp.value; });
  });
}

function simpanEditSoal() {
  if(!editSoalKuisId) return;
  syncEditSoalFromDOM();
  for(var i=0;i<editSoalData.length;i++){
    if(!editSoalData[i].pertanyaan.trim()){ alert('Pertanyaan soal '+(i+1)+' belum diisi.'); return; }
    var hasOpsi=editSoalData[i].opsi.some(function(o){ return o.trim(); });
    if(!hasOpsi){ alert('Soal '+(i+1)+' butuh minimal satu opsi.'); return; }
  }
  var kuis=DB.kuis.find(function(k){ return k.id===editSoalKuisId; });
  if(kuis){
    kuis.soal=editSoalData.map(function(s){ return {pertanyaan:s.pertanyaan,opsi:s.opsi.slice(),jawaban:s.jawaban}; });
    var judulEl=document.getElementById('edit-kuis-judul');
    var durasiEl=document.getElementById('edit-kuis-durasi');
    if(judulEl&&judulEl.value.trim()) kuis.judul=judulEl.value.trim();
    if(durasiEl&&parseInt(durasiEl.value)) kuis.durasi=parseInt(durasiEl.value);
  }
  closeModal();
  showToast('✅ Soal kuis berhasil disimpan! ('+editSoalData.length+' soal)');
}

// =====================
// EXCEL EXPORT
// =====================
function downloadExcel(data, headers, filename) {
  var wsData=[headers].concat(data);
  var ws=XLSX.utils.aoa_to_sheet(wsData);
  var colWidths=headers.map(function(h,i){ var max=h.length; data.forEach(function(row){ var len=String(row[i]||'').length; if(len>max) max=len; }); return {wch:max+2}; });
  ws['!cols']=colWidths;
  var wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,ws,'Data');
  XLSX.writeFile(wb,filename);
}

function exportNilai() {
  var headers=['No','Nama','Kelas','Waktu Kerjakan','Durasi','Nilai','Benar','Total'];
  var data=DB.nilaiKuis.map(function(n,i){ return [i+1,n.nama,n.kelas,n.waktu,n.durasi,n.nilai,n.benar,n.total]; });
  downloadExcel(data,headers,'nilai_kuis_kimia.xlsx');
}
// =====================
// FUNGSI LOGOUT
// =====================
function logout() {
  // Reset semua state
  currentRole = '';
  currentUserData = {};
  selectedRole = '';
  
  // Reset body class
  document.body.className = '';
  
  // Sembunyikan app screen
  var appScreen = document.getElementById('app-screen');
  if (appScreen) appScreen.classList.remove('active');
  
  // Tampilkan login screen
  var loginScreen = document.getElementById('login-screen');
  if (loginScreen) loginScreen.classList.add('active');
  
  // Reset form login
  var guruPw = document.getElementById('pw-guru');
  if (guruPw) guruPw.value = '';
  
  var roleBtnGuru = document.getElementById('role-btn-guru');
  var roleBtnSiswa = document.getElementById('role-btn-siswa');
  if (roleBtnGuru) roleBtnGuru.classList.remove('selected');
  if (roleBtnSiswa) roleBtnSiswa.classList.remove('selected');
  
  var pwSectionGuru = document.getElementById('pw-section-guru');
  var pwSectionSiswa = document.getElementById('pw-section-siswa');
  if (pwSectionGuru) pwSectionGuru.classList.add('hidden');
  if (pwSectionSiswa) pwSectionSiswa.classList.add('hidden');
  
  var btnMasuk = document.getElementById('btn-masuk');
  if (btnMasuk) {
    btnMasuk.disabled = true;
    btnMasuk.textContent = '→ Masuk';
  }
  
  // Reset form profil
  var guruNama = document.getElementById('guru-nama');
  var guruUsername = document.getElementById('guru-username');
  var guruEmail = document.getElementById('guru-email');
  var siswaNama = document.getElementById('siswa-nama');
  var siswaKelas = document.getElementById('siswa-kelas');
  var siswaAbsen = document.getElementById('siswa-absen');
  var siswaEmail = document.getElementById('siswa-email');
  
  if (guruNama) guruNama.value = '';
  if (guruUsername) guruUsername.value = '';
  if (guruEmail) guruEmail.value = '';
  if (siswaNama) siswaNama.value = '';
  if (siswaKelas) siswaKelas.value = '';
  if (siswaAbsen) siswaAbsen.value = '';
  if (siswaEmail) siswaEmail.value = '';
  
  // Reset sidebar state
  if (typeof sidebarInitialized !== 'undefined') {
    sidebarInitialized = false;
  }
  
  // Reset local storage sidebar state (opsional)
  // localStorage.removeItem('sichemor_sidebar_collapsed');
  
  console.log('Logout berhasil');
}
// =====================
// FUNGSI RENDER TUGAS DINAMIS
// =====================

// Render tugas untuk halaman GURU
function renderTugasGuruDinamis() {
  var container = document.getElementById('tugas-guru-container');
  if (!container) return;
  
  container.innerHTML = '';
  
  if (!DB.tugasGuru || DB.tugasGuru.length === 0) {
    container.innerHTML = '<div class="empty-state">' +
      '<div class="empty-icon">📋</div>' +
      '<div class="empty-text">Belum ada tugas</div>' +
      '<div class="empty-sub">Klik "+ Buat Tugas Baru" untuk membuat tugas pertama.</div>' +
      '</div>';
    return;
  }
  
  // Urutkan tugas dari yang terbaru
  DB.tugasGuru.sort(function(a, b) {
    return new Date(b.tanggalUpload) - new Date(a.tanggalUpload);
  });
  
  // Buat tab navigation
  var tabNav = document.createElement('div');
  tabNav.className = 'tab-nav';
  tabNav.style.marginBottom = '1rem';
  tabNav.style.overflowX = 'auto';
  tabNav.style.flexWrap = 'wrap';
  
  // Buat container untuk setiap tugas
  DB.tugasGuru.forEach(function(tugas, index) {
    var btn = document.createElement('button');
    btn.className = index === 0 ? 'tab-btn active' : 'tab-btn';
    btn.textContent = '📋 ' + tugas.judul.substring(0, 25) + (tugas.judul.length > 25 ? '...' : '');
    btn.setAttribute('onclick', 'pilihTugasGuruDinamis(' + tugas.id + ', this)');
    tabNav.appendChild(btn);
  });
  container.appendChild(tabNav);
  
  // Buat container untuk setiap tugas
  DB.tugasGuru.forEach(function(tugas, index) {
    var tugasContainer = document.createElement('div');
    tugasContainer.id = 'tugas-guru-dinamis-' + tugas.id;
    tugasContainer.style.display = index === 0 ? 'block' : 'none';
    tugasContainer.innerHTML = buatHTMLTugasGuruDinamis(tugas);
    container.appendChild(tugasContainer);
  });
  
  // Render tabel untuk tugas pertama
  if (DB.tugasGuru.length > 0) {
    renderTabelTugasGuruDinamis(DB.tugasGuru[0].id);
  }
}

function buatHTMLTugasGuruDinamis(tugas) {
  var icon = '📋';
  if (tugas.fileType) {
    if (tugas.fileType === 'application/pdf') icon = '📕';
    else if (tugas.fileType.startsWith('image/')) icon = '🖼️';
    else if (tugas.fileType.includes('word') || tugas.fileType.includes('document')) icon = '📝';
    else if (tugas.fileType.includes('presentation')) icon = '📊';
  } else {
    var ext = tugas.filename ? tugas.filename.split('.').pop().toLowerCase() : '';
    if (ext === 'pdf') icon = '📕';
    else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = '🖼️';
    else if (['doc', 'docx'].includes(ext)) icon = '📝';
    else if (['ppt', 'pptx'].includes(ext)) icon = '📊';
  }
  
  return '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.8rem;flex-wrap:wrap;gap:0.45rem;">' +
    '<div>' +
    '<div style="font-size:0.88rem;font-weight:700;">' + escapeHtml(tugas.judul) + '</div>' +
    '<div style="font-size:0.68rem;color:var(--text-secondary);margin-top:0.15rem;">' +
    'Deadline: ' + (tugas.deadlineFormatted || 'Tidak ada batas waktu') + '</div>' +
    (tugas.desc ? '<div style="font-size:0.68rem;color:var(--text-dim);margin-top:0.15rem;">📝 ' + escapeHtml(tugas.desc) + '</div>' : '') +
    '</div>' +
    '<div style="display:flex;gap:0.45rem;align-items:center;">' +
    '<span style="font-size:0.7rem;color:var(--emerald);">✓ <span id="t-count-' + tugas.id + '">0</span> terkumpul</span>' +
    '<span style="font-size:0.7rem;color:var(--text-dim);">·</span>' +
    '<span style="font-size:0.7rem;color:var(--accent);">⏳ <span id="t-remain-' + tugas.id + '">24</span> belum</span>' +
    '<button class="btn-secondary" style="padding:0.34rem 0.68rem;font-size:0.7rem;" onclick="downloadSemuaTugasDinamis(' + tugas.id + ')">📦 Download Semua</button>' +
    '<button class="btn-secondary" style="padding:0.34rem 0.68rem;font-size:0.7rem;" onclick="exportTugasDinamis(' + tugas.id + ')">📥 Export Excel</button>' +
    '</div>' +
    '</div>' +
    '<div style="margin-bottom:0.8rem;padding:0.5rem 0.75rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:6px;display:flex;align-items:center;gap:0.6rem;">' +
    '<span style="font-size:1rem;">📎</span>' +
    '<div style="flex:1;">' +
    '<div style="font-size:0.72rem;color:var(--text-secondary);">File Soal Tugas:</div>' +
    '<div style="font-size:0.78rem;font-weight:600;color:var(--blue);text-decoration:underline;cursor:pointer;" ' +
    'onclick="' + (tugas.fileUrl ? "window.open('" + tugas.fileUrl + "','_blank')" : "alert('File tidak tersedia')") + '">' +
    escapeHtml(tugas.filename || 'File tidak tersedia') + ' (' + (tugas.filesize || '0 KB') + ')' +
    '</div>' +
    '</div>' +
    '<button onclick="' + (tugas.fileUrl ? "window.open('" + tugas.fileUrl + "','_blank')" : "alert('File tidak tersedia')") + '" ' +
    'style="padding:0.24rem 0.6rem;background:transparent;border:1px solid var(--border);border-radius:4px;color:var(--text-secondary);cursor:pointer;font-size:0.68rem;">🔍 Lihat</button>' +
    '<button onclick="hapusTugasDinamis(' + tugas.id + ')" ' +
    'style="padding:0.24rem 0.6rem;background:rgba(248,81,73,0.07);border:1px solid rgba(248,81,73,0.20);border-radius:4px;color:var(--red);cursor:pointer;font-size:0.68rem;">🗑 Hapus Tugas</button>' +
    '</div>' +
    '<div class="filter-bar">' +
    '<label>Kelas:</label>' +
    '<button class="filter-btn active" onclick="filterTugasKelasDinamis(this, ' + tugas.id + ', \'semua\')">Semua</button>' +
    '<button class="filter-btn" onclick="filterTugasKelasDinamis(this, ' + tugas.id + ', \'XII MIPA 1\')">XII MIPA 1</button>' +
    '<button class="filter-btn" onclick="filterTugasKelasDinamis(this, ' + tugas.id + ', \'XII MIPA 2\')">XII MIPA 2</button>' +
    '<button class="filter-btn" onclick="filterTugasKelasDinamis(this, ' + tugas.id + ', \'XII MIPA 3\')">XII MIPA 3</button>' +
    '<button class="filter-btn" onclick="filterTugasKelasDinamis(this, ' + tugas.id + ', \'XII MIPA 4\')">XII MIPA 4</button>' +
    '</div>' +
    '<div class="progress-bar" style="margin-bottom:1.2rem;">' +
    '<div class="progress-fill" id="t-bar-' + tugas.id + '" style="width:0%"></div>' +
    '</div>' +
    '<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">' +
    '<table class="result-table">' +
    '<thead> <tr><th>No</th><th>Nama Siswa</th><th>Kelas</th><th>Waktu Kumpul</th><th>File Tugas</th><th>Ukuran</th><th>Status</th><th>Aksi</th></tr> </thead>' +
    '<tbody id="tugas-tbody-' + tugas.id + '">' +
    '<tr><td colspan="8" style="text-align:center;padding:1.8rem;color:var(--text-dim);">Belum ada siswa yang mengumpulkan tugas ini.</td></tr>' +
    '</tbody>' +
    '</table>' +
    '</div>';
}

function pilihTugasGuruDinamis(tugasId, btn) {
  // Nonaktifkan semua tab
  document.querySelectorAll('#tugas-guru-container .tab-btn').forEach(function(b) {
    b.classList.remove('active');
  });
  btn.classList.add('active');
  
  // Sembunyikan semua container tugas
  document.querySelectorAll('[id^="tugas-guru-dinamis-"]').forEach(function(container) {
    container.style.display = 'none';
  });
  
  // Tampilkan container yang dipilih
  var selectedContainer = document.getElementById('tugas-guru-dinamis-' + tugasId);
  if (selectedContainer) {
    selectedContainer.style.display = 'block';
    renderTabelTugasGuruDinamis(tugasId);
  }
}

function renderTabelTugasGuruDinamis(tugasId) {
  var tbody = document.getElementById('tugas-tbody-' + tugasId);
  if (!tbody) return;
  
  var total = 24; // Total siswa
  var allData = DB.tugasFiles[tugasId] || [];
  var kelas = filterTugasKelasAktifDinamis[tugasId] || 'semua';
  var data = kelas === 'semua' ? allData : allData.filter(function(d) { 
    return d.kelas === kelas; 
  });
  
  var collected = allData.length;
  var countEl = document.getElementById('t-count-' + tugasId);
  var remainEl = document.getElementById('t-remain-' + tugasId);
  var barEl = document.getElementById('t-bar-' + tugasId);
  
  if (countEl) countEl.textContent = collected;
  if (remainEl) remainEl.textContent = total - collected;
  if (barEl) barEl.style.width = Math.round(collected / total * 100) + '%';
  
  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:1.8rem;color:var(--text-dim);">' +
      (kelas === 'semua' ? 'Belum ada siswa yang mengumpulkan tugas ini.' : 'Belum ada tugas dari kelas ' + kelas + '.') + 
      '</td></tr>';
    return;
  }
  
  var iconMap = {
    pdf: '📕', jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
    doc: '📝', docx: '📝', ppt: '📊', pptx: '📊', 
    xls: '📊', xlsx: '📊', txt: '📄', zip: '📦', rar: '📦'
  };
  
  tbody.innerHTML = data.map(function(d, i) {
    var ext = d.filename.split('.').pop().toLowerCase();
    var icon = iconMap[ext] || '📄';
    var badgeCls = d.status === 'Tepat Waktu' ? 'hadir' : 'izin';
    var realIdx = allData.findIndex(function(x) { 
      return x.nama === d.nama && x.filename === d.filename; 
    });
    
    var openFileFn = d.fileUrl 
      ? "window.open('" + d.fileUrl + "','_blank')" 
      : "alert('File tidak tersedia untuk dilihat.')";
    
    var downloadFn = d.fileUrl 
      ? "unduhFile('" + d.fileUrl + "','" + d.filename + "')" 
      : "alert('File tidak tersedia untuk diunduh.')";
    
    return '<tr>' +
      '<td style="color:var(--text-dim);font-weight:700">' + (i + 1) + '</td>' +
      '<td style="font-weight:700">' + escapeHtml(d.nama) + '</td>' +
      '<td>' + d.kelas + '</td>' +
      '<td style="font-family:\'JetBrains Mono\';font-size:0.72rem;color:var(--text-secondary)">' + d.waktu + ' WIB</td>' +
      '<td>' +
      '<div style="display:flex;align-items:center;gap:0.4rem;">' +
      '<span style="font-size:1rem;">' + icon + '</span>' +
      '<span style="font-size:0.75rem;color:var(--blue);text-decoration:underline;cursor:pointer;" ' +
      'onclick="' + openFileFn + '">' + escapeHtml(d.filename) + '</span>' +
      '</div>' +
      '</td>' +
      '<td style="font-size:0.72rem;color:var(--text-secondary)">' + d.filesize + '</td>' +
      '<td><span class="badge-' + badgeCls + '">' + d.status + '</span></td>' +
      '<td>' +
      '<div style="display:flex;gap:0.3rem;">' +
      '<button onclick="' + openFileFn + '" ' +
      'style="padding:0.24rem 0.5rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:4px;color:var(--blue);cursor:pointer;font-size:0.68rem;">👁 Lihat</button>' +
      '<button onclick="' + downloadFn + '" ' +
      'style="padding:0.24rem 0.5rem;background:var(--emerald-dim);border:1px solid rgba(63,185,80,0.25);border-radius:4px;color:var(--emerald);cursor:pointer;font-size:0.68rem;">⬇ Download</button>' +
      '<button onclick="hapusFileTugasDinamis(' + tugasId + ',' + realIdx + ')" ' +
      'style="padding:0.24rem 0.5rem;background:rgba(248,81,73,0.07);border:1px solid rgba(248,81,73,0.20);border-radius:4px;color:var(--red);cursor:pointer;font-size:0.68rem;">🗑 Hapus</button>' +
      '</div>' +
      '</td>' +
      '</tr>';
  }).join('');
}

// Render tugas untuk halaman SISWA
function renderTugasSiswaDinamis() {
  var container = document.getElementById('tugas-siswa-container');
  if (!container) return;
  
  container.innerHTML = '';
  
  if (!DB.tugasGuru || DB.tugasGuru.length === 0) {
    container.innerHTML = '<div class="assignment-card">' +
      '<div class="empty-state" style="padding:1.2rem">' +
      '<div class="empty-icon">📂</div>' +
      '<div class="empty-text">Belum ada tugas</div>' +
      '<div class="empty-sub">Guru belum mengupload tugas apapun.</div>' +
      '</div>' +
      '</div>';
    return;
  }
  
  // Urutkan tugas dari yang terbaru
  DB.tugasGuru.sort(function(a, b) {
    return new Date(b.tanggalUpload) - new Date(a.tanggalUpload);
  });
  
  // Tampilkan setiap tugas
  DB.tugasGuru.forEach(function(tugas) {
    var taskCard = document.createElement('div');
    taskCard.className = 'assignment-card';
    taskCard.id = 'tugas-siswa-dinamis-' + tugas.id;
    taskCard.innerHTML = buatHTMLTugasSiswaDinamis(tugas);
    container.appendChild(taskCard);
    
    // Render status upload siswa
    renderTugasSiswaStatusDinamis(tugas.id);
  });
}

function buatHTMLTugasSiswaDinamis(tugas) {
  var icon = '📋';
  if (tugas.fileType) {
    if (tugas.fileType === 'application/pdf') icon = '📕';
    else if (tugas.fileType.startsWith('image/')) icon = '🖼️';
    else if (tugas.fileType.includes('word')) icon = '📝';
  }
  
  var uploadId = 'upload-area-' + tugas.id;
  var doneId = 'upload-done-' + tugas.id;
  var fileInputId = 'file-input-' + tugas.id;
  var filenameId = 'upload-filename-' + tugas.id;
  
  return '<div class="assign-header">' +
    '<div class="assign-icon">' + icon + '</div>' +
    '<div>' +
    '<div class="assign-title">' + escapeHtml(tugas.judul) + '</div>' +
    '<div class="assign-sub">Oleh: Guru Kimia · Kimia Organik</div>' +
    '</div>' +
    '</div>' +
    '<div class="assign-deadline">⏰ Deadline: ' + (tugas.deadlineFormatted || 'Tidak ada batas waktu') + '</div>' +
    (tugas.desc ? '<div style="margin-bottom:0.8rem;padding:0.5rem 0.75rem;background:var(--surface-3);border-radius:6px;font-size:0.72rem;color:var(--text-secondary);">📝 ' + escapeHtml(tugas.desc) + '</div>' : '') +
    '<div style="margin-bottom:0.8rem;padding:0.5rem 0.75rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:6px;display:flex;align-items:center;gap:0.6rem;">' +
    '<span style="font-size:1rem;">📎</span>' +
    '<div style="flex:1;">' +
    '<div style="font-size:0.72rem;color:var(--text-secondary);">File Soal Tugas:</div>' +
    '<div style="font-size:0.78rem;font-weight:600;color:var(--blue);text-decoration:underline;cursor:pointer;" ' +
    'onclick="' + (tugas.fileUrl ? "window.open('" + tugas.fileUrl + "','_blank')" : "alert('File tidak tersedia')") + '">' +
    escapeHtml(tugas.filename || 'File tidak tersedia') + ' (' + (tugas.filesize || '0 KB') + ')' +
    '</div>' +
    '</div>' +
    '<button onclick="' + (tugas.fileUrl ? "window.open('" + tugas.fileUrl + "','_blank')" : "alert('File tidak tersedia')") + '" ' +
    'style="padding:0.24rem 0.6rem;background:transparent;border:1px solid var(--border);border-radius:4px;color:var(--text-secondary);cursor:pointer;font-size:0.68rem;">🔍 Lihat</button>' +
    '</div>' +
    '<div id="' + uploadId + '" style="display:block">' +
    '<input type="file" id="' + fileInputId + '" style="display:none" onchange="handleFileUploadDinamis(' + tugas.id + ', this)">' +
    '<div class="upload-area" onclick="document.getElementById(\'' + fileInputId + '\').click()">' +
    '<div class="upload-icon">📤</div>' +
    '<div class="upload-text">Klik untuk unggah file jawaban</div>' +
    '<div class="upload-types">Semua format diterima · Maks: 50MB</div>' +
    '</div>' +
    '</div>' +
    '<div id="' + doneId + '" style="display:none;padding:0.65rem 0.85rem;background:rgba(63,185,80,0.06);border:1px solid rgba(63,185,80,0.20);border-radius:8px;">' +
    '<div style="display:flex;align-items:center;gap:0.6rem;">' +
    '<span style="font-size:1.1rem;">✅</span>' +
    '<div style="flex:1;">' +
    '<div style="font-size:0.78rem;font-weight:700;color:var(--emerald);">Tugas Berhasil Dikumpulkan!</div>' +
    '<div style="font-size:0.68rem;color:var(--text-secondary);" id="' + filenameId + '"></div>' +
    '</div>' +
    '<button onclick="hapusUploadDinamis(' + tugas.id + ')" style="background:none;border:none;color:var(--text-dim);cursor:pointer;font-size:0.7rem;">Ganti file</button>' +
    '</div>' +
    '</div>';
}

function renderTugasSiswaStatusDinamis(tugasId) {
  var nama = currentUserData.nama || 'Siswa';
  var userSubmission = DB.tugasFiles[tugasId] ? 
    DB.tugasFiles[tugasId].find(function(t) { return t.nama === nama; }) : null;
  
  var uploadArea = document.getElementById('upload-area-' + tugasId);
  var uploadDone = document.getElementById('upload-done-' + tugasId);
  var filenameEl = document.getElementById('upload-filename-' + tugasId);
  
  if (!uploadArea || !uploadDone || !filenameEl) return;
  
  if (userSubmission) {
    uploadArea.style.display = 'none';
    uploadDone.style.display = 'block';
    
    var icon = '📄';
    if (userSubmission.fileType) {
      if (userSubmission.fileType === 'application/pdf') icon = '📕';
      else if (userSubmission.fileType.startsWith('image/')) icon = '🖼️';
      else if (userSubmission.fileType.includes('word')) icon = '📝';
    } else {
      var ext = userSubmission.filename.split('.').pop().toLowerCase();
      if (ext === 'pdf') icon = '📕';
      else if (['jpg', 'jpeg', 'png'].includes(ext)) icon = '🖼️';
      else if (['doc', 'docx'].includes(ext)) icon = '📝';
    }
    
    filenameEl.innerHTML = icon + ' ' + escapeHtml(userSubmission.filename) + 
      ' · ' + userSubmission.filesize + ' · Pukul ' + userSubmission.waktu + ' WIB';
    
    // Tambahkan tombol lihat file
    var parentDiv = uploadDone.querySelector('div');
    if (parentDiv && userSubmission.fileUrl && !document.getElementById('btn-lihat-file-' + tugasId)) {
      var lihatBtn = document.createElement('button');
      lihatBtn.id = 'btn-lihat-file-' + tugasId;
      lihatBtn.innerHTML = '🔍 Lihat File';
      lihatBtn.style.cssText = 'background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:4px;color:var(--blue);cursor:pointer;font-size:0.68rem;padding:0.24rem 0.5rem;margin-left:0.5rem;';
      lihatBtn.onclick = function() { 
        if (userSubmission.fileUrl) window.open(userSubmission.fileUrl, '_blank');
        else alert('File tidak tersedia');
      };
      var gantiBtn = parentDiv.querySelector('button:last-child');
      if (gantiBtn) parentDiv.insertBefore(lihatBtn, gantiBtn);
    }
  } else {
    uploadArea.style.display = 'block';
    uploadDone.style.display = 'none';
  }
}

// Fungsi untuk handle upload file siswa (dinamis)
// =====================
// =====================
// FUNGSI UPLOAD FILE SISWA (DIPERBAIKI)
// =====================

function handleFileUpload(tugasId, input) {
  if (!input.files || !input.files[0]) {
    alert('Pilih file terlebih dahulu!');
    return;
  }
  
  var file = input.files[0];
  
  // Validasi ukuran file (maks 50MB)
  if (file.size > 50 * 1024 * 1024) {
    alert('Ukuran file terlalu besar! Maksimal 50MB.');
    return;
  }
  
  var now = new Date();
  var waktu = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
  var tanggal = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
  
  // Buat object URL untuk file
  var fileUrl = URL.createObjectURL(file);
  
  var sizeKB = file.size / 1024;
  var sizeStr = sizeKB > 1024 ? (sizeKB / 1024).toFixed(1) + ' MB' : Math.round(sizeKB) + ' KB';
  
  var nama = currentUserData.nama || 'Siswa';
  var kelas = currentUserData.kelas || 'XII MIPA 2';
  var absen = currentUserData.absen || '-';
  
  // Pastikan array untuk tugasId ini ada
  if (!DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId] = [];
  }
  
  var entry = {
    nama: nama,
    kelas: kelas,
    absen: absen,
    waktu: waktu,
    tanggal: tanggal,
    filename: file.name,
    filesize: sizeStr,
    fileUrl: fileUrl,
    fileType: file.type,
    status: 'Tepat Waktu',
    timestamp: now.toISOString()
  };
  
  // Cek apakah siswa sudah pernah upload
  var idx = DB.tugasFiles[tugasId].findIndex(function(t) { 
    return t.nama === nama; 
  });
  
  if (idx >= 0) {
    // Hapus URL object yang lama
    if (DB.tugasFiles[tugasId][idx].fileUrl && DB.tugasFiles[tugasId][idx].fileUrl.startsWith('blob:')) {
      URL.revokeObjectURL(DB.tugasFiles[tugasId][idx].fileUrl);
    }
    DB.tugasFiles[tugasId][idx] = entry;
    console.log('Update file tugas untuk ' + nama + ' pada tugas ID ' + tugasId);
  } else {
    DB.tugasFiles[tugasId].push(entry);
    console.log('Tambah file baru untuk ' + nama + ' pada tugas ID ' + tugasId);
  }
  
  // Simpan ke localStorage
  simpanTugasKeLocalStorage(tugasId);
  
  // Update tampilan upload area siswa
  var uploadArea = document.getElementById('upload-area-' + tugasId);
  var uploadDone = document.getElementById('upload-done-' + tugasId);
  var filenameEl = document.getElementById('upload-filename-' + tugasId);
  
  if (uploadArea) uploadArea.style.display = 'none';
  if (uploadDone) uploadDone.style.display = 'block';
  
  var ext = file.name.split('.').pop().toLowerCase();
  var icon = '📄';
  if (ext === 'pdf') icon = '📕';
  else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = '🖼️';
  else if (['doc', 'docx'].includes(ext)) icon = '📝';
  
  if (filenameEl) {
    filenameEl.innerHTML = icon + ' ' + file.name + ' · ' + sizeStr + ' · ' + tanggal + ' ' + waktu + ' WIB';
  }
  
  // Tambahkan tombol lihat file
  var parentDiv = uploadDone ? uploadDone.querySelector('div') : null;
  if (parentDiv && !document.getElementById('btn-lihat-file-' + tugasId)) {
    var lihatBtn = document.createElement('button');
    lihatBtn.id = 'btn-lihat-file-' + tugasId;
    lihatBtn.innerHTML = '🔍 Lihat File';
    lihatBtn.style.cssText = 'background:var(--blue-dim);border:1px solid rgba(88,166,255,0.25);border-radius:4px;color:var(--blue);cursor:pointer;font-size:0.68rem;padding:0.24rem 0.5rem;margin-left:0.5rem;';
    lihatBtn.onclick = function() { 
      if (entry.fileUrl) window.open(entry.fileUrl, '_blank');
      else alert('File tidak tersedia');
    };
    var gantiBtn = parentDiv.querySelector('button:last-child');
    if (gantiBtn) parentDiv.insertBefore(lihatBtn, gantiBtn);
  }
  
  // Refresh tampilan guru jika sedang aktif
  if (currentRole === 'guru' && currentSection === 'tugas') {
    renderTugasGuruDariDB();
  }
  
  showToast('✅ File berhasil diupload: ' + file.name);
}

// Fungsi hapus upload
function hapusUploadDinamis(tugasId) {
  if (!confirm('Hapus file yang sudah diupload?')) return;
  
  var nama = currentUserData.nama || 'Siswa';
  var userSubmission = DB.tugasFiles[tugasId] ? 
    DB.tugasFiles[tugasId].find(function(t) { return t.nama === nama; }) : null;
  
  if (userSubmission && userSubmission.fileUrl) {
    URL.revokeObjectURL(userSubmission.fileUrl);
  }
  
  if (DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId] = DB.tugasFiles[tugasId].filter(function(t) { 
      return t.nama !== nama; 
    });
  }
  
  simpanTugasKeLocalStorageDinamis(tugasId);
  
  var uploadArea = document.getElementById('upload-area-' + tugasId);
  var uploadDone = document.getElementById('upload-done-' + tugasId);
  var fileInput = document.getElementById('file-input-' + tugasId);
  
  if (uploadArea) uploadArea.style.display = 'block';
  if (uploadDone) uploadDone.style.display = 'none';
  if (fileInput) fileInput.value = '';
  
  if (currentRole === 'guru') {
    renderTabelTugasGuruDinamis(tugasId);
  }
  
  showToast('🗑️ File tugas berhasil dihapus');
}

// Fungsi hapus file tugas oleh guru
function hapusFileTugasDinamis(tugasId, fileIdx) {
  if (!confirm('Hapus file tugas siswa ini?')) return;
  
  if (DB.tugasFiles[tugasId] && DB.tugasFiles[tugasId][fileIdx]) {
    if (DB.tugasFiles[tugasId][fileIdx].fileUrl) {
      URL.revokeObjectURL(DB.tugasFiles[tugasId][fileIdx].fileUrl);
    }
    DB.tugasFiles[tugasId].splice(fileIdx, 1);
    simpanTugasKeLocalStorageDinamis(tugasId);
    renderTabelTugasGuruDinamis(tugasId);
    showToast('🗑️ File tugas berhasil dihapus.');
  }
}

// Fungsi hapus tugas oleh guru
function hapusTugasDinamis(tugasId) {
  if (!confirm('Hapus tugas ini? Semua file jawaban siswa juga akan dihapus.')) return;
  
  if (DB.tugasFiles[tugasId]) {
    DB.tugasFiles[tugasId].forEach(function(t) {
      if (t.fileUrl) URL.revokeObjectURL(t.fileUrl);
    });
    delete DB.tugasFiles[tugasId];
    localStorage.removeItem('sichemor_tugas_' + tugasId);
  }
  
  var tugasIndex = DB.tugasGuru.findIndex(function(t) { return t.id === tugasId; });
  if (tugasIndex >= 0) {
    if (DB.tugasGuru[tugasIndex].fileUrl) {
      URL.revokeObjectURL(DB.tugasGuru[tugasIndex].fileUrl);
    }
    DB.tugasGuru.splice(tugasIndex, 1);
    simpanTugasGuruKeLocalStorage();
  }
  
  renderTugasGuruDariDB();
  renderTugasSiswaDariDB();
  showToast('🗑️ Tugas berhasil dihapus');
}

// Fungsi download semua tugas
function downloadSemuaTugasDinamis(tugasId) {
  var data = DB.tugasFiles[tugasId];
  if (!data || data.length === 0) { 
    alert('Belum ada file tugas yang terkumpul.'); 
    return; 
  }
  var hasRealFile = data.filter(function(d){ return d.fileUrl; });
  if (hasRealFile.length === 0) { 
    alert('File tidak dapat diunduh.'); 
    return; 
  }
  hasRealFile.forEach(function(d){ 
    setTimeout(function(){ unduhFile(d.fileUrl, d.filename); }, 300);
  });
  showToast('⬇️ Mengunduh ' + hasRealFile.length + ' file tugas...');
}

// Fungsi export tugas ke Excel
function exportTugasDinamis(tugasId) {
  var headers = ['No', 'Nama Siswa', 'Kelas', 'Waktu Kumpul', 'Nama File', 'Ukuran', 'Status'];
  var rows = (DB.tugasFiles[tugasId] || []).map(function(d, i) { 
    return [i + 1, d.nama, d.kelas, d.waktu, d.filename, d.filesize, d.status]; 
  });
  downloadExcel(rows, headers, 'pengumpulan_tugas_' + tugasId + '.xlsx');
}

// Simpan data tugas ke localStorage
function simpanTugasKeLocalStorageDinamis(tugasId) {
  try {
    localStorage.setItem('sichemor_tugas_' + tugasId, JSON.stringify(DB.tugasFiles[tugasId] || []));
  } catch (e) {
    console.error('Gagal menyimpan ke localStorage:', e);
  }
}

// Filter kelas untuk tugas guru
var filterTugasKelasAktifDinamis = {};

function filterTugasKelasDinamis(btn, tugasId, kelas) {
  filterTugasKelasAktifDinamis[tugasId] = kelas;
  var container = document.getElementById('tugas-guru-dinamis-' + tugasId);
  if (container) {
    container.querySelectorAll('.filter-btn').forEach(function(b) { 
      b.classList.remove('active'); 
    });
    if (btn) btn.classList.add('active');
  }
  renderTabelTugasGuruDinamis(tugasId);
}

// Escape HTML untuk keamanan
function escapeHtml(str) {
  if (!str) return '';
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// Muat data tugas dari localStorage
function muatTugasDariLocalStorageDinamis(tugasId) {
  try {
    var saved = localStorage.getItem('sichemor_tugas_' + tugasId);
    if (saved) {
      DB.tugasFiles[tugasId] = JSON.parse(saved);
      DB.tugasFiles[tugasId].forEach(function(t) {
        if (t.fileUrl && t.fileUrl.startsWith('blob:')) {
          t.fileUrl = null;
        }
      });
    }
  } catch (e) {
    console.error('Gagal memuat dari localStorage:', e);
  }
}

// Muat semua tugas
function muatSemuaTugasDariLocalStorageDinamis() {
  try {
    var savedGuru = localStorage.getItem('sichemor_tugas_guru');
    if (savedGuru) {
      DB.tugasGuru = JSON.parse(savedGuru);
    }
    
    if (DB.tugasGuru) {
      DB.tugasGuru.forEach(function(tugas) {
        muatTugasDariLocalStorageDinamis(tugas.id);
      });
    }
  } catch (e) {
    console.error('Gagal memuat semua tugas:', e);
  }
}
// Escape HTML untuk mencegah XSS
function escapeHtml(str) {
  if (!str) return '';
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
// =====================
// FUNGSI HAPUS KUIS
// =====================

function hapusKuis(kuisId) {
  if (!confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus kuis ini?\n\nSemua data nilai siswa untuk kuis ini juga akan dihapus permanen.\n\nTindakan ini tidak dapat dibatalkan!')) {
    return;
  }
  
  // Cari kuis yang akan dihapus
  var kuis = DB.kuis.find(function(k) { return k.id === kuisId; });
  if (!kuis) {
    alert('Kuis tidak ditemukan!');
    return;
  }
  
  var judulKuis = kuis.judul;
  
  // Hapus dari array kuis
  var kuisIndex = DB.kuis.findIndex(function(k) { return k.id === kuisId; });
  if (kuisIndex >= 0) {
    DB.kuis.splice(kuisIndex, 1);
  }
  
  // Hapus semua nilai kuis yang terkait
  var nilaiTerhapus = DB.nilaiKuis.filter(function(n) { return n.id === kuisId; }).length;
  DB.nilaiKuis = DB.nilaiKuis.filter(function(n) { return n.id !== kuisId; });
  
  // Simpan ke localStorage
  simpanKuisKeLocalStorage();
  
  // Refresh tampilan
  renderKuisGuru();
  renderKuisSiswa();
  
  showToast('🗑️ Kuis "' + judulKuis + '" berhasil dihapus!\n(' + nilaiTerhapus + ' data nilai siswa ikut terhapus)');
  
  // Jika sedang menampilkan hasil kuis, kembali ke daftar kuis
  var hasilKuis = document.getElementById('hasil-kuis');
  if (hasilKuis && hasilKuis.style.display === 'block') {
    switchKuisTabByName('daftar-kuis');
  }
}
// Fungsi konfirmasi hapus kuis dengan modal
function confirmHapusKuis(kuisId, judulKuis) {
  // Buat modal konfirmasi sederhana
  var confirmModal = document.createElement('div');
  confirmModal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:10000;display:flex;align-items:center;justify-content:center;';
  
  confirmModal.innerHTML = '<div style="background:var(--surface);border-radius:16px;max-width:400px;width:90%;padding:1.5rem;box-shadow:var(--shadow-lg);">' +
    '<div style="font-size:1.2rem;margin-bottom:1rem;">⚠️ Hapus Kuis</div>' +
    '<div style="margin-bottom:1rem;">Apakah Anda yakin ingin menghapus kuis <strong>' + escapeHtml(judulKuis) + '</strong>?</div>' +
    '<div style="margin-bottom:1.5rem;color:var(--red);font-size:0.85rem;">Semua data nilai siswa untuk kuis ini juga akan dihapus permanen!</div>' +
    '<div style="display:flex;gap:0.8rem;justify-content:flex-end;">' +
    '<button id="confirm-hapus-batal" style="padding:0.5rem 1rem;background:var(--surface-3);border:1px solid var(--border);border-radius:8px;cursor:pointer;">Batal</button>' +
    '<button id="confirm-hapus-ya" style="padding:0.5rem 1rem;background:var(--red);border:none;border-radius:8px;color:white;cursor:pointer;">Ya, Hapus</button>' +
    '</div></div>';
  
  document.body.appendChild(confirmModal);
  
  document.getElementById('confirm-hapus-batal').onclick = function() {
    confirmModal.remove();
  };
  
  document.getElementById('confirm-hapus-ya').onclick = function() {
    confirmModal.remove();
    hapusKuis(kuisId);
  };
}
// =====================
// RENDER TUGAS GURU DARI MySQL
// =====================
function renderTugasGuruDariDB() {
  var container = document.getElementById('tugas-guru-container');
  if (!container) return;
  container.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-dim);">⏳ Memuat tugas...</div>';

  fetch('api_tugas.php?action=get_tugas_guru')
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success || !res.data || res.data.length === 0) {
        container.innerHTML = '<div class="empty-state">' +
          '<div class="empty-icon">📋</div>' +
          '<div class="empty-text">Belum ada tugas yang diupload</div>' +
          '<div class="empty-sub">Klik "Upload Tugas" untuk menambah tugas pertama.</div>' +
          '</div>';
        return;
      }

      // Buat tab
      var tabNav = '<div class="tab-nav" style="margin-bottom:1rem;overflow-x:auto;">';
      res.data.forEach(function(tugas, idx) {
        var label = tugas.keterangan.length > 28 ? tugas.keterangan.substring(0,28)+'...' : tugas.keterangan;
        tabNav += '<button class="tab-btn ' + (idx===0?'active':'') + '" ' +
          'onclick="pilihTugasDB(' + tugas.id + ', this)">📋 ' + escapeHtml(label) + '</button>';
      });
      tabNav += '</div>';

      // Buat card tiap tugas
      var cards = '';
      res.data.forEach(function(tugas, idx) {
        var bagian = tugas.keterangan.split(' - ');
        var jenis  = bagian[0] || 'Tugas';
        var judul  = bagian.slice(1).join(' - ') || tugas.nama_file;
        var icon   = jenis.includes('Materi') ? '📚' : jenis.includes('Kuis') ? '📋' : '📝';

        cards += '<div id="tugas-db-' + tugas.id + '" style="display:' + (idx===0?'block':'none') + '">' +
          // Header info tugas
          '<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:0.8rem;flex-wrap:wrap;gap:0.5rem;">' +
          '<div>' +
          '<div style="font-size:0.92rem;font-weight:700;">' + icon + ' ' + escapeHtml(judul) + '</div>' +
          '<div style="font-size:0.7rem;color:var(--text-secondary);margin-top:0.15rem;">Diupload: ' + tugas.tanggal + '</div>' +
          '</div>' +
          '<div style="display:flex;gap:0.4rem;">' +
          '<button onclick="window.open(\'' + tugas.path_file + '\',\'_blank\')" ' +
          'style="padding:0.3rem 0.7rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.3);border-radius:6px;color:var(--blue);cursor:pointer;font-size:0.72rem;">🔍 Lihat File</button>' +
          '<button onclick="hapusTugasDB(' + tugas.id + ')" ' +
          'style="padding:0.3rem 0.7rem;background:rgba(220,38,38,0.07);border:1px solid rgba(220,38,38,0.22);border-radius:6px;color:var(--red);cursor:pointer;font-size:0.72rem;">🗑 Hapus</button>' +
          '</div>' +
          '</div>' +
          // Deadline & Deskripsi
          (tugas.deadline ? '<div style="margin-bottom:0.6rem;padding:0.45rem 0.7rem;background:rgba(30,58,138,0.07);border:1px solid rgba(30,58,138,0.15);border-radius:6px;font-size:0.72rem;color:var(--accent);">⏰ Deadline: ' + escapeHtml(tugas.deadline) + '</div>' : '') +
          (tugas.deskripsi ? '<div style="margin-bottom:0.8rem;padding:0.5rem 0.75rem;background:var(--surface-2);border-left:3px solid var(--accent);border-radius:4px;font-size:0.78rem;color:var(--text-secondary);line-height:1.5;">📋 ' + escapeHtml(tugas.deskripsi) + '</div>' : '') +
          // File info box
          '<div style="margin-bottom:1rem;padding:0.6rem 0.8rem;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;display:flex;align-items:center;gap:0.6rem;">' +
          '<span style="font-size:1.1rem;">📎</span>' +
          '<span style="font-size:0.78rem;color:var(--accent);font-weight:600;">' + escapeHtml(tugas.nama_file) + '</span>' +
          '</div>' +
          // Tabel jawaban siswa
          '<div style="font-size:0.8rem;font-weight:700;margin-bottom:0.5rem;color:var(--text-secondary);">📥 Pengerjaan Siswa:</div>' +
          '<div id="jawaban-container-' + tugas.id + '">' +
          '<div style="text-align:center;padding:1.5rem;color:var(--text-dim);font-size:0.8rem;">⏳ Memuat...</div>' +
          '</div>' +
          '</div>';
      });

      container.innerHTML = tabNav + cards;

      // Load jawaban untuk tugas pertama
      if (res.data[0]) {
        loadJawabanSiswa(res.data[0].id);
      }
    })
    .catch(function(e) {
      container.innerHTML = '<div style="color:var(--red);padding:1rem;">❌ Gagal memuat data. Pastikan api_tugas.php ada di folder yang sama.</div>';
    });
}

// Pilih tab tugas (mode guru DB)
function pilihTugasDB(id, btn) {
  document.querySelectorAll('#tugas-guru-container .tab-btn').forEach(function(b) { b.classList.remove('active'); });
  btn.classList.add('active');
  document.querySelectorAll('[id^="tugas-db-"]').forEach(function(el) { el.style.display = 'none'; });
  var el = document.getElementById('tugas-db-' + id);
  if (el) { el.style.display = 'block'; loadJawabanSiswa(id); }
}

// Load jawaban siswa untuk satu tugas guru
function loadJawabanSiswa(idTugasGuru) {
  var container = document.getElementById('jawaban-container-' + idTugasGuru);
  if (!container) return;
  container.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-dim);font-size:0.8rem;">⏳ Memuat jawaban siswa...</div>';

  fetch('api_tugas.php?action=get_jawaban_siswa&id_tugas_guru=' + idTugasGuru)
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success || res.data.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:1.5rem;color:var(--text-dim);font-size:0.8rem;">Belum ada siswa yang mengumpulkan.</div>';
        return;
      }

      var html = '<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;">' +
        '<table class="result-table">' +
        '<thead><tr><th>No</th><th>Nama Siswa</th><th>Kelas</th><th>Waktu Kumpul</th><th>File Tugas</th><th>Aksi</th></tr></thead>' +
        '<tbody>';

      res.data.forEach(function(row, idx) {
        var bagian = row.keterangan.split(' - ');
        var nama   = bagian[0] || '-';
        var kelas  = bagian[1] || '-';

        html += '<tr>' +
          '<td>' + (idx+1) + '</td>' +
          '<td style="font-weight:600;">' + escapeHtml(nama) + '</td>' +
          '<td>' + escapeHtml(kelas) + '</td>' +
          '<td style="font-size:0.72rem;">' + row.tanggal + '</td>' +
          '<td><a href="' + row.path_file + '" target="_blank" style="color:var(--blue);font-size:0.78rem;">📄 ' + escapeHtml(row.nama_file) + '</a></td>' +
          '<td>' +
          '<button onclick="hapusJawabanSiswa(' + row.id + ',' + idTugasGuru + ')" ' +
          'style="padding:0.2rem 0.5rem;background:rgba(220,38,38,0.07);border:1px solid rgba(220,38,38,0.22);border-radius:4px;color:var(--red);cursor:pointer;font-size:0.7rem;">🗑</button>' +
          '</td>' +
          '</tr>';
      });

      html += '</tbody></table></div>';
      container.innerHTML = html;
    })
    .catch(function() {
      container.innerHTML = '<div style="color:var(--red);padding:1rem;font-size:0.8rem;">❌ Gagal memuat jawaban.</div>';
    });
}

// Hapus tugas guru dari DB
function hapusTugasDB(id) {
  if (!confirm('Hapus tugas ini? Semua jawaban siswa juga akan dihapus!')) return;
  var form = new FormData();
  form.append('action', 'hapus_tugas_guru');
  form.append('id', id);
  fetch('api_tugas.php', { method: 'POST', body: form })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        showToast('🗑️ Tugas berhasil dihapus');
        renderTugasGuruDariDB();
      } else {
        alert('Gagal hapus: ' + res.message);
      }
    });
}

// Hapus satu jawaban siswa
function hapusJawabanSiswa(id, idTugasGuru) {
  if (!confirm('Hapus pengerjaan siswa ini?')) return;
  var form = new FormData();
  form.append('action', 'hapus_jawaban_siswa');
  form.append('id', id);
  fetch('api_tugas.php', { method: 'POST', body: form })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        showToast('🗑️ Jawaban siswa dihapus');
        loadJawabanSiswa(idTugasGuru);
      } else {
        alert('Gagal hapus: ' + res.message);
      }
    });
}

// =====================
// RENDER TUGAS SISWA DARI MySQL
// =====================
function renderTugasSiswaDariDB() {
  var container = document.getElementById('tugas-siswa-container');
  if (!container) return;
  container.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--text-dim);">⏳ Memuat tugas...</div>';

  fetch('api_tugas.php?action=get_tugas_guru')
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success || !res.data || res.data.length === 0) {
        container.innerHTML = '<div class="empty-state">' +
          '<div class="empty-icon">📋</div>' +
          '<div class="empty-text">Belum ada tugas dari guru</div>' +
          '</div>';
        return;
      }

      var html = '';
      res.data.forEach(function(tugas) {
        var bagian = tugas.keterangan.split(' - ');
        var jenis  = bagian[0] || 'Tugas';
        var judul  = bagian.slice(1).join(' - ') || tugas.nama_file;
        var icon   = jenis.includes('Materi') ? '📚' : jenis.includes('Kuis') ? '📋' : '📝';

        html += '<div class="assignment-card" style="margin-bottom:1rem;">' +
          '<div class="assign-header">' +
          '<span class="assign-icon">' + icon + '</span>' +
          '<div>' +
          '<div class="assign-title">' + escapeHtml(judul) + '</div>' +
          '<div class="assign-sub">' + escapeHtml(jenis) + ' · Diupload: ' + tugas.tanggal + '</div>' +
          '</div>' +
          '</div>' +
          // Deadline & Deskripsi untuk siswa
          (tugas.deadline ? '<div style="margin-bottom:0.6rem;padding:0.45rem 0.7rem;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.18);border-radius:6px;font-size:0.72rem;color:var(--red);">⏰ Deadline: ' + escapeHtml(tugas.deadline) + '</div>' : '') +
          (tugas.deskripsi ? '<div style="margin-bottom:0.8rem;padding:0.5rem 0.75rem;background:var(--surface-2);border-left:3px solid var(--accent);border-radius:4px;font-size:0.78rem;color:var(--text-secondary);line-height:1.5;">📋 ' + escapeHtml(tugas.deskripsi) + '</div>' : '') +
          // Tombol lihat file guru
          '<div style="margin-bottom:0.8rem;">' +
          '<a href="' + tugas.path_file + '" target="_blank" ' +
          'style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.4rem 0.8rem;background:var(--blue-dim);border:1px solid rgba(88,166,255,0.3);border-radius:6px;color:var(--blue);text-decoration:none;font-size:0.78rem;font-weight:600;">' +
          '📎 Lihat / Download Soal</a>' +
          '</div>' +
          // Form upload jawaban
          '<div id="upload-area-' + tugas.id + '">' +
          '<div style="font-size:0.75rem;font-weight:700;color:var(--text-secondary);margin-bottom:0.4rem;">📤 Upload Jawabanmu:</div>' +
          '<form id="form-siswa-' + tugas.id + '" enctype="multipart/form-data">' +
          '<input type="hidden" name="action" value="upload_siswa">' +
          '<input type="hidden" name="id_tugas_guru" value="' + tugas.id + '">' +
          '<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:0.5rem;">' +
          '<input type="text" name="nama_siswa" placeholder="Nama Lengkap" required ' +
          'style="padding:0.5rem 0.7rem;border:1.5px solid var(--border);border-radius:6px;font-size:0.8rem;font-family:inherit;outline:none;"' +
          ' value="' + escapeHtml((currentUserData && currentUserData.nama) ? currentUserData.nama : '') + '">' +
          '<input type="text" name="kelas" placeholder="Kelas (XII MIPA 2)" required ' +
          'style="padding:0.5rem 0.7rem;border:1.5px solid var(--border);border-radius:6px;font-size:0.8rem;font-family:inherit;outline:none;"' +
          ' value="' + escapeHtml((currentUserData && currentUserData.kelas) ? currentUserData.kelas : '') + '">' +
          '</div>' +
          '<input type="text" name="keterangan" placeholder="Keterangan (mis: LKPD 1)" ' +
          'style="width:100%;padding:0.5rem 0.7rem;border:1.5px solid var(--border);border-radius:6px;font-size:0.8rem;font-family:inherit;outline:none;margin-bottom:0.5rem;">' +
          '<div style="display:flex;gap:0.5rem;align-items:center;">' +
          '<input type="file" name="file" required id="file-input-' + tugas.id + '" ' +
          'style="flex:1;padding:0.4rem;border:1.5px dashed var(--border);border-radius:6px;font-size:0.78rem;cursor:pointer;">' +
          '<button type="button" onclick="uploadJawabanSiswa(' + tugas.id + ')" ' +
          'style="padding:0.5rem 1rem;background:var(--emerald);border:none;border-radius:6px;color:white;cursor:pointer;font-size:0.8rem;font-weight:700;white-space:nowrap;">📤 Kumpulkan</button>' +
          '</div>' +
          '</form>' +
          '</div>' +
          '<div id="upload-done-' + tugas.id + '" style="display:none;padding:0.7rem;background:var(--emerald-dim);border:1px solid rgba(5,150,105,0.25);border-radius:6px;font-size:0.8rem;color:var(--emerald);font-weight:600;">✅ Tugas berhasil dikumpulkan!</div>' +
          '</div>';
      });

      container.innerHTML = html;
    })
    .catch(function() {
      container.innerHTML = '<div style="color:var(--red);padding:1rem;">❌ Gagal memuat tugas.</div>';
    });
}

// Fungsi upload jawaban siswa dari form inline
function uploadJawabanSiswa(tugasId) {
  var form = document.getElementById('form-siswa-' + tugasId);
  if (!form) return;

  var fileInput = document.getElementById('file-input-' + tugasId);
  if (!fileInput || !fileInput.files[0]) {
    alert('Pilih file terlebih dahulu!'); return;
  }

  var btn = form.querySelector('button[type=button]');
  if (btn) { btn.textContent = '⏳ Mengupload...'; btn.disabled = true; }

  var formData = new FormData(form);
  formData.set('action', 'upload_siswa');

  fetch('api_tugas.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        document.getElementById('upload-area-' + tugasId).style.display = 'none';
        document.getElementById('upload-done-' + tugasId).style.display = 'block';
        showToast('✅ Tugas berhasil dikumpulkan!');
      } else {
        alert('Gagal upload: ' + res.message);
        if (btn) { btn.textContent = '📤 Kumpulkan'; btn.disabled = false; }
      }
    })
    .catch(function() {
      alert('Terjadi kesalahan jaringan.');
      if (btn) { btn.textContent = '📤 Kumpulkan'; btn.disabled = false; }
    });
}
</script>
</body>
</html>




