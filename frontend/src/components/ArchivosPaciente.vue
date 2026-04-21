<template>
  <div>
    <div class="archivos-header">
      <h3>📎 Documentos adjuntos</h3>
      <button @click="abrirModalSubida" class="btn-add-file">+ Subir archivo</button>
    </div>

    <div v-if="cargando" class="loading">Cargando archivos...</div>
    <div v-else-if="archivos.length === 0" class="empty">
      No hay archivos subidos para este paciente.
    </div>
    <div v-else class="archivos-list">
      <div v-for="arch in archivos" :key="arch.id" class="archivo-item">
        <div class="archivo-info">
          <span class="archivo-nombre">{{ arch.nombre_original }}</span>
          <span class="archivo-tamaño">{{ formatearTamaño(arch.tamaño) }}</span>
          <span class="archivo-fecha">{{ formatearFecha(arch.created_at) }}</span>
        </div>
        <div class="archivo-acciones">
          <button @click="descargar(arch.id)" class="btn-download">📥 Descargar</button>
          <button @click="eliminar(arch.id)" class="btn-delete-file">🗑️ Eliminar</button>
        </div>
      </div>
    </div>

    <!-- Modal para subir archivo -->
    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2>Subir archivo</h2>
          <button class="btn-close" @click="cerrarModal">✕</button>
        </div>
        <div class="modal-body">
          <input type="file" ref="fileInput" @change="seleccionarArchivo" accept="image/*,application/pdf,.doc,.docx,.txt" />
          <p v-if="archivoSeleccionado">Archivo: {{ archivoSeleccionado.name }}</p>
        </div>
        <div class="modal-footer">
          <button @click="subirArchivo" class="btn-submit" :disabled="subiendo">
            {{ subiendo ? 'Subiendo...' : 'Subir' }}
          </button>
          <button @click="cerrarModal" class="btn-cancel">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  pacienteId: Number
})

const archivos = ref([])
const cargando = ref(true)
const mostrarModal = ref(false)
const archivoSeleccionado = ref(null)
const fileInput = ref(null)
const subiendo = ref(false)

const cargarArchivos = async () => {
  cargando.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get(`/api/archivos/paciente?paciente_id=${props.pacienteId}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    archivos.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    cargando.value = false
  }
}

const formatearTamaño = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatearFecha = (fecha) => {
  if (!fecha) return ''
  const d = new Date(fecha)
  return d.toLocaleDateString()
}

const abrirModalSubida = () => {
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  archivoSeleccionado.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const seleccionarArchivo = (e) => {
  archivoSeleccionado.value = e.target.files[0]
}

const subirArchivo = async () => {
  if (!archivoSeleccionado.value) {
    alert('Selecciona un archivo')
    return
  }
  subiendo.value = true
  const formData = new FormData()
  formData.append('archivo', archivoSeleccionado.value)
  formData.append('paciente_id', props.pacienteId)

  try {
    const token = localStorage.getItem('token')
    await axios.post('/api/archivos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${token}`
      }
    })
    cerrarModal()
    cargarArchivos()
  } catch (err) {
    console.error(err)
    alert('Error al subir archivo')
  } finally {
    subiendo.value = false
  }
}

const descargar = async (id) => {
  const token = localStorage.getItem('token')
  window.open(`/api/archivos/descargar/${id}?token=${token}`, '_blank')
}

const eliminar = async (id) => {
  if (!confirm('¿Eliminar este archivo?')) return
  try {
    const token = localStorage.getItem('token')
    await axios.delete(`/api/archivos/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    cargarArchivos()
  } catch (err) {
    console.error(err)
    alert('Error al eliminar archivo')
  }
}

onMounted(() => {
  cargarArchivos()
})
</script>

<style scoped>
.archivos-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.btn-add-file {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 6px 14px;
  border-radius: 40px;
  cursor: pointer;
}
.archivos-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.archivo-item {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}
.archivo-info {
  display: flex;
  gap: 15px;
  align-items: center;
  flex-wrap: wrap;
}
.archivo-nombre {
  font-weight: 500;
  color: #1e293b;
}
.archivo-tamaño, .archivo-fecha {
  font-size: 0.75rem;
  color: #64748b;
}
.archivo-acciones {
  display: flex;
  gap: 8px;
}
.btn-download, .btn-delete-file {
  padding: 4px 12px;
  border: none;
  border-radius: 40px;
  cursor: pointer;
}
.btn-download {
  background: #e0e7ff;
  color: #4338ca;
}
.btn-delete-file {
  background: #fee2e2;
  color: #b91c1c;
}
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
}
.modal-container {
  background: white;
  border-radius: 24px;
  width: 90%;
  max-width: 500px;
  padding: 20px;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-body {
  margin: 20px 0;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
.btn-submit, .btn-cancel {
  padding: 8px 20px;
  border: none;
  border-radius: 40px;
  cursor: pointer;
}
.btn-submit {
  background: #667eea;
  color: white;
}
.btn-cancel {
  background: #e2e8f0;
}
</style>
