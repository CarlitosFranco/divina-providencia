<template>
  <div class="modal" v-if="show">
    <div class="modal-content">
      <h2>{{ isEditing ? 'Editar Personal' : 'Nuevo Personal' }}</h2>
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
          <label>Documento de Identidad</label>
          <input v-model="form.documento_identidad">
        </div>
        <div class="form-group">
          <label>Teléfono</label>
          <input v-model="form.telefono">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" v-model="form.email">
        </div>
        <div class="form-group">
          <label>Cargo *</label>
          <input v-model="form.cargo" required>
        </div>
        <div class="form-group">
          <label>Especialidad</label>
          <input v-model="form.especialidad">
        </div>
        <div class="form-group">
          <label>Fecha de Contratación *</label>
          <input type="date" v-model="form.fecha_contratacion" required>
        </div>
        <div class="form-group">
          <label>Activo</label>
          <select v-model="form.activo">
            <option :value="1">Sí</option>
            <option :value="0">No</option>
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
}

watch(() => props.personal, (newVal) => {
  if (newVal) {
    form.value = { ...newVal }
  } else {
    resetForm()
  }
}, { immediate: true })

const guardar = async () => {
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
    alert('Error al guardar el personal')
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
