<template>
  <div class="container" v-if="paciente">
    <div class="header">
      <h1>{{ paciente.nombres }} {{ paciente.apellidos }}</h1>
      <button @click="$router.push('/')" class="btn-back">Volver</button>
    </div>

    <!-- Pestañas -->
    <div class="tabs">
      <button :class="{ active: activeTab === 'info' }" @click="activeTab = 'info'">Datos Personales</button>
      <button :class="{ active: activeTab === 'medical' }" @click="activeTab = 'medical'">Historial Médico</button>
      <button :class="{ active: activeTab === 'treatments' }" @click="activeTab = 'treatments'">Tratamientos</button>
      <button :class="{ active: activeTab === 'appointments' }" @click="activeTab = 'appointments'">Citas</button>
      <button :class="{ active: activeTab === 'evolutions' }" @click="activeTab = 'evolutions'">Evoluciones</button>
      <button :class="{ active: activeTab === 'activities' }" @click="activeTab = 'activities'">Actividades</button>
    </div>

    <!-- Contenido de cada pestaña -->
    <div class="tab-content">
      <!-- Datos personales -->
      <div v-if="activeTab === 'info'">
        <div class="info-grid">
          <div><strong>Documento:</strong> {{ paciente.documento_identidad || 'No registrado' }}</div>
          <div><strong>Fecha nacimiento:</strong> {{ formatFecha(paciente.fecha_nacimiento) }}</div>
          <div><strong>Género:</strong> {{ paciente.genero }}</div>
          <div><strong>Teléfono:</strong> {{ paciente.telefono || 'No registrado' }}</div>
          <div><strong>Celular:</strong> {{ paciente.celular || 'No registrado' }}</div>
          <div><strong>Email:</strong> {{ paciente.email || 'No registrado' }}</div>
          <div><strong>Dirección:</strong> {{ paciente.direccion || 'No registrada' }}</div>
          <div><strong>Contacto emergencia:</strong> {{ paciente.contacto_emergencia_nombre || 'No registrado' }} ({{ paciente.contacto_emergencia_telefono || 'N/A' }})</div>
          <div><strong>Fecha ingreso:</strong> {{ formatFecha(paciente.fecha_ingreso) }}</div>
          <div><strong>Estado:</strong> {{ paciente.estado }}</div>
          <div><strong>Observaciones:</strong> {{ paciente.observaciones || 'Ninguna' }}</div>
        </div>
      </div>

      <!-- Historial médico -->
      <div v-if="activeTab === 'medical'">
        <div v-if="historial.length > 0">
          <div v-for="item in historial" :key="item.id" class="card">
            <p><strong>Alergias:</strong> {{ item.alergias || 'Ninguna' }}</p>
            <p><strong>Enfermedades crónicas:</strong> {{ item.enfermedades_cronicas || 'Ninguna' }}</p>
            <p><strong>Antecedentes familiares:</strong> {{ item.antecedentes_familiares || 'Ninguno' }}</p>
            <p><strong>Cirugías previas:</strong> {{ item.cirugias_previas || 'Ninguna' }}</p>
            <p><strong>Grupo sanguíneo:</strong> {{ item.grupo_sanguineo || 'No registrado' }}</p>
          </div>
        </div>
        <p v-else>No hay historial médico registrado.</p>
      </div>

      <!-- Tratamientos -->
      <div v-if="activeTab === 'treatments'">
        <div v-if="tratamientos.length > 0">
          <div v-for="trat in tratamientos" :key="trat.id" class="card">
            <strong>{{ trat.nombre_comercial }} ({{ trat.principio_activo }})</strong>
            <p>Dosis: {{ trat.dosis }} | Frecuencia: {{ trat.frecuencia }}</p>
            <p>Inicio: {{ formatFecha(trat.fecha_inicio) }} | Fin: {{ trat.fecha_fin ? formatFecha(trat.fecha_fin) : 'Activo' }}</p>
            <p>Indicaciones: {{ trat.indicaciones || 'Ninguna' }}</p>
            <p>Estado: {{ trat.estado }}</p>
          </div>
        </div>
        <p v-else>No hay tratamientos activos o previos.</p>
      </div>

      <!-- Citas -->
      <div v-if="activeTab === 'appointments'">
        <div v-if="citas.length > 0">
          <div v-for="cita in citas" :key="cita.id" class="card">
            <strong>Fecha: {{ formatFecha(cita.fecha) }} a las {{ cita.hora }}</strong>
            <p>Personal: {{ cita.personal_nombres }} {{ cita.personal_apellidos }}</p>
            <p>Motivo: {{ cita.motivo || 'No especificado' }}</p>
            <p>Estado: {{ cita.estado }}</p>
            <p v-if="cita.observaciones">Observaciones: {{ cita.observaciones }}</p>
          </div>
        </div>
        <p v-else>No hay citas registradas.</p>
      </div>

      <!-- Evoluciones -->
      <div v-if="activeTab === 'evolutions'">
        <div v-if="evoluciones.length > 0">
          <div v-for="evo in evoluciones" :key="evo.id" class="card">
            <strong>Fecha: {{ formatFecha(evo.fecha) }}</strong>
            <p>Personal: {{ evo.personal_nombres }} {{ evo.personal_apellidos }}</p>
            <div class="grid-2">
              <span>Presión: {{ evo.presion_arterial || 'N/A' }}</span>
              <span>Pulso: {{ evo.frecuencia_cardiaca || 'N/A' }} lpm</span>
              <span>Temperatura: {{ evo.temperatura || 'N/A' }} °C</span>
              <span>Peso: {{ evo.peso || 'N/A' }} kg</span>
              <span>Altura: {{ evo.altura || 'N/A' }} cm</span>
            </div>
            <p><strong>Síntomas:</strong> {{ evo.sintomas || 'No registrados' }}</p>
            <p><strong>Diagnóstico:</strong> {{ evo.diagnostico || 'No registrado' }}</p>
            <p><strong>Tratamiento indicado:</strong> {{ evo.tratamiento_indicado || 'No registrado' }}</p>
            <p><strong>Notas:</strong> {{ evo.notas || 'Ninguna' }}</p>
          </div>
        </div>
        <p v-else>No hay evoluciones registradas.</p>
      </div>

      <!-- Actividades del personal -->
      <div v-if="activeTab === 'activities'">
        <div v-if="actividades.length > 0">
          <div v-for="act in actividades" :key="act.id" class="card">
            <strong>Fecha: {{ formatFecha(act.fecha) }}</strong>
            <p>Personal: {{ act.personal_nombres }} {{ act.personal_apellidos }}</p>
            <p>Tipo: {{ act.tipo_actividad || 'No especificado' }}</p>
            <p>Descripción: {{ act.descripcion }}</p>
          </div>
        </div>
        <p v-else>No hay actividades registradas para este paciente.</p>
      </div>
    </div>
  </div>
  <div v-else class="container">Cargando...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const paciente = ref(null)
