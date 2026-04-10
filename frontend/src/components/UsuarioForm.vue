<template>
  <div v-if="show" class="modal-overlay" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Usuario' : '➕ Nuevo Usuario' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-group">
          <label>Nombre *</label>
          <input type="text" v-model="form.nombre" required />
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input type="email" v-model="form.email" required />
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
  email: '',
  password: '',
  rol_id: '',
  activo: 1
})
const roles = ref([])
const guardando = ref(false)

// Cargar roles disponibles
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
        email: props.usuario.email,
        password: '',
        rol_id: props.usuario.rol_id,
        activo: props.usuario.activo
      }
    } else {
      form.value = { nombre: '', email: '', password: '', rol_id: '', activo: 1 }
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
/* mismos estilos del modal de PacienteForm */
.modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:1000; }
.modal-container { background:white; border-radius:24px; width:90%; max-width:500px; max-height:85vh; overflow-y:auto; }
.modal-header { padding:20px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; }
.modal-form { padding:24px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; margin-bottom:6px; font-weight:500; }
.form-group input, .form-group select { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:12px; }
.modal-footer { display:flex; justify-content:flex-end; gap:12px; margin-top:24px; }
.btn-cancel { background:#f1f5f9; border:none; padding:10px 20px; border-radius:12px; }
.btn-submit { background:linear-gradient(135deg,#667eea,#764ba2); border:none; padding:10px 24px; border-radius:12px; color:white; }
</style>
