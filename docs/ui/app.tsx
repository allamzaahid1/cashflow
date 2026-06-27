import { useState } from "react";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  Tooltip,
  ResponsiveContainer,
} from "recharts";
import {
  LayoutDashboard,
  PlusCircle,
  BarChart3,
  Settings,
  Wallet,
  TrendingUp,
  ShoppingCart,
  Banknote,
  QrCode,
  Store,
  Shield,
  Plus,
  Check,
  Upload,
  X,
  Edit2,
  Trash2,
  Download,
  Calendar,
  ChevronRight,
  ChevronLeft,
  ChevronDown,
} from "lucide-react";

// ─── Types ────────────────────────────────────────────────────────────────────
type Page = "dashboard" | "transaksi" | "rekap" | "pengaturan" | "penarikan";

// ─── Helpers ──────────────────────────────────────────────────────────────────
const fmt = (n: number) => "Rp " + n.toLocaleString("id-ID");

const todayLabel = new Date().toLocaleDateString("id-ID", {
  weekday: "long",
  year: "numeric",
  month: "long",
  day: "numeric",
});

// ─── Static Data ──────────────────────────────────────────────────────────────
const recentTx = [
  { id: "TRX-045", time: "08:15", desc: "Nasi Goreng Spesial x3", method: "Cash", nominal: 45000 },
  { id: "TRX-044", time: "09:30", desc: "Mie Ayam + Es Teh Manis", method: "QRIS", nominal: 28000 },
  { id: "TRX-043", time: "10:05", desc: "Paket Sarapan Komplit", method: "Cash", nominal: 35000 },
  { id: "TRX-042", time: "11:20", desc: "Nasi Uduk x2 + Jus Alpukat", method: "QRIS", nominal: 52000 },
  { id: "TRX-041", time: "12:45", desc: "Soto Ayam + Kerupuk", method: "Cash", nominal: 33000 },
];

const weeklyData = [
  { day: "Sen", pemasukan: 1200000 },
  { day: "Sel", pemasukan: 980000 },
  { day: "Rab", pemasukan: 1450000 },
  { day: "Kam", pemasukan: 870000 },
  { day: "Jum", pemasukan: 1680000 },
  { day: "Sab", pemasukan: 1950000 },
  { day: "Min", pemasukan: 1500000 },
];

const reportRows = [
  { date: "26/06/2026", id: "TRX-045", type: "Pemasukan", kategori: "Penjualan", method: "QRIS", masuk: 52000, keluar: 0, saldo: 2552000 },
  { date: "26/06/2026", id: "TRX-044", type: "Pemasukan", kategori: "Penjualan", method: "Cash", masuk: 33000, keluar: 0, saldo: 2500000 },
  { date: "26/06/2026", id: "TRX-043", type: "Pengeluaran", kategori: "Bahan Baku", method: "Cash", masuk: 0, keluar: 150000, saldo: 2467000 },
  { date: "25/06/2026", id: "TRX-042", type: "Pemasukan", kategori: "Penjualan", method: "QRIS", masuk: 78000, keluar: 0, saldo: 2617000 },
  { date: "25/06/2026", id: "TRX-041", type: "Pengeluaran", kategori: "Operasional", method: "Cash", masuk: 0, keluar: 50000, saldo: 2539000 },
  { date: "24/06/2026", id: "TRX-040", type: "Pemasukan", kategori: "Penjualan", method: "Cash", masuk: 125000, keluar: 0, saldo: 2589000 },
  { date: "24/06/2026", id: "TRX-039", type: "Pengeluaran", kategori: "Bahan Baku", method: "Cash", masuk: 0, keluar: 200000, saldo: 2464000 },
];

const withdrawalHistory = [
  { date: "20/06/2026", bank: "BCA", nominal: 500000, admin: 2500, status: "Success" },
  { date: "15/06/2026", bank: "BCA", nominal: 1000000, admin: 2500, status: "Success" },
  { date: "10/06/2026", bank: "Mandiri", nominal: 750000, admin: 2500, status: "Failed" },
  { date: "05/06/2026", bank: "BCA", nominal: 500000, admin: 2500, status: "Pending" },
  { date: "01/06/2026", bank: "BRI", nominal: 250000, admin: 2500, status: "Success" },
];

const initialQrisAccounts = [
  { id: 1, name: "Gopay Warung", account: "0812-3456-7890" },
  { id: 2, name: "BCA Kasir", account: "1234567890" },
];

// Fixed QR-like pattern (deterministic, no Math.random)
const QR_PATTERN = [1,1,1,1,1, 1,0,1,0,1, 1,1,0,1,1, 1,0,1,0,1, 1,1,1,1,1];

// ─── Sidebar ──────────────────────────────────────────────────────────────────
const navItems: { id: Page; label: string; icon: React.ElementType }[] = [
  { id: "dashboard",   label: "Dashboard",         icon: LayoutDashboard },
  { id: "transaksi",   label: "Catat Transaksi",   icon: PlusCircle },
  { id: "rekap",       label: "Rekap Penjualan",   icon: BarChart3 },
  { id: "pengaturan",  label: "Pengaturan Toko",   icon: Settings },
  { id: "penarikan",   label: "Penarikan Dana",    icon: Wallet },
];

