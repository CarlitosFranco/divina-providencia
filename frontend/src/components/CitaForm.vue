<template>
  <div class="modal-overlay" v-if="show" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Cita' : '➕ Nueva Cita' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-row">
          <div class="form-group">
            <label>Paciente *</label>
            <select v-model="form.paciente_id" required>
              <option v-for="p in pacientes" :key="p.id" :value="p.id">
                {{ p.nombres }} {{ p.apellidos }}
              </option>
            </select>
            <span v-if="errors.paciente_id" class="error-text">{{ errors.paciente_id }}</span>
          </div>
          <div class="form-group">
            <label>Personal (Médico/Enfermero) *</label>
            <select v-model="form.personal_id" required>
              <option v-for="p in personal" :key="p.id" :value="p.id">
                {{ p.nombres }} {{ p.apellidos }} - {{ p.cargo }}
              </option>
            </select>
            <span v-if="errors.personal_id" class="error-text">{{ errors.personal_id }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Fecha *</label>
            <input type="date" v-model="form.fecha" required />
            <span v-if="errors.fecha" class="error-text">{{ errors.fecha }}</span>
          </div>
          <div class="form-group">
            <label>Hora *</label>
            <input type="time" v-model="form.hora" required />
            <span v-if="errors.hora" class="error-text">{{ errors.hora }}</span>
          </div>
        </div>

        <div class="form-group">
          <label>Motivo</label>
          <textarea v-model="form.motivo" rows="2"></textarea>
        </div>

        <div class="form-group">
          <label>Estado</label>
          <select v-model="form.estado">
            <option>Programada</option>
            <option>Confirmada</option>
            <option>Atendida</option>
            <option>Cancelada</option>
          </select>
        </div>

        <div class="form-group">
          <label>Observaciones</label>
          <textarea v-model="form.observaciones" rows="2"></textarea>
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
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  cita: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  paciente_id: '',
  personal_id: '',
  fecha: new Date().toISOString().slice(0,10),
  hora: '',
  motivo: '',
  estado: 'Programada',
  observaciones: ''
})

const pacientes = ref([])
const personal = ref([])
const errors = ref({})
const cargando = ref(false)

const cargarPacientes = async () => {
  try {
    const res = await axios.get('/api/pacientes')
    pacientes.value = res.data
  } catch (err) {
    console.error('Error al cargar pacientes:', err)
  }
}
const cargarPersonal = async () => {
  try {
    const res = await axios.get('/api/personal')
    personal.value = res.data
  } catch (err) {
    console.error('Error al cargar personal:', err)
  }
}

onMounted(() => {
  cargarPacientes()
  cargarPersonal()
})

const resetForm = () => {
  form.value = {
    paciente_id: '',
    personal_id: '',
    fecha: new Date().toISOString().slice(0,10),
    hora: '',
    motivo: '',
    estado: 'Programada',
    observaciones: ''
  }
  errors.value = {}
}

watch(() => props.cita, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    resetForm()
  }
}, { immediate: true })

const validar = () => {
  const newErrors = {}
  if (!form.value.paciente_id) newErrors.paciente_id = 'Debe seleccionar un paciente'
  if (!form.value.personal_id) newErrors.personal_id = 'Debe seleccionar un personal'
  if (!form.value.fecha) newErrors.fecha = 'La fecha es obligatoria'
  if (!form.value.hora) newErrors.hora = 'La hora es obligatoria'
  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  cargando.value = true
  try {
    if (props.isEditing) {
      await axios.put(`/api/citas/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/citas', form.value)
    }
    emit('saved')
    cerrar()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert(error.response?.data?.error || 'Error al guardar la cita')
  } finally {
    cargando.value = false
  }
}

const cerrar = () => {
  emit('close')
}
</script>

<style scoped>
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
.form-group input, .form-group select, .form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  font-family: inherit;
  transition: all 0.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
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
