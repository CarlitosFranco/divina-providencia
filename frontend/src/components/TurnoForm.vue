<template>
  <div class="modal-overlay" v-if="show" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Turno' : '➕ Nuevo Turno' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-group">
          <label>Personal *</label>
          <select v-model="form.personal_id" required>
            <option v-for="p in personalList" :key="p.id" :value="p.id">
              {{ p.nombres }} {{ p.apellidos }} - {{ p.cargo }}
            </option>
          </select>
          <span v-if="errors.personal_id" class="error-text">{{ errors.personal_id }}</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Fecha *</label>
            <input type="date" v-model="form.fecha" required />
          </div>
          <div class="form-group">
            <label>Hora Inicio *</label>
            <input type="time" v-model="form.hora_inicio" @change="actualizarTurnoSugerido" required />
          </div>
          <div class="form-group">
            <label>Hora Fin *</label>
            <input type="time" v-model="form.hora_fin" required />
          </div>
        </div>

        <div class="form-group">
          <label>Turno *</label>
          <select v-model="turnoSeleccionado" required>
            <option value="1">Turno 1 (7:00 - 15:00)</option>
            <option value="2">Turno 2 (15:00 - 23:00)</option>
            <option value="3">Turno 3 (23:00 - 7:00)</option>
          </select>
          <small class="text-muted" v-if="turnoSugerido && turnoSugerido !== turnoSeleccionado">
            ⚡ Sugerido según hora: Turno {{ turnoSugerido }}
            <button type="button" class="btn-link" @click="usarTurnoSugerido">Usar</button>
          </small>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-submit" :disabled="cargando">
            <span v-if="cargando" class="spinner-small"></span>
            {{ isEditing ? 'Actualizar' : 'Crear' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  turno: Object,
  isEditing: Boolean,
  fechaInicial: String   // 👈 NUEVA PROP: fecha seleccionada desde el calendario
})

const emit = defineEmits(['close', 'saved'])

// Datos del formulario
const form = ref({
  personal_id: '',
  fecha: '',
  hora_inicio: '',
  hora_fin: '',
  tipo_turno: 'Mañana'
})

// Selector visible para el usuario (1,2,3)
const turnoSeleccionado = ref('1')
const turnoSugerido = ref(null)
const personalList = ref([])
const errors = ref({})
const cargando = ref(false)

// Mapeo entre turno visible (1,2,3) y valor real en BD
const mapaTurnoBD = {
  '1': 'Mañana',
  '2': 'Tarde',
  '3': 'Noche'
}
const mapaTurnoVisible = {
  'Mañana': '1',
  'Tarde': '2',
  'Noche': '3'
}

// Obtener turno según hora (0-23)
const obtenerTurnoPorHora = (hora) => {
  if (hora >= 7 && hora < 15) return '1'
  if (hora >= 15 && hora < 23) return '2'
  return '3'
}

// Actualizar turno sugerido basado en hora_inicio
const actualizarTurnoSugerido = () => {
  if (form.value.hora_inicio) {
    const hora = parseInt(form.value.hora_inicio.split(':')[0])
    turnoSugerido.value = obtenerTurnoPorHora(hora)
  } else {
    turnoSugerido.value = null
  }
}

// Usar turno sugerido
const usarTurnoSugerido = () => {
  if (turnoSugerido.value) {
    turnoSeleccionado.value = turnoSugerido.value
  }
}

// Sincronizar turnoSeleccionado con form.tipo_turno
watch(() => form.value.tipo_turno, (nuevo) => {
  if (nuevo && mapaTurnoVisible[nuevo]) {
    turnoSeleccionado.value = mapaTurnoVisible[nuevo]
  }
})

// Cuando cambia turnoSeleccionado, actualizar form.tipo_turno
watch(turnoSeleccionado, (nuevo) => {
  if (nuevo && mapaTurnoBD[nuevo]) {
    form.value.tipo_turno = mapaTurnoBD[nuevo]
  }
})

