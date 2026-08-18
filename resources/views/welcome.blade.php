<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lahendong PEKA – Sistem monitoring pengamatan keselamatan kerja SIPEKA untuk Pertamina Geothermal Energy Area Lahendong.">
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
            --green:  #A2AD00;
            --green-d:#858E00;
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
        .nav-logo { height: 32px; }
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
        .hero-desc { font-size: 15px; line-height: 1.8; color: var(--body); max-width: 460px; margin-bottom: 48px; }
        .hero-desc strong { color: var(--ink); font-weight: 600; }
        .hero-actions { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 13px 26px; background: var(--green); color: var(--ink);
            border-radius: 7px; font-size: 14px; font-weight: 700; text-decoration: none;
            transition: background .18s, box-shadow .18s, transform .15s;
        }
        .btn-primary:hover { background: var(--green-d); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(162,173,0,.25); }
        .btn-ghost {
            font-size: 13px; font-weight: 500; color: var(--muted);
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: color .18s;
        }
        .btn-ghost:hover { color: var(--ink); }
        .btn-ghost svg { transition: transform .18s; }
        .btn-ghost:hover svg { transform: translateX(3px); }

        .hero-right {
            background-color: var(--ink);
            background-image: url('{{ asset('assets/images/hero-bg.jpg.JPG') }}');
            background-size: cover;
            background-position: center;
            position: relative; overflow: hidden;
        }


        /* STATS */
        .stats { background: var(--bg); border-top: 1px solid var(--rule); border-bottom: 1px solid var(--rule); }
        .stats-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4,1fr); padding: 0 64px; }
        .stat { padding: 40px 36px; border-right: 1px solid var(--rule); }
        .stat:first-child { padding-left: 0; }
        .stat:last-child  { border-right: none; }
        .stat-num { font-size: 36px; font-weight: 800; letter-spacing: -0.04em; line-height: 1; margin-bottom: 8px; }
        .c-blue  { color: var(--blue); }
        .c-green { color: var(--green-d); }
        .c-ink   { color: var(--ink); }
        .stat-label { font-size: 13px; color: var(--muted); line-height: 1.5; }

        /* ABOUT */
        .about { padding: 96px 64px; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 320px 1fr; gap: 96px; align-items: start; }
        .about-sticky { position: sticky; top: 96px; }
        .sec-label { font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--green-d); display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
        .sec-label::before { content: ''; width: 22px; height: 2px; background: var(--green); border-radius: 1px; }
        .sec-h2 { font-size: clamp(26px, 3vw, 38px); font-weight: 800; letter-spacing: -0.025em; line-height: 1.15; color: var(--ink); margin-bottom: 18px; }
        .sec-h2 em { font-style: normal; color: var(--blue); }
        .about-lead { font-size: 14px; line-height: 1.8; color: var(--body); }
        .about-row { display: grid; grid-template-columns: 48px 1fr; gap: 20px; padding: 28px 0; border-bottom: 1px solid var(--rule); }
        .about-row:first-child { padding-top: 0; }
        .about-row:last-child  { border-bottom: none; padding-bottom: 0; }
        .row-num { font-size: 11px; font-weight: 700; color: var(--rule); letter-spacing: 0.05em; padding-top: 3px; transition: color .2s; }
        .about-row:hover .row-num { color: var(--green); }
        .row-title { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 8px; letter-spacing: -0.01em; }
        .row-body  { font-size: 14px; line-height: 1.75; color: var(--body); }

        /* PILLARS */
        .pillars { background: var(--bg); border-top: 1px solid var(--rule); border-bottom: 1px solid var(--rule); padding: 80px 64px; }
        .pillars-hd { max-width: 1200px; margin: 0 auto 48px; display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: end; }
        .pillars-hd-right { font-size: 15px; color: var(--body); line-height: 1.75; }
        .cards { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(3,1fr); gap: 28px; }
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
        .card-word { font-size: 28px; font-weight: 800; letter-spacing: -0.025em; line-height: 1.2; color: var(--ink); margin-bottom: 16px; }
        .card-text { font-size: 14px; line-height: 1.7; color: var(--body); flex-grow: 1; }
        .card-tag  { 
            align-self: flex-start;
            margin-top: 28px; padding: 6px 14px; border-radius: 100px; 
            font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; 
        }
        .card-blue  .card-tag { background: rgba(0,87,184,0.08); color: var(--blue-d); }
        .card-green .card-tag { background: rgba(162,173,0,0.12); color: var(--green-d); }
        .card-ink   .card-tag { background: rgba(200,16,46,0.08); color: var(--red); }

        /* CTA */
        .cta { 
            background: linear-gradient(135deg, var(--ink) 0%, #111 100%);
            padding: 80px 64px; display: flex; align-items: center; justify-content: space-between; gap: 48px; 
            position: relative; overflow: hidden;
        }
        .cta::before {
            content: ''; position: absolute; top: -50%; left: -10%;
            width: 50%; height: 200%;
            background: radial-gradient(ellipse at center, rgba(0,87,184,0.15) 0%, rgba(0,0,0,0) 70%);
            transform: rotate(-45deg);
        }
        .cta h2 { position: relative; font-size: clamp(24px, 3.5vw, 40px); font-weight: 900; letter-spacing: -0.02em; color: var(--white); line-height: 1.2; margin-bottom: 12px; }
        .cta p  { position: relative; font-size: 16px; color: rgba(255,255,255,.6); max-width: 500px; line-height: 1.6; }

        /* FOOTER */
        footer { background: var(--white); border-top: 1px solid var(--rule); padding: 20px 64px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        footer p { font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 5px; }
        footer .heart { color: var(--red); }
        .foot-links { display: flex; gap: 24px; }
        .foot-links a { font-size: 12px; color: var(--muted); text-decoration: none; transition: color .18s; }
        .foot-links a:hover { color: var(--ink); }
        .colorbar { height: 3px; display: flex; }
        .colorbar span:nth-child(1) { flex: 3; background: var(--blue); }
        .colorbar span:nth-child(2) { flex: 1; background: var(--red); }
        .colorbar span:nth-child(3) { flex: 1; background: var(--green); }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            nav, footer, .cta, .pillars { padding-left: 32px; padding-right: 32px; }
            .about { padding-left: 32px; padding-right: 32px; gap: 48px; }
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
            .stats-grid { grid-template-columns: repeat(2,1fr); padding: 0 24px; }
            .stat { padding: 28px 20px; }
            .stat:nth-child(2) { border-right: none; }
            .stat:nth-child(3) { border-right: 1px solid var(--rule); }
            .about { grid-template-columns: 1fr; padding: 64px 24px; gap: 40px; }
            .about-sticky { position: static; }
            .pillars-hd { grid-template-columns: 1fr; gap: 12px; }
            .cards { grid-template-columns: 1fr; gap: 20px; }
            .pillars { padding: 56px 24px; }
            .cta { flex-direction: column; padding: 56px 24px; gap: 24px; }
            footer { padding: 20px 24px; flex-wrap: wrap; gap: 12px; }
            .foot-links { gap: 16px; }
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
            <p class="hero-desc">Sistem monitoring data pengamatan keselamatan kerja yang dikembangkan khusus untuk mendukung proses pengelolaan data <strong>SIPEKA</strong> di Pertamina Geothermal Energy Area Lahendong.</p>
            <div class="hero-actions">
                <a href="{{ route('login') }}" class="btn-primary">Masuk ke Dashboard <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
                <a href="#tentang" class="btn-ghost">Tentang PEKA <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
            </div>
        </div>
        <div class="hero-right">
        </div>
    </div>

    <div class="stats">
        <div class="stats-grid">
            <div class="stat">
                <div class="stat-num c-blue">4<span style="font-size:.6em">+</span></div>
                <div class="stat-label">Fungsi kerja yang dipantau secara terpusat</div>
            </div>
            <div class="stat">
                <div class="stat-num c-green">Real<span style="font-size:.55em;letter-spacing:0">-time</span></div>
                <div class="stat-label">Data langsung dari export sistem SIPEKA</div>
            </div>
            <div class="stat">
                <div class="stat-num c-ink">360<span style="font-size:.55em">°</span></div>
                <div class="stat-label">Visibilitas dari identifikasi hingga tindak lanjut</div>
            </div>
            <div class="stat">
                <div class="stat-num c-blue">HSSE</div>
                <div class="stat-label">Berbasis budaya keselamatan &amp; proaktif bahaya</div>
            </div>
        </div>
    </div>

    <div class="about" id="tentang">
        <div class="about-sticky">
            <p class="sec-label">Tentang</p>
            <h2 class="sec-h2">Mengapa <em>Lahendong PEKA</em>?</h2>
            <p class="about-lead">Nama ini merepresentasikan identitas area kerja sekaligus sumber utama data yang digunakan, sehingga mudah dikenali oleh seluruh pengguna di lingkungan perusahaan.</p>
        </div>
        <div class="about-rows">
            <div class="about-row">
                <div class="row-num">01</div>
                <div>
                    <div class="row-title">Identitas Area Kerja</div>
                    <p class="row-body">Dashboard ini dikembangkan khusus untuk mendukung proses monitoring data PEKA di <strong>Pertamina Geothermal Energy Area Lahendong</strong>. Nama tersebut merepresentasikan identitas area kerja sekaligus sumber utama data, yaitu hasil export <strong>SIPEKA</strong>, sehingga mudah dikenali oleh seluruh pengguna di lingkungan perusahaan.</p>
                </div>
            </div>
            <div class="about-row">
                <div class="row-num">02</div>
                <div>
                    <div class="row-title">Makna Filosofis PEKA</div>
                    <p class="row-body">Kata "PEKA" memiliki makna filosofis: sikap <strong>peduli</strong>, <strong>peka</strong> terhadap situasi dan kondisi, serta <strong>tanggap</strong> dalam mengidentifikasi dan menindaklanjuti setiap temuan di lingkungan kerja.</p>
                </div>
            </div>
            <div class="about-row">
                <div class="row-num">03</div>
                <div>
                    <div class="row-title">Selaras Budaya HSSE</div>
                    <p class="row-body">Nilai tersebut sejalan dengan budaya keselamatan (HSSE) yang mendorong setiap pekerja untuk lebih <strong>sadar</strong>, <strong>peduli</strong>, dan <strong>proaktif</strong> terhadap potensi bahaya maupun peluang perbaikan di seluruh area operasi Pertamina Geothermal Energy.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="pillars">
        <div class="pillars-hd">
            <div>
                <p class="sec-label">Tiga Pilar</p>
                <h2 class="sec-h2">Nilai di Balik <em>PEKA</em></h2>
            </div>
            <p class="pillars-hd-right">Tiga sikap yang membentuk budaya keselamatan proaktif — bukan sekadar kepatuhan, melainkan kepedulian nyata terhadap sesama dan lingkungan kerja.</p>
        </div>
        <div class="cards">
            <div class="card card-blue">
                <div class="card-num">01</div>
                <div class="card-word">Peduli</div>
                <p class="card-text">Rasa kepedulian yang tulus terhadap sesama rekan kerja, lingkungan, dan keberlangsungan operasi. Setiap potensi bahaya layak dilaporkan, sekecil apapun.</p>
                <span class="card-tag">Awareness</span>
            </div>
            <div class="card card-green">
                <div class="card-num">02</div>
                <div class="card-word">Peka</div>
                <p class="card-text">Kemampuan memperhatikan detail — melihat kondisi tidak aman, perilaku menyimpang, atau peluang perbaikan yang sering terlewatkan dalam rutinitas harian.</p>
                <span class="card-tag">Observation</span>
            </div>
            <div class="card card-ink">
                <div class="card-num">03</div>
                <div class="card-word">Tanggap</div>
                <p class="card-text">Bertindak cepat dan tepat. Temuan yang teridentifikasi segera ditindaklanjuti sehingga risiko dapat dimitigasi sebelum berkembang menjadi insiden.</p>
                <span class="card-tag">Action</span>
            </div>
        </div>
    </div>

    <div class="cta">
        <div>
            <h2>Siap mulai monitoring keselamatan?</h2>
            <p>Masuk menggunakan akun yang telah diberikan oleh Admin HSSE Area Lahendong.</p>
        </div>
        <a href="{{ route('login') }}" class="btn-primary">Login Sekarang <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }}, Made with <span class="heart">&#9829;</span> by HSSE PGE Area Lahendong</p>
        <div class="foot-links">
            <a href="#tentang">Tentang</a>
            <a href="#tentang">Filosofi PEKA</a>
            <a href="{{ route('login') }}">Login</a>
        </div>
    </footer>
    <div class="colorbar"><span></span><span></span><span></span></div>

</body>
</html>
