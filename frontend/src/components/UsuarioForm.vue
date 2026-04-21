<template>
  <div v-if="show" class="modal-overlay" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Usuario' : '➕ Nuevo Usuario' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-group">
          <label>Nombre completo *</label>
          <input type="text" v-model="form.nombre" required />
        </div>

        <div class="form-group">
          <label>Apellidos (para el empleado)</label>
          <input type="text" v-model="form.apellidos" />
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input type="email" v-model="form.email" required />
        </div>

        <div class="form-group">
          <label>Documento de identidad</label>
          <input type="text" v-model="form.documento_identidad" />
        </div>

        <div class="form-group">
          <label>Teléfono</label>
          <input type="text" v-model="form.telefono" />
        </div>

        <div class="form-group" v-if="!isEditing">
          <label>Contraseña *</label>
          <input type="password" v-model="form.password" required />
        </div>

        <div class="form-group" v-if="isEditing">
          <label>Nueva contraseña (dejar vacío para no cambiar)</label>
          <input type="password" v-model="form.password" />
        </div>

        <div class="form-group">
          <label>Rol *</label>
          <select v-model="form.rol_id" required>
            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Estado</label>
          <select v-model="form.activo">
            <option :value="1">Activo</option>
            <option :value="0">Inactivo</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-submit" :disabled="guardando">
            <span v-if="guardando" class="spinner-small"></span>
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
  usuario: Object,
  isEditing: Boolean
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  nombre: '',
  apellidos: '',
  email: '',
  documento_identidad: '',
  telefono: '',
  password: '',
  rol_id: '',
  activo: 1
})
const roles = ref([])
const guardando = ref(false)

const cargarRoles = async () => {
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('/api/roles', {
      headers: { Authorization: `Bearer ${token}` }
    })
    roles.value = res.data
  } catch (err) {
    console.error(err)
  }
}

watch(() => props.show, (visible) => {
  if (visible) {
    cargarRoles()
    if (props.usuario && props.isEditing) {
      form.value = {
        nombre: props.usuario.nombre,
        apellidos: props.usuario.apellidos || '',
        email: props.usuario.email,
        documento_identidad: props.usuario.documento_identidad || '',
        telefono: props.usuario.telefono || '',
        password: '',
        rol_id: props.usuario.rol_id,
        activo: props.usuario.activo
      }
    } else {
      form.value = {
        nombre: '',
        apellidos: '',
        email: '',
        documento_identidad: '',
        telefono: '',
        password: '',
        rol_id: '',
        activo: 1
      }
    }
  }
}, { immediate: true })

const cerrar = () => emit('close')

const guardar = async () => {
  guardando.value = true
  try {
    const token = localStorage.getItem('token')
    const url = props.isEditing ? `/api/usuarios/${props.usuario.id}` : '/api/usuarios'
    const method = props.isEditing ? 'put' : 'post'
    await axios[method](url, form.value, {
      headers: { Authorization: `Bearer ${token}` }
    })
    emit('saved')
    cerrar()
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.error || 'Error al guardar usuario')
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
/* tus estilos de modal */
</style>
