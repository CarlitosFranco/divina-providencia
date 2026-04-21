<template>
  <div class="container">
    <div class="page-header">
      <h1>📊 Reporte de Asistencias del Personal</h1>
    </div>

    <!-- Filtros -->
    <div class="filtros">
      <div class="filtro-group">
        <label>Empleado:</label>
        <select v-model="filtros.personal_id">
          <option :value="null">Todos</option>
          <option v-for="emp in personalList" :key="emp.id" :value="emp.id">
            {{ emp.nombres }} {{ emp.apellidos }}
          </option>
        </select>
      </div>
      <div class="filtro-group">
        <label>Desde:</label>
        <input type="date" v-model="filtros.fecha_inicio" />
      </div>
      <div class="filtro-group">
        <label>Hasta:</label>
        <input type="date" v-model="filtros.fecha_fin" />
      </div>
      <button @click="cargarReporte" class="btn-filtrar">Filtrar</button>
      <button @click="exportarPDF" class="btn-pdf">📄 Exportar PDF</button>
    </div>

    <div v-if="cargando" class="loading-state">Cargando...</div>
    <div v-else-if="error" class="error-state">{{ errorMessage }}</div>
    <div v-else>
      <div class="table-responsive">
        <table class="reporte-table">
          <thead>
            <tr>
              <th>Empleado</th>
              <th>Fecha</th>
              <th>Entrada</th>
              <th>Salida</th>
              <th>Horas trabajadas</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in asistencias" :key="a.id">
              <td>{{ a.nombres }} {{ a.apellidos }}</td>
              <td>{{ formatearFecha(a.fecha) }}</td>
              <td>{{ a.hora_entrada || '—' }}</td>
              <td>{{ a.hora_salida || '—' }}</td>
              <td>{{ a.horas_trabajadas ? a.horas_trabajadas + ' h' : '—' }}</td>
            </tr>
            <tr v-if="asistencias.length === 0">
              <td colspan="5" class="text-center">No hay registros en el período seleccionado.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Resumen de totales por empleado -->
      <div v-if="resumen.length > 0" class="resumen">
        <h3>Resumen por empleado</h3>
        <div class="resumen-cards">
          <div v-for="emp in resumen" :key="emp.personal_id" class="resumen-card">
            <strong>{{ emp.nombres }} {{ emp.apellidos }}</strong>
            <span>Horas totales: {{ emp.total_horas }} h</span>
            <span>Días trabajados: {{ emp.dias_trabajados }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const asistencias = ref([])
const resumen = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const personalList = ref([])

const filtros = ref({
  personal_id: null,
  fecha_inicio: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10),
  fecha_fin: new Date().toISOString().slice(0, 10)
})

const formatearFecha = (fecha) => {
  if (!fecha) return ''
  const [y, m, d] = fecha.split('-')
  return `${d}/${m}/${y}`
}

const cargarPersonal = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/personal', {
      headers: { Authorization: `Bearer ${token}` }
    })
    personalList.value = res.data
  } catch (err) {
    console.error(err)
  }
}

const cargarReporte = async () => {
  cargando.value = true
  error.value = false
  try {
    const token = localStorage.getItem('token')
    const params = {
      fecha_inicio: filtros.value.fecha_inicio,
      fecha_fin: filtros.value.fecha_fin
    }
    if (filtros.value.personal_id) {
      params.personal_id = filtros.value.personal_id
    }
    const response = await axios.get('/api/asistencias/reporte', {
      params,
      headers: { Authorization: `Bearer ${token}` }
    })
    asistencias.value = response.data.asistencias
    resumen.value = response.data.resumen
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.error || err.message
  } finally {
    cargando.value = false
  }
}

const exportarPDF = () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('No hay sesión activa')
    return
  }
  let url = `/api/reporte/asistencias?fecha_inicio=${filtros.value.fecha_inicio}&fecha_fin=${filtros.value.fecha_fin}&token=${token}`
  if (filtros.value.personal_id) {
    url += `&personal_id=${filtros.value.personal_id}`
  }
  window.open(url, '_blank')
}

onMounted(() => {
  cargarPersonal()
  cargarReporte()
})
</script>

<style scoped>
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.filtros { display: flex; gap: 16px; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; }
.filtro-group { display: flex; flex-direction: column; gap: 4px; }
.btn-filtrar, .btn-pdf { background: #667eea; color: white; border: none; padding: 8px 20px; border-radius: 40px; cursor: pointer; }
.btn-pdf { background: #dc3545; }
.table-responsive { overflow-x: auto; background: white; border-radius: 16px; padding: 16px; }
.reporte-table { width: 100%; border-collapse: collapse; }
.reporte-table th, .reporte-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
.reporte-table th { background: #f8fafc; }
.resumen { margin-top: 30px; }
.resumen-cards { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px; }
.resumen-card { background: white; border-radius: 16px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); min-width: 200px; display: flex; flex-direction: column; gap: 8px; }
.loading-state, .error-state { text-align: center; padding: 40px; }
.text-center { text-align: center; }
</style>
