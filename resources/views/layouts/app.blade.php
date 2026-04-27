<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — KUVVET Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ═══════════════════════════════════════════════════════
           KUVVET PORTAL — DAYBREAK THEME
           All CSS lives here. Pages use semantic classes only.
        ═══════════════════════════════════════════════════════ */

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:         #C2531B;
            --accent-light:   #E87A45;
            --accent-bg:      #FEF2EC;
            --accent-border:  #F5D5C0;

            --bg-page:        #FBF8F5;
            --bg-card:        #FFFFFF;
            --bg-input:       #F7F3EF;
            --bg-muted:       #F5F0EB;
            --bg-hover:       #FEF2EC;

            --border:         #F0EAE2;
            --border-strong:  #E8DDD5;

            --text-primary:   #2D1F14;
            --text-secondary: #6B5347;
            --text-muted:     #A89080;
            --text-accent:    #C2531B;

            --green:          #22C55E;   --green-bg:    #F0FDF4;   --green-border:  #BBF7D0;
            --yellow:         #F59E0B;   --yellow-bg:   #FFFBEB;   --yellow-border: #FDE68A;
            --red:            #EF4444;   --red-bg:      #FEF2F2;   --red-border:    #FECACA;
            --blue:           #3B82F6;   --blue-bg:     #EFF6FF;   --blue-border:   #BFDBFE;
            --purple:         #8B5CF6;   --purple-bg:   #F5F3FF;   --purple-border: #DDD6FE;
            --pink:           #EC4899;   --pink-bg:     #FDF2F8;   --pink-border:   #FBCFE8;
        }

        body { background: var(--bg-page); color: var(--text-primary); font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; line-height: 1.5; }

        /* ── Scrollbar ───────────────────────────────────── */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 4px; }

        /* ═══════════════════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════════════════ */
        .portal-layout { display: flex; height: 100vh; overflow: hidden; }
        .main-area     { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .page-content  { flex: 1; overflow-y: auto; padding: 24px 28px; animation: pageIn .3s ease; }

        @keyframes pageIn    { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeSlide { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shimmer   { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes pulse     { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }

        /* ═══════════════════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════════════════ */
        #sidebar {
            width: 220px; flex-shrink: 0; background: var(--bg-card);
            border-right: 1px solid var(--border); height: 100vh;
            display: flex; flex-direction: column; overflow: hidden;
            transition: width .3s cubic-bezier(.4,0,.2,1);
        }
        #sidebar.collapsed { width: 64px; }
        #sidebar.collapsed .nav-label,
        #sidebar.collapsed .nav-group-label,
        #sidebar.collapsed .brand-text,
        #sidebar.collapsed .nav-badge  { display: none !important; }
        #sidebar.collapsed .nav-item   { justify-content: center; padding: 9px; }
        #sidebar.collapsed .nav-arrow  { display: none !important; }

        /* Brand */
        .sidebar-brand  { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-bottom: 1px solid var(--border); min-height: 56px; flex-shrink: 0; }
        .sidebar-logo   { width: 34px; height: 34px; border-radius: 9px; background: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sidebar-logo i { color: #fff; font-size: 15px; }
        .brand-name     { font-size: 13px; font-weight: 700; color: var(--text-primary); letter-spacing: .8px; }
        .brand-sub      { font-size: 9px; color: var(--text-muted); letter-spacing: .4px; margin-top: 1px; }

        /* Accent line */
        .accent-line {
            height: 2px; flex-shrink: 0;
            background: linear-gradient(90deg, transparent, var(--accent), var(--accent-light), var(--accent), transparent);
            background-size: 200% 100%; animation: shimmer 3s linear infinite;
        }

        /* Nav */
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 8px; scrollbar-width: thin; }

        .nav-group-label {
            font-size: 9px; font-weight: 600; color: var(--text-muted);
            letter-spacing: 1.2px; text-transform: uppercase;
            padding: 10px 8px 4px; display: block;
        }
        .nav-item {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px; border-radius: 8px; cursor: pointer;
            transition: all .15s; color: var(--text-secondary); font-size: 13px;
            font-weight: 500; text-decoration: none; position: relative;
            margin-bottom: 1px;
        }
        .nav-item:hover  { background: var(--bg-hover); color: var(--accent); }
        .nav-item.active { background: var(--accent-bg); color: var(--accent); font-weight: 600; }
        .nav-item i.fa-fw { width: 16px; text-align: center; font-size: 13px; flex-shrink: 0; }

        /* Sub-menu */
        .nav-sub { padding-left: 24px; overflow: hidden; max-height: 0; transition: max-height .3s ease; }
        .nav-sub.open { max-height: 400px; }
        .nav-sub .nav-item { font-size: 12px; padding: 6px 10px; border-radius: 6px; }
        .nav-arrow { font-size: 9px; margin-left: auto; transition: transform .2s; color: var(--text-muted); }
        .nav-arrow.open { transform: rotate(90deg); color: var(--accent); }

        /* Nav badge */
        .nav-badge {
            margin-left: auto; font-size: 9px; font-weight: 700;
            background: var(--accent-bg); color: var(--accent);
            border-radius: 10px; padding: 1px 7px;
        }

        /* Sidebar footer / user */
        .sidebar-footer { padding: 10px 8px; border-top: 1px solid var(--border); flex-shrink: 0; }
        .sidebar-user   { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: 8px; background: var(--bg-muted); cursor: pointer; }
        .sidebar-collapse { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-radius: 8px; cursor: pointer; color: var(--text-muted); font-size: 12px; font-weight: 500; transition: all .15s; }
        .sidebar-collapse:hover { background: var(--bg-hover); color: var(--accent); }

        /* ═══════════════════════════════════════════════════
           TOPBAR
        ═══════════════════════════════════════════════════ */
        #topbar { height: 56px; background: var(--bg-card); border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; flex-shrink: 0; }
        .topbar-title  { font-size: 15px; font-weight: 700; color: var(--text-primary); line-height: 1.2; }
        .topbar-bread  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
        .topbar-right  { display: flex; align-items: center; gap: 10px; }

        .topbar-clock  { font-size: 11px; color: var(--text-secondary); background: var(--bg-muted); border: 1px solid var(--border); border-radius: 7px; padding: 5px 10px; white-space: nowrap; font-weight: 500; }

        .topbar-icon-btn {
            width: 36px; height: 36px; background: var(--bg-muted); border: 1px solid var(--border);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; position: relative; transition: border-color .15s;
        }
        .topbar-icon-btn:hover { border-color: var(--accent-border); }
        .topbar-icon-btn i { font-size: 13px; color: var(--text-secondary); }
        .notif-dot { position: absolute; top: 7px; right: 7px; width: 7px; height: 7px; background: var(--accent); border-radius: 50%; border: 1.5px solid var(--bg-card); }

        .topbar-user-btn {
            display: flex; align-items: center; gap: 8px; background: var(--bg-muted);
            border: 1px solid var(--border); border-radius: 8px; padding: 5px 10px;
            cursor: pointer; transition: border-color .15s;
        }
        .topbar-user-btn:hover { border-color: var(--accent-border); }
        .topbar-uname { font-size: 12px; font-weight: 600; color: var(--text-primary); max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .topbar-urole { font-size: 10px; color: var(--accent); }

        /* ═══════════════════════════════════════════════════
           CARDS
        ═══════════════════════════════════════════════════ */
        .card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; padding: 20px;
        }
        .card-sm { padding: 14px 16px; }
        .card-flush { padding: 0; overflow: hidden; }

        /* Stat card */
        .stat-card {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; padding: 18px;
            transition: transform .2s, border-color .2s;
        }
        .stat-card:hover { transform: translateY(-2px); border-color: var(--accent-border); }
        .stat-label { font-size: 10px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .7px; }
        .stat-num   { font-size: 28px; font-weight: 700; color: var(--text-primary); line-height: 1.1; margin: 6px 0 2px; }
        .stat-sub   { font-size: 11px; color: var(--text-muted); }
        .stat-trend-up   { font-size: 11px; color: var(--green);  font-weight: 600; margin-top: 4px; }
        .stat-trend-down { font-size: 11px; color: var(--red);    font-weight: 600; margin-top: 4px; }
        .stat-trend-flat { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        .stat-icon {
            width: 34px; height: 34px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .stat-icon i { font-size: 14px; }
        .stat-icon-green  { background: var(--green-bg);  } .stat-icon-green  i { color: var(--green);  }
        .stat-icon-yellow { background: var(--yellow-bg); } .stat-icon-yellow i { color: var(--yellow); }
        .stat-icon-red    { background: var(--red-bg);    } .stat-icon-red    i { color: var(--red);    }
        .stat-icon-blue   { background: var(--blue-bg);   } .stat-icon-blue   i { color: var(--blue);   }
        .stat-icon-purple { background: var(--purple-bg); } .stat-icon-purple i { color: var(--purple); }
        .stat-icon-accent { background: var(--accent-bg); } .stat-icon-accent i { color: var(--accent); }
        .stat-icon-pink   { background: var(--pink-bg);   } .stat-icon-pink   i { color: var(--pink);   }

        /* Card section title */
        .card-title { font-size: 13px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
        .card-title i { font-size: 12px; color: var(--accent); }

        /* ═══════════════════════════════════════════════════
           GRIDS
        ═══════════════════════════════════════════════════ */
        .stats-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
        .stats-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
        .stats-grid-6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-bottom: 22px; }
        .grid-2-1     { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .grid-1-340   { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
        .grid-sidebar-main { display: grid; grid-template-columns: 280px 1fr; gap: 20px; align-items: start; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .col-span-2 { grid-column: span 2; }
        .col-span-3 { grid-column: span 3; }

        /* ═══════════════════════════════════════════════════
           TOOLBAR / FILTER BAR
        ═══════════════════════════════════════════════════ */
        .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .toolbar-search { position: relative; flex: 1; min-width: 220px; }
        .toolbar-search i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px; pointer-events: none; }
        .toolbar-search input { padding-left: 32px; }
        .ml-auto { margin-left: auto; }

        /* ═══════════════════════════════════════════════════
           FORM ELEMENTS
        ═══════════════════════════════════════════════════ */
        .form-label { display: block; font-size: 10px; font-weight: 600; color: var(--text-secondary); letter-spacing: .6px; text-transform: uppercase; margin-bottom: 5px; }
        .form-label .req { color: var(--red); }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%; background: var(--bg-input); border: 1px solid var(--border-strong);
            border-radius: 8px; padding: 9px 12px; color: var(--text-primary); font-size: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif; outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-bg); }
        .form-input::placeholder { color: var(--text-muted); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2 4l4 4 4-4' stroke='%23A89080' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; }
        .form-textarea { resize: vertical; line-height: 1.6; }
        .form-error { font-size: 11px; color: var(--red); margin-top: 4px; }
        .form-group { margin-bottom: 0; }

        /* Section heading inside a card form */
        .form-section { font-size: 11px; font-weight: 700; color: var(--accent); letter-spacing: .6px; text-transform: uppercase; margin-bottom: 16px; display: flex; align-items: center; gap: 7px; }

        /* ═══════════════════════════════════════════════════
           BUTTONS
        ═══════════════════════════════════════════════════ */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all .15s; font-family: 'Plus Jakarta Sans', sans-serif; white-space: nowrap; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-xs { padding: 4px 10px; font-size: 11px; border-radius: 6px; }

        .btn-primary  { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-light); }

        .btn-secondary { background: var(--bg-muted); color: var(--text-secondary); border: 1px solid var(--border-strong); }
        .btn-secondary:hover { border-color: var(--accent-border); color: var(--accent); }

        .btn-success  { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-border); }
        .btn-success:hover { background: #dcfce7; }

        .btn-danger   { background: var(--red-bg); color: var(--red); border: 1px solid var(--red-border); }
        .btn-danger:hover { background: #fee2e2; }

        .btn-warning  { background: var(--yellow-bg); color: var(--yellow); border: 1px solid var(--yellow-border); }
        .btn-blue     { background: var(--blue-bg); color: var(--blue); border: 1px solid var(--blue-border); }
        .btn-purple   { background: var(--purple-bg); color: var(--purple); border: 1px solid var(--purple-border); }
        .btn-pink     { background: var(--pink-bg); color: var(--pink); border: 1px solid var(--pink-border); }

        /* Icon-only action buttons */
        .action-btn {
            width: 30px; height: 30px; border-radius: 7px; display: inline-flex;
            align-items: center; justify-content: center; border: 1px solid var(--border);
            background: var(--bg-muted); cursor: pointer; transition: all .15s;
            text-decoration: none; font-family: inherit;
        }
        .action-btn i { font-size: 11px; color: var(--text-secondary); }
        .action-btn:hover { border-color: var(--accent-border); background: var(--accent-bg); }
        .action-btn:hover i { color: var(--accent); }
        .action-btn.danger:hover { border-color: var(--red-border); background: var(--red-bg); }
        .action-btn.danger:hover i { color: var(--red); }
        .action-btn.success:hover { border-color: var(--green-border); background: var(--green-bg); }
        .action-btn.success:hover i { color: var(--green); }

        /* ═══════════════════════════════════════════════════
           BADGES
        ═══════════════════════════════════════════════════ */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid transparent; }
        .badge-green  { background: var(--green-bg);  color: var(--green);  border-color: var(--green-border);  }
        .badge-yellow { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
        .badge-red    { background: var(--red-bg);    color: var(--red);    border-color: var(--red-border);    }
        .badge-blue   { background: var(--blue-bg);   color: var(--blue);   border-color: var(--blue-border);   }
        .badge-purple { background: var(--purple-bg); color: var(--purple); border-color: var(--purple-border); }
        .badge-accent { background: var(--accent-bg); color: var(--accent); border-color: var(--accent-border); }
        .badge-muted  { background: var(--bg-muted);  color: var(--text-muted); border-color: var(--border); }
        .badge-pink   { background: var(--pink-bg);   color: var(--pink);   border-color: var(--pink-border);   }

        /* Emergency badge */
        .badge-emergency { background: var(--red-bg); color: var(--red); border-color: var(--red-border); font-size: 9px; padding: 1px 6px; border-radius: 4px; }

        /* ═══════════════════════════════════════════════════
           TABLES
        ═══════════════════════════════════════════════════ */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead tr { background: var(--bg-muted); border-bottom: 1px solid var(--border); }
        .data-table th { padding: 11px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--text-muted); letter-spacing: .8px; text-transform: uppercase; }
        .data-table th.center { text-align: center; }
        .data-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
        .data-table tbody tr:hover { background: var(--bg-muted); }
        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table td { padding: 12px 16px; font-size: 13px; color: var(--text-secondary); vertical-align: middle; }
        .data-table td.center { text-align: center; }
        .data-table td.muted { color: var(--text-muted); font-size: 12px; }

        /* Employee / avatar cell */
        .td-employee { display: flex; align-items: center; gap: 10px; }
        .td-employee img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--border-strong); flex-shrink: 0; }
        .td-employee .name { font-size: 13px; font-weight: 600; color: var(--text-primary); text-decoration: none; }
        .td-employee .name:hover { color: var(--accent); }
        .td-employee .id { font-size: 11px; color: var(--accent); }

        /* ═══════════════════════════════════════════════════
           PAGINATION
        ═══════════════════════════════════════════════════ */
        .pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--border); }
        .pagination-info { font-size: 12px; color: var(--text-muted); }
        .pagination-btns { display: flex; gap: 4px; }
        .page-btn { padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid var(--border); background: var(--bg-muted); color: var(--text-secondary); text-decoration: none; cursor: pointer; }
        .page-btn:hover { border-color: var(--accent-border); color: var(--accent); }
        .page-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }
        .page-btn.disabled { color: var(--text-muted); cursor: default; pointer-events: none; }

        /* ═══════════════════════════════════════════════════
           TAB NAVIGATION
        ═══════════════════════════════════════════════════ */
        .tab-nav { display: flex; gap: 2px; border-bottom: 1px solid var(--border); margin-bottom: 20px; }
        .tab-btn {
            padding: 9px 16px; border: none; background: none; cursor: pointer;
            font-size: 12px; font-weight: 500; font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-muted); border-bottom: 2px solid transparent; margin-bottom: -1px;
            display: flex; align-items: center; gap: 6px; transition: color .15s;
        }
        .tab-btn i { font-size: 11px; }
        .tab-btn:hover  { color: var(--text-secondary); }
        .tab-btn.active { color: var(--accent); border-color: var(--accent); font-weight: 600; }

        /* ═══════════════════════════════════════════════════
           QUICK LINK BUTTONS (row of nav pills)
        ═══════════════════════════════════════════════════ */
        .quick-links { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .quick-link  { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 500; text-decoration: none; border: 1px solid; transition: opacity .15s; }
        .quick-link:hover { opacity: .8; }
        .ql-accent  { background: var(--accent-bg);  color: var(--accent);  border-color: var(--accent-border);  }
        .ql-blue    { background: var(--blue-bg);    color: var(--blue);    border-color: var(--blue-border);    }
        .ql-green   { background: var(--green-bg);   color: var(--green);   border-color: var(--green-border);   }
        .ql-purple  { background: var(--purple-bg);  color: var(--purple);  border-color: var(--purple-border);  }
        .ql-yellow  { background: var(--yellow-bg);  color: var(--yellow);  border-color: var(--yellow-border);  }
        .ql-red     { background: var(--red-bg);     color: var(--red);     border-color: var(--red-border);     }
        .ql-pink    { background: var(--pink-bg);    color: var(--pink);    border-color: var(--pink-border);    }

        /* ═══════════════════════════════════════════════════
           INFO / DETAIL ROWS
        ═══════════════════════════════════════════════════ */
        .info-row { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border); }
        .info-row:last-child { border-bottom: none; }
        .info-row i { font-size: 11px; color: var(--accent); margin-top: 2px; width: 14px; text-align: center; flex-shrink: 0; }
        .info-row-label { font-size: 10px; color: var(--text-muted); }
        .info-row-value { font-size: 12px; color: var(--text-secondary); word-break: break-all; }

        /* Detail block (small label + value) */
        .detail-block { background: var(--bg-muted); border-radius: 8px; padding: 12px; }
        .detail-block-label { font-size: 10px; color: var(--text-muted); margin-bottom: 3px; letter-spacing: .3px; }
        .detail-block-value { font-size: 13px; color: var(--text-primary); font-weight: 500; }

        /* Reason / note block with left border */
        .note-block { background: var(--bg-muted); border-radius: 8px; padding: 14px; border-left: 3px solid var(--accent); }
        .note-block-label { font-size: 10px; color: var(--text-muted); letter-spacing: .4px; margin-bottom: 5px; }
        .note-block-text  { font-size: 13px; color: var(--text-secondary); line-height: 1.7; }

        /* ═══════════════════════════════════════════════════
           MODAL
        ═══════════════════════════════════════════════════ */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 200; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: var(--bg-card); border: 1px solid var(--border-strong); border-radius: 14px; padding: 28px; width: 500px; max-width: 95vw; position: relative; }
        .modal-title { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 9px; }
        .modal-title i { color: var(--accent); }
        .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

        /* ═══════════════════════════════════════════════════
           DROPDOWN
        ═══════════════════════════════════════════════════ */
        .dropdown-menu { background: var(--bg-card); border: 1px solid var(--border-strong); border-radius: 10px; min-width: 200px; padding: 4px 0; box-shadow: 0 8px 24px rgba(0,0,0,.1); }
        .dropdown-item { display: flex; align-items: center; gap: 9px; padding: 9px 16px; font-size: 13px; color: var(--text-secondary); cursor: pointer; transition: all .15s; text-decoration: none; }
        .dropdown-item:hover { background: var(--bg-hover); color: var(--accent); }
        .dropdown-item i { font-size: 12px; color: var(--accent); width: 14px; text-align: center; }
        .dropdown-item.danger { color: var(--red); }
        .dropdown-item.danger i { color: var(--red); }
        .dropdown-item.danger:hover { background: var(--red-bg); }
        .dropdown-divider { border: none; border-top: 1px solid var(--border); margin: 4px 0; }

        /* Notification panel */
        .notification-panel { animation: fadeSlide .2s ease; }

        /* ═══════════════════════════════════════════════════
           FLASH MESSAGES
        ═══════════════════════════════════════════════════ */
        .flash { display: flex; align-items: center; gap: 10px; padding: 11px 16px; border-radius: 9px; margin-bottom: 16px; font-size: 13px; font-weight: 500; border: 1px solid; }
        .flash-success { background: var(--green-bg);  color: var(--green);  border-color: var(--green-border);  }
        .flash-error   { background: var(--red-bg);    color: var(--red);    border-color: var(--red-border);    }
        .flash-warning { background: var(--yellow-bg); color: var(--yellow); border-color: var(--yellow-border); }
        .flash-info    { background: var(--blue-bg);   color: var(--blue);   border-color: var(--blue-border);   }

        /* ═══════════════════════════════════════════════════
           VALIDATION ERROR BOX
        ═══════════════════════════════════════════════════ */
        .error-box { background: var(--red-bg); border: 1px solid var(--red-border); border-radius: 9px; padding: 14px 16px; margin-bottom: 20px; }
        .error-box-title { font-size: 13px; color: var(--red); font-weight: 600; margin-bottom: 6px; }
        .error-box li { font-size: 12px; color: var(--red); margin-top: 3px; }

        /* ═══════════════════════════════════════════════════
           EMPTY STATES
        ═══════════════════════════════════════════════════ */
        .empty-state { padding: 48px; text-align: center; color: var(--text-muted); }
        .empty-state i { font-size: 32px; display: block; margin-bottom: 10px; color: var(--border-strong); }
        .empty-state a { color: var(--accent); text-decoration: none; font-weight: 500; }

        /* ═══════════════════════════════════════════════════
           AVATAR
        ═══════════════════════════════════════════════════ */
        .avatar    { border-radius: 50%; object-fit: cover; border: 2px solid var(--border-strong); flex-shrink: 0; }
        .avatar-sm { width: 28px; height: 28px; }
        .avatar-md { width: 34px; height: 34px; }
        .avatar-lg { width: 54px; height: 54px; }
        .avatar-xl { width: 80px; height: 80px; }

        /* Avatar initials circle */
        .avatar-circle { border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .avatar-circle-md { width: 34px; height: 34px; font-size: 12px; background: var(--accent-bg); color: var(--accent); }

        /* ═══════════════════════════════════════════════════
           MISC UTILITIES
        ═══════════════════════════════════════════════════ */
        .divider    { border: none; border-top: 1px solid var(--border); margin: 16px 0; }
        .text-accent { color: var(--accent) !important; }
        .text-muted  { color: var(--text-muted) !important; }
        .text-green  { color: var(--green); }
        .text-red    { color: var(--red); }
        .text-blue   { color: var(--blue); }
        .text-yellow { color: var(--yellow); }
        .fw-bold     { font-weight: 700; }
        .section-label { font-size: 10px; font-weight: 700; color: var(--text-muted); letter-spacing: .8px; text-transform: uppercase; margin-bottom: 14px; }
        .progress-track { height: 5px; background: var(--border); border-radius: 3px; overflow: hidden; }
        .progress-fill  { height: 100%; border-radius: 3px; background: var(--accent); }
        .live-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--green); animation: pulse 1.5s infinite; }

        /* Status badge helpers — used in PHP match() */
        .status-active     { background: var(--green-bg);  color: var(--green);  border: 1px solid var(--green-border);  }
        .status-inactive   { background: var(--red-bg);    color: var(--red);    border: 1px solid var(--red-border);    }
        .status-pending    { background: var(--yellow-bg); color: var(--yellow); border: 1px solid var(--yellow-border); }
        .status-approved   { background: var(--green-bg);  color: var(--green);  border: 1px solid var(--green-border);  }
        .status-rejected   { background: var(--red-bg);    color: var(--red);    border: 1px solid var(--red-border);    }
        .status-cancelled  { background: var(--bg-muted);  color: var(--text-muted); border: 1px solid var(--border); }
        .status-resigned   { background: var(--yellow-bg); color: var(--yellow); border: 1px solid var(--yellow-border); }
        .status-terminated { background: var(--red-bg);    color: var(--red);    border: 1px solid var(--red-border);    }
        .status-on-leave   { background: var(--blue-bg);   color: var(--blue);   border: 1px solid var(--blue-border);   }

        /* Type badge helpers */
        .type-permanent    { background: var(--green-bg);  color: var(--green);  border: 1px solid var(--green-border);  }
        .type-contract     { background: var(--blue-bg);   color: var(--blue);   border: 1px solid var(--blue-border);   }
        .type-probationary { background: var(--yellow-bg); color: var(--yellow); border: 1px solid var(--yellow-border); }
        .type-part_time    { background: var(--purple-bg); color: var(--purple); border: 1px solid var(--purple-border); }
        .type-internship   { background: var(--accent-bg); color: var(--accent); border: 1px solid var(--accent-border); }
        .type-daily_wages  { background: var(--bg-muted);  color: var(--text-muted); border: 1px solid var(--border); }
    </style>
    @stack('styles')
