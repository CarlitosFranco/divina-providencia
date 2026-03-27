<template>
  <div class="modal" v-if="show">
    <div class="modal-content">
      <h2>{{ isEditing ? 'Editar Paciente' : 'Nuevo Paciente' }}</h2>
      <form @submit.prevent="guardar">
        <div class="form-group">
          <label>Nombres *</label>
          <input v-model="form.nombres" required>
        </div>
        <div class="form-group">
          <label>Apellidos *</label>
          <input v-model="form.apellidos" required>
        </div>
        <div class="form-group">
          <label>Fecha de Nacimiento *</label>
          <input type="date" v-model="form.fecha_nacimiento" required>
        </div>
        <div class="form-group">
          <label>Género</label>
          <select v-model="form.genero">
            <option>M</option>
            <option>F</option>
            <option>Otro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Documento de Identidad</label>
          <input v-model="form.documento_identidad">
        </div>
        <div class="form-group">
          <label>Teléfono</label>
          <input v-model="form.telefono">
        </div>
        <div class="form-group">
          <label>Celular</label>
          <input v-model="form.celular">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="form.email">
        </div>
        <div class="form-group">
          <label>Dirección</label>
          <textarea v-model="form.direccion"></textarea>
        </div>
        <div class="form-group">
          <label>Contacto de Emergencia (Nombre)</label>
          <input v-model="form.contacto_emergencia_nombre">
        </div>
        <div class="form-group">
          <label>Contacto de Emergencia (Teléfono)</label>
          <input v-model="form.contacto_emergencia_telefono">
        </div>
        <div class="form-group">
          <label>Fecha de Ingreso</label>
          <input type="date" v-model="form.fecha_ingreso">
        </div>
        <div class="form-group">
          <label>Estado</label>
          <select v-model="form.estado">
            <option>Activo</option>
            <option>Inactivo</option>
            <option>Trasladado</option>
            <option>Fallecido</option>
          </select>
        </div>
        <div class="form-group">
          <label>Observaciones</label>
          <textarea v-model="form.observaciones"></textarea>
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
import { ref, watch } from 'vue'
import axios from 'axios'

// Props y emits (no es necesario importarlos)
const props = defineProps({
  show: Boolean,
  paciente: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

// Estado del formulario
const form = ref({
  nombres: '',
  apellidos: '',
  fecha_nacimiento: '',
  genero: 'M',
  documento_identidad: '',
  telefono: '',
  celular: '',
  email: '',
  direccion: '',
  contacto_emergencia_nombre: '',
  contacto_emergencia_telefono: '',
  fecha_ingreso: new Date().toISOString().slice(0,10),
  estado: 'Activo',
  observaciones: ''
})

// Función para resetear el formulario
const resetForm = () => {
  form.value = {
    nombres: '',
    apellidos: '',
    fecha_nacimiento: '',
    genero: 'M',
    documento_identidad: '',
    telefono: '',
    celular: '',
    email: '',
    direccion: '',
    contacto_emergencia_nombre: '',
    contacto_emergencia_telefono: '',
    fecha_ingreso: new Date().toISOString().slice(0,10),
    estado: 'Activo',
    observaciones: ''
  }
}

// Watch para actualizar el formulario cuando cambia el paciente
watch(() => props.paciente, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    resetForm()
  }
}, { immediate: true })

// Guardar paciente
const guardar = async () => {
  try {
    if (props.isEditing) {
      await axios.put(`/api/pacientes/${form.value.id}`, form.value)
    } else {
      await axios.post('/api/pacientes', form.value)
    }
    emit('saved')
    cerrar()
  } catch (error) {
    console.error('Error al guardar:', error)
    alert('Error al guardar el paciente')
  }
}

// Cerrar modal
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
  max-width: 600px;
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
