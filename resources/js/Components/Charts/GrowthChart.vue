<template>
  <div class="w-full">
    <div class="flex gap-2 mb-3 overflow-x-auto">
      <button v-for="tab in tabs" :key="tab.key"
              @click="activeTab = tab.key"
              :class="['px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap flex-shrink-0',
                       activeTab === tab.key
                         ? 'bg-blue-600 text-white'
                         : 'bg-gray-100 text-gray-600']">
        {{ tab.label }}
      </button>
    </div>

    <div class="relative h-64">
      <Line :data="chartData" :options="chartOptions" />
    </div>

    <!-- Legenda -->
    <div class="flex flex-wrap gap-x-4 gap-y-2 mt-3 text-xs">
      <div class="flex items-center gap-1.5">
        <svg width="24" height="10" xmlns="http://www.w3.org/2000/svg">
          <line x1="0" y1="5" x2="24" y2="5"
                stroke="rgba(239,68,68,0.7)" stroke-width="1.5"
                stroke-dasharray="4,3"/>
        </svg>
        <span class="text-gray-500">±3 SD</span>
      </div>
      <div class="flex items-center gap-1.5">
        <svg width="24" height="10" xmlns="http://www.w3.org/2000/svg">
          <line x1="0" y1="5" x2="24" y2="5"
                stroke="rgba(249,115,22,0.8)" stroke-width="1.5"/>
        </svg>
        <span class="text-gray-500">±2 SD</span>
      </div>
      <div v-if="showPlus1" class="flex items-center gap-1.5">
        <svg width="24" height="10" xmlns="http://www.w3.org/2000/svg">
          <line x1="0" y1="5" x2="24" y2="5"
                stroke="rgba(234,179,8,0.9)" stroke-width="1.5"
                stroke-dasharray="6,3"/>
        </svg>
        <span class="text-gray-500">+1 SD</span>
      </div>
      <div class="flex items-center gap-1.5">
        <svg width="24" height="10" xmlns="http://www.w3.org/2000/svg">
          <line x1="0" y1="5" x2="24" y2="5"
                stroke="#16a34a" stroke-width="2"/>
        </svg>
        <span class="text-gray-500">Median</span>
      </div>
      <div class="flex items-center gap-1.5">
        <svg width="24" height="10" xmlns="http://www.w3.org/2000/svg">
          <line x1="0" y1="5" x2="24" y2="5"
                stroke="#2563eb" stroke-width="2"/>
          <circle cx="12" cy="5" r="3" fill="#2563eb"/>
        </svg>
        <span class="text-gray-500">Data balita</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  LinearScale, PointElement,
  LineElement, Title, Tooltip, Legend, Filler
} from 'chart.js'

ChartJS.register(
  LinearScale, PointElement,
  LineElement, Title, Tooltip, Legend, Filler
)

const props = defineProps({
  pengukuran: Array,
  curveBbU:   Array,
  curveTbU:   Array,
  curveBbTb:  Array,
  curveImtU:  Array,
  gender:     String,
})

const activeTab = ref('bb_u')

const tabs = [
  { key: 'bb_u',     label: 'BB/U' },
  { key: 'tb_u',     label: 'TB/U' },
  { key: 'bb_tb',    label: 'BB/TB' },
  { key: 'imt_u',    label: 'IMT/U' },
  { key: 'trend',    label: 'Tren BB' },
  { key: 'trend_tb', label: 'Tren TB' },
]

// Tabs yang menampilkan garis +1 SD (batas klasifikasi Permenkes 2020)
const showPlus1 = computed(() => ['bb_u', 'bb_tb', 'imt_u'].includes(activeTab.value))

function curveXY(curveData, field) {
  return curveData.map(r => ({ x: Number(r.parameter), y: r[field] }))
}

