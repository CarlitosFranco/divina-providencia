<template>
  <div class="calendario-container">
    <FullCalendar
      ref="fullCalendarRef"
      :options="calendarOptions"
    />

    <!-- Modal para crear/editar turno desde el calendario -->
    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2>{{ turnoEditando ? 'Editar Turno' : 'Nuevo Turno' }}</h2>
          <button class="btn-close" @click="cerrarModal">✕</button>
        </div>
        <form @submit.prevent="guardarTurno" class="modal-form">
          <div class="form-group">
            <label>Personal *</label>
            <select v-model="turnoForm.personal_id" required>
              <option v-for="p in personalList" :key="p.id" :value="p.id">
                {{ p.nombres }} {{ p.apellidos }} - {{ p.cargo }}
              </option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Fecha *</label>
              <input type="date" v-model="turnoForm.fecha" required />
            </div>
            <div class="form-group">
              <label>Hora Inicio *</label>
              <input type="time" v-model="turnoForm.hora_inicio" required />
            </div>
            <div class="form-group">
              <label>Hora Fin *</label>
              <input type="time" v-model="turnoForm.hora_fin" required />
            </div>
          </div>
          <div class="form-group">
            <label>Tipo de Turno *</label>
            <select v-model="turnoForm.tipo_turno" required>
              <option>Mañana</option>
              <option>Tarde</option>
              <option>Noche</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="cerrarModal">Cancelar</button>
            <button type="submit" class="btn-submit" :disabled="guardando">
              <span v-if="guardando" class="spinner-small"></span>
              {{ turnoEditando ? 'Actualizar' : 'Crear' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import axios from 'axios'
import Swal from 'sweetalert2'

const fullCalendarRef = ref(null)
const personalList = ref([])
const turnos = ref([])
const mostrarModal = ref(false)
const turnoEditando = ref(null)
const guardando = ref(false)

const turnoForm = ref({
  personal_id: '',
  fecha: '',
  hora_inicio: '',
  hora_fin: '',
  tipo_turno: 'Mañana'
})

// Cargar personal y turnos
const cargarPersonal = async () => {
  try {
    const res = await axios.get('/api/personal')
    personalList.value = res.data
  } catch (err) {
    console.error('Error al cargar personal:', err)
  }
}

const cargarTurnos = async () => {
  try {
    const res = await axios.get('/api/turnos')
    turnos.value = res.data
    // Refrescar el calendario con los nuevos eventos
    if (fullCalendarRef.value) {
      const calendarApi = fullCalendarRef.value.getApi()
      calendarApi.refetchEvents()
    }
  } catch (err) {
    console.error('Error al cargar turnos:', err)
  }
}

// Convertir turnos a eventos de FullCalendar
const obtenerEventos = () => {
  const colores = {
    'Mañana': '#10b981', // verde
    'Tarde': '#f59e0b',  // ámbar
    'Noche': '#3b82f6'   // azul
  }
  return turnos.value.map(turno => {
    const fecha = turno.fecha
    const inicio = `${fecha}T${turno.hora_inicio}:00`
    const fin = `${fecha}T${turno.hora_fin}:00`
    return {
      id: turno.id,
      title: `${turno.nombres} ${turno.apellidos} (${turno.tipo_turno})`,
      start: inicio,
      end: fin,
      backgroundColor: colores[turno.tipo_turno] || '#6b7280',
      borderColor: colores[turno.tipo_turno] || '#6b7280',
      extendedProps: {
        personal_id: turno.personal_id,
        tipo_turno: turno.tipo_turno,
        hora_inicio: turno.hora_inicio,
        hora_fin: turno.hora_fin
      }
    }
  })
}

// Opciones del calendario
const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  locale: 'es',
  buttonText: {
    today: 'Hoy',
    month: 'Mes',
    week: 'Semana',
    day: 'Día'
  },
  events: obtenerEventos,
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  // Al hacer clic en una celda vacía (crear nuevo turno)
  select: (info) => {
    const fecha = info.startStr.split('T')[0]
    turnoForm.value = {
      personal_id: '',
      fecha: fecha,
      hora_inicio: info.startStr.split('T')[1]?.slice(0,5) || '08:00',
      hora_fin: info.endStr.split('T')[1]?.slice(0,5) || '09:00',
      tipo_turno: 'Mañana'
    }
    turnoEditando.value = null
    mostrarModal.value = true
  },
  // Al hacer clic en un evento (editar)
  eventClick: (info) => {
    const turno = turnos.value.find(t => t.id == info.event.id)
    if (turno) {
      turnoForm.value = {
        personal_id: turno.personal_id,
        fecha: turno.fecha,
        hora_inicio: turno.hora_inicio,
        hora_fin: turno.hora_fin,
        tipo_turno: turno.tipo_turno
      }
      turnoEditando.value = turno
      mostrarModal.value = true
    }
  },
  // Al arrastrar un evento (cambiar fecha/hora)
  eventDrop: async (info) => {
    const turnoId = info.event.id
    const nuevaFecha = info.event.startStr.split('T')[0]
    const nuevaHoraInicio = info.event.startStr.split('T')[1]?.slice(0,5)
    const nuevaHoraFin = info.event.endStr.split('T')[1]?.slice(0,5)
    try {
      await axios.put(`/api/turnos/${turnoId}`, {
        fecha: nuevaFecha,
        hora_inicio: nuevaHoraInicio,
        hora_fin: nuevaHoraFin,
        personal_id: info.event.extendedProps.personal_id,
        tipo_turno: info.event.extendedProps.tipo_turno
      })
      Swal.fire('Actualizado', 'El turno se ha reubicado correctamente', 'success')
      cargarTurnos()
    } catch (error) {
      Swal.fire('Error', 'No se pudo actualizar el turno', 'error')
      cargarTurnos() // revertir cambios visuales
    }
  }
})

