<template>
  <div v-if="show" class="modal-overlay" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Dieta' : '➕ Nueva Dieta' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-group">
          <label>Paciente *</label>
          <select v-model="form.paciente_id" required>
            <option v-for="p in pacientes" :key="p.id" :value="p.id">
              {{ p.nombres }} {{ p.apellidos }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Tipo de comida *</label>
          <select v-model="form.tipo_comida" required>
            <option value="Desayuno">Desayuno</option>
            <option value="Almuerzo">Almuerzo</option>
            <option value="Merienda">Merienda</option>
            <option value="Cena">Cena</option>
          </select>
        </div>

        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" v-model="form.fecha" required />
        </div>

        <div class="form-group">
          <label>Descripción *</label>
          <textarea v-model="form.descripcion" rows="3" required placeholder="Ej: Dieta blanda, sin azúcar, ..."></textarea>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-submit" :disabled="guardando">
            <span v-if="guardando" class="spinner-small"></span>
            {{ isEditing ? 'Actualizar' : 'Guardar' }}
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
  dieta: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  paciente_id: '',
  tipo_comida: 'Desayuno',
  fecha: new Date().toISOString().slice(0,10),
  descripcion: ''
})
const pacientes = ref([])
const guardando = ref(false)

const cargarPacientes = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/pacientes', {
      headers: { Authorization: `Bearer ${token}` }
    })
    pacientes.value = res.data
  } catch (err) {
    console.error('Error cargando pacientes:', err)
  }
}

watch(() => props.show, (visible) => {
  if (visible) {
    cargarPacientes()
    if (props.dieta && props.isEditing) {
      form.value = { ...props.dieta }
    } else {
      form.value = {
        paciente_id: '',
        tipo_comida: 'Desayuno',
        fecha: new Date().toISOString().slice(0,10),
        descripcion: ''
      }
    }
  }
}, { immediate: true })

const cerrar = () => emit('close')

const guardar = async () => {
  guardando.value = true
  try {
    const token = localStorage.getItem('token')
    const url = props.isEditing ? `/api/dietas/${form.value.id}` : '/api/dietas'
    const method = props.isEditing ? 'put' : 'post'
    await axios[method](url, form.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    emit('saved')
    cerrar()
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.error || 'Error al guardar la dieta')
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
/* Estilos del modal (igual que en otros formularios) */
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
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}
.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}
.modal-form {
  padding: 24px;
}
.form-group {
  margin-bottom: 16px;
}
.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 6px;
}
.form-group select, .form-group input, .form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}
.btn-cancel {
  background: #f1f5f9;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  cursor: pointer;
}
.btn-submit {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  padding: 10px 24px;
  border-radius: 12px;
  color: white;
  cursor: pointer;
}
.spinner-small {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid white;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