</head>
<body>
<div class="portal-layout">

<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════════ -->
<aside id="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-logo"><i class="fa-solid fa-grid-2"></i>K</div>
        <div class="brand-text">
            <div class="brand-name">KUVVET</div>
            <div class="brand-sub">HR PORTAL · ADMIN</div>
        </div>
    </div>

    <div class="accent-line"></div>
<nav class="sidebar-nav">

        <span class="nav-group-label">Main</span>
        @can('dashboard.view')
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie fa-fw"></i>
            <span class="nav-label">Dashboard</span>
        </a>
        @endcan

        @canany(['employees.view','departments.manage','recruitment.view'])
        <span class="nav-group-label">Workforce</span>
        @endcanany

        @can('employees.view')
        <a href="{{ route('employees.index') }}" class="nav-item {{ request()->is('admin/employees*') ? 'active' : '' }}">
            <i class="fa-solid fa-users fa-fw"></i>
            <span class="nav-label">Employees</span>
        </a>
        @endcan

        @can('departments.manage')
        <a href="{{ route('departments.index') }}" class="nav-item {{ request()->is('admin/departments*') ? 'active' : '' }}">
            <i class="fa-solid fa-sitemap fa-fw"></i>
            <span class="nav-label">Departments</span>
        </a>
        @endcan

        @can('recruitment.view')
        <a href="{{ route('recruitment.index') }}" class="nav-item {{ request()->is('admin/recruitment*') ? 'active' : '' }}">
            <i class="fa-solid fa-user-plus fa-fw"></i>
            <span class="nav-label">Recruitment</span>
        </a>
        @endcan

        @canany(['attendance.view','leaves.view'])
        <span class="nav-group-label">Time & Leave</span>
        @endcanany

        @can('attendance.view')
        <div>
            <div class="nav-item {{ request()->is('admin/attendance*') ? 'active' : '' }}" onclick="toggleSub('sub-attendance')">
                <i class="fa-solid fa-clock fa-fw"></i>
                <span class="nav-label">Attendance</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/attendance*') ? 'open' : '' }}" id="arrow-sub-attendance"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/attendance*') ? 'open' : '' }}" id="sub-attendance">
                <a href="{{ route('attendance.index') }}"  class="nav-item {{ request()->routeIs('attendance.index')  ? 'active' : '' }}"><i class="fa-solid fa-calendar-day fa-fw"></i><span class="nav-label">Daily Board</span></a>
                @can('attendance.report')
                <a href="{{ route('attendance.report') }}" class="nav-item {{ request()->routeIs('attendance.report') ? 'active' : '' }}"><i class="fa-solid fa-chart-bar fa-fw"></i><span class="nav-label">Monthly Report</span></a>
                @endcan
                @can('attendance.manage')
                <a href="{{ route('attendance.shifts') }}" class="nav-item {{ request()->routeIs('attendance.shifts') ? 'active' : '' }}"><i class="fa-solid fa-rotate fa-fw"></i><span class="nav-label">Shift Management</span></a>
                @endcan
            </div>
        </div>
        @endcan

        @can('locations.view')
        <a href="{{ route('locations.index') }}" class="nav-item {{ request()->is('admin/locations*') ? 'active' : '' }}">
            <i class="fa-solid fa-map-location-dot fa-fw"></i>
            <span class="nav-label">Locations</span>
        </a>
        @endcan

        @can('leaves.view')
        <a href="{{ route('leaves.index') }}" class="nav-item {{ request()->is('admin/leaves*') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-check fa-fw"></i>
            <span class="nav-label">Leave Management</span>
        </a>
        @endcan

        @can('payroll.view')
        <span class="nav-group-label">Finance</span>
        <div>
            <div class="nav-item {{ request()->is('admin/payroll*') ? 'active' : '' }}" onclick="toggleSub('sub-payroll')">
                <i class="fa-solid fa-money-check-dollar fa-fw"></i>
                <span class="nav-label">Payroll</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/payroll*') ? 'open' : '' }}" id="arrow-sub-payroll"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/payroll*') ? 'open' : '' }}" id="sub-payroll">
                <a href="{{ route('payroll.index') }}"             class="nav-item {{ request()->routeIs('payroll.index')             ? 'active' : '' }}"><i class="fa-solid fa-chart-pie fa-fw"></i><span class="nav-label">Dashboard</span></a>
                @can('payroll.process')
                <a href="{{ route('payroll.salary-structures') }}" class="nav-item {{ request()->routeIs('payroll.salary-structures') ? 'active' : '' }}"><i class="fa-solid fa-sliders fa-fw"></i><span class="nav-label">Salary Structures</span></a>
                <a href="{{ route('payroll.tax-calculator') }}"    class="nav-item {{ request()->routeIs('payroll.tax-calculator')    ? 'active' : '' }}"><i class="fa-solid fa-calculator fa-fw"></i><span class="nav-label">Tax Calculator</span></a>
                @endcan
                <a href="{{ route('payroll.report') }}"            class="nav-item {{ request()->routeIs('payroll.report')            ? 'active' : '' }}"><i class="fa-solid fa-file-chart-line fa-fw"></i><span class="nav-label">Payroll Report</span></a>
            </div>
        </div>
        @endcan

        @canany(['performance.view','training.view'])
        <span class="nav-group-label">Growth</span>
        @endcanany

        @can('performance.view')
        <div>
            <div class="nav-item {{ request()->is('admin/performance*') ? 'active' : '' }}" onclick="toggleSub('sub-performance')">
                <i class="fa-solid fa-chart-line fa-fw"></i>
                <span class="nav-label">Performance</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/performance*') ? 'open' : '' }}" id="arrow-sub-performance"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/performance*') ? 'open' : '' }}" id="sub-performance">
                <a href="{{ route('performance.index') }}"  class="nav-item {{ request()->routeIs('performance.index')  ? 'active' : '' }}"><i class="fa-solid fa-chart-pie fa-fw"></i><span class="nav-label">Dashboard</span></a>
                @can('performance.manage')
                <a href="{{ route('performance.cycles') }}" class="nav-item {{ request()->routeIs('performance.cycles') ? 'active' : '' }}"><i class="fa-solid fa-rotate fa-fw"></i><span class="nav-label">Cycles</span></a>
                <a href="{{ route('performance.kpis') }}"   class="nav-item {{ request()->routeIs('performance.kpis')   ? 'active' : '' }}"><i class="fa-solid fa-bullseye fa-fw"></i><span class="nav-label">KPI Library</span></a>
                @endcan
                <a href="{{ route('performance.report') }}" class="nav-item {{ request()->routeIs('performance.report') ? 'active' : '' }}"><i class="fa-solid fa-file-chart-line fa-fw"></i><span class="nav-label">Report</span></a>
            </div>
        </div>
        @endcan

        @can('training.view')
        <div>
            <div class="nav-item {{ request()->is('admin/training*') ? 'active' : '' }}" onclick="toggleSub('sub-training')">
                <i class="fa-solid fa-graduation-cap fa-fw"></i>
                <span class="nav-label">Training</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/training*') ? 'open' : '' }}" id="arrow-sub-training"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/training*') ? 'open' : '' }}" id="sub-training">
                <a href="{{ route('training.index') }}"        class="nav-item {{ request()->routeIs('training.index')        ? 'active' : '' }}"><i class="fa-solid fa-chart-pie fa-fw"></i><span class="nav-label">Dashboard</span></a>
                @can('training.manage')
                <a href="{{ route('training.programs') }}"     class="nav-item {{ request()->routeIs('training.programs')     ? 'active' : '' }}"><i class="fa-solid fa-book-open fa-fw"></i><span class="nav-label">Programs</span></a>
                <a href="{{ route('training.sessions') }}"     class="nav-item {{ request()->routeIs('training.sessions')     ? 'active' : '' }}"><i class="fa-solid fa-calendar fa-fw"></i><span class="nav-label">Sessions</span></a>
                @endcan
                <a href="{{ route('training.certifications') }}" class="nav-item {{ request()->routeIs('training.certifications') ? 'active' : '' }}"><i class="fa-solid fa-certificate fa-fw"></i><span class="nav-label">Certifications</span></a>
                <a href="{{ route('training.skill-matrix') }}" class="nav-item {{ request()->routeIs('training.skill-matrix') ? 'active' : '' }}"><i class="fa-solid fa-table-cells fa-fw"></i><span class="nav-label">Skill Matrix</span></a>
                <a href="{{ route('training.report') }}"       class="nav-item {{ request()->routeIs('training.report')       ? 'active' : '' }}"><i class="fa-solid fa-chart-bar fa-fw"></i><span class="nav-label">Report</span></a>
            </div>
        </div>
        @endcan

        @canany(['assets.view','documents.view'])
        <span class="nav-group-label">Operations</span>
        @endcanany

        @can('assets.view')
        <div>
            <div class="nav-item {{ request()->is('admin/assets*') ? 'active' : '' }}" onclick="toggleSub('sub-assets')">
                <i class="fa-solid fa-truck-moving fa-fw"></i>
                <span class="nav-label">Assets & Equipment</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/assets*') ? 'open' : '' }}" id="arrow-sub-assets"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/assets*') ? 'open' : '' }}" id="sub-assets">
                <a href="{{ route('assets.index') }}"       class="nav-item {{ request()->routeIs('assets.index')       ? 'active' : '' }}"><i class="fa-solid fa-chart-pie fa-fw"></i><span class="nav-label">Dashboard</span></a>
                <a href="{{ route('assets.list') }}"        class="nav-item {{ request()->routeIs('assets.list')        ? 'active' : '' }}"><i class="fa-solid fa-list fa-fw"></i><span class="nav-label">Asset Register</span></a>
                @can('assets.manage')
                <a href="{{ route('assets.maintenance') }}" class="nav-item {{ request()->routeIs('assets.maintenance') ? 'active' : '' }}"><i class="fa-solid fa-wrench fa-fw"></i><span class="nav-label">Maintenance</span></a>
                <a href="{{ route('assets.categories') }}"  class="nav-item {{ request()->routeIs('assets.categories')  ? 'active' : '' }}"><i class="fa-solid fa-tags fa-fw"></i><span class="nav-label">Categories</span></a>
                @endcan
                <a href="{{ route('assets.report') }}"      class="nav-item {{ request()->routeIs('assets.report')      ? 'active' : '' }}"><i class="fa-solid fa-chart-bar fa-fw"></i><span class="nav-label">Report</span></a>
            </div>
        </div>
        @endcan

        @can('documents.view')
        <div>
            <div class="nav-item {{ request()->is('admin/documents*') ? 'active' : '' }}" onclick="toggleSub('sub-docs')">
                <i class="fa-solid fa-folder-open fa-fw"></i>
                <span class="nav-label">Documents</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/documents*') ? 'open' : '' }}" id="arrow-sub-docs"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/documents*') ? 'open' : '' }}" id="sub-docs">
                <a href="{{ route('documents.index') }}"      class="nav-item {{ request()->routeIs('documents.index')      ? 'active' : '' }}"><i class="fa-solid fa-chart-pie fa-fw"></i><span class="nav-label">Dashboard</span></a>
                <a href="{{ route('documents.list') }}"       class="nav-item {{ request()->routeIs('documents.list')       ? 'active' : '' }}"><i class="fa-solid fa-list fa-fw"></i><span class="nav-label">All Documents</span></a>
                @can('documents.manage')
                <a href="{{ route('documents.categories') }}" class="nav-item {{ request()->routeIs('documents.categories') ? 'active' : '' }}"><i class="fa-solid fa-tags fa-fw"></i><span class="nav-label">Categories</span></a>
                @endcan
            </div>
        </div>
        @endcan

        @canany(['reports.hr','reports.attendance','reports.payroll','reports.all','leaves.report'])
        <div>
            <div class="nav-item {{ request()->is('admin/reports*') ? 'active' : '' }}" onclick="toggleSub('sub-reports')">
                <i class="fa-solid fa-file-chart-column fa-fw"></i>
                <span class="nav-label">Reports</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/reports*') ? 'open' : '' }}" id="arrow-sub-reports"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/reports*') ? 'open' : '' }}" id="sub-reports">
                <a href="{{ route('reports.index') }}"      class="nav-item {{ request()->routeIs('reports.index')      ? 'active' : '' }}"><i class="fa-solid fa-chart-pie fa-fw"></i><span class="nav-label">Reports Hub</span></a>
                @canany(['reports.hr','reports.all'])
                <a href="{{ route('reports.workforce') }}"  class="nav-item {{ request()->routeIs('reports.workforce')  ? 'active' : '' }}"><i class="fa-solid fa-users fa-fw"></i><span class="nav-label">Workforce</span></a>
                @endcanany
                @canany(['reports.attendance','reports.all'])
                <a href="{{ route('reports.attendance') }}" class="nav-item {{ request()->routeIs('reports.attendance') ? 'active' : '' }}"><i class="fa-solid fa-clock fa-fw"></i><span class="nav-label">Attendance</span></a>
                @endcanany
                @canany(['leaves.report','reports.all'])
                <a href="{{ route('reports.leave') }}"      class="nav-item {{ request()->routeIs('reports.leave')      ? 'active' : '' }}"><i class="fa-solid fa-calendar-check fa-fw"></i><span class="nav-label">Leave</span></a>
                @endcanany
                @canany(['reports.payroll','reports.all'])
                <a href="{{ route('payroll.report') }}"     class="nav-item {{ request()->routeIs('payroll.report')     ? 'active' : '' }}"><i class="fa-solid fa-money-check-dollar fa-fw"></i><span class="nav-label">Payroll</span></a>
                @endcanany
                @canany(['reports.hr','reports.all'])
                <a href="{{ route('performance.report') }}" class="nav-item {{ request()->routeIs('performance.report') ? 'active' : '' }}"><i class="fa-solid fa-chart-line fa-fw"></i><span class="nav-label">Performance</span></a>
                <a href="{{ route('training.report') }}"    class="nav-item {{ request()->routeIs('training.report')    ? 'active' : '' }}"><i class="fa-solid fa-graduation-cap fa-fw"></i><span class="nav-label">Training</span></a>
                @endcanany
            </div>
        </div>
        @endcanany

        @canany(['settings.view','users.manage','roles.manage','audit_logs.view'])
        <span class="nav-group-label">System</span>
        <div>
            <div class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}" onclick="toggleSub('sub-settings')">
                <i class="fa-solid fa-gear fa-fw"></i>
                <span class="nav-label">Settings</span>
                <i class="fa-solid fa-chevron-right nav-arrow {{ request()->is('admin/settings*') ? 'open' : '' }}" id="arrow-sub-settings"></i>
            </div>
            <div class="nav-sub {{ request()->is('admin/settings*') ? 'open' : '' }}" id="sub-settings">
                @can('settings.view')
                <a href="{{ route('settings.index') }}"     class="nav-item {{ request()->routeIs('settings.index')     ? 'active' : '' }}"><i class="fa-solid fa-sliders fa-fw"></i><span class="nav-label">All Settings</span></a>
                <a href="{{ route('settings.company') }}"   class="nav-item {{ request()->routeIs('settings.company')   ? 'active' : '' }}"><i class="fa-solid fa-building fa-fw"></i><span class="nav-label">Company Profile</span></a>
                <a href="{{ route('settings.hr') }}"        class="nav-item {{ request()->routeIs('settings.hr')        ? 'active' : '' }}"><i class="fa-solid fa-file-lines fa-fw"></i><span class="nav-label">HR Policies</span></a>
                <a href="{{ route('settings.payroll') }}"   class="nav-item {{ request()->routeIs('settings.payroll')   ? 'active' : '' }}"><i class="fa-solid fa-money-check-dollar fa-fw"></i><span class="nav-label">Payroll Settings</span></a>
                @endcan
                @can('users.manage')
                <a href="{{ route('settings.users') }}"     class="nav-item {{ request()->routeIs('settings.users')     ? 'active' : '' }}"><i class="fa-solid fa-users-gear fa-fw"></i><span class="nav-label">Users & Access</span></a>
                @endcan
                @can('roles.manage')
                <a href="{{ route('settings.roles') }}"     class="nav-item {{ request()->routeIs('settings.roles')     ? 'active' : '' }}"><i class="fa-solid fa-shield-halved fa-fw"></i><span class="nav-label">Roles & Permissions</span></a>
                @endcan
                @can('audit_logs.view')
                <a href="{{ route('settings.audit-log') }}" class="nav-item {{ request()->routeIs('settings.audit-log') ? 'active' : '' }}"><i class="fa-solid fa-shield-check fa-fw"></i><span class="nav-label">Audit Log</span></a>
                @endcan
                <a href="{{ route('settings.profile') }}"   class="nav-item {{ request()->routeIs('settings.profile')   ? 'active' : '' }}"><i class="fa-solid fa-user-circle fa-fw"></i><span class="nav-label">My Profile</span></a>
            </div>
        </div>
        @endcanany

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-collapse" onclick="document.getElementById('sidebar').classList.toggle('collapsed');this.querySelector('i').classList.toggle('fa-bars');this.querySelector('i').classList.toggle('fa-bars-staggered');">
            <i class="fa-solid fa-bars fa-fw"></i>
            <span class="nav-label">Collapse</span>
        </div>
    </div>

