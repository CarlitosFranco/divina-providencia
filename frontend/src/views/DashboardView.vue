<template>
  <div class="dashboard-container">
    <h1>📊 Panel de Control</h1>

    <!-- Tarjetas de resumen -->
    <div class="cards-grid">
      <div class="card-resumen">
        <div class="card-icon">👥</div>
        <div class="card-info">
          <h3>Pacientes Activos</h3>
          <p class="valor">{{ resumen.pacientes_activos }}</p>
        </div>
      </div>
      <div class="card-resumen">
        <div class="card-icon">👩‍⚕️</div>
        <div class="card-info">
          <h3>Personal Activo</h3>
          <p class="valor">{{ resumen.personal_activo }}</p>
        </div>
      </div>
      <div class="card-resumen">
        <div class="card-icon">⏰</div>
        <div class="card-info">
          <h3>Turnos Hoy</h3>
          <p class="valor">{{ resumen.turnos_hoy }}</p>
        </div>
      </div>
      <div class="card-resumen">
        <div class="card-icon">📅</div>
        <div class="card-info">
          <h3>Citas Hoy</h3>
          <p class="valor">{{ resumen.citas_hoy }}</p>
        </div>
      </div>
      <div class="card-resumen">
        <div class="card-icon">✅</div>
        <div class="card-info">
          <h3>Asistencias Hoy</h3>
          <p class="valor">{{ resumen.asistencias_hoy }}</p>
        </div>
      </div>
    </div>

    <!-- Gráficos -->
    <div class="charts-row">
      <div class="chart-box">
        <h3>Pacientes por Estado</h3>
        <canvas id="chartPacientesEstado"></canvas>
      </div>
      <div class="chart-box">
        <h3>Asistencias por Mes</h3>
        <canvas id="chartAsistenciasMes"></canvas>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'

const resumen = ref({
  pacientes_activos: 0,
  personal_activo: 0,
  turnos_hoy: 0,
  citas_hoy: 0,
  asistencias_hoy: 0
})

let chartPacientes = null
let chartAsistencias = null

const cargarResumen = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/dashboard/resumen', {
      headers: { Authorization: `Bearer ${token}` }
    })
    resumen.value = response.data
  } catch (err) {
    console.error(err)
  }
}

const cargarGraficoPacientes = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/dashboard/pacientes-estado', {
      headers: { Authorization: `Bearer ${token}` }
    })
    const data = response.data
    const labels = data.map(item => item.estado)
    const valores = data.map(item => item.cantidad)
    if (chartPacientes) chartPacientes.destroy()
    const ctx = document.getElementById('chartPacientesEstado').getContext('2d')
    chartPacientes = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: valores,
          backgroundColor: ['#4caf50', '#ff9800', '#f44336', '#9e9e9e']
        }]
      }
    })
  } catch (err) {
    console.error(err)
  }
}

const cargarGraficoAsistencias = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/dashboard/asistencias-mes', {
      headers: { Authorization: `Bearer ${token}` }
    })
    const { labels, data } = response.data
    if (chartAsistencias) chartAsistencias.destroy()
    const ctx = document.getElementById('chartAsistenciasMes').getContext('2d')
    chartAsistencias = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Asistencias',
          data: data,
          borderColor: '#42a5f5',
          backgroundColor: 'rgba(66,165,245,0.1)',
          tension: 0.3,
          fill: true
        }]
      }
    })
  } catch (err) {
    console.error(err)
  }
}

onMounted(() => {
  cargarResumen()
  cargarGraficoPacientes()
  cargarGraficoAsistencias()
})
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
  background: #f0f2f5;
  min-height: 100vh;
}
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}
.card-resumen {
  background: white;
  border-radius: 20px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.card-icon {
  font-size: 2.5rem;
}
.card-info h3 {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 8px;
}
.card-info .valor {
  font-size: 1.8rem;
  font-weight: 600;
  color: #1e293b;
}
.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 20px;
}
.chart-box {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.chart-box h3 {
  margin-bottom: 20px;
  color: #334155;
}
@media (max-width: 768px) {
  .charts-row {
    grid-template-columns: 1fr;
  }
}
</style>
