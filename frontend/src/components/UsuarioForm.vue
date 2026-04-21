<template>
  <div v-if="show" class="modal-overlay" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Usuario' : '➕ Nuevo Usuario' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nombre completo <span class="required">*</span></label>
            <input type="text" v-model="form.nombre" required placeholder="Ej: María González" />
          </div>
          <div class="form-group">
            <label>Apellidos <span class="optional">(opcional)</span></label>
            <input type="text" v-model="form.apellidos" placeholder="Ej: López" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email <span class="required">*</span></label>
            <input type="email" v-model="form.email" required placeholder="correo@ejemplo.com" />
          </div>
          <div class="form-group">
            <label>Documento de identidad</label>
            <input type="text" v-model="form.documento_identidad" placeholder="Ej: 12345678" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Teléfono</label>
            <input type="tel" v-model="form.telefono" placeholder="Ej: 987654321" />
          </div>
          <div class="form-group" v-if="!isEditing">
            <label>Contraseña <span class="required">*</span></label>
            <input type="password" v-model="form.password" required placeholder="Mínimo 6 caracteres" />
          </div>
          <div class="form-group" v-if="isEditing">
            <label>Nueva contraseña <span class="optional">(dejar vacío para no cambiar)</span></label>
            <input type="password" v-model="form.password" placeholder="••••••••" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Rol <span class="required">*</span></label>
            <select v-model="form.rol_id" required>
              <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Estado</label>
            <div class="radio-group">
              <label class="radio-option">
                <input type="radio" v-model="form.activo" :value="1" /> Activo
              </label>
              <label class="radio-option">
                <input type="radio" v-model="form.activo" :value="0" /> Inactivo
              </label>
            </div>
          </div>
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
    console.error('Error cargando roles:', err)
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
/* Fondo oscuro semitransparente */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

/* Contenedor del modal */
.modal-container {
  background: white;
  border-radius: 28px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  animation: fadeInUp 0.2s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Cabecera del modal */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 28px;
  border-bottom: 1px solid #e9eef3;
  background: #fafcff;
  border-radius: 28px 28px 0 0;
}

.modal-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  background: linear-gradient(135deg, #1e293b, #2d3a4f);
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  margin: 0;
}

.btn-close {
  background: none;
  border: none;
  font-size: 26px;
  cursor: pointer;
  color: #94a3b8;
  transition: color 0.2s;
  line-height: 1;
}

.btn-close:hover {
  color: #ef4444;
}

/* Formulario */
.modal-form {
  padding: 28px;
}

.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.form-group {
  flex: 1;
  min-width: 180px;
}

.form-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 8px;
  color: #1e293b;
  font-size: 0.85rem;
  letter-spacing: 0.3px;
}

.required {
  color: #ef4444;
  font-size: 0.75rem;
  margin-left: 2px;
}

.optional {
  color: #94a3b8;
  font-size: 0.7rem;
  font-weight: normal;
  margin-left: 4px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 16px;
  font-family: inherit;
  font-size: 0.9rem;
  transition: all 0.2s;
  background: #fff;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Grupo de radios */
.radio-group {
  display: flex;
  gap: 20px;
  align-items: center;
  margin-top: 6px;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: #334155;
  cursor: pointer;
}

.radio-option input[type="radio"] {
  width: 18px;
  height: 18px;
  margin: 0;
  accent-color: #667eea;
}

/* Botones */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid #e9eef3;
}

.btn-cancel {
  background: #f1f5f9;
  border: none;
  padding: 10px 24px;
  border-radius: 40px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cancel:hover {
  background: #e2e8f0;
}

.btn-submit {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
  padding: 10px 28px;
  border-radius: 40px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .modal-container {
    width: 95%;
    max-height: 85vh;
  }
  .form-row {
    flex-direction: column;
    gap: 16px;
  }
  .modal-header {
    padding: 18px 20px;
  }
  .modal-form {
    padding: 20px;
  }
  .radio-group {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}
</style>
