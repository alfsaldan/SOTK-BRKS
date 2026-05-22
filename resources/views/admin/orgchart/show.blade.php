@extends('admin.layouts.app')

@section('title', $title)
@section('breadcrumb')
    <a href="{{ route('admin.orgchart.index', ['period_id' => $period->id]) }}">Struktur Organisasi</a>
    <span class="sep">›</span>
    <span class="current">{{ $unitKantor ?? 'Semua Unit' }}</span>
@endsection

@section('content')

{{-- ── Toolbar ──────────────────────────────────────────── --}}
<div class="oc-toolbar">
    <div class="oc-toolbar-left">
        <a href="{{ route('admin.orgchart.index', ['period_id' => $period->id]) }}" class="btn btn-secondary btn-sm">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <div class="oc-title">
            <strong>{{ $title }}</strong>
            <span class="badge badge-blue">{{ $period->label }}</span>
        </div>
    </div>
    <div class="oc-toolbar-right">
        <div class="oc-search-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="oc-search" placeholder="Cari nama / NIK / jabatan..." autocomplete="off">
        </div>
        <button class="btn btn-secondary btn-sm" id="btn-zoom-in"  title="Zoom In">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>
        <button class="btn btn-secondary btn-sm" id="btn-zoom-out" title="Zoom Out">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
        </button>
        <button class="btn btn-secondary btn-sm" id="btn-fit"      title="Fit">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
        </button>
        <button class="btn btn-success  btn-sm" id="btn-png">PNG</button>
        <button class="btn btn-primary  btn-sm" id="btn-pdf">PDF</button>
        <button class="btn btn-secondary btn-sm" id="btn-print">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
        </button>
    </div>
</div>

{{-- ── Canvas ────────────────────────────────────────────── --}}
<div class="oc-canvas-wrap" id="oc-canvas-wrap">
    <div class="oc-print-header" id="oc-print-header">
        <div>
            <div style="font-size:10px;color:#999;font-weight:600;letter-spacing:.08em;">PROYEK SOTK ONLINE</div>
            <div style="font-size:16px;font-weight:800;color:#1e293b;">STRUKTUR ORGANISASI</div>
            <div style="font-size:13px;font-weight:700;color:#a00000;">{{ strtoupper($unitKantor ?? 'SEMUA UNIT') }}</div>
        </div>
        <img src="{{ asset('images/brks-logo.png') }}" alt="BRKS" style="height:48px;" onerror="this.style.display='none'">
    </div>

    @if(empty($chartNodes))
        <div class="empty-state" style="padding:80px 20px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p>Tidak ada data untuk unit ini pada periode {{ $period->label }}.</p>
            <a href="{{ route('admin.orgchart.index', ['period_id' => $period->id]) }}" class="btn btn-primary" style="margin-top:16px;">Pilih Unit Lain</a>
        </div>
    @else
        {{-- SVG akan dirender oleh JS ke sini --}}
        <div id="oc-chart-container" style="overflow:hidden;width:100%;min-height:600px;cursor:grab;background:#f0f4fb;">
            <svg id="oc-svg" xmlns="http://www.w3.org/2000/svg" style="display:block;"></svg>
        </div>
    @endif

    <div class="oc-print-footer" id="oc-print-footer">
        Lampiran Keputusan Direksi PT. Bank Pembangunan Daerah Riau Kepri Syariah (Perseroda)
        &nbsp;|&nbsp; Periode: {{ $period->label }} &nbsp;|&nbsp; www.brksyariah.co.id
    </div>

    {{-- Employee Modal --}}
    <div id="emp-modal" class="emp-modal" style="display:none;">
        <div class="emp-modal-content">
            <div class="emp-modal-header">
                <h3 id="emp-modal-title">Detail Pegawai</h3>
                <button onclick="closeEmpModal()" class="emp-modal-close">&times;</button>
            </div>
            <div class="emp-modal-body" id="emp-modal-body"></div>
        </div>
    </div>
</div>

{{-- ── Legend ────────────────────────────────────────────── --}}
<div class="oc-legend">
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#a00000;"></span>Direktur Utama / Direktur</span>
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#f2cc5c;"></span>Pemimpin Divisi</span>
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#fff5cc;"></span>Pemimpin Bagian</span>
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#ffffff;border:1px solid #ccc;"></span>Staf / Pelaksana</span>
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:50%;"></span>Pegawai Divisi / MPP</span>
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#dbeafe;border:1px solid #93c5fd;border-radius:50%;"></span>Fungsional</span>
    <span class="oc-legend-item"><span class="oc-legend-dot" style="background:#f5f5f5;border:1px dashed #ccc;"></span>Belum Terisi</span>
