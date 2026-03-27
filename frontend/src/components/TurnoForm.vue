<template>
  <div class="modal" v-if="show">
    <div class="modal-content">
      <h2>{{ isEditing ? 'Editar Turno' : 'Nuevo Turno' }}</h2>
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
          <label>Fecha *</label>
          <input type="date" v-model="form.fecha" required>
        </div>
        <div class="form-group">
          <label>Hora Inicio *</label>
          <input type="time" v-model="form.hora_inicio" required>
        </div>
        <div class="form-group">
          <label>Hora Fin *</label>
          <input type="time" v-model="form.hora_fin" required>
        </div>
        <div class="form-group">
          <label>Tipo de Turno *</label>
          <select v-model="form.tipo_turno" required>
            <option>Mañana</option>
            <option>Tarde</option>
            <option>Noche</option>
          </select>
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
  turno: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  personal_id: '',
  fecha: new Date().toISOString().slice(0,10),
  hora_inicio: '',
  hora_fin: '',
  tipo_turno: 'Mañana'
})

const personalList = ref([])

// Cargar lista de personal para el select
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

const resetForm = () => {
  form.value = {
    personal_id: '',
    fecha: new Date().toISOString().slice(0,10),
    hora_inicio: '',
    hora_fin: '',
    tipo_turno: 'Mañana'
  }
}

watch(() => props.turno, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    resetForm()
  }
}, { immediate: true })

const guardar = async () => {
  try {
    if (props.isEditing) {
      await axios.put(`/api/turnos/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/turnos', form.value)
    }
    emit('saved')
    cerrar()
  } catch (error) {
    console.error('Error al guardar turno:', error)
    alert(error.response?.data?.error || 'Error al guardar el turno')
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
.form-group input, .form-group select {
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
