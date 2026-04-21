<template>
  <div class="container">
    <div class="page-header">
      <h1>Turnos del Personal</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nuevo Turno</button>
    </div>

    <!-- Estado de carga -->
    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando turnos...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarTurnos" class="btn-retry">Reintentar</button>
    </div>

    <!-- Lista vacía -->
    <div v-else-if="turnos.length === 0" class="empty-state">
      <p>📭 No hay turnos registrados.</p>
      <button @click="abrirFormulario()" class="btn-primary">Agregar el primero</button>
    </div>

    <!-- Lista de turnos -->
    <div v-else class="cards-grid">
      <div v-for="turno in turnos" :key="turno.id" class="turno-card">
        <div class="card-header">
          <h3>{{ turno.nombres }} {{ turno.apellidos }}</h3>
          <span class="turno-badge">{{ turno.tipo_turno }}</span>
        </div>
        <div class="card-body">
          <p><strong>Fecha:</strong> {{ formatFecha(turno.fecha) }}</p>
          <p><strong>Hora inicio:</strong> {{ turno.hora_inicio }}</p>
          <p><strong>Hora fin:</strong> {{ turno.hora_fin }}</p>
        </div>
        <div class="card-actions">
          <button @click="editar(turno)" class="btn-edit">Editar</button>
          <button @click="confirmarEliminar(turno.id)" class="btn-delete">Eliminar</button>
        </div>
      </div>
    </div>

    <!-- Formulario de turno -->
    <TurnoForm
      :show="mostrarForm"
      :turno="turnoSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />

    <!-- Botón flotante para marcar asistencia (solo si el usuario tiene personal_id) -->
    <button v-if="personalId" class="btn-flotante" @click="mostrarOpcionesAsistencia">
      ⏱️ Marcar Asistencia
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import TurnoForm from '@/components/TurnoForm.vue'
import Swal from 'sweetalert2'

const turnos = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const turnoSeleccionado = ref(null)
const modoEdicion = ref(false)
const personalId = ref(null)  // ← ID del empleado para asistencia

const cargarTurnos = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/turnos')
    turnos.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

const abrirFormulario = () => {
  turnoSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (turno) => {
  turnoSeleccionado.value = { ...turno }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  turnoSeleccionado.value = null
}

const recargarLista = () => {
  cargarTurnos()
}

const confirmarEliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción no se puede revertir",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/turnos/${id}`)
        Swal.fire('Eliminado', 'El turno ha sido eliminado', 'success')
        cargarTurnos()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar el turno', 'error')
      }
    }
  })
}

// ========== ASISTENCIA (BOTÓN FLOTANTE) ==========
const mostrarOpcionesAsistencia = async () => {
  if (!personalId.value) {
    Swal.fire('Error', 'No se ha encontrado su información de empleado. Contacte al administrador.', 'error')
    return
  }
  const hoy = new Date().toISOString().slice(0,10)
  try {
    const res = await axios.get(`/api/asistencia?fecha=${hoy}&personal_id=${personalId.value}`)
    const asistencia = res.data
    const entradaRegistrada = asistencia && asistencia.hora_entrada
    const salidaRegistrada = asistencia && asistencia.hora_salida

    let mensaje = `Fecha: ${hoy}<br>`
    mensaje += `Entrada: ${asistencia?.hora_entrada || 'No registrada'}<br>`
    mensaje += `Salida: ${asistencia?.hora_salida || 'No registrada'}<br>`
    if (asistencia?.horas_trabajadas) mensaje += `Horas trabajadas: ${asistencia.horas_trabajadas}`

    if (!entradaRegistrada) {
      const result = await Swal.fire({
        title: 'Registrar Entrada',
        html: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Marcar Entrada',
        cancelButtonText: 'Cancelar'
      })
      if (result.isConfirmed) {
        await axios.post('/api/asistencia/entrada', { personal_id: personalId.value, fecha: hoy })
        Swal.fire('Éxito', 'Entrada registrada correctamente', 'success')
      }
    } else if (!salidaRegistrada) {
      const result = await Swal.fire({
        title: 'Registrar Salida',
        html: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Marcar Salida',
        cancelButtonText: 'Cancelar'
      })
      if (result.isConfirmed) {
        await axios.post('/api/asistencia/salida', { personal_id: personalId.value, fecha: hoy })
        Swal.fire('Éxito', 'Salida registrada correctamente', 'success')
      }
    } else {
      Swal.fire('Info', 'Ya has registrado entrada y salida hoy.', 'info')
    }
  } catch (err) {
    Swal.fire('Error', err.response?.data?.error || 'Error al consultar asistencia', 'error')
  }
}

// Obtener personal_id del usuario logueado
onMounted(() => {
  cargarTurnos()
  const usuario = JSON.parse(localStorage.getItem('usuario') || '{}')
  personalId.value = usuario.personal_id || null
})
</script>

<style scoped>
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}
.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 8px 20px;
  border: none;
  border-radius: 40px;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102,126,234,0.3);
}
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}
.turno-card {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}
.turno-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -12px rgba(0,0,0,0.15);
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.card-header h3 {
  font-size: 1.2rem;
  color: #1e293b;
}
.turno-badge {
  background: #e0e7ff;
  color: #4338ca;
  padding: 4px 10px;
  border-radius: 40px;
  font-size: 0.7rem;
  font-weight: 600;
}
.card-body {
  margin: 16px 0;
  font-size: 0.85rem;
  color: #475569;
}
.card-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}
.btn-edit, .btn-delete {
  padding: 6px 14px;
  border: none;
  border-radius: 40px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-edit { background: #e0e7ff; color: #4338ca; }
.btn-edit:hover { background: #c7d2fe; }
.btn-delete { background: #fee2e2; color: #b91c1c; }
.btn-delete:hover { background: #fecaca; }
.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 24px;
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
  margin-top: 16px;
  background: #667eea;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 40px;
  cursor: pointer;
}

/* Botón flotante para asistencia */
.btn-flotante {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: bold;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  cursor: pointer;
  z-index: 1000;
  transition: transform 0.2s;
}
.btn-flotante:hover {
  transform: scale(1.05);
}
</style>
