<template>
  <div class="container">
    <h1>Gestión de Personal</h1>
    <button @click="abrirFormulario()" class="btn-nuevo">+ Nuevo Personal</button>

    <div v-if="cargando">Cargando...</div>
    <div v-else-if="error" class="error">
      <p>Error al cargar el personal:</p>
      <pre>{{ errorMessage }}</pre>
    </div>
    <ul v-else>
      <li v-for="persona in personal" :key="persona.id">
        <strong>{{ persona.nombres }} {{ persona.apellidos }}</strong><br />
        <small>
          Documento: {{ persona.documento_identidad }} |
          Cargo: {{ persona.cargo }} |
          Especialidad: {{ persona.especialidad || 'N/A' }} |
          Activo: {{ persona.activo ? 'Sí' : 'No' }}
        </small>
        <div class="actions">
          <button @click="editar(persona)">Editar</button>
          <button @click="confirmarEliminar(persona.id)">Eliminar</button>
        </div>
      </li>
    </ul>

    <PersonalForm
      :show="mostrarForm"
      :personal="personalSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import PersonalForm from '@/components/PersonalForm.vue'
import Swal from 'sweetalert2'

const personal = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const personalSeleccionado = ref(null)
const modoEdicion = ref(false)

onMounted(async () => {
  await cargarPersonal()
})

const cargarPersonal = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/personal')
    personal.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

const abrirFormulario = () => {
  personalSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (persona) => {
  personalSeleccionado.value = { ...persona }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  personalSeleccionado.value = null
}

const recargarLista = () => {
  cargarPersonal()
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
        await axios.delete(`/api/personal/${id}`)
        Swal.fire('Eliminado', 'El personal ha sido eliminado', 'success')
        cargarPersonal()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar el personal', 'error')
      }
    }
  })
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