function Sidebar({ active, setActive }: { active: Page; setActive: (p: Page) => void }) {
  return (
    <aside
      className="w-56 min-h-screen flex flex-col flex-shrink-0"
      style={{ background: "#0f172a" }}
    >
      {/* Brand */}
      <div className="px-5 py-5 border-b" style={{ borderColor: "rgba(255,255,255,0.07)" }}>
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center">
            <Store className="w-4 h-4 text-white" />
          </div>
          <div>
            <p className="text-white font-bold text-sm leading-tight" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
              Warung Barokah
            </p>
            <p className="text-xs" style={{ color: "#64748b" }}>Kasir Pro v2</p>
          </div>
        </div>
      </div>

      {/* Nav */}
      <nav className="flex-1 px-3 py-4 space-y-0.5">
        {navItems.map(({ id, label, icon: Icon }) => (
          <button
            key={id}
            onClick={() => setActive(id)}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all ${
              active === id
                ? "bg-green-600 text-white font-semibold"
                : "text-slate-400 hover:text-white hover:bg-white/5"
            }`}
          >
            <Icon className="w-4 h-4 flex-shrink-0" />
            {label}
          </button>
        ))}
      </nav>

      {/* User footer */}
      <div className="px-4 py-4 border-t" style={{ borderColor: "rgba(255,255,255,0.07)" }}>
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 rounded-full bg-green-900 flex items-center justify-center flex-shrink-0">
            <span className="text-green-400 text-xs font-bold">WB</span>
          </div>
          <div className="min-w-0">
            <p className="text-white text-xs font-semibold truncate">Admin Toko</p>
            <p className="text-xs truncate" style={{ color: "#475569" }}>warungbarokah@gmail.com</p>
          </div>
        </div>
      </div>
    </aside>
  );
}

// ─── Page: Dashboard ──────────────────────────────────────────────────────────
function Dashboard({ setPage }: { setPage: (p: Page) => void }) {
  const metricCards = [
    {
      label: "Total Pemasukan Hari Ini",
      value: fmt(1500000),
      icon: TrendingUp,
      iconBg: "bg-green-50",
      iconColor: "text-green-600",
      valueColor: "text-green-600",
      sub: "+12% vs kemarin",
    },
    {
      label: "Total Transaksi",
      value: "45",
      icon: ShoppingCart,
      iconBg: "bg-blue-50",
      iconColor: "text-blue-600",
      valueColor: "text-slate-900",
      sub: "Hari ini",
    },
    {
      label: "Pembayaran Cash",
      value: fmt(900000),
      icon: Banknote,
      iconBg: "bg-amber-50",
      iconColor: "text-amber-600",
      valueColor: "text-slate-900",
      sub: "60% dari total",
    },
    {
      label: "Pembayaran QRIS",
      value: fmt(600000),
      icon: QrCode,
      iconBg: "bg-purple-50",
      iconColor: "text-purple-600",
      valueColor: "text-slate-900",
      sub: "40% dari total",
    },
  ];

  return (
    <div className="p-6 space-y-5">
      {/* Page header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-bold text-slate-900" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
            Dashboard
          </h1>
          <p className="text-sm text-slate-500 mt-0.5">{todayLabel}</p>
        </div>
        <div className="flex items-center gap-2">
          <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
            ● Toko Buka
          </span>
        </div>
      </div>

      {/* Metric cards */}
      <div className="grid grid-cols-4 gap-4">
        {metricCards.map((c) => {
          const Icon = c.icon;
          return (
            <div key={c.label} className="bg-white rounded-xl border border-slate-100 p-5 shadow-sm hover:shadow-md transition-shadow">
              <div className="flex items-start justify-between mb-3">
                <p className="text-xs font-medium text-slate-500 leading-snug pr-2">{c.label}</p>
                <div className={`w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 ${c.iconBg}`}>
                  <Icon className={`w-4 h-4 ${c.iconColor}`} />
                </div>
              </div>
              <p className={`text-xl font-bold ${c.valueColor}`} style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                {c.value}
              </p>
              <p className="text-xs text-slate-400 mt-1">{c.sub}</p>
            </div>
          );
        })}
      </div>

      {/* Main split */}
      <div className="grid grid-cols-3 gap-5">
        {/* Recent transactions — 2/3 */}
        <div className="col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
          <div className="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <h2 className="font-semibold text-slate-900">Transaksi Terbaru</h2>
            <button
              onClick={() => setPage("rekap")}
              className="text-xs text-green-600 font-semibold hover:underline flex items-center gap-1"
            >
              Lihat Semua <ChevronRight className="w-3 h-3" />
            </button>
          </div>
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50/80">
                <th className="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Waktu</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Keterangan</th>
                <th className="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Metode</th>
                <th className="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Nominal</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {recentTx.map((tx) => (
                <tr key={tx.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-5 py-3.5 text-xs text-slate-500" style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                    {tx.time}
                  </td>
                  <td className="px-5 py-3.5 text-slate-800 text-sm">{tx.desc}</td>
                  <td className="px-5 py-3.5">
                    <span
                      className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${
                        tx.method === "Cash"
                          ? "bg-amber-50 text-amber-700"
                          : "bg-purple-50 text-purple-700"
                      }`}
                    >
                      {tx.method}
                    </span>
                  </td>
                  <td className="px-5 py-3.5 text-right font-semibold text-green-600 text-sm" style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                    {fmt(tx.nominal)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Right action panel — 1/3 */}
        <div className="space-y-4">
          <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <button
              onClick={() => setPage("transaksi")}
              className="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold py-5 px-5 rounded-xl text-base transition-all shadow-lg shadow-green-200/60 flex items-center justify-center gap-2"
              style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}
            >
              <Plus className="w-5 h-5" />
              Catat Transaksi Baru
            </button>
            <p className="text-xs text-slate-400 text-center mt-3">Tekan Ctrl+N untuk pintasan</p>
          </div>

          <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <h3 className="text-sm font-semibold text-slate-800 mb-1">Pendapatan Minggu Ini</h3>
            <p className="text-xs text-slate-400 mb-4">20 Jun – 26 Jun 2026</p>
            <ResponsiveContainer width="100%" height={130}>
              <BarChart data={weeklyData} barSize={18} margin={{ top: 0, right: 0, left: 0, bottom: 0 }}>
                <XAxis
                  dataKey="day"
                  tick={{ fontSize: 10, fill: "#94a3b8", fontFamily: "Inter, sans-serif" }}
                  axisLine={false}
                  tickLine={false}
                />
                <YAxis hide />
                <Tooltip
                  formatter={(v: number) => [fmt(v), "Pemasukan"]}
                  contentStyle={{
                    fontSize: 12,
                    borderRadius: 8,
                    border: "1px solid #e2e8f0",
                    fontFamily: "Inter, sans-serif",
                  }}
                  cursor={{ fill: "#f8fafc" }}
                />
                <Bar dataKey="pemasukan" fill="#16a34a" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>

          <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-sm font-semibold text-slate-800">Akses Cepat</h3>
            </div>
            <div className="space-y-2">
              {[
                { label: "Rekap Harian", page: "rekap" as Page },
                { label: "Penarikan Dana", page: "penarikan" as Page },
                { label: "Pengaturan QRIS", page: "pengaturan" as Page },
              ].map((item) => (
                <button
                  key={item.label}
                  onClick={() => setPage(item.page)}
                  className="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-slate-50 hover:text-green-700 transition-colors text-left"
                >
                  {item.label}
                  <ChevronRight className="w-4 h-4 text-slate-300" />
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Catat Transaksi ────────────────────────────────────────────────────
function CatatTransaksi() {
  const [activeTab, setActiveTab] = useState<"pemasukan" | "pengeluaran">("pemasukan");
  const [payMethod, setPayMethod] = useState<"tunai" | "nontunai">("tunai");
  const [dragging, setDragging] = useState(false);
  const [uploadedFile, setUploadedFile] = useState<string | null>(null);

  const kategoriPemasukan = ["Penjualan Makanan", "Penjualan Minuman", "Katering", "Lain-lain"];
  const kategoriPengeluaran = ["Bahan Baku", "Operasional", "Gaji Karyawan", "Utilitas", "Lain-lain"];

  const defaultDate = new Date().toISOString().slice(0, 16);

  // Dark palette tokens
  const bg       = "#1a1f2e";   // page bg (charcoal-navy)
  const card     = "#242938";   // card surface
  const surface  = "#2e3347";   // input / raised surface
  const border   = "#363c52";   // subtle border
  const borderHi = "#22c55e";   // green accent border (active)
  const muted    = "#6b7280";   // muted label text
  const body     = "#c9d1e0";   // body text

  const inputCls =
    "w-full px-4 py-3 text-sm rounded-xl border outline-none transition-all focus:ring-2 focus:ring-green-500/30 focus:border-green-500";

  return (
    <div className="min-h-full p-6" style={{ background: bg }}>
      {/* Page header */}
      <div className="mb-6">
        <h1
          className="text-xl font-bold"
          style={{ color: "#f0f4ff", fontFamily: "'Plus Jakarta Sans', sans-serif" }}
        >
          Catat Transaksi
        </h1>
        <p className="text-sm mt-0.5" style={{ color: muted }}>
          Tambah pemasukan atau pengeluaran baru
        </p>
      </div>

      <div className="max-w-3xl mx-auto">
        <div className="rounded-2xl overflow-hidden shadow-2xl" style={{ background: card, border: `1px solid ${border}` }}>

          {/* ── Tabs ────────────────────────────────────────────────── */}
          <div className="flex" style={{ borderBottom: `1px solid ${border}` }}>
            {(["pemasukan", "pengeluaran"] as const).map((tab) => {
              const isActive = activeTab === tab;
              const activeBg = tab === "pemasukan" ? "#16a34a" : "#dc2626";
              return (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className="flex-1 py-4 text-sm font-bold tracking-widest uppercase transition-all"
                  style={{
                    fontFamily: "'Plus Jakarta Sans', sans-serif",
                    background: isActive ? activeBg : "transparent",
                    color: isActive ? "#ffffff" : muted,
                  }}
                >
                  {tab === "pemasukan" ? "💰  Pemasukan" : "💸  Pengeluaran"}
                </button>
              );
            })}
          </div>

          <div className="p-6 space-y-6">
            {/* ── Form grid ───────────────────────────────────────────── */}
            <div className="grid grid-cols-2 gap-6">
              {/* Col 1 */}
              <div className="space-y-4">
                {/* Nominal */}
                <div>
                  <label className="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style={{ color: muted }}>
                    Nominal (Rp)
                  </label>
                  <div className="relative">
                    <span
                      className="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold"
                      style={{ color: muted }}
                    >
                      Rp
                    </span>
                    <input
                      type="text"
                      placeholder="0"
                      className={inputCls}
                      style={{
                        background: surface,
                        borderColor: border,
                        color: "#f0f4ff",
                        fontFamily: "'JetBrains Mono', monospace",
                        fontSize: "1.5rem",
                        fontWeight: 700,
                        paddingLeft: "2.75rem",
                      }}
                    />
                  </div>
                </div>

                {/* Kategori */}
                <div>
                  <label className="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style={{ color: muted }}>
                    Kategori
                  </label>
                  <div className="relative">
                    <select
                      className={inputCls + " appearance-none"}
                      style={{ background: surface, borderColor: border, color: body }}
                    >
                      <option value="">Pilih kategori…</option>
                      {(activeTab === "pemasukan" ? kategoriPemasukan : kategoriPengeluaran).map((k) => (
                        <option key={k}>{k}</option>
                      ))}
                    </select>
                    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style={{ color: muted }} />
                  </div>
                </div>

                {/* Date/time */}
                <div>
                  <label className="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style={{ color: muted }}>
                    Tanggal &amp; Waktu
                  </label>
                  <input
                    type="datetime-local"
                    defaultValue={defaultDate}
                    className={inputCls}
                    style={{ background: surface, borderColor: border, color: body, colorScheme: "dark" }}
                  />
                </div>
              </div>

              {/* Col 2 — notes */}
              <div className="flex flex-col">
                <label className="block text-xs font-semibold mb-1.5 uppercase tracking-wider" style={{ color: muted }}>
                  Deskripsi / Catatan
                </label>
                <textarea
                  placeholder="Contoh: Nasi goreng x3, es teh manis x2, kerupuk…"
                  rows={10}
                  className={inputCls + " resize-none flex-1"}
                  style={{ background: surface, borderColor: border, color: body }}
                />
              </div>
            </div>

            {/* ── Payment method ──────────────────────────────────────── */}
            <div>
              <label className="block text-xs font-semibold mb-3 uppercase tracking-wider" style={{ color: muted }}>
                Metode Pembayaran
              </label>
              <div className="grid grid-cols-2 gap-3">
                {(["tunai", "nontunai"] as const).map((m) => {
                  const isActive = payMethod === m;
                  return (
                    <button
                      key={m}
                      onClick={() => setPayMethod(m)}
                      className="flex items-center gap-4 px-5 py-4 rounded-xl border-2 transition-all text-left"
                      style={{
                        borderColor: isActive ? borderHi : border,
                        background: isActive ? "rgba(34,197,94,0.08)" : surface,
                      }}
                    >
                      {/* Radio dot */}
                      <div
                        className="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                        style={{ borderColor: isActive ? borderHi : muted }}
                      >
                        {isActive && <div className="w-2 h-2 rounded-full" style={{ background: borderHi }} />}
                      </div>
                      <span className="text-2xl">{m === "tunai" ? "💵" : "📱"}</span>
                      <div>
                        <p
                          className="font-bold text-sm"
                          style={{ color: isActive ? "#4ade80" : "#f0f4ff" }}
                        >
                          {m === "tunai" ? "Tunai" : "Non-Tunai"}
                        </p>
                        <p className="text-xs" style={{ color: muted }}>
                          {m === "tunai" ? "Uang tunai / cash" : "Transfer, e-wallet, QRIS"}
                        </p>
                      </div>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* ── Proof upload (mandatory, always visible) ─────────────── */}
            <div>
              <div className="flex items-center gap-2 mb-3">
                <label className="block text-xs font-semibold uppercase tracking-wider" style={{ color: muted }}>
                  Unggah Bukti Transaksi
                </label>
                <span
                  className="px-2 py-0.5 rounded-full text-xs font-bold uppercase"
                  style={{ background: "rgba(220,38,38,0.15)", color: "#f87171" }}
                >
                  Wajib
                </span>
              </div>

              <div
                onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
                onDragLeave={() => setDragging(false)}
                onDrop={(e) => {
                  e.preventDefault();
                  setDragging(false);
                  const f = e.dataTransfer.files[0];
                  if (f) setUploadedFile(f.name);
                }}
                className="rounded-xl transition-all cursor-pointer"
                style={{
                  border: `2px dashed ${dragging ? borderHi : uploadedFile ? "#16a34a" : border}`,
                  background: dragging
                    ? "rgba(34,197,94,0.07)"
                    : uploadedFile
                    ? "rgba(34,197,94,0.05)"
                    : surface,
                  padding: "2rem 1.5rem",
                }}
              >
                {uploadedFile ? (
                  <div className="flex items-center justify-center gap-3">
                    <div
                      className="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                      style={{ background: "rgba(34,197,94,0.15)" }}
                    >
                      <Check className="w-5 h-5" style={{ color: "#4ade80" }} />
                    </div>
                    <div>
                      <p className="text-sm font-semibold" style={{ color: "#4ade80" }}>{uploadedFile}</p>
                      <p className="text-xs" style={{ color: muted }}>Bukti berhasil diunggah</p>
                    </div>
                    <button
                      onClick={() => setUploadedFile(null)}
                      className="ml-4 transition-colors"
                      style={{ color: muted }}
                    >
                      <X className="w-4 h-4" />
                    </button>
                  </div>
                ) : (
                  <div className="text-center">
                    {/* Icon cluster */}
                    <div className="flex items-center justify-center gap-3 mb-4">
                      <div
                        className="w-14 h-14 rounded-2xl flex items-center justify-center"
                        style={{ background: "rgba(34,197,94,0.10)", border: `1px solid ${border}` }}
                      >
                        <Upload className="w-6 h-6" style={{ color: "#4ade80" }} />
                      </div>
                    </div>

                    <p className="font-semibold mb-1" style={{ color: "#f0f4ff", fontSize: "0.9rem" }}>
                      Seret &amp; lepas file di sini
                    </p>
                    <label className="inline-block cursor-pointer text-sm font-bold" style={{ color: "#4ade80" }}>
                      atau klik untuk memilih file
                      <input
                        type="file"
                        accept="image/*,.pdf"
                        className="hidden"
                        onChange={(e) => {
                          const f = e.target.files?.[0];
                          if (f) setUploadedFile(f.name);
                        }}
                      />
                    </label>

                    {/* Helper text */}
                    <div
                      className="mt-4 mx-auto max-w-sm rounded-xl px-4 py-3 text-left space-y-1"
                      style={{ background: "rgba(255,255,255,0.04)", border: `1px solid ${border}` }}
                    >
                      <p className="text-xs font-semibold" style={{ color: body }}>Panduan unggah bukti:</p>
                      <p className="text-xs" style={{ color: muted }}>
                        <span style={{ color: "#fbbf24" }}>💵 Tunai:</span> Foto uang atau struk fisik kasir
                      </p>
                      <p className="text-xs" style={{ color: muted }}>
                        <span style={{ color: "#60a5fa" }}>📱 Non-Tunai:</span> Screenshot mutasi rekening, bukti transfer, atau notifikasi e-wallet
                      </p>
                    </div>

                    <p className="text-xs mt-3" style={{ color: muted }}>
                      PNG, JPG, PDF — maks. 10 MB
                    </p>
                  </div>
                )}
              </div>
            </div>

            {/* ── Submit ──────────────────────────────────────────────── */}
            <div
              className="flex items-center justify-between pt-4"
              style={{ borderTop: `1px solid ${border}` }}
            >
              <p className="text-xs" style={{ color: muted }}>
                Semua kolom &amp; bukti transaksi wajib diisi
              </p>
              <button
                className="flex items-center gap-2.5 font-bold px-8 py-3.5 rounded-xl transition-all text-sm"
                style={{
                  fontFamily: "'Plus Jakarta Sans', sans-serif",
                  background: activeTab === "pemasukan" ? "#16a34a" : "#dc2626",
                  color: "#ffffff",
                  boxShadow: activeTab === "pemasukan"
                    ? "0 4px 24px rgba(22,163,74,0.35)"
                    : "0 4px 24px rgba(220,38,38,0.30)",
                }}
              >
                <Check className="w-4 h-4" />
                Simpan Transaksi
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Rekap Penjualan ────────────────────────────────────────────────────
function RekapPenjualan() {
  const [currentPage, setCurrentPage] = useState(1);
  const totalPages = 5;

  const summaryCards = [
    { label: "Total Kas Masuk", value: fmt(4850000), color: "green", sub: "1 Jun – 26 Jun 2026" },
    { label: "Total Kas Keluar", value: fmt(400000), color: "red", sub: "1 Jun – 26 Jun 2026" },
    { label: "Saldo Akhir", value: fmt(4450000), highlight: true, sub: "Per hari ini" },
  ];

  const paginationItems = [1, 2, 3, "…", totalPages];

  return (
    <div className="p-6 space-y-5">
      <div>
        <h1 className="text-xl font-bold text-slate-900" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
          Rekap Penjualan
        </h1>
        <p className="text-sm text-slate-500 mt-0.5">Laporan arus kas lengkap</p>
      </div>

      {/* Filter bar */}
      <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
        <div className="flex items-center gap-3 flex-wrap">
          <div className="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
            <Calendar className="w-4 h-4 text-slate-400" />
            <input
              type="date"
              defaultValue="2026-06-01"
              className="text-sm bg-transparent border-0 outline-none text-slate-700 w-32"
            />
            <span className="text-slate-300 font-medium">—</span>
            <input
              type="date"
              defaultValue="2026-06-26"
              className="text-sm bg-transparent border-0 outline-none text-slate-700 w-32"
            />
          </div>

          <div className="relative">
            <select className="appearance-none bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 pr-8 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/25">
              <option>Harian</option>
              <option>Mingguan</option>
              <option>Bulanan</option>
            </select>
            <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
          </div>

          <div className="ml-auto flex gap-2">
            <button className="flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
              <Download className="w-4 h-4" />
              PDF
            </button>
            <button className="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
              <Download className="w-4 h-4" />
              Excel
            </button>
          </div>
        </div>
      </div>

      {/* Summary cards */}
      <div className="grid grid-cols-3 gap-4">
        {summaryCards.map((c) => (
          <div
            key={c.label}
            className={`rounded-xl border p-5 shadow-sm ${
              c.highlight
                ? "bg-slate-900 border-slate-900"
                : "bg-white border-slate-100"
            }`}
          >
            <p className={`text-xs font-semibold uppercase tracking-wider ${c.highlight ? "text-slate-400" : "text-slate-500"}`}>
              {c.label}
            </p>
            <p
              className={`text-2xl font-bold mt-2 ${
                c.highlight
                  ? "text-white"
                  : c.color === "green"
                  ? "text-green-600"
                  : "text-red-600"
              }`}
              style={{ fontFamily: "'JetBrains Mono', monospace" }}
            >
              {c.value}
            </p>
            <p className={`text-xs mt-1.5 ${c.highlight ? "text-slate-500" : "text-slate-400"}`}>{c.sub}</p>
          </div>
        ))}
      </div>

      {/* Data table */}
      <div className="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="bg-slate-50 border-b border-slate-100">
                {["Tanggal", "ID Transaksi", "Jenis", "Kategori", "Metode", "Nominal Masuk", "Nominal Keluar", "Saldo"].map((h) => (
                  <th
                    key={h}
                    className="text-left px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider whitespace-nowrap"
                  >
                    {h}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50">
              {reportRows.map((row) => (
                <tr key={row.id} className="hover:bg-slate-50/50 transition-colors">
                  <td className="px-4 py-3.5 text-xs text-slate-500" style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                    {row.date}
                  </td>
                  <td className="px-4 py-3.5 text-xs text-slate-400" style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                    {row.id}
                  </td>
                  <td className="px-4 py-3.5">
                    <span
                      className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${
                        row.type === "Pemasukan" ? "bg-green-50 text-green-700" : "bg-red-50 text-red-600"
                      }`}
                    >
                      {row.type}
                    </span>
                  </td>
                  <td className="px-4 py-3.5 text-slate-700 text-xs">{row.kategori}</td>
                  <td className="px-4 py-3.5">
                    <span
                      className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${
                        row.method === "QRIS" ? "bg-purple-50 text-purple-700" : "bg-amber-50 text-amber-700"
                      }`}
                    >
                      {row.method}
                    </span>
                  </td>
                  <td
                    className="px-4 py-3.5 text-xs font-semibold text-green-600"
                    style={{ fontFamily: "'JetBrains Mono', monospace" }}
                  >
                    {row.masuk > 0 ? fmt(row.masuk) : <span className="text-slate-200">—</span>}
                  </td>
                  <td
                    className="px-4 py-3.5 text-xs font-semibold text-red-600"
                    style={{ fontFamily: "'JetBrains Mono', monospace" }}
                  >
                    {row.keluar > 0 ? fmt(row.keluar) : <span className="text-slate-200">—</span>}
                  </td>
                  <td
                    className="px-4 py-3.5 text-xs font-bold text-slate-800"
                    style={{ fontFamily: "'JetBrains Mono', monospace" }}
                  >
                    {fmt(row.saldo)}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        <div className="flex items-center justify-between px-5 py-4 border-t border-slate-100">
          <p className="text-xs text-slate-400">Menampilkan 1–7 dari 127 transaksi</p>
          <div className="flex items-center gap-1">
            <button
              onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
              disabled={currentPage === 1}
              className="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 transition-colors"
            >
              <ChevronLeft className="w-4 h-4" />
            </button>
            {paginationItems.map((p, i) => (
              <button
                key={i}
                onClick={() => typeof p === "number" && setCurrentPage(p)}
                className={`w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold transition-colors ${
                  p === currentPage
                    ? "bg-green-600 text-white"
                    : typeof p === "number"
                    ? "border border-slate-200 text-slate-600 hover:bg-slate-50"
                    : "text-slate-400 cursor-default"
                }`}
              >
                {p}
              </button>
            ))}
            <button
              onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
              disabled={currentPage === totalPages}
              className="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 disabled:opacity-40 transition-colors"
            >
              <ChevronRight className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

// ─── Page: Pengaturan Toko ────────────────────────────────────────────────────
type SettingsTab = "profil" | "pembayaran" | "keamanan";

function PengaturanToko() {
  const [activeTab, setActiveTab] = useState<SettingsTab>("pembayaran");
  const [showModal, setShowModal] = useState(false);
  const [accounts, setAccounts] = useState(initialQrisAccounts);

  const tabs: { id: SettingsTab; label: string; icon: React.ElementType }[] = [
    { id: "profil", label: "Profil Toko", icon: Store },
    { id: "pembayaran", label: "Metode Pembayaran", icon: QrCode },
    { id: "keamanan", label: "Keamanan", icon: Shield },
  ];

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-xl font-bold text-slate-900" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
          Pengaturan Toko
        </h1>
        <p className="text-sm text-slate-500 mt-0.5">Kelola informasi dan preferensi toko Anda</p>
      </div>

      <div className="flex gap-6">
        {/* Vertical tabs */}
        <div className="w-52 flex-shrink-0">
          <nav className="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            {tabs.map(({ id, label, icon: Icon }) => (
              <button
                key={id}
                onClick={() => setActiveTab(id)}
                className={`w-full flex items-center gap-3 px-4 py-3.5 text-sm text-left transition-all border-b border-slate-50 last:border-0 ${
                  activeTab === id
                    ? "bg-green-50 text-green-700 font-semibold border-l-2 border-l-green-500"
                    : "text-slate-600 hover:bg-slate-50"
                }`}
              >
                <Icon className="w-4 h-4 flex-shrink-0" />
                {label}
              </button>
            ))}
          </nav>
        </div>

        {/* Tab content */}
        <div className="flex-1">
          {activeTab === "profil" && (
            <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
              <h2 className="font-semibold text-slate-900 mb-5">Informasi Profil Toko</h2>
              <div className="space-y-4 max-w-md">
                <div>
                  <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Toko</label>
                  <input
                    defaultValue="Warung Barokah"
                    className="w-full px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400 transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Alamat</label>
                  <textarea
                    rows={3}
                    defaultValue="Jl. Pahlawan No. 12, Kelurahan Sukamaju, Bandung 40123"
                    className="w-full px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400 resize-none transition-all"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kontak</label>
                  <input
                    defaultValue="0812-3456-7890"
                    className="w-full px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400 transition-all"
                  />
                </div>
                <button className="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors mt-2">
                  Simpan Profil
                </button>
              </div>
            </div>
          )}

          {activeTab === "pembayaran" && (
            <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
              <div className="flex items-center justify-between mb-5">
                <h2 className="font-semibold text-slate-900">Daftar Akun Penerima &amp; QRIS</h2>
                <button
                  onClick={() => setShowModal(true)}
                  className="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                >
                  <Plus className="w-4 h-4" />
                  Tambah Akun QRIS Baru
                </button>
              </div>
              <div className="space-y-3">
                {accounts.map((acc) => (
                  <div
                    key={acc.id}
                    className="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/40 transition-colors"
                  >
                    {/* QR thumbnail */}
                    <div className="w-14 h-14 flex-shrink-0 rounded-lg bg-white border border-slate-200 flex items-center justify-center p-1.5">
                      <div className="grid gap-0.5" style={{ gridTemplateColumns: "repeat(5, 1fr)" }}>
                        {QR_PATTERN.map((v, i) => (
                          <div
                            key={i}
                            className="w-1.5 h-1.5 rounded-sm"
                            style={{ background: v ? "#1e293b" : "white" }}
                          />
                        ))}
                      </div>
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-semibold text-slate-800 text-sm">{acc.name}</p>
                      <p className="text-xs text-slate-500 mt-0.5" style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                        {acc.account}
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <button className="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-colors">
                        <Edit2 className="w-3.5 h-3.5" />
                      </button>
                      <button
                        onClick={() => setAccounts((a) => a.filter((x) => x.id !== acc.id))}
                        className="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-colors"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                ))}
                {accounts.length === 0 && (
                  <div className="text-center py-10 text-slate-400 text-sm">
                    Belum ada akun QRIS. Tambahkan akun baru.
                  </div>
                )}
              </div>
            </div>
          )}

          {activeTab === "keamanan" && (
            <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
              <h2 className="font-semibold text-slate-900 mb-5">Keamanan Akun</h2>
              <div className="space-y-4 max-w-md">
                {["Password Lama", "Password Baru", "Konfirmasi Password Baru"].map((label) => (
                  <div key={label}>
                    <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                      {label}
                    </label>
                    <input
                      type="password"
                      placeholder="••••••••"
                      className="w-full px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400 transition-all"
                    />
                  </div>
                ))}
                <button className="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition-colors">
                  Ubah Password
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* QRIS Modal */}
      {showModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div
            className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
            onClick={() => setShowModal(false)}
          />
          <div className="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100">
              <h3 className="font-bold text-slate-900" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
                Tambah Akun QRIS Baru
              </h3>
              <button
                onClick={() => setShowModal(false)}
                className="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 transition-colors text-slate-500"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
            <div className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nama Akun</label>
                <input
                  placeholder="cth: Gopay Warung"
                  className="w-full px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400"
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tipe Akun</label>
                <div className="relative">
                  <select className="w-full appearance-none px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25">
                    <option>Gopay</option>
                    <option>OVO</option>
                    <option>DANA</option>
                    <option>BCA</option>
                    <option>Mandiri</option>
                    <option>BRI</option>
                  </select>
                  <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                </div>
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Nomor Telepon / Rekening
                </label>
                <input
                  placeholder="0812-xxxx-xxxx"
                  className="w-full px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400"
                  style={{ fontFamily: "'JetBrains Mono', monospace" }}
                />
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                  Upload File QR Code
                </label>
                <div className="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center bg-slate-50">
                  <QrCode className="w-8 h-8 text-slate-300 mx-auto mb-2" />
                  <label className="cursor-pointer text-sm font-semibold text-green-600 hover:underline">
                    Pilih file QR Code
                    <input type="file" accept="image/*" className="hidden" />
                  </label>
                  <p className="text-xs text-slate-400 mt-1">PNG, JPG — maks. 5 MB</p>
                </div>
              </div>
            </div>
            <div className="flex gap-3 px-6 py-4 border-t border-slate-100">
              <button
                onClick={() => setShowModal(false)}
                className="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors"
              >
                Batal
              </button>
              <button className="flex-1 px-4 py-2.5 text-sm font-bold bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                Simpan Akun
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

// ─── Page: Penarikan Dana ─────────────────────────────────────────────────────
function PenarikanDana() {
  const [nominal, setNominal] = useState("");
  const adminFee = 2500;
  const parsedNominal = parseInt(nominal.replace(/\./g, "")) || 0;
  const total = parsedNominal + adminFee;

  const handleNominalChange = (raw: string) => {
    const digits = raw.replace(/\D/g, "");
    const formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    setNominal(formatted);
  };

  const statusConfig: Record<string, { label: string; cls: string }> = {
    Success: { label: "Sukses", cls: "bg-green-50 text-green-700" },
    Pending: { label: "Pending", cls: "bg-amber-50 text-amber-700" },
    Failed:  { label: "Gagal",  cls: "bg-red-50 text-red-600" },
  };

  return (
    <div className="p-6 space-y-5">
      <div>
        <h1 className="text-xl font-bold text-slate-900" style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
          Penarikan Dana
        </h1>
        <p className="text-sm text-slate-500 mt-0.5">Tarik saldo toko ke rekening bank pilihan</p>
      </div>

      {/* Balance card */}
      <div
        className="rounded-2xl p-6 text-white shadow-lg"
        style={{ background: "linear-gradient(135deg, #16a34a 0%, #059669 100%)" }}
      >
        <div className="flex items-start justify-between">
          <div>
            <p className="text-green-100 text-xs font-semibold uppercase tracking-wider">Saldo Tersedia</p>
            <p
              className="text-3xl font-bold mt-1.5"
              style={{ fontFamily: "'JetBrains Mono', monospace" }}
            >
              {fmt(2500000)}
            </p>
            <p className="text-green-200 text-xs mt-1">Diperbarui hari ini pukul 12:00</p>
          </div>
          <div className="text-right">
            <p className="text-green-200 text-xs font-semibold uppercase tracking-wider">Kuota Penarikan</p>
            <p className="text-3xl font-bold mt-1.5" style={{ fontFamily: "'JetBrains Mono', monospace" }}>
              4<span className="text-green-300 text-xl">/5</span>
            </p>
            <p className="text-green-200 text-xs mt-1">Periode Juni 2026</p>
          </div>
        </div>
        {/* Progress bar */}
        <div className="mt-5 bg-green-700/40 rounded-full h-1.5">
          <div className="bg-white/80 rounded-full h-1.5 transition-all" style={{ width: "80%" }} />
        </div>
        <p className="text-green-200 text-xs mt-1.5">4 dari 5 penarikan terpakai bulan ini</p>
      </div>

      <div className="grid grid-cols-5 gap-5">
        {/* Form — 3/5 */}
        <div className="col-span-3 bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-4">
          <h2 className="font-semibold text-slate-900">Form Penarikan</h2>

          <div>
            <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
              Nominal Penarikan (Rp)
            </label>
            <div className="relative">
              <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rp</span>
              <input
                type="text"
                value={nominal}
                onChange={(e) => handleNominalChange(e.target.value)}
                placeholder="0"
                className="w-full pl-10 pr-4 py-3.5 text-2xl font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400 placeholder-slate-200 transition-all"
                style={{ fontFamily: "'JetBrains Mono', monospace" }}
              />
            </div>
            {/* Quick amounts */}
            <div className="flex gap-2 mt-2">
              {[500000, 1000000, 2000000].map((v) => (
                <button
                  key={v}
                  onClick={() => setNominal(v.toLocaleString("id-ID"))}
                  className="flex-1 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors"
                >
                  {fmt(v)}
                </button>
              ))}
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Bank Tujuan</label>
            <div className="relative">
              <select className="w-full appearance-none px-4 py-2.5 text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/25 transition-all">
                <option>BCA</option>
                <option>Mandiri</option>
                <option>BRI</option>
                <option>BNI</option>
                <option>BSI</option>
              </select>
              <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nomor Rekening</label>
            <input
              placeholder="cth: 1234567890"
              className="w-full px-4 py-2.5 text-base text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/25 focus:border-green-400 tracking-widest transition-all"
              style={{ fontFamily: "'JetBrains Mono', monospace" }}
            />
          </div>
        </div>

        {/* Calculation + CTA — 2/5 */}
        <div className="col-span-2 space-y-4">
          <div className="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <h3 className="font-semibold text-slate-900 mb-4">Rincian Biaya</h3>
            <div className="space-y-3">
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Nominal Tarik</span>
                <span
                  className="font-semibold text-slate-800"
                  style={{ fontFamily: "'JetBrains Mono', monospace" }}
                >
                  {parsedNominal > 0 ? fmt(parsedNominal) : <span className="text-slate-300">—</span>}
                </span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-slate-500">Biaya Admin</span>
                <span
                  className="font-semibold text-slate-800"
                  style={{ fontFamily: "'JetBrains Mono', monospace" }}
                >
                  {fmt(adminFee)}
                </span>
              </div>
              <div className="border-t border-dashed border-slate-100 pt-3 flex justify-between items-baseline">
                <span className="font-semibold text-slate-900 text-sm">Total Potongan Saldo</span>
                <span
                  className="font-bold text-red-600 text-base"
                  style={{ fontFamily: "'JetBrains Mono', monospace" }}
                >
                  {parsedNominal > 0 ? fmt(total) : <span className="text-slate-300 text-sm">—</span>}
                </span>
              </div>
            </div>
          </div>

          <button className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-green-200/60 text-sm"
            style={{ fontFamily: "'Plus Jakarta Sans', sans-serif" }}>
            Proses Penarikan
          </button>

          <div className="bg-amber-50 border border-amber-100 rounded-xl p-4">
            <p className="text-xs text-amber-700 font-medium">
              ⚠️ Proses transfer membutuhkan waktu 1×24 jam kerja setelah disetujui.
            </p>
          </div>
        </div>
      </div>

      {/* Withdrawal history */}
      <div className="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div className="px-5 py-4 border-b border-slate-100">
          <h2 className="font-semibold text-slate-900">Riwayat Penarikan</h2>
        </div>
        <table className="w-full text-sm">
          <thead>
            <tr className="bg-slate-50">
              {["Tanggal", "Bank Tujuan", "Nominal", "Biaya Admin", "Status"].map((h) => (
                <th
                  key={h}
                  className="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider"
                >
                  {h}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50">
            {withdrawalHistory.map((w, i) => {
              const s = statusConfig[w.status];
              return (
                <tr key={i} className="hover:bg-slate-50/50 transition-colors">
                  <td
                    className="px-5 py-3.5 text-xs text-slate-500"
                    style={{ fontFamily: "'JetBrains Mono', monospace" }}
                  >
                    {w.date}
                  </td>
                  <td className="px-5 py-3.5 text-slate-700 font-semibold text-sm">{w.bank}</td>
                  <td
                    className="px-5 py-3.5 font-semibold text-slate-800 text-sm"
                    style={{ fontFamily: "'JetBrains Mono', monospace" }}
                  >
                    {fmt(w.nominal)}
                  </td>
                  <td
                    className="px-5 py-3.5 text-xs text-slate-500"
                    style={{ fontFamily: "'JetBrains Mono', monospace" }}
                  >
                    {fmt(w.admin)}
                  </td>
                  <td className="px-5 py-3.5">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${s.cls}`}>
                      {s.label}
                    </span>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}

// ─── Root ─────────────────────────────────────────────────────────────────────
export default function App() {
  const [page, setPage] = useState<Page>("dashboard");

  return (
    <div
      className="flex h-screen overflow-hidden"
      style={{ fontFamily: "Inter, sans-serif", background: "#f1f5f9" }}
    >
      <Sidebar active={page} setActive={setPage} />
      <main className="flex-1 overflow-y-auto">
        {page === "dashboard"  && <Dashboard setPage={setPage} />}
        {page === "transaksi"  && <CatatTransaksi />}
        {page === "rekap"      && <RekapPenjualan />}
        {page === "pengaturan" && <PengaturanToko />}
        {page === "penarikan"  && <PenarikanDana />}
      </main>
    </div>
  );
}
