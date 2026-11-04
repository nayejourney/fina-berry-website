/* Prototipe klik Fina Berry: tanpa API/backend. Data contoh + alur antarhalaman + penyimpanan di localStorage. */
(function () {
  var STORE = 'Warung Makan Fina Berry', WA = '6285647731631';
  var PRODUCTS = [
    { id: 1, name: 'Bakso Daging Sapi', cat: 'Makanan', price: 15000, cost: 8000, img: 'bakso-daging-sapi', ok: true, feat: true, desc: 'Bakso sapi kenyal dengan kuah kaldu gurih, mie, dan topping lengkap.' },
    { id: 2, name: 'Bakso Keju', cat: 'Makanan', price: 17000, cost: 9500, img: 'bakso-keju', ok: true, desc: '' },
    { id: 3, name: 'Soto Ayam Komplit', cat: 'Makanan', price: 20000, cost: 11000, img: 'soto-ayam-komplit', ok: true, feat: true, desc: '' },
    { id: 4, name: 'Soto Daging Sapi', cat: 'Makanan', price: 23000, cost: 13000, img: 'soto-daging-sapi', ok: true, desc: '' },
    { id: 5, name: 'Nasi Goreng', cat: 'Makanan', price: 15000, cost: 8000, img: 'nasi-goreng', ok: true, feat: true, desc: '' },
    { id: 6, name: 'Paket Ayam Goreng', cat: 'Makanan', price: 15000, cost: 8500, img: 'paket-ayam-goreng', ok: true, desc: 'Termasuk nasi, lauk, dan lalapan sambal.' },
    { id: 7, name: 'Paket Ayam Bakar Kampung', cat: 'Makanan', price: 27000, cost: 15000, img: 'paket-ayam-bakar-kampung', ok: true, feat: true, desc: 'Termasuk nasi, lauk, dan lalapan sambal.' },
    { id: 8, name: 'Paket Ikan Mujair Bakar', cat: 'Makanan', price: 20000, cost: 11000, img: 'paket-ikan-bakar', ok: true, feat: true, desc: 'Termasuk nasi, lauk, dan lalapan sambal.' },
    { id: 9, name: 'Es Teh Manis', cat: 'Minuman', price: 4000, cost: 1500, img: 'es-teh-manis', ok: true, desc: '' },
    { id: 10, name: 'Es Jeruk', cat: 'Minuman', price: 5000, cost: 2200, img: 'es-jeruk', ok: true, desc: '' },
    { id: 11, name: 'Jus Strawberry', cat: 'Minuman', price: 12000, cost: 6500, img: 'jus-strawberry', ok: false, desc: '' },
    { id: 12, name: 'Wedang Jahe', cat: 'Minuman', price: 7000, cost: 3000, img: 'wedang-jahe', ok: true, desc: '' },
    { id: 13, name: 'Mendoan', cat: 'Cemilan', price: 10000, cost: 5000, img: 'mendoan', ok: true, feat: true, desc: '1 porsi.' },
    { id: 14, name: 'Tahu Brontak', cat: 'Cemilan', price: 10000, cost: 5000, img: 'tahu-brontak', ok: true, desc: '1 porsi.' },
    { id: 15, name: 'Pisang Goreng', cat: 'Cemilan', price: 10000, cost: 5000, img: 'pisang-goreng', ok: true, desc: '1 porsi.' }
  ];
  var ORDERS = [
    { code: 'FB-20261015-003', time: '15/10 11:42', ch: 'Online', name: 'Siti', phone: '081311112222', ful: 'Ambil di warung', pay: 'QRIS', status: 'menunggu', paid: false, items: [[3, 1], [9, 2]], notes: 'Tidak pedas' },
    { code: 'FB-20261015-002', time: '15/10 10:15', ch: 'Online', name: 'Budi', phone: '081234567890', ful: 'Diantar', pay: 'Tunai', status: 'diproses', paid: false, items: [[3, 1], [13, 1]], notes: '' },
    { code: 'FB-20261015-001', time: '15/10 09:03', ch: 'Warung', name: '-', phone: '', ful: 'Makan di tempat', pay: 'Tunai', status: 'selesai', paid: true, items: [[7, 1]], notes: '' },
    { code: 'FB-20261014-011', time: '14/10 16:20', ch: 'Warung', name: 'Meja 4', phone: '', ful: 'Makan di tempat', pay: 'QRIS', status: 'selesai', paid: true, items: [[1, 1], [9, 1]], notes: '' },
    { code: 'FB-20261014-010', time: '14/10 15:02', ch: 'Online', name: 'Ani', phone: '081200000000', ful: 'Ambil di warung', pay: 'Transfer bank', status: 'dibatalkan', paid: false, items: [[6, 2], [10, 2]], notes: '', reason: 'Pelanggan tidak merespons WhatsApp' }
  ];
  var STATUS = { menunggu: ['Menunggu', 'text-bg-warning'], diproses: ['Diproses', 'text-bg-info'], selesai: ['Selesai', 'text-bg-success'], dibatalkan: ['Dibatalkan', 'text-bg-secondary'] };
  var ROLES = { staf: ['Staf Warung', 1], pemilik: ['Manager/Pemilik', 2], bos: ['Bos (Super Admin)', 3] };

  function rp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function prod(id) { return PRODUCTS.filter(function (p) { return p.id === +id; })[0]; }
  function qs(k) { return new URLSearchParams(location.search).get(k); }
  function orderTotal(o) { return o.items.reduce(function (s, it) { return s + prod(it[0]).price * it[1]; }, 0); }
  function badges(o, st, paid) {
    var s = STATUS[st || o.status];
    var pd = paid === undefined ? o.paid : paid;
    return '<span class="badge ' + s[1] + '">' + s[0] + '</span> <span class="badge ' + (pd ? 'text-bg-success' : 'text-bg-danger') + '">' + (pd ? 'Lunas' : 'Belum bayar') + '</span>';
  }

  /* ---------- toast ---------- */
  function toast(msg, type) {
    var box = $('#toastBox');
    if (!box) { box = document.createElement('div'); box.id = 'toastBox'; box.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:2000;display:flex;flex-direction:column;gap:8px'; document.body.appendChild(box); }
    var t = document.createElement('div');
    t.className = 'alert alert-' + (type || 'dark') + ' shadow mb-0 py-2';
    t.textContent = msg; box.appendChild(t);
    setTimeout(function () { t.remove(); }, 2800);
  }

  /* ---------- keranjang (localStorage) ---------- */
  function getCart() {
    var c = null; try { c = JSON.parse(localStorage.getItem('fb_cart')); } catch (e) {}
    if (!c) { c = { 1: 2, 13: 1, 9: 2 }; localStorage.setItem('fb_cart', JSON.stringify(c)); }
    return c;
  }
  function saveCart(c) { localStorage.setItem('fb_cart', JSON.stringify(c)); updateBadge(); }
  function cartCount() { var c = getCart(), n = 0; for (var k in c) n += c[k]; return n; }
  function cartTotal() { var c = getCart(), t = 0; for (var k in c) t += prod(k).price * c[k]; return t; }
  function updateBadge() { $$('[data-cart-badge]').forEach(function (b) { var n = cartCount(); b.textContent = n; b.style.display = n ? '' : 'none'; }); }

  /* ---------- kartu menu ---------- */
  function card(p) {
    return '<div class="card fb-card' + (p.ok ? '' : ' fb-soldout') + '"><img src="assets/img/menu/' + p.img + '.webp" alt="' + p.name + '" class="fb-card-img" loading="lazy">' +
      '<div class="card-body d-flex flex-column"><div class="small text-muted">' + p.cat + '</div><h3 class="h6 fw-bold mb-1" style="font-family:inherit">' + p.name + '</h3>' +
      (p.desc ? '<p class="small text-muted mb-2">' + p.desc + '</p>' : '') +
      '<div class="mt-auto d-flex align-items-center justify-content-between pt-2"><span class="fb-price">' + rp(p.price) + '</span>' +
      (p.ok ? '<button type="button" class="btn btn-primary btn-sm" data-add="' + p.id + '"><i class="bi bi-bag-plus"></i> Tambah</button>' : '<span class="badge text-bg-secondary">Habis</span>') +
      '</div></div></div>';
  }
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-add]'); if (!b) return;
    var c = getCart(), id = b.getAttribute('data-add'); c[id] = Math.min(50, (c[id] || 0) + 1); saveCart(c);
    toast(prod(id).name + ' ditambahkan ke keranjang.', 'success');
  });

  /* ---------- chrome publik ---------- */
  function pubChrome(active) {
    var links = [['home', 'index.html', 'Beranda'], ['menu', 'menu.html', 'Menu'], ['promo', 'promo.html', 'Promo'], ['galeri', 'galeri.html', 'Galeri'], ['kontak', 'kontak.html', 'Kontak']];
    $('#nav').innerHTML =
      '<nav class="navbar navbar-expand-lg sticky-top fb-nav"><div class="container">' +
      '<a class="navbar-brand d-flex align-items-center gap-2" href="index.html"><img src="assets/img/logo.webp" class="fb-logo" width="38" height="38" alt=""><span>' + STORE + '</span></a>' +
      '<div class="d-flex align-items-center gap-2 order-lg-last"><a href="keranjang.html" class="btn btn-outline-primary position-relative" aria-label="Keranjang"><i class="bi bi-bag"></i>' +
      '<span data-cart-badge class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"></span></a>' +
      '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button></div>' +
      '<div class="collapse navbar-collapse" id="navMain"><ul class="navbar-nav mx-lg-auto">' +
      links.map(function (l) { return '<li class="nav-item"><a class="nav-link' + (l[0] === active ? ' active' : '') + '" href="' + l[1] + '">' + l[2] + '</a></li>'; }).join('') +
      '</ul></div></div></nav>';
    $('#foot').innerHTML =
      '<footer class="fb-footer pt-5 pb-4 mt-5"><div class="container"><div class="row g-4">' +
      '<div class="col-md-5"><h2 class="h5 text-white">' + STORE + '</h2><p class="small mb-2">Rasa rumahan yang tak terlupakan, sejak 2012</p></div>' +
      '<div class="col-md-4 small"><div class="mb-2"><i class="bi bi-geo-alt"></i> Kawasan D\'LAS, Desa Serang, Karangreja, Purbalingga.</div><div><i class="bi bi-clock"></i> 08.00 - 17.00 WIB</div></div>' +
      '<div class="col-md-3 small"><div class="mb-2 fw-semibold text-white">Hubungi kami</div><div class="d-flex gap-3 fs-5"><a href="https://wa.me/' + WA + '" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i></a><a href="https://www.instagram.com/fina.berry" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a></div></div>' +
      '</div><hr class="border-secondary my-4"><div class="d-flex flex-wrap justify-content-between small"><span>&copy; 2026 ' + STORE + '</span><a href="login.html" class="text-secondary">Masuk admin</a></div></div></footer>' +
      '<a href="https://wa.me/' + WA + '" class="fb-wa-float" target="_blank" rel="noopener" aria-label="Chat WhatsApp"><i class="bi bi-whatsapp"></i></a>';
    updateBadge();
  }

  /* ---------- peran dan chrome admin ---------- */
  function role() { var r = localStorage.getItem('fb_role'); return ROLES[r] ? r : 'bos'; }
  function lvl() { return ROLES[role()][1]; }
  function adminChrome(active, min, title) {
    var page = $('#page'); page.parentNode.removeChild(page);
    var items = [
      ['Dashboard', 'bi-speedometer2', 'admin-dashboard.html', 'dash', 1],
      ['#Operasional'],
      ['Pesanan', 'bi-receipt', 'admin-pesanan.html', 'pesanan', 1, '3'],
      ['Input di warung', 'bi-calculator', 'admin-kasir.html', 'kasir', 1],
      ['Menu', 'bi-egg-fried', 'admin-menu.html', 'menu', 1],
      ['#Konten dan toko'],
      ['Kategori', 'bi-tags', 'admin-kategori.html', 'kategori', 2],
      ['Promo', 'bi-megaphone', 'admin-promo.html', 'promo', 2],
      ['Galeri', 'bi-images', 'admin-galeri.html', 'galeri', 2],
      ['QR Code', 'bi-qr-code', 'admin-qr.html', 'qr', 2],
      ['Pengaturan toko', 'bi-gear', 'admin-pengaturan.html', 'pengaturan', 2],
      ['#Laporan'],
      ['Laporan penjualan', 'bi-bar-chart-line', 'admin-laporan.html', 'laporan', 2],
      ['Biaya operasional', 'bi-wallet2', 'admin-biaya.html', 'biaya', 2],
      ['Laba rugi dan analitik', 'bi-graph-up-arrow', 'admin-analitik.html', 'analitik', 3],
      ['#Sistem'],
      ['Akun pengguna', 'bi-people', 'admin-pengguna.html', 'pengguna', 2],
      ['Log aktivitas', 'bi-journal-check', 'admin-log.html', 'log', 2]
    ];
    var nav = items.filter(function (i) { return i[0].charAt(0) === '#' || i[4] <= lvl(); }).map(function (i) {
      if (i[0].charAt(0) === '#') return '<div class="nav-section">' + i[0].slice(1) + '</div>';
      return '<a class="nav-link' + (i[3] === active ? ' active' : '') + '" href="' + i[2] + '"><i class="bi ' + i[1] + '"></i> ' + i[0] + (i[5] ? '<span class="badge bg-warning text-dark ms-auto">' + i[5] + '</span>' : '') + '</a>';
    }).join('').replace(/<div class="nav-section">[^<]*<\/div>(?=<div class="nav-section">|$)/g, '');
    var r = ROLES[role()];
    var shell =
      '<div class="d-flex"><aside class="offcanvas-lg offcanvas-start ad-sidebar p-3 flex-shrink-0" tabindex="-1" id="adSide">' +
      '<div class="d-flex justify-content-between align-items-center mb-3"><a href="admin-dashboard.html" class="ad-brand"><img src="assets/img/logo.webp" alt=""><span>Fina Berry<br><small class="fw-normal text-secondary">Admin</small></span></a>' +
      '<button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adSide"></button></div>' +
      '<nav class="nav flex-column">' + nav + '<div class="nav-section">Lainnya</div><a class="nav-link" href="index.html"><i class="bi bi-box-arrow-up-right"></i> Lihat website</a></nav></aside>' +
      '<div class="ad-main"><header class="bg-white border-bottom px-3 py-2 d-flex align-items-center gap-2">' +
      '<button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adSide"><i class="bi bi-list"></i></button>' +
      '<h1 class="h5 mb-0 me-auto">' + title + '</h1>' +
      '<div class="dropdown"><button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown"><span class="badge text-bg-secondary">' + r[0] + '</span></button>' +
      '<ul class="dropdown-menu dropdown-menu-end"><li><h6 class="dropdown-header">Ganti peran (demo)</h6></li>' +
      Object.keys(ROLES).map(function (k) { return '<li><a class="dropdown-item' + (k === role() ? ' active' : '') + '" href="#" data-role="' + k + '">' + ROLES[k][0] + '</a></li>'; }).join('') +
      '<li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="admin-akun.html">Akun saya</a></li></ul></div>' +
      '<a href="login.html" class="btn btn-outline-danger btn-sm" id="btnLogout" title="Keluar"><i class="bi bi-box-arrow-right"></i></a></header>' +
      '<div class="p-3 p-lg-4" id="main"></div></div></div>';
    document.body.insertAdjacentHTML('afterbegin', shell);
    $$('[data-role]').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); localStorage.setItem('fb_role', a.getAttribute('data-role')); location.reload(); }); });
    $('#btnLogout').addEventListener('click', function () { localStorage.removeItem('fb_role'); });
    var main = $('#main');
    if (lvl() < min) {
      main.innerHTML = '<div class="ad-card p-5 text-center mx-auto" style="max-width:520px"><div class="display-1 fw-bold" style="color:#a3173f">403</div><h2 class="h4">Akses ditolak</h2>' +
        '<p class="text-muted">Peran <strong>' + r[0] + '</strong> tidak punya izin membuka halaman ini. Coba ganti peran lewat menu di kanan atas.</p><a href="admin-dashboard.html" class="btn btn-dark">Kembali ke dashboard</a></div>';
      return false;
    }
    main.appendChild(page); page.classList.remove('d-none');
    return true;
  }

  function csv(name, rows) {
    var body = '\uFEFF' + rows.map(function (r) { return r.map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(';'); }).join('\n');
    var a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([body], { type: 'text/csv;charset=utf-8' })); a.download = name; a.click();
    toast('Berkas ' + name + ' diunduh (data contoh).', 'success');
  }

  window.FB = { STORE: STORE, WA: WA, PRODUCTS: PRODUCTS, ORDERS: ORDERS, STATUS: STATUS, ROLES: ROLES, rp: rp, $: $, $$: $$, prod: prod, qs: qs, toast: toast, getCart: getCart, saveCart: saveCart, cartTotal: cartTotal,
    card: card, pubChrome: pubChrome, adminChrome: adminChrome, role: role, lvl: lvl, csv: csv, badges: badges, orderTotal: orderTotal };
})();