// Cargar lista de personal
const cargarPersonal = async () => {
  try {
    const response = await axios.get('/api/personal')
    personalList.value = response.data
  } catch (error) {
    console.error('Error al cargar personal:', error)
  }
}

onMounted(() => {
  cargarPersonal()
})

// Resetear formulario a valores por defecto (para nuevo turno)
const resetForm = () => {
  form.value = {
    personal_id: '',
    fecha: '',
    hora_inicio: '',
    hora_fin: '',
    tipo_turno: 'Mañana'
  }
  turnoSeleccionado.value = '1'
  turnoSugerido.value = null
  errors.value = {}
}

// Al recibir un turno para editar
watch(() => props.turno, (newVal) => {
  if (newVal && props.isEditing) {
    form.value = { ...newVal }
    // Sincronizar el select visible
    if (newVal.tipo_turno && mapaTurnoVisible[newVal.tipo_turno]) {
      turnoSeleccionado.value = mapaTurnoVisible[newVal.tipo_turno]
    }
    actualizarTurnoSugerido()
  } else if (!props.isEditing && props.show) {
    // Modo creación: usar fechaInicial si está presente, sino fecha actual
    resetForm()
    if (props.fechaInicial) {
      form.value.fecha = props.fechaInicial
    } else {
      form.value.fecha = new Date().toISOString().slice(0,10)
    }
    // Si además se quiere precargar hora sugerida (opcional)
    if (!form.value.hora_inicio) {
      form.value.hora_inicio = '08:00'
      actualizarTurnoSugerido()
    }
  }
}, { immediate: true, deep: true })

// También cuando se abre el modal en modo creación sin haber cambiado turno
watch(() => props.show, (visible) => {
  if (visible && !props.isEditing) {
    resetForm()
    if (props.fechaInicial) {
      form.value.fecha = props.fechaInicial
    } else {
      form.value.fecha = new Date().toISOString().slice(0,10)
    }
    if (!form.value.hora_inicio) {
      form.value.hora_inicio = '08:00'
      actualizarTurnoSugerido()
    }
  }
})

// Validación básica
const validar = () => {
  const newErrors = {}
  if (!form.value.personal_id) newErrors.personal_id = 'Debe seleccionar un personal'
  if (!form.value.fecha) newErrors.fecha = 'La fecha es obligatoria'
  if (!form.value.hora_inicio) newErrors.hora_inicio = 'La hora de inicio es obligatoria'
  if (!form.value.hora_fin) newErrors.hora_fin = 'La hora de fin es obligatoria'

  // Validar que hora_fin sea mayor que hora_inicio (excepto Turno 3 que cruza medianoche)
  if (form.value.hora_inicio && form.value.hora_fin) {
    const inicio = form.value.hora_inicio
    const fin = form.value.hora_fin
    if (turnoSeleccionado.value !== '3' && inicio >= fin) {
      newErrors.hora_fin = 'La hora de fin debe ser mayor a la de inicio'
    }
  }

  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

// Guardar turno (crear o actualizar)
const guardar = async () => {
  if (!validar()) return
  cargando.value = true
  try {
    if (props.isEditing) {
      await axios.put(`/api/turnos/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/turnos', form.value)
    }
    emit('saved')
    cerrar()
  } catch (error) {
    console.error('Error al guardar:', error)
    const msg = error.response?.data?.error || 'Error al guardar el turno'
    // Usar alert o sweetalert, pero aquí usamos alert por simplicidad
    alert(msg)
  } finally {
    cargando.value = false
  }
}

const cerrar = () => {
  emit('close')
}
</script>

<style scoped>
/* Tus estilos existentes se mantienen */
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
  max-width: 700px;
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
.error-text {
  color: #ef4444;
  font-size: 0.75rem;
  margin-top: 4px;
  display: block;
}
.text-muted {
  font-size: 0.7rem;
  color: #6c757d;
  display: block;
  margin-top: 4px;
}
.btn-link {
  background: none;
  border: none;
  color: #667eea;
  text-decoration: underline;
  cursor: pointer;
  font-size: 0.7rem;
  margin-left: 8px;
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