const guardarTurno = async () => {
  guardando.value = true
  try {
    if (turnoEditando.value) {
      await axios.put(`/api/turnos/${turnoEditando.value.id}`, turnoForm.value)
      Swal.fire('Actualizado', 'Turno actualizado correctamente', 'success')
    } else {
      await axios.post('/api/turnos', turnoForm.value)
      Swal.fire('Creado', 'Turno creado correctamente', 'success')
    }
    cargarTurnos()
    cerrarModal()
  } catch (error) {
    Swal.fire('Error', error.response?.data?.error || 'Error al guardar el turno', 'error')
  } finally {
    guardando.value = false
  }
}

const cerrarModal = () => {
  mostrarModal.value = false
  turnoEditando.value = null
}

onMounted(async () => {
  await cargarPersonal()
  await cargarTurnos()
})
</script>

<style scoped>
.calendario-container {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}
.modal-container {
  background: white;
  border-radius: 24px;
  width: 90%;
  max-width: 600px;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 20px 35px -10px rgba(0,0,0,0.3);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  border-radius: 24px 24px 0 0;
}
.modal-header h2 {
  font-size: 1.4rem;
  color: #1e293b;
}
.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #94a3b8;
  transition: color 0.2s;
}
.btn-close:hover {
  color: #ef4444;
}
.modal-form {
  padding: 24px;
}
.form-row {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}
.form-group {
  flex: 1;
  margin-bottom: 8px;
}
.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 6px;
  color: #334155;
  font-size: 0.85rem;
}
.form-group input, .form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-family: inherit;
  transition: all 0.2s;
}
.form-group input:focus, .form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
}
.btn-cancel {
  padding: 10px 20px;
  background: #f1f5f9;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
}
.btn-submit {
  padding: 10px 24px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
@media (max-width: 640px) {
  .form-row {
    flex-direction: column;
    gap: 0;
  }
}
</style>
