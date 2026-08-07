<template>
  <AppLayout>
    <div class="p-5 space-y-5 max-w-screen-xl mx-auto">

      <!-- ══ WELCOME BANNER ══ -->
      <div :class="['rounded-2xl p-5 text-white shadow-md', bannerBg]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-xl font-bold">{{ headerJudul }}</h1>
            <p class="text-white/70 text-sm mt-0.5">{{ subJudul }}</p>
          </div>
          <!-- Tanggal hari ini di kanan banner -->
          <div class="text-right shrink-0">
            <p class="text-lg font-semibold">{{ tanggalLengkap }}</p>
          </div>
        </div>
      </div>

      <!-- ══ KARTU RINGKASAN ══ -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

        <!-- Total Balita -->
        <Link href="/balita"
              class="bg-white rounded-xl px-3 py-2.5 shadow-sm border-l-4 border-blue-500 hover:shadow-md transition group flex items-center gap-3">
          <span class="text-lg shrink-0">👶</span>
          <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide leading-none mb-0.5">Total Balita</p>
            <p class="text-2xl font-bold text-slate-800 group-hover:text-blue-600 transition leading-none">{{ statistik.total_balita }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Terdaftar</p>
          </div>
        </Link>

        <!-- Belum Diukur -->
        <Link :href="balitaUrl({ belum_diukur: '1' })"
              class="bg-white rounded-xl px-3 py-2.5 shadow-sm border-l-4 border-slate-300 hover:shadow-md transition group flex items-center gap-3">
          <span class="text-lg shrink-0">📋</span>
          <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide leading-none mb-0.5">Belum Diukur</p>
            <p class="text-2xl font-bold leading-none transition group-hover:text-slate-600"
               :class="statistik.belum_diukur > 0 ? 'text-slate-700' : 'text-slate-300'">{{ statistik.belum_diukur }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Bulan ini</p>
          </div>
        </Link>

        <!-- Double Burden -->
        <Link :href="balitaUrl({ double_burden: '1' })"
              class="bg-white rounded-xl px-3 py-2.5 shadow-sm border-l-4 border-red-400 hover:shadow-md transition group flex items-center gap-3">
          <span class="text-lg shrink-0">⚖️</span>
          <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide leading-none mb-0.5">Double Burden</p>
            <p class="text-2xl font-bold leading-none transition group-hover:text-red-600"
               :class="statistik.double_burden > 0 ? 'text-red-600' : 'text-slate-300'">{{ statistik.double_burden }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Wasting + Stunting</p>
          </div>
        </Link>

        <!-- KBB Tidak Naik -->
        <Link :href="balitaUrl({ status_kbb: ['T', 'O'] })"
              class="bg-white rounded-xl px-3 py-2.5 shadow-sm border-l-4 border-orange-400 hover:shadow-md transition group flex items-center gap-3">
          <span class="text-lg shrink-0">📉</span>
          <div class="min-w-0">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide leading-none mb-0.5">KBB Tdk Naik</p>
            <p class="text-2xl font-bold leading-none group-hover:text-orange-700 transition"
               :class="(statistik.kbb_turun + statistik.kbb_kurang) > 0 ? 'text-orange-600' : 'text-slate-300'">
              {{ (statistik.kbb_turun ?? 0) + (statistik.kbb_kurang ?? 0) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5">T + O bulan ini</p>
          </div>
        </Link>
      </div>

      <!-- ══ KONTEN UTAMA: 2 KOLOM ══ -->
      <div class="flex flex-col lg:flex-row gap-5 items-start">

        <!-- Kolom kanan -->
        <div class="w-full lg:w-72 space-y-4 lg:sticky lg:top-5 lg:order-2">

          <!-- Usulan Nonaktif -->
          <div v-if="usulanMenunggu?.length"
               class="bg-white rounded-xl shadow-sm overflow-hidden border-t-4 border-orange-400">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-2">
              <span class="font-semibold text-sm text-slate-700">Usulan Nonaktif</span>
              <span class="bg-orange-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">
                {{ usulanMenunggu.length }}
              </span>
            </div>
            <div class="divide-y divide-slate-100">
              <Link v-for="u in usulanMenunggu" :key="u.id"
                    :href="`/balita/${u.balita_id}`"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-orange-50 transition">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-sm font-bold text-orange-600 shrink-0">
                  {{ u.balita_nama.charAt(0) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-slate-800 truncate">{{ u.balita_nama }}</p>
                  <p class="text-xs text-slate-400 truncate">{{ u.alasan }}</p>
                </div>
                <span class="text-orange-400 text-xs shrink-0">→</span>
              </Link>
            </div>
          </div>

          <!-- Jadwal Posyandu -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
              <div>
                <h3 class="font-semibold text-slate-700 text-sm">Jadwal Posyandu</h3>
                <p class="text-xs text-slate-400">Agenda terdekat</p>
              </div>
              <Link href="/sesi" class="text-xs text-blue-600 font-medium hover:underline">Semua →</Link>
            </div>
            <div v-if="sesiMendatang.length === 0" class="px-4 py-4 text-center text-slate-400 text-sm">
              <p>Belum ada sesi terjadwal</p>
              <div v-if="jadwalRuntin?.length" class="mt-2 text-left bg-amber-50 border border-amber-200 rounded-lg p-2.5 space-y-1">
                <p class="text-xs font-semibold text-amber-700 mb-1">📌 Jadwal rutin:</p>
                <div v-for="j in jadwalRuntin" :key="j.nama" class="text-xs text-amber-800">
                  <span class="font-medium">{{ j.nama }}</span> —
                  <span v-if="j.jadwal_minggu">Minggu ke-{{ j.jadwal_minggu }}; </span>{{ j.jadwal_hari }}: {{ j.jadwal_jam }}
                </div>
              </div>
              <Link href="/sesi" class="mt-2 inline-block text-blue-600 text-xs font-medium">+ Buat Sesi →</Link>
            </div>
            <div class="divide-y divide-slate-100">
              <div v-for="s in sesiMendatang" :key="s.id"
                   class="px-4 py-2.5 flex items-center gap-3">
                <div :class="['w-9 text-center shrink-0 rounded-lg py-1',
                              s.is_today ? 'bg-green-600 text-white' : 'bg-blue-600 text-white']">
                  <p class="text-[10px] font-medium">{{ s.hari?.substring(0,3) }}</p>
                  <p class="text-sm font-bold leading-tight">{{ s.tanggal?.split(' ')[0] }}</p>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-slate-800">{{ s.tema || 'Posyandu Rutin' }}</p>
                  <p class="text-[10px] text-slate-400">{{ s.posyandu_nama }}</p>
                </div>
                <span :class="s.is_today ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                      class="text-[10px] px-1.5 py-0.5 rounded-full font-medium shrink-0">
                  {{ s.is_today ? 'Hari ini' : 'Terjadwal' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Balita Prioritas Intervensi -->
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
              <h3 class="font-semibold text-sm text-slate-700">Balita Prioritas Intervensi</h3>
              <span class="text-xs text-slate-400">{{ balitaPerhatian.length }}</span>
            </div>

            <div v-if="balitaPerhatian.length === 0" class="px-4 py-8 text-center text-slate-400 text-sm">
              <div class="text-2xl mb-2">🎉</div>
              Semua balita kondisi baik
            </div>

            <div class="divide-y divide-slate-100">
              <Link v-for="b in balitaPerhatian" :key="b.id"
                    :href="`/balita/${b.id}`"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition group">
                <!-- Avatar inisial berwarna -->
                <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0',
                              b.jenis_kelamin === 'L' ? 'bg-blue-500' : 'bg-pink-500']">
                  {{ b.nama.charAt(0) }}
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-700 truncate">
                    {{ b.nama }}
                  </p>
                  <p class="text-xs text-slate-400">
                    {{ b.posyandu }} · {{ b.umur_bulan }} bulan
                  </p>
                </div>
                <!-- Badge status kondisi aktual -->
                <span :class="badgeKondisi(b).cls"
                      class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0">
                  {{ badgeKondisi(b).label }}
                </span>
              </Link>
            </div>

            <div v-if="balitaPerhatian.length > 0"
                 class="px-4 py-2.5 border-t border-slate-100">
              <Link href="/balita"
                    class="block text-center text-xs text-blue-600 font-medium hover:underline">
                Lihat semua balita →
              </Link>
            </div>
          </div>

        </div><!-- /kolom kanan -->
        <!-- Kolom kiri -->
        <div class="flex-1 space-y-4 min-w-0 lg:order-1">


          <!-- ══ 5 STAT CARDS COMPACT ══ -->
          <!-- BB/U -->
          <div class="bg-white rounded-xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-xs font-bold text-slate-700">Status Gizi</span>
                <span class="text-xs text-slate-400 ml-1">BB/U</span>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-1.5 mb-2">
              <Link :href="balitaUrl({ status_gizi: 'GIZI_BURUK' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-600 leading-none">{{ statistik.gizi_buruk }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Sangat<br>Kurang</p>
              </Link>
              <Link :href="balitaUrl({ status_gizi: 'GIZI_KURANG' })"
                    class="text-center py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 transition">
                <p class="text-base font-bold text-orange-500 leading-none">{{ statistik.gizi_kurang }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Kurang</p>
              </Link>
              <Link :href="balitaUrl({ status_gizi: 'GIZI_BAIK' })"
                    class="text-center py-1.5 rounded-lg bg-green-50 hover:bg-green-100 transition">
                <p class="text-base font-bold text-green-600 leading-none">{{ statistik.gizi_baik }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Normal</p>
              </Link>
              <Link :href="balitaUrl({ status_gizi: 'RISIKO_LEBIH' })"
                    class="text-center py-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 transition">
                <p class="text-base font-bold text-yellow-600 leading-none">{{ statistik.risiko_lebih }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Risiko<br>Lebih</p>
              </Link>
            </div>
            <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
              <div class="bg-red-500"    :style="`width:${pct(statistik.gizi_buruk)}%`" />
              <div class="bg-orange-400" :style="`width:${pct(statistik.gizi_kurang)}%`" />
              <div class="bg-green-400"  :style="`width:${pct(statistik.gizi_baik)}%`" />
              <div class="bg-yellow-300" :style="`width:${pct(statistik.risiko_lebih)}%`" />
            </div>
          </div>

          <!-- TB/U -->
          <div class="bg-white rounded-xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-xs font-bold text-slate-700">Stunting</span>
                <span class="text-xs text-slate-400 ml-1">TB/U</span>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-1.5 mb-2">
              <Link :href="balitaUrl({ status_stunting: 'SANGAT_PENDEK' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-600 leading-none">{{ statistik.stunting_sangat_pendek }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Sangat<br>Pendek</p>
              </Link>
              <Link :href="balitaUrl({ status_stunting: 'PENDEK' })"
                    class="text-center py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 transition">
                <p class="text-base font-bold text-orange-500 leading-none">{{ statistik.stunting_pendek }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Pendek</p>
              </Link>
              <Link :href="balitaUrl({ status_stunting: 'NORMAL' })"
                    class="text-center py-1.5 rounded-lg bg-green-50 hover:bg-green-100 transition">
                <p class="text-base font-bold text-green-600 leading-none">{{ statistik.stunting_normal }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Normal</p>
              </Link>
              <Link :href="balitaUrl({ status_stunting: 'TINGGI' })"
                    class="text-center py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 transition">
                <p class="text-base font-bold text-blue-500 leading-none">{{ statistik.stunting_tinggi }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-tight">Tinggi</p>
              </Link>
            </div>
            <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
              <div class="bg-red-500"    :style="`width:${pctS(statistik.stunting_sangat_pendek)}%`" />
              <div class="bg-orange-400" :style="`width:${pctS(statistik.stunting_pendek)}%`" />
              <div class="bg-green-400"  :style="`width:${pctS(statistik.stunting_normal)}%`" />
              <div class="bg-blue-400"   :style="`width:${pctS(statistik.stunting_tinggi)}%`" />
            </div>
          </div>

          <!-- BB/TB (Wasting) -->
          <div class="bg-white rounded-xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-xs font-bold text-slate-700">Wasting</span>
                <span class="text-xs text-slate-400 ml-1">BB/TB</span>
              </div>
            </div>
            <div class="grid grid-cols-6 gap-1 mb-2">
              <Link :href="balitaUrl({ status_wasting: 'SANGAT_KURUS' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-600 leading-none">{{ statistik.wasting_sangat_kurus }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Sgt<br>Kurus</p>
              </Link>
              <Link :href="balitaUrl({ status_wasting: 'KURUS' })"
                    class="text-center py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 transition">
                <p class="text-base font-bold text-orange-500 leading-none">{{ statistik.wasting_kurus }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Kurus</p>
              </Link>
              <Link :href="balitaUrl({ status_wasting: 'NORMAL' })"
                    class="text-center py-1.5 rounded-lg bg-green-50 hover:bg-green-100 transition">
                <p class="text-base font-bold text-green-600 leading-none">{{ statistik.wasting_normal }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Normal</p>
              </Link>
              <Link :href="balitaUrl({ status_wasting: 'BERISIKO_GEMUK' })"
                    class="text-center py-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 transition">
                <p class="text-base font-bold text-yellow-600 leading-none">{{ statistik.wasting_berisiko_gemuk }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Brs<br>Gemuk</p>
              </Link>
              <Link :href="balitaUrl({ status_wasting: 'GEMUK' })"
                    class="text-center py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 transition">
                <p class="text-base font-bold text-orange-500 leading-none">{{ statistik.wasting_gemuk }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Gemuk</p>
              </Link>
              <Link :href="balitaUrl({ status_wasting: 'OBESITAS' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-700 leading-none">{{ statistik.obesitas_wasting }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Obst</p>
              </Link>
            </div>
            <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
              <div class="bg-red-500"    :style="`width:${pctW(statistik.wasting_sangat_kurus)}%`" />
              <div class="bg-orange-400" :style="`width:${pctW(statistik.wasting_kurus)}%`" />
              <div class="bg-green-400"  :style="`width:${pctW(statistik.wasting_normal)}%`" />
              <div class="bg-yellow-300" :style="`width:${pctW(statistik.wasting_berisiko_gemuk)}%`" />
              <div class="bg-orange-400" :style="`width:${pctW(statistik.wasting_gemuk)}%`" />
              <div class="bg-red-700"    :style="`width:${pctW(statistik.obesitas_wasting)}%`" />
            </div>
          </div>

          <!-- IMT/U -->
          <div class="bg-white rounded-xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-xs font-bold text-slate-700">Gizi (IMT)</span>
                <span class="text-xs text-slate-400 ml-1">IMT/U</span>
              </div>
            </div>
            <div class="grid grid-cols-6 gap-1 mb-2">
              <Link :href="balitaUrl({ status_imt_u: 'SANGAT_KURUS' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-600 leading-none">{{ statistik.imt_sangat_kurus }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Gizi<br>Buruk</p>
              </Link>
              <Link :href="balitaUrl({ status_imt_u: 'KURUS' })"
                    class="text-center py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 transition">
                <p class="text-base font-bold text-orange-500 leading-none">{{ statistik.imt_kurus }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Kurang</p>
              </Link>
              <Link :href="balitaUrl({ status_imt_u: 'NORMAL' })"
                    class="text-center py-1.5 rounded-lg bg-green-50 hover:bg-green-100 transition">
                <p class="text-base font-bold text-green-600 leading-none">{{ statistik.imt_normal }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Baik</p>
              </Link>
              <Link :href="balitaUrl({ status_imt_u: 'BERISIKO_GEMUK' })"
                    class="text-center py-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 transition">
                <p class="text-base font-bold text-yellow-600 leading-none">{{ statistik.imt_berisiko_gemuk }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Brs<br>Lebih</p>
              </Link>
              <Link :href="balitaUrl({ status_imt_u: 'GEMUK' })"
                    class="text-center py-1.5 rounded-lg bg-orange-50 hover:bg-orange-100 transition">
                <p class="text-base font-bold text-orange-500 leading-none">{{ statistik.imt_gemuk }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Lebih</p>
              </Link>
              <Link :href="balitaUrl({ status_imt_u: 'OBESITAS' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-700 leading-none">{{ statistik.imt_obesitas }}</p>
                <p class="text-[9px] text-slate-500 mt-0.5 leading-tight">Obst</p>
              </Link>
            </div>
            <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
              <div class="bg-red-500"    :style="`width:${pctW(statistik.imt_sangat_kurus)}%`" />
              <div class="bg-orange-400" :style="`width:${pctW(statistik.imt_kurus)}%`" />
              <div class="bg-green-400"  :style="`width:${pctW(statistik.imt_normal)}%`" />
              <div class="bg-yellow-300" :style="`width:${pctW(statistik.imt_berisiko_gemuk)}%`" />
              <div class="bg-orange-400" :style="`width:${pctW(statistik.imt_gemuk)}%`" />
              <div class="bg-red-700"    :style="`width:${pctW(statistik.imt_obesitas)}%`" />
            </div>
          </div>

          <!-- KBB -->
          <div class="bg-white rounded-xl shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
              <div>
                <span class="text-xs font-bold text-slate-700">Kenaikan BB</span>
                <span class="text-xs text-slate-400 ml-1">KBB</span>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-1.5 mb-2">
              <Link :href="balitaUrl({ status_kbb: 'N' })"
                    class="text-center py-1.5 rounded-lg bg-green-50 hover:bg-green-100 transition">
                <p class="text-base font-bold text-green-600 leading-none">{{ statistik.kbb_naik }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Naik (N)</p>
              </Link>
              <Link :href="balitaUrl({ status_kbb: 'T' })"
                    class="text-center py-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 transition">
                <p class="text-base font-bold text-yellow-600 leading-none">{{ statistik.kbb_kurang }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Kurang (T)</p>
              </Link>
              <Link :href="balitaUrl({ status_kbb: 'O' })"
                    class="text-center py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition">
                <p class="text-base font-bold text-red-600 leading-none">{{ statistik.kbb_turun }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Tdk Naik (O)</p>
              </Link>
            </div>
            <div class="flex rounded-full overflow-hidden h-1.5 bg-slate-100">
              <div class="bg-green-500"  :style="`width:${Math.round(((statistik.kbb_naik??0)/((statistik.kbb_naik??0)+(statistik.kbb_kurang??0)+(statistik.kbb_turun??0)||1))*100)}%`" />
              <div class="bg-yellow-400" :style="`width:${Math.round(((statistik.kbb_kurang??0)/((statistik.kbb_naik??0)+(statistik.kbb_kurang??0)+(statistik.kbb_turun??0)||1))*100)}%`" />
              <div class="bg-red-500"    :style="`width:${Math.round(((statistik.kbb_turun??0)/((statistik.kbb_naik??0)+(statistik.kbb_kurang??0)+(statistik.kbb_turun??0)||1))*100)}%`" />
            </div>
          </div>

        </div><!-- /kolom kiri -->
      </div><!-- /2 kolom -->

    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Components/UI/AppLayout.vue'
import dayjs from 'dayjs'
import 'dayjs/locale/id'

dayjs.locale('id')

const props = defineProps({
  statistik:            Object,
  balitaPerhatian:      Array,
  sesiMendatang:        Array,
  ringkasanPerPosyandu: Array,
  usulanMenunggu:       Array,
  jadwalRuntin:         Array,
})

const page    = usePage()
const role    = computed(() => page.props.auth.user.role)
const headerJudul = computed(() => {
  if (role.value === 'kader') {
    const nama = page.props.auth.user.posyandu_nama
    return nama ? `Dashboard ${nama}` : 'Dashboard Kader'
  }
  return { admin: 'Dashboard Admin', petugas: 'Dashboard Petugas', nakes: 'Dashboard Nakes' }[role.value] ?? 'Dashboard'
})

const tanggalLengkap = computed(() => dayjs().format('dddd, D MMMM YYYY'))

const bannerBg = computed(() => ({
  admin:   'bg-gradient-to-r from-purple-700 to-purple-900',
  petugas: 'bg-gradient-to-r from-blue-700 to-blue-900',
  nakes:   'bg-gradient-to-r from-emerald-600 to-emerald-800',
  kader:   'bg-gradient-to-r from-blue-600 to-blue-800',
}[role.value] ?? 'bg-gradient-to-r from-blue-600 to-blue-800'))

const subJudul = computed(() => {
  if (role.value === 'kader') {
    const nama = page.props.auth.user.posyandu_nama
    return `Pantau kondisi balita ${nama ?? ''} · ${dayjs().format('MMMM YYYY')}`
  }
  return `Pantau kondisi gizi balita di semua posyandu · ${dayjs().format('MMMM YYYY')}`
})

const stuntingTotal   = computed(() =>
  (props.statistik.stunting_sangat_pendek ?? 0) + (props.statistik.stunting_pendek ?? 0)
)
const giziKurangTotal = computed(() =>
  (props.statistik.gizi_buruk ?? 0) + (props.statistik.gizi_kurang ?? 0)
)

function badgeKondisi(b) {
  if (b.status_stunting === 'SANGAT_PENDEK')
    return { label: 'Sangat Pendek', cls: 'bg-red-100 text-red-700' }
  if (b.status_stunting === 'PENDEK')
    return { label: 'Stunting', cls: 'bg-red-100 text-red-700' }
  if (b.status_gizi === 'GIZI_BURUK')
    return { label: 'Gizi Buruk', cls: 'bg-red-100 text-red-700' }
  if (b.status_gizi === 'GIZI_KURANG')
    return { label: 'Gizi Kurang', cls: 'bg-orange-100 text-orange-700' }
  return { label: 'Perhatian', cls: 'bg-yellow-100 text-yellow-700' }
}

function balitaUrl(params) {
  const qs = new URLSearchParams()
  for (const [key, val] of Object.entries(params)) {
    if (Array.isArray(val)) {
      val.forEach(v => qs.append(key + '[]', v))
    } else {
      qs.append(key, val)
    }
  }
  return '/balita?' + qs.toString()
}

function pct(val) {
  const total = props.statistik.total_balita
  if (!total) return 0
  return Math.round((val / total) * 100)
}

function pctS(val) {
  const total = (props.statistik.stunting_sangat_pendek ?? 0)
    + (props.statistik.stunting_pendek ?? 0)
    + (props.statistik.stunting_normal ?? 0)
    + (props.statistik.stunting_tinggi ?? 0)
  if (!total) return 0
  return Math.round((val / total) * 100)
}

function pctW(val) {
  const total = (props.statistik.wasting_sangat_kurus ?? 0)
    + (props.statistik.wasting_kurus ?? 0)
    + (props.statistik.wasting_normal ?? 0)
    + (props.statistik.wasting_berisiko_gemuk ?? 0)
    + (props.statistik.wasting_gemuk ?? 0)
    + (props.statistik.obesitas_wasting ?? 0)
  if (!total) return 0
  return Math.round((val / total) * 100)
}
</script>
