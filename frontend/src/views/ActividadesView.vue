<template>
  <div class="container">
    <h1>Actividades del Personal</h1>
    <button @click="abrirFormulario()" class="btn-nuevo">+ Nueva Actividad</button>

    <div v-if="cargando">Cargando...</div>
    <div v-else-if="error" class="error">
      <p>Error al cargar las actividades:</p>
      <pre>{{ errorMessage }}</pre>
    </div>
    <ul v-else>
      <li v-for="actividad in actividades" :key="actividad.id">
        <strong>{{ actividad.personal_nombres }} {{ actividad.personal_apellidos }}</strong>
        <span> → </span>
        <strong>{{ actividad.paciente_nombres }} {{ actividad.paciente_apellidos }}</strong>
        <br />
        <small>
          Fecha: {{ formatFecha(actividad.fecha) }} |
          Tipo: {{ actividad.tipo_actividad || 'Sin especificar' }} |
          Descripción: {{ actividad.descripcion }}
        </small>
        <div class="actions">
          <button @click="editar(actividad)">Editar</button>
          <button @click="confirmarEliminar(actividad.id)">Eliminar</button>
        </div>
      </li>
    </ul>

    <ActividadForm
      :show="mostrarForm"
      :actividad="actividadSeleccionada"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import ActividadForm from '@/components/ActividadForm.vue'
import Swal from 'sweetalert2'

const actividades = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const actividadSeleccionada = ref(null)
const modoEdicion = ref(false)

onMounted(async () => {
  await cargarActividades()
})

const cargarActividades = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/actividades')
    actividades.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

const abrirFormulario = () => {
  actividadSeleccionada.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (actividad) => {
  actividadSeleccionada.value = { ...actividad }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  actividadSeleccionada.value = null
}

const recargarLista = () => {
  cargarActividades()
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
        await axios.delete(`/api/actividades/${id}`)
        Swal.fire('Eliminado', 'La actividad ha sido eliminada', 'success')
        cargarActividades()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar la actividad', 'error')
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