</div>

{{-- ── Data JSON ─────────────────────────────────────────── --}}
<script>
const CHART_NODES  = @json($chartNodes);
const CHART_PERIOD = @json($period->label);
</script>

{{-- ── Libraries ─────────────────────────────────────────── --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

{{-- ═══════════════════════════════════════════════════════════
     CUSTOM ORTHOGONAL ORGCHART ENGINE
     - Tidak pakai d3-org-chart, tidak pakai d3-flextree
     - Layout manual: rank-by-rank, sejajar sempurna
     - Garis orthogonal (patah 90°), tidak pernah melengkung
═══════════════════════════════════════════════════════════ --}}
<script>
// ════════════════════════════════════════════════════════════
//  KONFIGURASI LAYOUT
// ════════════════════════════════════════════════════════════
const CFG = {
    nodeW:        180,   // lebar node standar
    nodeH:        80,    // tinggi node standar (diperbesar jika teks panjang)
    nodeW_top:    200,   // lebar node rank 1-2
    nodeH_top:    90,
    nodeW_div:    200,   // lebar node rank 3
    nodeH_div:    95,
    nodeW_small:  155,   // rank 6
    nodeH_small:  65,
    nodeW_oval:   155,
    nodeH_oval:   105,
    hGap:         20,    // jarak horizontal antar sibling
    vGap:         55,    // jarak vertikal antar rank
    padX:         60,    // padding kiri-kanan canvas
    padY:         50,    // padding atas canvas
    padYBottom:   80,    // padding bawah canvas
    lineColor:    '#000000',
    lineDash:     '#10b981',
    lineW:        1.5,
    // Kolom orphan (di kanan struktur utama)
    orphanGapX:   80,    // jarak kolom orphan dari ujung kanan struktur utama
    accentW: {1:20, 2:20, 3:22, 4:14, 5:12, 6:10},
};

// ════════════════════════════════════════════════════════════
//  DIMENSI NODE
// ════════════════════════════════════════════════════════════
function nodeWidth(n) {
    if (n.node_shape === 'oval')  return CFG.nodeW_oval;
    if (n.rank <= 2)              return CFG.nodeW_top;
    if (n.rank === 3)             return CFG.nodeW_div;
    if (n.rank >= 6)              return CFG.nodeW_small;
    return CFG.nodeW;
}
function nodeHeight(n) {
    if (n.node_shape === 'oval')  return CFG.nodeH_oval;
    if (n.rank <= 2)              return CFG.nodeH_top;
    if (n.rank === 3)             return CFG.nodeH_div;
    if (n.rank >= 6)              return CFG.nodeH_small;
    let h = CFG.nodeH;
    if (!n.is_vacant) {
        const jl = (n.jabatan||'').length, nl = (n.nama||'').length;
        if (jl > 25) h += 16;
        if (jl > 50) h += 16;
        if (nl > 25) h += 14;
        if (nl > 50) h += 14;
    }
    return h;
}

// ════════════════════════════════════════════════════════════
//  TREE BUILDER
// ════════════════════════════════════════════════════════════
const nodeMap = {};
CHART_NODES.forEach(n => { nodeMap[n.id] = n; });

// Bangun children map
const children = {};   // id → [child, ...]
const orphans  = [];   // node yang parentId tidak ada di nodeMap (mis. virtual root anak)
CHART_NODES.forEach(n => {
    if (!n.parentId) return; // root
    if (!children[n.parentId]) children[n.parentId] = [];
    children[n.parentId].push(n);
});

// Sort children per parent berdasarkan rank lalu id (agar urutan konsisten)
Object.keys(children).forEach(pid => {
    children[pid].sort((a, b) => a.rank - b.rank || a.id.localeCompare(b.id));
});

// Temukan root node(s)
const roots = CHART_NODES.filter(n => !n.parentId || n.parentId === '');

// ════════════════════════════════════════════════════════════
//  PEMISAHAN: NODE ORPHAN vs NODE UTAMA
//
//  "Orphan dalam konteks gambar" = node yang parentnya adalah
//  divisi/root (rank 3) TAPI rank-nya adalah 5 atau 6
//  (bukan rank 4 = bagian). Di gambar, node ini muncul di
//  kolom kanan dengan garis khusus dari divisi.
// ════════════════════════════════════════════════════════════
function classifyChildren(parentNode) {
    const ch = children[parentNode.id] || [];
    const structural = [];  // rank 3,4,5,6 normal
    const sidePanel  = [];  // rank 5 yg tidak punya "jalur bagian" → tampil di kanan
    const ovals      = [];  // pegawai divisi & fungsional

    ch.forEach(c => {
        if (c.node_shape === 'oval' || c.is_pegawai_divisi || c.is_fungsional) {
            ovals.push(c);
        } else if (parentNode.rank <= 3 && c.rank === 5) {
            // Node rank-5 yang parentnya divisi (bukan bagian) → side panel
            sidePanel.push(c);
        } else {
            structural.push(c);
        }
    });

    return { structural, sidePanel, ovals };
}

// ════════════════════════════════════════════════════════════
//  LAYOUT ENGINE: hitung x, y setiap node
//  Pendekatan: bottom-up width calculation, top-down placement
// ════════════════════════════════════════════════════════════

// Hitung subtree width (lebar total yang dibutuhkan node beserta seluruh descendant-nya)
// Hanya untuk node "structural" (bukan sidePanel)
function subtreeWidth(n) {
    const { structural } = classifyChildren(n);
    const nw = nodeWidth(n);
    if (!structural.length) return nw;
    const childTotal = structural.reduce((s, c) => s + subtreeWidth(c), 0)
                     + CFG.hGap * (structural.length - 1);
    return Math.max(nw, childTotal);
}

// Assign posisi (cx = center-x, cy = center-y) ke setiap node
// returns { maxY }
function assignPositions(node, cx, cy, positions) {
    const nw = nodeWidth(node);
    const nh = nodeHeight(node);
    positions[node.id] = { cx, cy, w: nw, h: nh };

    const { structural } = classifyChildren(node);
    if (!structural.length) return cy + nh;

    const nextY  = cy + nh + CFG.vGap;
    const totalW = structural.reduce((s, c) => s + subtreeWidth(c), 0)
                 + CFG.hGap * (structural.length - 1);

    let childX = cx - totalW / 2;
    let maxY = nextY;
    structural.forEach(c => {
        const sw  = subtreeWidth(c);
        const ccx = childX + sw / 2;
        const my  = assignPositions(c, ccx, nextY, positions);
        if (my > maxY) maxY = my;
        childX += sw + CFG.hGap;
    });
    return maxY;
}

// ════════════════════════════════════════════════════════════
//  RENDER ENGINE
// ════════════════════════════════════════════════════════════
const NS = 'http://www.w3.org/2000/svg';

function el(tag, attrs, text) {
    const e = document.createElementNS(NS, tag);
    if (attrs) Object.entries(attrs).forEach(([k, v]) => e.setAttribute(k, v));
    if (text !== undefined) e.textContent = text;
    return e;
}

// Buat <foreignObject> berisi card HTML
function renderCard(svg, node, pos, highlighted) {
    const { cx, cy, w, h } = pos;
    const x = cx - w / 2;
    const y = cy;
    const n = node;

    const fo = el('foreignObject', { x, y, width: w, height: h + 60 });
    const div = document.createElement('div');
    div.style.cssText = `width:${w}px;height:${h}px;overflow:visible;`;

    const isVacant = n.is_vacant;
    const isOval   = n.node_shape === 'oval';

    let card;
    if (isOval) {
        card = buildOvalCard(n, w, h, highlighted);
    } else {
        card = buildRectCard(n, w, h, highlighted);
    }
    div.innerHTML = card;
    fo.appendChild(div);
    svg.appendChild(fo);

    // Click handler
    if (!isVacant && !isOval) {
        fo.style.cursor = 'pointer';
        fo.addEventListener('click', (e) => {
            e.stopPropagation();
            showEmployeeDetail(n.id);
        });
    }
}

function buildOvalCard(n, w, h, hl) {
    const borderColor = hl ? '#f59e0b' : n.border;
    const hlStyle = hl ? 'box-shadow:0 0 0 3px #f59e0b,0 4px 16px rgba(245,158,11,0.3);' : 'box-shadow:0 2px 8px rgba(0,0,0,0.08);';
    const label = n.display_label || (n.is_pegawai_divisi ? 'Pegawai Divisi' : 'Fungsional');
    return `<div style="width:${w}px;min-height:${h}px;background:${n.bg};border:2px dashed ${borderColor};
        border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;
        padding:10px 8px;font-family:'Inter',sans-serif;text-align:center;${hlStyle}">
        <div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:${n.color};line-height:1.2;margin-bottom:2px;">${label}</div>
        <div style="font-size:10px;font-weight:600;color:${n.color};line-height:1.2;margin-bottom:2px;word-break:break-word;">${n.jabatan||''}</div>
        <div style="font-size:9px;color:${n.color};opacity:.75;">NIK: ${n.nik||''}</div>
        <div style="font-size:9px;font-weight:500;color:${n.color};opacity:.85;word-break:break-word;">${n.nama||''}</div>
    </div>`;
}

function buildRectCard(n, w, h, hl) {
    const isVacant = n.is_vacant;
    const rank     = n.rank;
    const aw       = CFG.accentW[rank] || 12;
    const hlStyle  = hl
        ? 'outline:3px solid #f59e0b;outline-offset:1px;'
        : (rank <= 2 ? 'box-shadow:2px 3px 6px rgba(0,0,0,0.3);'
           : rank === 3 ? 'box-shadow:3px 4px 10px rgba(0,0,0,0.25);'
           : rank === 4 ? 'box-shadow:2px 3px 6px rgba(0,0,0,0.2);' : '');

    let borderStyle = `1px solid ${isVacant ? '#ccc' : n.border}`;
    if (rank >= 6 || isVacant) borderStyle = `1px ${isVacant?'dashed':'dashed'} ${isVacant?'#ccc':n.border}`;

    const pad = rank <= 2 ? `10px 12px 10px ${aw+8}px`
              : rank === 3 ? `12px 14px 12px ${aw+8}px`
              : rank === 4 ? `10px 12px 10px ${aw+6}px`
              : rank === 5 ? `8px 10px 8px ${aw+4}px`
              : `6px 8px 6px ${aw+4}px`;

    const accent = (!isVacant && rank >= 1)
        ? `<div style="position:absolute;left:0;top:0;bottom:0;width:${aw}px;background:${n.accent};
              clip-path:polygon(0 0,0% 100%,100% 100%);${rank>=6?'opacity:0.7;':''}"></div>`
        : '';

    const jabFS = rank <= 3 ? '13px' : rank >= 6 ? '10px' : '12px';
    const jabFW = rank <= 3 ? '800'  : rank >= 6 ? '600'  : '700';
    const nikC  = rank <= 2 ? 'rgba(255,255,255,.65)' : rank >= 6 ? '#888' : '#555';
    const namaC = rank <= 2 ? 'rgba(255,255,255,.9)'  : rank >= 6 ? '#555' : '#333';
    const nikFS = rank >= 6 ? '9px' : '10px';
    const naFS  = rank >= 6 ? '10px' : '11px';

    const jabHtml = isVacant
        ? `<div style="font-size:11px;font-weight:600;color:#999;margin-bottom:4px;word-break:break-word;">${n.jabatan||''}</div>`
        : `<div style="font-size:${jabFS};font-weight:${jabFW};line-height:1.25;margin-bottom:3px;color:${n.color};word-break:break-word;">${n.jabatan||''}</div>`;

    const empHtml = !isVacant
        ? `<div style="font-size:${nikFS};color:${nikC};">NIK: ${n.nik||''}</div>
           <div style="font-size:${naFS};color:${namaC};font-weight:600;word-break:break-word;">${n.nama||''}</div>`
        : '';

    return `<div style="position:relative;width:${w}px;min-height:${h}px;
        background:${isVacant?'#f5f5f5':n.bg};${borderStyle};
        overflow:visible;display:flex;flex-direction:column;justify-content:center;
        padding:${pad};font-family:'Inter',sans-serif;text-align:left;${hlStyle}">
        ${accent}
        ${jabHtml}
        ${empHtml}
    </div>`;
}

// ── Garis orthogonal (patah 90°) ─────────────────────────────────────────
// parent bottom-center → child top-center, dengan titik patah di tengah jarak vertikal
function drawOrthLine(svg, x1, y1, x2, y2, dashed, color) {
    const midY = y1 + (y2 - y1) / 2;
    const d = `M ${x1} ${y1} L ${x1} ${midY} L ${x2} ${midY} L ${x2} ${y2}`;
    const path = el('path', {
        d,
        fill: 'none',
        stroke: color || CFG.lineColor,
        'stroke-width': CFG.lineW,
        'stroke-dasharray': dashed ? '7,5' : 'none',
    });
    svg.insertBefore(path, svg.firstChild); // garis di belakang node
}

// Garis horizontal bus: satu garis horizontal menghubungkan semua sibling,
// lalu vertikal turun ke masing-masing node
function drawBusLines(svg, parentPos, childPosList, dashed, color) {
    if (!childPosList.length) return;

    const px = parentPos.cx;
    const py = parentPos.cy + parentPos.h;  // bottom parent

    if (childPosList.length === 1) {
        const c = childPosList[0];
        drawOrthLine(svg, px, py, c.cx, c.cy, dashed, color);
        return;
    }

    // titik patah vertikal antara parent dan bus horizontal
    const busY = py + CFG.vGap / 2;

    // bus horizontal dari anak paling kiri ke paling kanan
    const leftX  = Math.min(...childPosList.map(c => c.cx));
    const rightX = Math.max(...childPosList.map(c => c.cx));

    // vertikal turun dari parent ke bus
    drawLine(svg, px, py, px, busY, false, color);
    // bus horizontal
    drawLine(svg, leftX, busY, rightX, busY, dashed, color);
    // vertikal turun dari bus ke setiap anak
    childPosList.forEach(c => {
        drawLine(svg, c.cx, busY, c.cx, c.cy, dashed, color);
    });
}

function drawLine(svg, x1, y1, x2, y2, dashed, color) {
    const line = el('line', {
        x1, y1, x2, y2,
        stroke: color || CFG.lineColor,
        'stroke-width': CFG.lineW,
        'stroke-dasharray': dashed ? '7,5' : 'none',
    });
    svg.insertBefore(line, svg.firstChild);
}

// ════════════════════════════════════════════════════════════
//  MAIN RENDER
// ════════════════════════════════════════════════════════════
let currentScale = 1;
let panX = 0, panY = 0;
let isDragging = false, dragStartX, dragStartY, panStartX, panStartY;

function renderChart(highlightIds) {
    if (!CHART_NODES || CHART_NODES.length === 0) return;

    highlightIds = highlightIds || new Set();

    const svg = document.getElementById('oc-svg');
    while (svg.firstChild) svg.removeChild(svg.firstChild);

    // ── 1. Pisahkan side-panel nodes dari root ─────────────────────────────
    // Side-panel = node rank-5 yang parentnya adalah root (rank 3)
    //   → tampil di kolom kanan
    const mainRoots   = [];
    const sidePanelNodes = []; // { node, parentNode }

    roots.forEach(root => {
        const { structural, sidePanel } = classifyChildren(root);
        mainRoots.push({ root, structural, sidePanel });
        sidePanel.forEach(sp => sidePanelNodes.push({ node: sp, parentNode: root }));
    });

    // ── 2. Hitung posisi struktur utama ───────────────────────────────────
    const positions = {};
    let curX = CFG.padX;

    mainRoots.forEach(({ root }) => {
        const sw  = subtreeWidth(root);
        const cx  = curX + sw / 2;
        const cy  = CFG.padY;
        assignPositions(root, cx, cy, positions);
        curX += sw + CFG.hGap * 3;
    });

    // ── 3. Temukan batas kanan & bawah struktur utama ─────────────────────
    let mainMaxX = 0, mainMaxY = 0;
    Object.values(positions).forEach(p => {
        const right  = p.cx + p.w / 2;
        const bottom = p.cy + p.h;
        if (right  > mainMaxX) mainMaxX = right;
        if (bottom > mainMaxY) mainMaxY = bottom;
    });

    // ── 4. Hitung posisi side-panel di kolom kanan ────────────────────────
    // Side panel mulai di x = mainMaxX + orphanGapX
    // y = sejajar baris paling bawah (rank 6 atau rank 5 jika tidak ada rank 6)
    let spX = mainMaxX + CFG.orphanGapX;

    // Cari Y paling bawah dari rank terbawah di struktur utama
    let deepestY = CFG.padY;
    CHART_NODES.forEach(n => {
        const pos = positions[n.id];
        if (!pos) return;
        if (pos.cy > deepestY) deepestY = pos.cy;
    });

    // Posisi side panel: cy = deepestY (sejajar rank terbawah)
    sidePanelNodes.forEach(({ node }) => {
        const w = nodeWidth(node);
        const h = nodeHeight(node);
        positions[node.id] = { cx: spX + w / 2, cy: deepestY, w, h };
        spX += w + CFG.hGap;
    });

    // ── 5. Hitung total canvas size ────────────────────────────────────────
    let canvasW = spX + CFG.padX;
    let canvasH = mainMaxY + CFG.padYBottom;
    sidePanelNodes.forEach(({ node }) => {
        const pos = positions[node.id];
        if (pos && pos.cy + pos.h + CFG.padYBottom > canvasH)
            canvasH = pos.cy + pos.h + CFG.padYBottom;
    });

    svg.setAttribute('width',  canvasW);
    svg.setAttribute('height', canvasH);
    svg.setAttribute('viewBox', `0 0 ${canvasW} ${canvasH}`);

    // ── 6. Gambar semua garis (SEBELUM node agar tertimpa) ────────────────
    // Rekursif untuk struktur utama
    function drawLines(node) {
        const pos = positions[node.id];
        if (!pos) return;
        const { structural, ovals } = classifyChildren(node);

        // Garis ke structural children
        const structChildPositions = structural.map(c => positions[c.id]).filter(Boolean);
        drawBusLines(svg, pos, structChildPositions.map((p, i) => ({cx: p.cx, cy: p.cy})), false, CFG.lineColor);

        // Garis dashed ke oval children
        ovals.forEach(oc => {
            const op = positions[oc.id];
            if (op) drawOrthLine(svg, pos.cx, pos.cy + pos.h, op.cx, op.cy, true, CFG.lineDash);
        });

        structural.forEach(c => drawLines(c));
    }

    roots.forEach(root => drawLines(root));

    // Garis side-panel: dari parentNode → kanan (elbow line)
    // Garis keluar dari sisi kanan parent (bukan bawah), lalu ke bawah ke sisi kiri node
    sidePanelNodes.forEach(({ node, parentNode }) => {
        const pp  = positions[parentNode.id];
        const cp  = positions[node.id];
        if (!pp || !cp) return;

        // Titik awal: kanan tengah parent node
        const x1 = pp.cx + pp.w / 2;
        const y1 = pp.cy + pp.h / 2;

        // Titik akhir: atas tengah node side-panel
        const x2 = cp.cx;
        const y2 = cp.cy;

        // Elbow: kanan dari parent → turun ke Y side-panel → kiri ke node
        // x1 → rightEdge → rightEdge,y2 → x2,y2
        const rightEdge = Math.max(x1, x2 - CFG.orphanGapX / 2);
        const d = `M ${x1} ${y1} L ${rightEdge} ${y1} L ${rightEdge} ${y2} L ${x2} ${y2}`;
        const path = el('path', {
            d,
            fill: 'none',
            stroke: CFG.lineColor,
            'stroke-width': CFG.lineW,
        });
        svg.insertBefore(path, svg.firstChild);
    });

    // ── 7. Render semua node ──────────────────────────────────────────────
    const allRendered = new Set();

    function renderNodeTree(node) {
        const pos = positions[node.id];
        if (!pos || allRendered.has(node.id)) return;
        allRendered.add(node.id);
        renderCard(svg, node, pos, highlightIds.has(node.id));

        const { structural, ovals } = classifyChildren(node);
        structural.forEach(c => renderNodeTree(c));
        ovals.forEach(c => {
            const op = positions[c.id];
            if (op && !allRendered.has(c.id)) {
                allRendered.add(c.id);
                renderCard(svg, c, op, highlightIds.has(c.id));
            }
        });
    }

    roots.forEach(root => renderNodeTree(root));

    sidePanelNodes.forEach(({ node }) => {
        const pos = positions[node.id];
        if (pos && !allRendered.has(node.id)) {
            allRendered.add(node.id);
            renderCard(svg, node, pos, highlightIds.has(node.id));
        }
    });

    applyTransform();
}

// ════════════════════════════════════════════════════════════
//  PAN & ZOOM
// ════════════════════════════════════════════════════════════
function applyTransform() {
    const svg = document.getElementById('oc-svg');
    svg.style.transform = `translate(${panX}px,${panY}px) scale(${currentScale})`;
    svg.style.transformOrigin = '0 0';
}

document.getElementById('btn-zoom-in') .addEventListener('click', () => { currentScale = Math.min(currentScale * 1.2, 4);    applyTransform(); });
document.getElementById('btn-zoom-out').addEventListener('click', () => { currentScale = Math.max(currentScale / 1.2, 0.15); applyTransform(); });
document.getElementById('btn-fit')     .addEventListener('click', () => {
    const wrap = document.getElementById('oc-chart-container');
    const svg  = document.getElementById('oc-svg');
    const sw   = parseFloat(svg.getAttribute('width'))  || 1;
    const sh   = parseFloat(svg.getAttribute('height')) || 1;
    const ww   = wrap.clientWidth;
    const wh   = wrap.clientHeight || 600;
    currentScale = Math.min(ww / sw, wh / sh, 1) * 0.95;
    panX = (ww - sw * currentScale) / 2;
    panY = CFG.padY;
    applyTransform();
});

const container = document.getElementById('oc-chart-container');
if (container) {
    container.addEventListener('mousedown', e => {
        isDragging = true; dragStartX = e.clientX; dragStartY = e.clientY;
        panStartX = panX; panStartY = panY;
        container.style.cursor = 'grabbing';
    });
    window.addEventListener('mousemove', e => {
        if (!isDragging) return;
        panX = panStartX + (e.clientX - dragStartX);
        panY = panStartY + (e.clientY - dragStartY);
        applyTransform();
    });
    window.addEventListener('mouseup', () => { isDragging = false; container.style.cursor = 'grab'; });
    container.addEventListener('wheel', e => {
        e.preventDefault();
        const factor = e.deltaY < 0 ? 1.1 : 0.9;
        currentScale = Math.min(Math.max(currentScale * factor, 0.15), 4);
        applyTransform();
    }, { passive: false });
}

// ════════════════════════════════════════════════════════════
//  SEARCH
// ════════════════════════════════════════════════════════════
document.getElementById('oc-search').addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    const ids = new Set();
    if (q) {
        CHART_NODES.forEach(n => {
            if ((n.nama||'').toLowerCase().includes(q)
             || (n.nik||'').includes(q)
             || (n.jabatan||'').toLowerCase().includes(q)) {
                ids.add(n.id);
            }
        });
    }
    renderChart(ids);
});

