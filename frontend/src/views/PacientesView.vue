<template>
  <div class="container">
    <h1>Lista de Pacientes</h1>
    <button @click="abrirFormulario()" class="btn-nuevo">+ Nuevo Paciente</button>

    <div v-if="cargando">Cargando...</div>
    <div v-else-if="error" class="error">
      <p>Error al cargar los pacientes:</p>
      <pre>{{ errorMessage }}</pre>
    </div>
    <ul v-else>
      <li v-for="paciente in pacientes" :key="paciente.id">
        <strong>{{ paciente.nombres }} {{ paciente.apellidos }}</strong><br />
        <small>Documento: {{ paciente.documento_identidad }} | Estado: {{ paciente.estado }}</small>
        <div class="actions">
          <button @click="editar(paciente)">Editar</button>
          <button @click="confirmarEliminar(paciente.id)">Eliminar</button>
        </div>
      </li>
    </ul>

    <PacienteForm
      :show="mostrarForm"
      :paciente="pacienteSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import PacienteForm from '@/components/PacienteForm.vue'
import Swal from 'sweetalert2'

// Estado reactivo
const pacientes = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const pacienteSeleccionado = ref(null)
const modoEdicion = ref(false)

// Cargar pacientes al montar el componente
onMounted(async () => {
  await cargarPacientes()
})

// Obtener lista de pacientes desde el backend
const cargarPacientes = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/pacientes')
    pacientes.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

// Abrir formulario para crear nuevo paciente
const abrirFormulario = () => {
  pacienteSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

// Abrir formulario para editar paciente existente
const editar = (paciente) => {
  pacienteSeleccionado.value = { ...paciente }
  modoEdicion.value = true
  mostrarForm.value = true
}

// Cerrar formulario
const cerrarFormulario = () => {
  mostrarForm.value = false
  pacienteSeleccionado.value = null
}

// Recargar lista después de guardar
const recargarLista = () => {
  cargarPacientes()
}

// Confirmar y eliminar paciente
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
        await axios.delete(`/api/pacientes/${id}`)
        Swal.fire('Eliminado', 'El paciente ha sido eliminado', 'success')
        cargarPacientes()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar el paciente', 'error')
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
