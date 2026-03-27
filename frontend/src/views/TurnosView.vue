<template>
  <div class="container">
    <h1>Gestión de Turnos</h1>
    <button @click="abrirFormulario()" class="btn-nuevo">+ Nuevo Turno</button>

    <div v-if="cargando">Cargando...</div>
    <div v-else-if="error" class="error">
      <p>Error al cargar los turnos:</p>
      <pre>{{ errorMessage }}</pre>
    </div>
    <ul v-else>
      <li v-for="turno in turnos" :key="turno.id">
        <strong>{{ turno.nombres }} {{ turno.apellidos }}</strong><br />
        <small>
          Fecha: {{ formatFecha(turno.fecha) }} |
          Hora: {{ turno.hora_inicio }} - {{ turno.hora_fin }} |
          Turno: {{ turno.tipo_turno }}
        </small>
        <div class="actions">
          <button @click="editar(turno)">Editar</button>
          <button @click="confirmarEliminar(turno.id)">Eliminar</button>
        </div>
      </li>
    </ul>

    <TurnoForm
      :show="mostrarForm"
      :turno="turnoSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import TurnoForm from '@/components/TurnoForm.vue'
import Swal from 'sweetalert2'

const turnos = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const turnoSeleccionado = ref(null)
const modoEdicion = ref(false)

onMounted(async () => {
  await cargarTurnos()
})

const cargarTurnos = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/turnos')
    turnos.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

const abrirFormulario = () => {
  turnoSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (turno) => {
  turnoSeleccionado.value = { ...turno }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  turnoSeleccionado.value = null
}

const recargarLista = () => {
  cargarTurnos()
}

const confirmarEliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción no se puede revertir",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/turnos/${id}`)
        Swal.fire('Eliminado', 'El turno ha sido eliminado', 'success')
        cargarTurnos()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar el turno', 'error')
      }
    }
  })
}

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
li {
  background: #f5f5f5;
  margin: 10px 0;
  padding: 10px;
  border-radius: 5px;
  list-style: none;
}
.actions {
  margin-top: 10px;
}
.actions button {
  margin-right: 10px;
}
.btn-nuevo {
  margin-bottom: 20px;
  padding: 8px 16px;
  background-color: #42b983;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.error {
  background-color: #ffebee;
  border-left: 4px solid #f44336;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}
</style>