const chartData = computed(() => {
  const sorted = [...props.pengukuran].sort((a, b) => a.umur_bulan - b.umur_bulan)

  if (activeTab.value === 'trend') {
    return {
      datasets: [{
        label: 'Berat Badan (kg)',
        data: sorted.map(p => ({ x: Number(p.umur_bulan), y: p.berat_badan_kg })),
        borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)',
        pointBackgroundColor: '#2563eb', pointRadius: 5,
        tension: 0.3, fill: true, showLine: true,
      }]
    }
  }

  if (activeTab.value === 'trend_tb') {
    return {
      datasets: [{
        label: 'Tinggi Badan (cm)',
        data: sorted.map(p => ({ x: Number(p.umur_bulan), y: p.tinggi_badan_cm })),
        borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,0.1)',
        pointBackgroundColor: '#7c3aed', pointRadius: 5,
        tension: 0.3, fill: true, showLine: true,
      }]
    }
  }

  // BB/TB — sumbu X adalah tinggi badan (cm), bukan umur
  if (activeTab.value === 'bb_tb') {
    const balitaXY = sorted
      .filter(p => p.tinggi_badan_cm != null)
      .map(p => ({ x: Number(p.tinggi_badan_cm), y: p.berat_badan_kg }))

    return {
      datasets: [
        {
          label: '-3 SD', data: curveXY(props.curveBbTb, 'sd_minus3'),
          borderColor: 'rgba(239,68,68,0.6)', borderDash: [5,3],
          borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
        },
        {
          label: '-2 SD', data: curveXY(props.curveBbTb, 'sd_minus2'),
          borderColor: 'rgba(249,115,22,0.8)',
          borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
        },
        {
          label: 'Median', data: curveXY(props.curveBbTb, 'median_val'),
          borderColor: '#16a34a',
          borderWidth: 2, pointRadius: 0, fill: false, showLine: true,
        },
        {
          label: '+1 SD', data: curveXY(props.curveBbTb, 'sd_plus1'),
          borderColor: 'rgba(234,179,8,0.9)', borderDash: [6,3],
          borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
        },
        {
          label: '+2 SD', data: curveXY(props.curveBbTb, 'sd_plus2'),
          borderColor: 'rgba(249,115,22,0.8)',
          borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
        },
        {
          label: '+3 SD', data: curveXY(props.curveBbTb, 'sd_plus3'),
          borderColor: 'rgba(239,68,68,0.6)', borderDash: [5,3],
          borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
        },
        {
          label: 'Data Balita',
          data: balitaXY,
          borderColor: '#2563eb', backgroundColor: '#2563eb',
          pointRadius: 5, pointHoverRadius: 7,
          borderWidth: 2, tension: 0.2, fill: false, showLine: true, order: 0,
        },
      ]
    }
  }

  // IMT/U — sumbu X = umur, Y = nilai IMT
  if (activeTab.value === 'imt_u') {
    const balitaXY = sorted
      .filter(p => p.imt_u != null)
      .map(p => ({ x: Number(p.umur_bulan), y: p.imt_u }))

    const datasets = [
      {
        label: '-3 SD', data: curveXY(props.curveImtU, 'sd_minus3'),
        borderColor: 'rgba(239,68,68,0.6)', borderDash: [5,3],
        borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
      },
      {
        label: '-2 SD', data: curveXY(props.curveImtU, 'sd_minus2'),
        borderColor: 'rgba(249,115,22,0.8)',
        borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
      },
      {
        label: 'Median', data: curveXY(props.curveImtU, 'median_val'),
        borderColor: '#16a34a',
        borderWidth: 2, pointRadius: 0, fill: false, showLine: true,
      },
      {
        label: '+1 SD', data: curveXY(props.curveImtU, 'sd_plus1'),
        borderColor: 'rgba(234,179,8,0.9)', borderDash: [6,3],
        borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
      },
      {
        label: '+2 SD', data: curveXY(props.curveImtU, 'sd_plus2'),
        borderColor: 'rgba(249,115,22,0.8)',
        borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
      },
      {
        label: '+3 SD', data: curveXY(props.curveImtU, 'sd_plus3'),
        borderColor: 'rgba(239,68,68,0.6)', borderDash: [5,3],
        borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
      },
      {
        label: 'Data Balita',
        data: balitaXY,
        borderColor: '#2563eb', backgroundColor: '#2563eb',
        pointRadius: 5, pointHoverRadius: 7,
        borderWidth: 2, tension: 0.2, fill: false, showLine: true, order: 0,
      },
    ]
    return { datasets }
  }

  // BB/U atau TB/U
  const curveData  = activeTab.value === 'bb_u' ? props.curveBbU : props.curveTbU
  const dataKey    = activeTab.value === 'bb_u' ? 'berat_badan_kg' : 'tinggi_badan_cm'
  const balitaXY   = sorted.map(p => ({ x: Number(p.umur_bulan), y: p[dataKey] }))

  const datasets = [
    {
      label: '-3 SD', data: curveXY(curveData, 'sd_minus3'),
      borderColor: 'rgba(239,68,68,0.6)', borderDash: [5,3],
      borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
    },
    {
      label: '-2 SD', data: curveXY(curveData, 'sd_minus2'),
      borderColor: 'rgba(249,115,22,0.8)',
      borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
    },
    {
      label: 'Median', data: curveXY(curveData, 'median_val'),
      borderColor: '#16a34a',
      borderWidth: 2, pointRadius: 0, fill: false, showLine: true,
    },
    {
      label: '+2 SD', data: curveXY(curveData, 'sd_plus2'),
      borderColor: 'rgba(249,115,22,0.8)',
      borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
    },
    {
      label: '+3 SD', data: curveXY(curveData, 'sd_plus3'),
      borderColor: 'rgba(239,68,68,0.6)', borderDash: [5,3],
      borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
    },
    {
      label: 'Data Balita',
      data: balitaXY,
      borderColor: '#2563eb', backgroundColor: '#2563eb',
      pointRadius: 5, pointHoverRadius: 7,
      borderWidth: 2, tension: 0.2, fill: false, showLine: true, order: 0,
    },
  ]

  // Tambah +1 SD hanya untuk BB/U (batas Permenkes 2020: Gizi Baik ≤ +1 SD)
  if (activeTab.value === 'bb_u') {
    datasets.splice(3, 0, {
      label: '+1 SD', data: curveXY(curveData, 'sd_plus1'),
      borderColor: 'rgba(234,179,8,0.9)', borderDash: [6,3],
      borderWidth: 1.5, pointRadius: 0, fill: false, showLine: true,
    })
  }

  return { datasets }
})