// ════════════════════════════════════════════════════════════
//  EXPORT
// ════════════════════════════════════════════════════════════
document.getElementById('btn-png').addEventListener('click', async () => {
    const container = document.getElementById('oc-chart-container');
    const svg = document.getElementById('oc-svg');
    const origTransform = svg.style.transform;
    const origOrigin    = svg.style.transformOrigin;
    svg.style.transform = 'none';
    svg.style.transformOrigin = '0 0';
    const canvas = await html2canvas(container, { backgroundColor: '#f0f4fb', scale: 2, useCORS: true });
    svg.style.transform = origTransform;
    svg.style.transformOrigin = origOrigin;
    const a = document.createElement('a');
    a.download = 'Struktur_' + CHART_PERIOD.replace(/ /g, '_') + '.png';
    a.href = canvas.toDataURL('image/png');
    a.click();
});

document.getElementById('btn-pdf').addEventListener('click', async () => {
    const container = document.getElementById('oc-chart-container');
    const svg = document.getElementById('oc-svg');
    const origTransform = svg.style.transform;
    svg.style.transform = 'none';
    const canvas = await html2canvas(container, { backgroundColor: '#f0f4fb', scale: 2, useCORS: true });
    svg.style.transform = origTransform;
    const { jsPDF } = window.jspdf;
    const pdf   = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a3' });
    const pw = pdf.internal.pageSize.getWidth();
    const ph = pdf.internal.pageSize.getHeight();
    const ratio = Math.min((pw - 20) / (canvas.width / 2), (ph - 25) / (canvas.height / 2));
    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 10, 10, (canvas.width/2)*ratio, (canvas.height/2)*ratio);
    pdf.setFontSize(8); pdf.setTextColor(120);
    pdf.text('PT Bank Riau Kepri Syariah | Periode: ' + CHART_PERIOD, pw / 2, ph - 5, { align: 'center' });
    pdf.save('Struktur_' + CHART_PERIOD.replace(/ /g, '_') + '.pdf');
});

