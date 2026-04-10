<template>
  <div class="calendario-wrapper">
    <div class="calendario-header">
      <h2>📅 Turnos del Personal</h2>
      <button class="btn-nuevo-turno" @click="abrirModalNuevo">
        + Nuevo Turno
      </button>
    </div>

    <FullCalendar
      ref="fullCalendarRef"
      :options="calendarOptions"
    />

    <!-- Modal para crear/editar turnos -->
    <TurnoForm
      :show="mostrarModal"
      :turno="turnoEditando"
      :isEditing="!!turnoEditando"
      :fechaInicial="fechaSeleccionada"
      @close="cerrarModal"
      @saved="onTurnoSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import TurnoForm from './TurnoForm.vue'
import axios from 'axios'
import Swal from 'sweetalert2'

// Referencias
const fullCalendarRef = ref(null)
const turnos = ref([])
const mostrarModal = ref(false)
const turnoEditando = ref(null)
const fechaSeleccionada = ref('')

// Cargar turnos desde el backend
const cargarTurnos = async () => {
  try {
    const res = await axios.get('/api/turnos')
    turnos.value = res.data
    console.log('Turnos cargados:', turnos.value)
    // Forzar refresco del calendario
    if (fullCalendarRef.value) {
      const calendarApi = fullCalendarRef.value.getApi()
      calendarApi.refetchEvents()
    }
  } catch (err) {
    console.error('Error cargando turnos:', err)
    Swal.fire('Error', 'No se pudieron cargar los turnos', 'error')
  }
}

// Convertir turnos a eventos de FullCalendar (reactivo)
const eventosCalendario = computed(() => {
  if (!turnos.value.length) return []

  const colores = {
    'Mañana': '#10b981', // verde
    'Tarde': '#f59e0b',  // ámbar
    'Noche': '#3b82f6'   // azul
  }

  return turnos.value.map(turno => {
    // Validar datos mínimos
    if (!turno.fecha || !turno.hora_inicio || !turno.hora_fin) {
      console.warn('Turno incompleto:', turno)
      return null
    }

    const fecha = turno.fecha
    const inicio = `${fecha}T${turno.hora_inicio}:00`
    const fin = `${fecha}T${turno.hora_fin}:00`

    // Nombre completo del personal (asumiendo que viene en el objeto)
    const nombrePersonal = turno.nombres && turno.apellidos
      ? `${turno.nombres} ${turno.apellidos}`
      : `Personal ID: ${turno.personal_id}`

    return {
      id: turno.id,
      title: `${nombrePersonal} (${turno.tipo_turno})`,
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
  }).filter(event => event !== null)
})

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
  events: [], // Se llenará dinámicamente con watch
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  // Al hacer clic en una celda vacía (crear nuevo turno)
  select: (info) => {
    const fecha = info.startStr.split('T')[0]
    const horaInicio = info.startStr.split('T')[1]?.slice(0,5) || '07:00'
    fechaSeleccionada.value = fecha
    turnoEditando.value = null
    // Opcional: también se podría pasar la hora de inicio por defecto al modal,
    // pero lo dejamos así; el modal usará la fecha y hora por defecto 08:00.
    mostrarModal.value = true
  },
  // Al hacer clic en un evento (editar)
  eventClick: (info) => {
    const turno = turnos.value.find(t => t.id == info.event.id)
    if (turno) {
      turnoEditando.value = turno
      fechaSeleccionada.value = turno.fecha
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
      Swal.fire('Actualizado', 'Turno reubicado correctamente', 'success')
      await cargarTurnos()
    } catch (error) {
      Swal.fire('Error', 'No se pudo actualizar el turno', 'error')
      await cargarTurnos() // revertir cambios visuales
    }
  }
})

// Sincronizar eventos del calendario con la fuente reactiva
watch(eventosCalendario, (newEvents) => {
  if (fullCalendarRef.value) {
    const calendarApi = fullCalendarRef.value.getApi()
    calendarApi.removeAllEvents()
    calendarApi.addEventSource(newEvents)
  }
}, { deep: true })

// Métodos del modal
const abrirModalNuevo = () => {
  turnoEditando.value = null
  fechaSeleccionada.value = new Date().toISOString().slice(0,10)
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  turnoEditando.value = null
  fechaSeleccionada.value = ''
}

const onTurnoSaved = async () => {
  await cargarTurnos()
  cerrarModal()
}

onMounted(() => {
  cargarTurnos()
})
</script>

<style scoped>
/* ESTILOS MEJORADOS */
.calendario-wrapper {
  background: #ffffff;
  border-radius: 28px;
  padding: 24px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.03), 0 2px 6px rgba(0,0,0,0.05);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.calendario-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.calendario-header h2 {
  font-size: 1.6rem;
  font-weight: 600;
  background: linear-gradient(135deg, #1e293b, #2d3a4f);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  margin: 0;
}

.btn-nuevo-turno {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  padding: 10px 24px;
  border-radius: 40px;
  color: white;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  font-size: 0.9rem;
}

.btn-nuevo-turno:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 14px rgba(102,126,234,0.3);
}

/* Personalizar el calendario de FullCalendar */
:deep(.fc) {
  --fc-border-color: #e9eef3;
  --fc-button-bg-color: #f8fafc;
  --fc-button-border-color: #cbd5e1;
  --fc-button-text-color: #1e293b;
  --fc-button-hover-bg-color: #f1f5f9;
  --fc-today-bg-color: #fefce8;
  --fc-event-border-radius: 12px;
  --fc-event-bg-color: #667eea;
  font-family: inherit;
}

:deep(.fc .fc-toolbar-title) {
  font-size: 1.3rem;
  font-weight: 600;
  color: #0f172a;
}

:deep(.fc .fc-button) {
  border-radius: 40px;
  padding: 6px 14px;
  font-weight: 500;
  text-transform: capitalize;
}

:deep(.fc .fc-button-primary:not(:disabled):focus) {
  box-shadow: none;
}

:deep(.fc .fc-daygrid-day) {
  transition: background 0.1s;
}

:deep(.fc .fc-daygrid-day:hover) {
  background: #fafcff;
}

:deep(.fc .fc-daygrid-day-number) {
  font-size: 0.85rem;
  font-weight: 500;
  color: #334155;
  padding: 6px 8px;
}

:deep(.fc .fc-day-today .fc-daygrid-day-number) {
  background: #eef2ff;
  border-radius: 30px;
  padding: 6px 12px;
  color: #4f46e5;
}

:deep(.fc-event) {
  border: none;
  border-radius: 16px;
  padding: 2px 6px;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: transform 0.1s, box-shadow 0.1s;
}

:deep(.fc-event:hover) {
  transform: scale(0.98);
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

:deep(.fc-daygrid-event-harness) {
  margin: 2px 0;
}

:deep(.fc-timegrid-event) {
  border-radius: 16px;
  padding: 4px 8px;
}

:deep(.fc-timegrid-slot-label) {
  font-size: 0.7rem;
  color: #64748b;
}

:deep(.fc-col-header-cell-cushion) {
  font-weight: 600;
  color: #1e293b;
  padding: 10px 0;
}
</style>
