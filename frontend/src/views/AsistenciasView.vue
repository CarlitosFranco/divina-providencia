<template>
  <div class="asistencias-container">
    <div class="page-header">
      <h1>📋 Registro de Asistencias</h1>
      <div class="filters">
        <label>
          Desde:
          <input type="date" v-model="fechaInicio" @change="cargarAsistencias" />
        </label>
        <label>
          Hasta:
          <input type="date" v-model="fechaFin" @change="cargarAsistencias" />
        </label>
        <label v-if="esAdmin">
          Empleado:
          <select v-model="personalIdFiltro" @change="cargarAsistencias">
            <option :value="null">Todos</option>
            <option v-for="emp in personalList" :key="emp.id" :value="emp.id">
              {{ emp.nombres }} {{ emp.apellidos }}
            </option>
          </select>
        </label>
      </div>
    </div>

    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando asistencias...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarAsistencias" class="btn-retry">Reintentar</button>
    </div>

    <div v-else class="table-responsive">
      <table class="asistencias-table">
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
            <td colspan="5" class="text-center">No hay registros de asistencia en el período seleccionado.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const asistencias = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const fechaInicio = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10))
const fechaFin = ref(new Date().toISOString().slice(0,10))
const personalIdFiltro = ref(null)
const personalList = ref([])
const esAdmin = ref(false)

const formatearFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

const cargarPersonal = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/personal', {
      headers: { Authorization: `Bearer ${token}` }
    })
    personalList.value = res.data
  } catch (err) {
    console.error('Error cargando personal:', err)
  }
}

const cargarAsistencias = async () => {
  cargando.value = true
  error.value = false
  try {
    const token = localStorage.getItem('token')
    const params = {
      fecha_inicio: fechaInicio.value,
      fecha_fin: fechaFin.value
    }
    if (personalIdFiltro.value) {
      params.personal_id = personalIdFiltro.value
    }
    const res = await axios.get('/api/asistencias', {
      params,
      headers: { Authorization: `Bearer ${token}` }
    })
    asistencias.value = res.data
  } catch (err) {
    console.error(err)
    error.value = true
    errorMessage.value = err.response?.data?.error || err.message
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  const usuario = JSON.parse(localStorage.getItem('usuario') || '{}')
  esAdmin.value = usuario.rol_id === 1
  cargarAsistencias()
  if (esAdmin.value) cargarPersonal()
})
</script>

<style scoped>
.asistencias-container {
  background: white;
  border-radius: 28px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  font-family: 'Inter', sans-serif;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
}
.filters {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}
.filters label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
}
.filters input, .filters select {
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #cbd5e1;
}
.table-responsive {
  overflow-x: auto;
}
.asistencias-table {
  width: 100%;
  border-collapse: collapse;
}
.asistencias-table th, .asistencias-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}
.asistencias-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #1e293b;
}
.text-center {
  text-align: center;
}
.loading-state, .error-state {
  text-align: center;
  padding: 40px;
}
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }
.btn-retry {
  background: #667eea;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 40px;
  cursor: pointer;
  margin-top: 16px;
}
</style>