document.getElementById('btn-print').addEventListener('click', () => {
    document.getElementById('oc-print-header').style.display = 'flex';
    document.getElementById('oc-print-footer').style.display = 'block';
    window.print();
    setTimeout(() => {
        document.getElementById('oc-print-header').style.display = 'none';
        document.getElementById('oc-print-footer').style.display = 'none';
    }, 1500);
});

// ════════════════════════════════════════════════════════════
//  MODAL
// ════════════════════════════════════════════════════════════
function showEmployeeDetail(id) {
    const n = nodeMap[id];
    if (!n || n.is_vacant) return;
    document.getElementById('emp-modal-body').innerHTML = `
        <table>
            <tr><th>NIK</th><td>${n.nik||'-'}</td></tr>
            <tr><th>Nama</th><td>${n.nama||'-'}</td></tr>
            <tr><th>Jabatan</th><td>${n.jabatan||'-'}</td></tr>
            <tr><th>Level Jabatan</th><td>${n.level_jabatan||'-'}</td></tr>
            <tr><th>Klasifikasi</th><td>${n.klasifikasi_jabatan||'-'}</td></tr>
            <tr><th>Unit Kantor</th><td>${n.unit_kantor||'-'}</td></tr>
            <tr><th>Penempatan</th><td>${n.penempatan||'-'}</td></tr>
            <tr><th>Kelas</th><td>${n.kelas||'-'}</td></tr>
        </table>`;
    document.getElementById('emp-modal').style.display = 'flex';
}
function closeEmpModal() { document.getElementById('emp-modal').style.display = 'none'; }
window.addEventListener('click', e => { if (e.target === document.getElementById('emp-modal')) closeEmpModal(); });