const chartOptions = computed(() => {
  const isBbTb    = activeTab.value === 'bb_tb'
  const isImtU    = activeTab.value === 'imt_u'
  const isTbUnit  = ['tb_u', 'trend_tb'].includes(activeTab.value)

  return {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          title: (items) => {
            const x = items[0].parsed.x
            return isBbTb ? `Tinggi ${x} cm` : `${x} bulan`
          },
          label: (ctx) => {
            const unit = isTbUnit ? ' cm' : isImtU ? ' kg/m²' : ' kg'
            return `${ctx.dataset.label}: ${ctx.parsed.y}${unit}`
          }
        }
      }
    },
    scales: {
      x: {
        type: 'linear',
        title: {
          display: true,
          text: isBbTb ? 'Tinggi Badan (cm)' : 'Usia (bulan)',
          font: { size: 10 }
        },
        ticks: {
          font: { size: 10 },
          callback: (v) => isBbTb ? `${v} cm` : `${v} bln`
        },
        grid: { display: false },
      },
      y: {
        grid: { color: 'rgba(0,0,0,0.05)' },
        ticks: { font: { size: 10 } },
        title: {
          display: true,
          text: isImtU ? 'IMT (kg/m²)' : isBbTb || !isTbUnit ? 'Berat (kg)' : 'Tinggi (cm)',
          font: { size: 10 }
        }
      }
    }
  }
})
</script>
