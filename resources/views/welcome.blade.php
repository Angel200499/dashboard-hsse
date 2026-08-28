<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lahendong PEKA – Dashboard monitoring pengamatan keselamatan kerja PEKA.">
    <title>Lahendong PEKA – HSSE PGE Area Lahendong</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:   #0057B8;
            --blue-d: #003F8A;
            --green:  #9DBF2A;
            --green-d:#89A924;
            --red:    #C8102E;
            --ink:    #222222;
            --body:   #3D4E60;
            --muted:  #8699AD;
            --rule:   #E8EDF3;
            --bg:     #F7F9FC;
            --white:  #FFFFFF;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--white);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        /* ACCENT BAR */
        .accent-bar { height: 3px; display: flex; }
        .accent-bar .ab-blue  { flex: 3; background: var(--blue); }
        .accent-bar .ab-green { flex: 1; background: var(--green); }

        /* NAV */
        nav {
            position: sticky; top: 0; z-index: 100;
            background: var(--white);
            border-bottom: 1px solid var(--rule);
            padding: 0 64px; height: 66px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-logo { height: 44px; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-label { font-size: 12px; color: var(--muted); font-weight: 500; letter-spacing: 0.02em; }
        .nav-divider { width: 1px; height: 16px; background: var(--rule); }
        .nav-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 20px; border-radius: 6px;
            background: var(--blue); color: var(--white);
            font-size: 13px; font-weight: 600; text-decoration: none;
            transition: background .18s, transform .15s;
        }
        .nav-btn:hover { background: var(--blue-d); transform: translateY(-1px); }

        /* HERO */
        .hero { display: grid; grid-template-columns: 1.15fr 0.85fr; min-height: calc(100vh - 69px); }
        .hero-left {
            padding: 88px 72px 88px 64px;
            display: flex; flex-direction: column; justify-content: center;
            border-right: 1px solid var(--rule);
        }
        .hero-tag {
            display: inline-flex; align-items: center; gap: 10px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.14em;
            text-transform: uppercase; color: var(--muted); margin-bottom: 36px;
        }
        .hero-tag::before {
            content: ''; display: block;
            width: 28px; height: 2px; background: var(--red); border-radius: 1px;
        }
        .hero h1 {
            font-size: clamp(48px, 6.5vw, 84px); font-weight: 900;
            line-height: 0.97; letter-spacing: -0.04em; color: var(--ink); margin-bottom: 32px;
        }
        .hl-blue  { color: var(--blue); }
        .hl-red { color: var(--red); position: relative; display: inline-block; }
        .hl-red::after {
            content: ''; position: absolute; left: 0; bottom: -3px;
            width: 100%; height: 4px; background: var(--red); border-radius: 2px;
        }
        .hero-desc { font-size: 15px; line-height: 1.8; color: var(--body); max-width: 480px; margin-bottom: 48px; }
        .hero-actions { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 13px 26px; background: var(--green); color: var(--ink);
            border-radius: 7px; font-size: 14px; font-weight: 700; text-decoration: none;
            transition: background .18s, box-shadow .18s, transform .15s;
        }
        .btn-primary:hover { background: var(--green-d); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(157,191,42,.25); }

        .hero-right {
            background-color: var(--ink);
            background-image: url('{{ asset('assets/images/hero-bg.jpg.JPG') }}');
            background-size: cover;
            background-position: center;
            position: relative; overflow: hidden;
        }

        /* STATS (INFO CARDS) */
        .stats { background: var(--bg); border-top: 1px solid var(--rule); border-bottom: 1px solid var(--rule); }
        .stats-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(3, 1fr); padding: 0 64px; }
        .stat { padding: 40px 36px; border-right: 1px solid var(--rule); text-align: center; }
        .stat:last-child  { border-right: none; }
        .stat-num { font-size: 24px; font-weight: 800; letter-spacing: -0.02em; line-height: 1; margin-bottom: 8px; color: var(--ink); }
        .stat-label { font-size: 14px; color: var(--muted); line-height: 1.5; font-weight: 500; }
        
        .stat:nth-child(1) .stat-num { color: var(--blue); }
        .stat:nth-child(2) .stat-num { color: var(--red); }
        .stat:nth-child(3) .stat-num { color: var(--green-d); }

        /* NILAI SECTION */
        .pillars { padding: 96px 64px; max-width: 1200px; margin: 0 auto; }
        .sec-label { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--green-d); display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
        .sec-label::before { content: ''; width: 22px; height: 2px; background: var(--green); border-radius: 1px; }
        .sec-h2 { font-size: clamp(28px, 3.5vw, 42px); font-weight: 800; letter-spacing: -0.025em; line-height: 1.15; color: var(--ink); margin-bottom: 24px; }
        .sec-desc { font-size: 16px; line-height: 1.8; color: var(--body); max-width: 600px; margin-bottom: 64px; }
        
        .cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; }
        .card { 
            padding: 48px 40px; 
            background: var(--white);
            border: 1px solid var(--rule);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform .25s, box-shadow .25s; 
            display: flex; flex-direction: column;
        }
        .card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
        .card-blue  { border-top: 4px solid var(--blue); }
        .card-green { border-top: 4px solid var(--green); }
        .card-ink   { border-top: 4px solid var(--red); }
        .card-num  { font-size: 13px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 24px; }
        .card-blue .card-num { color: var(--blue); }
        .card-green .card-num { color: var(--green-d); }
        .card-ink .card-num { color: var(--red); }
        .card-word { font-size: 26px; font-weight: 800; letter-spacing: -0.025em; line-height: 1.3; color: var(--ink); margin-bottom: 12px; }
        .card-subtitle { font-size: 15px; font-weight: 600; color: var(--ink); margin-bottom: 16px; }
        .card-text { font-size: 15px; line-height: 1.7; color: var(--body); flex-grow: 1; }

        /* CTA */
        .cta { 
            background: linear-gradient(135deg, var(--ink) 0%, #111 100%);
            padding: 80px 64px; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; gap: 32px; 
            position: relative; overflow: hidden;
        }
        .cta::before {
            content: ''; position: absolute; top: -50%; left: -10%;
            width: 50%; height: 200%;
            background: radial-gradient(ellipse at center, rgba(0,87,184,0.15) 0%, rgba(0,0,0,0) 70%);
            transform: rotate(-45deg);
        }
        .cta h2 { position: relative; font-size: clamp(28px, 4vw, 48px); font-weight: 900; letter-spacing: -0.02em; color: var(--white); line-height: 1.2; }

        /* FOOTER */
        footer { background: var(--white); border-top: 1px solid var(--rule); padding: 24px 64px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        footer p { font-size: 13px; color: var(--muted); display: flex; align-items: center; gap: 5px; }
        footer .heart { color: var(--red); }
        .foot-links { display: flex; gap: 24px; }
        .foot-links a { font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none; transition: color .18s; }
        .foot-links a:hover { color: var(--ink); }
        .colorbar { height: 4px; display: flex; }
        .colorbar span:nth-child(1) { flex: 3; background: var(--blue); }
        .colorbar span:nth-child(2) { flex: 1; background: var(--red); }
        .colorbar span:nth-child(3) { flex: 1; background: var(--green); }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            nav, footer, .cta, .pillars { padding-left: 32px; padding-right: 32px; }
            .stats-grid { padding: 0 32px; }
            .hero { grid-template-columns: 1fr 300px; }
            .hero-left { padding-left: 32px; padding-right: 40px; }
        }
        @media (max-width: 768px) {
            nav { padding: 0 24px; }
            .nav-label, .nav-divider { display: none; }
            .hero { grid-template-columns: 1fr; }
            .hero-right { min-height: 220px; }
            .hero-left { padding: 56px 24px; }
            .stats-grid { grid-template-columns: 1fr; padding: 0 24px; }
            .stat { padding: 24px 20px; border-right: none; border-bottom: 1px solid var(--rule); }
            .stat:last-child { border-bottom: none; }
            .cards { grid-template-columns: 1fr; gap: 24px; }
            .pillars { padding: 64px 24px; }
            .cta { padding: 64px 24px; }
            footer { padding: 24px; flex-wrap: wrap; gap: 16px; justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>


    <nav>
        <img class="nav-logo" src="{{ asset('assets/images/logo/logo-pge.png') }}" alt="Pertamina Geothermal Energy">
        <div class="nav-right">
            @auth
                <a href="{{ route('dashboard') }}" class="nav-btn">Dashboard <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></a>
            @else
                <a href="{{ route('login') }}" class="nav-btn">Login <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></a>
            @endauth
        </div>
    </nav>

    <div class="hero">
        <div class="hero-left">
            <span class="hero-tag">HSSE · PGE Area Lahendong</span>
            <h1>Dashboard<br><span class="hl-blue">Lahendong</span><br><span class="hl-red">PEKA</span></h1>
            <p class="hero-desc">Dashboard monitoring pengamatan keselamatan kerja untuk mendukung pengelolaan data <strong>PEKA</strong> di Pertamina Geothermal Energy Area Lahendong.</p>
            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">Masuk ke Dashboard &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">Masuk ke Dashboard &rarr;</a>
                @endauth
                <a href="#tentang" class="btn-ghost" style="font-size: 13px; font-weight: 500; color: var(--muted); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: color .18s; padding: 13px 26px;">Tentang PEKA &rsaquo;</a>
            </div>
        </div>
        <div class="hero-right">
        </div>
    </div>

    <div class="stats">
        <div class="stats-grid">
            <div class="stat">
                <div class="stat-num">PEKA</div>
                <div class="stat-label">Sumber data monitoring</div>
            </div>
            <div class="stat">
                <div class="stat-num">Temuan</div>
                <div class="stat-label">Monitoring &amp; tindak lanjut</div>
            </div>
            <div class="stat">
                <div class="stat-num">HSSE</div>
                <div class="stat-label">Budaya keselamatan kerja</div>
            </div>
        </div>
    </div>

    <div class="pillars" id="tentang">
        <p class="sec-label">NILAI LAHENDONG PEKA</p>
        <h2 class="sec-h2">Badengar. Ba Inga. Parduli.</h2>
        <p class="sec-desc">Tiga nilai sederhana yang mengingatkan setiap pekerja Lahendong untuk memperhatikan, mengingat, dan peduli terhadap keselamatan di lingkungan kerja.</p>
        
        <div class="cards">
            <div class="card card-blue">
                <div class="card-num">01</div>
                <div class="card-word">Badengar</div>
                <div class="card-subtitle">Setiap temuan layak didengar.</div>
                <p class="card-text">Perhatikan kondisi dan kejadian di sekitar kita. Jangan abaikan potensi bahaya maupun hal yang dapat diperbaiki.</p>
            </div>
            <div class="card card-green">
                <div class="card-num">02</div>
                <div class="card-word">Ba Inga</div>
                <div class="card-subtitle">Setiap temuan perlu diingat.</div>
                <p class="card-text">Pastikan informasi temuan dan proses tindak lanjut tetap tercatat dan terpantau.</p>
            </div>
            <div class="card card-ink">
                <div class="card-num">03</div>
                <div class="card-word">Parduli</div>
                <div class="card-subtitle">Peduli melalui tindakan.</div>
                <p class="card-text">Wujudkan kepedulian dengan melaporkan temuan dan memastikan tindak lanjut dilakukan.</p>
            </div>
        </div>
    </div>

    <div class="cta">
        <h2>Mari membangun budaya keselamatan bersama.</h2>
        @auth
            <a href="{{ route('dashboard') }}" class="btn-primary">Masuk ke Dashboard <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
        @else
            <a href="{{ route('login') }}" class="btn-primary">Masuk ke Dashboard <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
        @endauth
    </div>

    <footer>
        <p>&copy; {{ date('Y') }}, Made with <span class="heart">&#9829;</span> by HSSE PGE Area Lahendong</p>

    </footer>
    <div class="colorbar"><span></span><span></span><span></span></div>

</body>
</html>