</aside>

<!-- ═══════════════════════════════════════════════════════════
     MAIN AREA
════════════════════════════════════════════════════════════ -->
<div class="main-area">

    <!-- Topbar -->
    <header id="topbar">
        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-bread">@yield('breadcrumb', 'KUVVET Portal')</div>
        </div>
        <div class="topbar-right">

            <div id="pk-time" class="topbar-clock"></div>

            <!-- Notifications -->
            <div style="position:relative;" x-data="{ open: false }">
                <button class="topbar-icon-btn" @click="open = !open">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="notification-panel dropdown-menu" style="position:absolute;right:0;top:calc(100% + 8px);width:300px;z-index:100;padding:0;overflow:hidden;">
                    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:12px;font-weight:600;color:var(--text-primary);">Notifications</span>
                        <span style="font-size:11px;color:var(--accent);cursor:pointer;">Mark all read</span>
                    </div>
                    <div class="empty-state" style="padding:28px 16px;">
                        <i class="fa-solid fa-bell-slash" style="font-size:22px;"></i>
                        <div style="font-size:12px;">No new notifications</div>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div style="position:relative;" x-data="{ open: false }">
                <button class="topbar-user-btn" @click="open = !open">
                    <img src="{{ auth()->user()->avatar_url }}" class="avatar avatar-sm" alt="{{ auth()->user()->name }}">
                    <div>
                        <div class="topbar-uname">{{ Str::words(auth()->user()->name, 1, '') }}</div>
                        <div class="topbar-urole">{{ auth()->user()->display_role }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:9px;color:var(--text-muted);"></i>
                </button>

                <div x-show="open" @click.outside="open = false" class="dropdown-menu" style="position:absolute;right:0;top:calc(100% + 8px);z-index:100;">
                    <div style="padding:10px 16px 8px;border-bottom:1px solid var(--border);">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ auth()->user()->name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:1px;">{{ auth()->user()->email }}</div>
                        <div style="margin-top:6px;"><span class="badge badge-accent">{{ auth()->user()->display_role }}</span></div>
                    </div>
                    <a href="{{ route('settings.profile') }}" class="dropdown-item"><i class="fa-solid fa-user-circle fa-fw"></i> My Profile</a>
                    <a href="{{ route('settings.profile') }}" class="dropdown-item"><i class="fa-solid fa-key fa-fw"></i> Change Password</a>
                    <hr class="dropdown-divider">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item danger" style="width:100%;background:none;border:none;cursor:pointer;text-align:left;">
                            <i class="fa-solid fa-right-from-bracket fa-fw"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </header>

    <div class="accent-line"></div>

    <!-- Page Content -->
    <main class="page-content">

        @if(session('success'))
            <div class="flash flash-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash flash-error"><i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="flash flash-info"><i class="fa-solid fa-circle-info"></i> {{ session('info') }}</div>
        @endif
        @if(session('warning'))
            <div class="flash flash-warning"><i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}</div>
        @endif

        @yield('content')
    </main>
</div>

</div><!-- /.portal-layout -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
<script>
    function updateClock() {
        const el = document.getElementById('pk-time');
        if (!el) return;
        el.textContent = new Intl.DateTimeFormat('en-PK', {
            timeZone: 'Asia/Karachi', hour: '2-digit', minute: '2-digit',
            second: '2-digit', day: '2-digit', month: 'short', hour12: true
        }).format(new Date()) + ' PKT';
    }
    updateClock(); setInterval(updateClock, 1000);

    function toggleSub(id) {
        const sub   = document.getElementById(id);
        const arrow = document.getElementById('arrow-' + id);
        const isOpen = sub.classList.contains('open');
        sub.classList.toggle('open', !isOpen);
        if (arrow) arrow.classList.toggle('open', !isOpen);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.nav-sub').forEach(sub => {
            if (sub.querySelector('.nav-item.active')) {
                sub.classList.add('open');
                const arrow = document.getElementById('arrow-' + sub.id);
                if (arrow) arrow.classList.add('open');
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>