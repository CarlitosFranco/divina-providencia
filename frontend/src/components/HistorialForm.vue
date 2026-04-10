<template>
  <div v-if="show" class="modal-overlay" @click.self="cerrar">
    <div class="modal-container">
      <div class="modal-header">
        <h2>{{ isEditing ? '✏️ Editar Historial Médico' : '➕ Nuevo Historial Médico' }}</h2>
        <button class="btn-close" @click="cerrar">✕</button>
      </div>

      <form @submit.prevent="guardar" class="modal-form">
        <div class="form-group">
          <label>Alergias</label>
          <textarea v-model="form.alergias" rows="2" placeholder="Ej: Penicilina, polen, ..."></textarea>
        </div>

        <div class="form-group">
          <label>Enfermedades crónicas</label>
          <textarea v-model="form.enfermedades_cronicas" rows="2" placeholder="Ej: Hipertensión, diabetes, ..."></textarea>
        </div>

        <div class="form-group">
          <label>Antecedentes familiares</label>
          <textarea v-model="form.antecedentes_familiares" rows="2" placeholder="Ej: Madre con cáncer, padre hipertenso..."></textarea>
        </div>

        <div class="form-group">
          <label>Cirugías previas</label>
          <textarea v-model="form.cirugias_previas" rows="2" placeholder="Ej: Apendicectomía, cesárea..."></textarea>
        </div>

        <div class="form-group">
          <label>Grupo sanguíneo</label>
          <select v-model="form.grupo_sanguineo">
            <option value="">Seleccionar</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" @click="cerrar">Cancelar</button>
          <button type="submit" class="btn-submit" :disabled="guardando">
            <span v-if="guardando" class="spinner-small"></span>
            {{ isEditing ? 'Actualizar' : 'Guardar' }}
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
  historial: Object,
  pacienteId: Number
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  alergias: '',
  enfermedades_cronicas: '',
  antecedentes_familiares: '',
  cirugias_previas: '',
  grupo_sanguineo: ''
})
const isEditing = ref(false)
const guardando = ref(false)

watch(() => props.show, (visible) => {
  if (visible) {
    if (props.historial && props.historial.id) {
      isEditing.value = true
      form.value = { ...props.historial }
    } else {
      isEditing.value = false
      form.value = {
        alergias: '',
        enfermedades_cronicas: '',
        antecedentes_familiares: '',
        cirugias_previas: '',
        grupo_sanguineo: ''
      }
    }
  }
}, { immediate: true })

const cerrar = () => {
  emit('close')
}

const guardar = async () => {
  guardando.value = true
  try {
    const token = localStorage.getItem('token')
    const url = isEditing.value
      ? `/api/historial/${form.value.id}`
      : '/api/historial'
    const method = isEditing.value ? 'put' : 'post'
    const payload = {
      paciente_id: props.pacienteId,
      ...form.value
    }
    await axios[method](url, payload, {
      headers: { Authorization: `Bearer ${token}` }
    })
    emit('saved')
    cerrar()
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.error || 'Error al guardar historial')
  } finally {
    guardando.value = false
  }
}
</script>

<style scoped>
/* Estilos iguales al modal de PacienteForm (o reutiliza los mismos) */
.modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:1000; backdrop-filter:blur(4px); }
.modal-container { background:white; border-radius:24px; width:90%; max-width:600px; max-height:85vh; overflow-y:auto; box-shadow:0 20px 35px -10px rgba(0,0,0,0.3); }
.modal-header { display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid #e2e8f0; background:#f8fafc; border-radius:24px 24px 0 0; }
.modal-header h2 { font-size:1.4rem; color:#1e293b; }
.btn-close { background:none; border:none; font-size:24px; cursor:pointer; color:#94a3b8; }
.btn-close:hover { color:#ef4444; }
.modal-form { padding:24px; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-weight:500; margin-bottom:6px; color:#334155; font-size:0.85rem; }
.form-group textarea, .form-group select { width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:12px; font-family:inherit; }
.modal-footer { display:flex; justify-content:flex-end; gap:12px; margin-top:24px; padding-top:16px; border-top:1px solid #e2e8f0; }
.btn-cancel { padding:10px 20px; background:#f1f5f9; border:none; border-radius:12px; cursor:pointer; }
.btn-submit { padding:10px 24px; background:linear-gradient(135deg,#667eea,#764ba2); border:none; border-radius:12px; color:white; font-weight:500; cursor:pointer; display:flex; align-items:center; gap:8px; }
.btn-submit:disabled { opacity:0.6; }
.spinner-small { width:16px; height:16px; border:2px solid rgba(255,255,255,0.3); border-top-color:white; border-radius:50%; animation:spin 0.6s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
