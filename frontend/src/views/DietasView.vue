<template>
  <div class="dietas-container">
    <div class="page-header">
      <h1>🍽️ Dietas</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nueva Dieta</button>
    </div>

    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando dietas...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarDietas" class="btn-retry">Reintentar</button>
    </div>

    <div v-else-if="dietas.length === 0" class="empty-state">
      <p>📭 No hay dietas registradas.</p>
      <button @click="abrirFormulario()" class="btn-primary">Crear la primera</button>
    </div>

    <div v-else class="cards-grid">
      <div v-for="dieta in dietas" :key="dieta.id" class="dieta-card">
        <div class="card-header">
          <h3>{{ dieta.nombres }} {{ dieta.apellidos }}</h3>
          <span class="badge-comida">{{ dieta.tipo_comida }}</span>
        </div>
        <div class="card-body">
          <p><strong>Fecha:</strong> {{ formatFecha(dieta.fecha) }}</p>
          <p><strong>Descripción:</strong> {{ dieta.descripcion }}</p>
        </div>
        <div class="card-actions">
          <button @click="editar(dieta)" class="btn-edit">Editar</button>
          <button @click="eliminar(dieta.id)" class="btn-delete">Eliminar</button>
        </div>
      </div>
    </div>

    <DietaForm
      :show="mostrarForm"
      :dieta="dietaSeleccionada"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import DietaForm from '@/components/DietaForm.vue'
import Swal from 'sweetalert2'

const dietas = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const dietaSeleccionada = ref(null)
const modoEdicion = ref(false)

const cargarDietas = async () => {
  cargando.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/dietas', {
      headers: { Authorization: `Bearer ${token}` }
    })
    dietas.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.error || err.message
  } finally {
    cargando.value = false
  }
}

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

const abrirFormulario = () => {
  dietaSeleccionada.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (dieta) => {
  dietaSeleccionada.value = { ...dieta }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  dietaSeleccionada.value = null
}

const recargarLista = () => {
  cargarDietas()
}

const eliminar = (id) => {
  Swal.fire({
    title: '¿Eliminar dieta?',
    text: 'Esta acción no se puede revertir',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const token = localStorage.getItem('token')
        await axios.delete(`/api/dietas/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        })
        Swal.fire('Eliminada', 'Dieta eliminada correctamente', 'success')
        cargarDietas()
      } catch (error) {
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar', 'error')
      }
    }
  })
}

onMounted(() => {
  cargarDietas()
})
</script>

<style scoped>
.dietas-container {
  background: white;
  border-radius: 28px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 40px;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102,126,234,0.3);
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.dieta-card {
  background: #f8fafc;
  border-radius: 20px;
  padding: 20px;
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}
.dieta-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -12px rgba(0,0,0,0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.card-header h3 {
  font-size: 1.2rem;
  color: #1e293b;
}
.badge-comida {
  background: #e0e7ff;
  color: #4338ca;
  padding: 4px 10px;
  border-radius: 40px;
  font-size: 0.7rem;
  font-weight: 600;
}
.card-body {
  margin: 16px 0;
  font-size: 0.85rem;
  color: #475569;
}
.card-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}
.btn-edit, .btn-delete {
  padding: 6px 14px;
  border: none;
  border-radius: 40px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
}
.btn-edit { background: #e0e7ff; color: #4338ca; }
.btn-edit:hover { background: #c7d2fe; }
.btn-delete { background: #fee2e2; color: #b91c1c; }
.btn-delete:hover { background: #fecaca; }
.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 60px 20px;
}
.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }
.btn-retry {
  margin-top: 16px;
  background: #667eea;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 40px;
  cursor: pointer;
}
</style>