// ════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════
if (CHART_NODES && CHART_NODES.length > 0) {
    renderChart(new Set());
    // Auto-fit setelah render
    setTimeout(() => document.getElementById('btn-fit').click(), 100);
}
</script>

{{-- ── Styles ────────────────────────────────────────────── --}}
<style>
.oc-toolbar {
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:10px; margin-bottom:14px;
}
.oc-toolbar-left  { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.oc-toolbar-right { display:flex; align-items:center; gap:6px;  flex-wrap:wrap; }
.oc-title { font-size:14px; font-weight:600; display:flex; align-items:center; gap:8px; }

.oc-search-wrap {
    display:flex; align-items:center; gap:6px;
    border:1.5px solid #e2e8f0; border-radius:8px;
    padding:5px 10px; background:white;
}
.oc-search-wrap svg   { width:15px; height:15px; color:#94a3b8; flex-shrink:0; }
.oc-search-wrap input { border:none; outline:none; font-size:12.5px; width:180px; font-family:inherit; color:#1e293b; }

.oc-canvas-wrap {
    background:#ffffff; border-radius:14px;
    border:1px solid #e2e8f0; min-height:600px; overflow:hidden;
}

#oc-chart-container {
    position:relative;
    user-select:none;
    -webkit-user-select:none;
}

#oc-svg {
    will-change: transform;
    transition: none;
}

.oc-print-header { display:none; align-items:center; justify-content:space-between; padding:16px 24px; border-bottom:2px solid #a00000; }
.oc-print-footer { display:none; padding:10px 24px; font-size:10px; color:#64748b; border-top:1px solid #e2e8f0; text-align:right; }

.oc-legend { display:flex; align-items:center; flex-wrap:wrap; gap:14px; margin-top:12px; font-size:12px; color:#475569; }
.oc-legend-item  { display:flex; align-items:center; gap:5px; }
.oc-legend-dot   { width:14px; height:14px; border-radius:3px; display:inline-block; flex-shrink:0; }

/* Modal */
.emp-modal {
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.5); z-index:9999;
    display:flex; align-items:center; justify-content:center;
}
.emp-modal-content {
    background:#fff; width:450px; max-width:90%;
    border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2);
    font-family:'Inter',sans-serif; overflow:hidden;
}
.emp-modal-header {
    background:#f8fafc; padding:16px 20px;
    display:flex; align-items:center; justify-content:space-between;
    border-bottom:1px solid #e2e8f0;
}
.emp-modal-header h3 { margin:0; font-size:16px; font-weight:600; color:#1e293b; }
.emp-modal-close { background:none; border:none; font-size:24px; line-height:1; cursor:pointer; color:#64748b; }
.emp-modal-close:hover { color:#0f172a; }
.emp-modal-body { padding:20px; font-size:13px; color:#334155; }
.emp-modal-body table { width:100%; border-collapse:collapse; }
.emp-modal-body th, .emp-modal-body td { padding:10px 0; border-bottom:1px solid #f1f5f9; text-align:left; vertical-align:top; }
.emp-modal-body tr:last-child th, .emp-modal-body tr:last-child td { border-bottom:none; }
.emp-modal-body th { width:35%; font-weight:600; color:#475569; }
.emp-modal-body td { font-weight:500; color:#0f172a; }

@media print {
    .sidebar, .topbar, .oc-toolbar, .oc-legend, .emp-modal { display:none !important; }
    .main-wrapper { margin-left:0 !important; }
    .content { padding:0 !important; }
    .oc-canvas-wrap { border:none; border-radius:0; min-height:unset; overflow:visible; }
    #oc-chart-container { overflow:visible !important; height:auto !important; }
    #oc-svg { transform:none !important; }
    .oc-print-header { display:flex !important; }
    .oc-print-footer { display:block !important; }
    @page { size: A3 landscape; margin: 10mm; }
}
@media (max-width:768px) {
    .oc-toolbar { flex-direction:column; align-items:flex-start; }
    .oc-search-wrap input { width:100px; }
    .oc-toolbar-right { width:100%; overflow-x:auto; }
}
</style>

@endsection