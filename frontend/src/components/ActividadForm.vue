<template>
  <div class="modal" v-if="show">
    <div class="modal-content">
      <h2>{{ isEditing ? 'Editar Actividad' : 'Nueva Actividad' }}</h2>
      <form @submit.prevent="guardar">
        <div class="form-group">
          <label>Personal *</label>
          <select v-model="form.personal_id" required>
            <option v-for="p in personalList" :key="p.id" :value="p.id">
              {{ p.nombres }} {{ p.apellidos }} - {{ p.cargo }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Paciente *</label>
          <select v-model="form.paciente_id" required>
            <option v-for="p in pacienteList" :key="p.id" :value="p.id">
              {{ p.nombres }} {{ p.apellidos }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" v-model="form.fecha" required>
        </div>
        <div class="form-group">
          <label>Tipo de Actividad</label>
          <input v-model="form.tipo_actividad" placeholder="Ej: Control, Medicación, Terapia">
        </div>
        <div class="form-group">
          <label>Descripción *</label>
          <textarea v-model="form.descripcion" rows="3" required></textarea>
        </div>
        <div class="buttons">
          <button type="submit">{{ isEditing ? 'Actualizar' : 'Crear' }}</button>
          <button type="button" @click="cerrar">Cancelar</button>
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
  actividad: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  personal_id: '',
  paciente_id: '',
  fecha: new Date().toISOString().slice(0,10),
  tipo_actividad: '',
  descripcion: ''
})

const personalList = ref([])
const pacienteList = ref([])

// Cargar listas para selects
const cargarPersonal = async () => {
  try {
    const response = await axios.get('/api/personal')
    personalList.value = response.data
  } catch (error) {
    console.error('Error al cargar personal:', error)
  }
}

const cargarPacientes = async () => {
  try {
    const response = await axios.get('/api/pacientes')
    pacienteList.value = response.data
  } catch (error) {
    console.error('Error al cargar pacientes:', error)
  }
}

onMounted(() => {
  cargarPersonal()
  cargarPacientes()
})

const resetForm = () => {
  form.value = {
    personal_id: '',
    paciente_id: '',
    fecha: new Date().toISOString().slice(0,10),
    tipo_actividad: '',
    descripcion: ''
  }
}

watch(() => props.actividad, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    resetForm()
  }
}, { immediate: true })

const guardar = async () => {
  try {
    if (props.isEditing) {
      await axios.put(`/api/actividades/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/actividades', form.value)
    }
    emit('saved')
    cerrar()
  } catch (error) {
    console.error('Error al guardar actividad:', error)
    alert(error.response?.data?.error || 'Error al guardar la actividad')
  }
}

const cerrar = () => {
  emit('close')
}
</script>

<style scoped>
.modal {
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
}
.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  max-height: 80%;
  overflow-y: auto;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
}
.form-group input, .form-group select, .form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
.buttons {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}
</style>