const historial = ref([])
const tratamientos = ref([])
const citas = ref([])
const evoluciones = ref([])
const actividades = ref([])
const activeTab = ref('info')

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

onMounted(async () => {
  const id = route.params.id
  try {
    const res = await axios.get(`/api/pacientes/${id}/completo`)
    paciente.value = res.data.paciente
    historial.value = res.data.historial
    tratamientos.value = res.data.tratamientos
    citas.value = res.data.citas
    evoluciones.value = res.data.evoluciones
    actividades.value = res.data.actividades
  } catch (err) {
    console.error(err)
    alert('Error al cargar los datos del paciente')
  }
})
</script>

<style scoped>
.container { max-width: 1000px; margin: 0 auto; padding: 20px; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.btn-back { background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
.tabs { display: flex; gap: 5px; margin-bottom: 20px; border-bottom: 1px solid #ddd; }
.tabs button { background: none; border: none; padding: 10px 15px; cursor: pointer; font-size: 1rem; }
.tabs button.active { border-bottom: 2px solid #42b983; color: #42b983; font-weight: bold; }
.tab-content { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px,1fr)); gap: 10px; }
.card { background: #f8f9fa; border-left: 4px solid #42b983; padding: 12px; margin-bottom: 12px; border-radius: 4px; }
.grid-2 { display: grid; grid-template-columns: repeat(2,1fr); gap: 5px; margin: 10px 0; }
</style>
