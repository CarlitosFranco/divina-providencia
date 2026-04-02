<template>
  <div class="modal-overlay" v-if="show" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Personal' : '➕ Nuevo Personal' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nombres *</label>
            <input v-model="form.nombres" type="text" required />
            <span v-if="errors.nombres" class="error-text">{{ errors.nombres }}</span>
          </div>
          <div class="form-group">
            <label>Apellidos *</label>
            <input v-model="form.apellidos" type="text" required />
            <span v-if="errors.apellidos" class="error-text">{{ errors.apellidos }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Documento de Identidad</label>
            <input v-model="form.documento_identidad" />
          </div>
          <div class="form-group">
            <label>Teléfono</label>
            <input v-model="form.telefono" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="form.email" />
            <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
          </div>
          <div class="form-group">
            <label>Cargo *</label>
            <input v-model="form.cargo" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Especialidad</label>
            <input v-model="form.especialidad" />
          </div>
          <div class="form-group">
            <label>Fecha de Contratación *</label>
            <input type="date" v-model="form.fecha_contratacion" required />
          </div>
        </div>

        <div class="form-group">
          <label>Activo</label>
          <select v-model="form.activo">
            <option :value="1">Sí</option>
            <option :value="0">No</option>
          </select>
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
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  personal: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  nombres: '',
  apellidos: '',
  documento_identidad: '',
  telefono: '',
  email: '',
  cargo: '',
  especialidad: '',
  fecha_contratacion: new Date().toISOString().slice(0,10),
  activo: 1
})

const errors = ref({})
const cargando = ref(false)

const resetForm = () => {
  form.value = {
    nombres: '',
    apellidos: '',
    documento_identidad: '',
    telefono: '',
    email: '',
    cargo: '',
    especialidad: '',
    fecha_contratacion: new Date().toISOString().slice(0,10),
    activo: 1
  }
  errors.value = {}
}

watch(() => props.personal, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    resetForm()
  }
}, { immediate: true })

const validar = () => {
  const newErrors = {}
  if (!form.value.nombres) newErrors.nombres = 'Los nombres son obligatorios'
  if (!form.value.apellidos) newErrors.apellidos = 'Los apellidos son obligatorios'
  if (!form.value.cargo) newErrors.cargo = 'El cargo es obligatorio'
  if (!form.value.fecha_contratacion) newErrors.fecha_contratacion = 'La fecha de contratación es obligatoria'
  if (form.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    newErrors.email = 'Email no válido'
  }
  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

const guardar = async () => {
  if (!validar()) return
  cargando.value = true
  try {
    if (props.isEditing) {
      await axios.put(`/api/personal/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/personal', form.value)
    }
    emit('saved')
    cerrar()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert(error.response?.data?.error || 'Error al guardar el personal')
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
