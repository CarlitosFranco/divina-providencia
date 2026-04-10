import { createRouter, createWebHistory } from 'vue-router'
import PacientesView from '../views/PacientesView.vue'
import LoginView from '../views/LoginView.vue'
import PersonalView from '../views/PersonalView.vue'
import NotFoundView from '../views/NotFoundView.vue'
import TurnosView from '../views/TurnosView.vue'
import ActividadesView from '../views/ActividadesView.vue'
import CitasView from '../views/CitasView.vue'
import PatientDetailsView from '../views/PatientDetailsView.vue'
import UsuariosView from '../views/UsuariosView.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    name: 'pacientes',
    component: PacientesView,
    meta: { requiresAuth: true }
  },
  {
    path: '/personal',
    name: 'personal',
    component: PersonalView,
    meta: { requiresAuth: true }
  },
  {
    path: '/turnos',
    name: 'turnos',
    component: TurnosView,
    meta: { requiresAuth: true }
  },
  {
    path: '/actividades',
    name: 'actividades',
    component: ActividadesView,
    meta: { requiresAuth: true }
  },
  {
    path: '/citas',
    name: 'citas',
    component: CitasView,
    meta: { requiresAuth: true }
  },
  {
    path: '/pacientes/:id',
    name: 'patient-details',
    component: PatientDetailsView,
    meta: { requiresAuth: true }
  },
  {
    path: '/usuarios',
    name: 'usuarios',
    component: UsuariosView,
    meta: { requiresAuth: true, roles: [1] }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
    meta: { requiresAuth: false }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guardia de navegación moderna (sin next())
router.beforeEach((to, from) => {
  const token = localStorage.getItem('token')
  const requiresAuth = to.meta.requiresAuth
  const rolId = localStorage.getItem('rol_id') ? parseInt(localStorage.getItem('rol_id')) : null

  if (requiresAuth && !token) {
    return '/login'
  }
  if (to.path === '/login' && token) {
    return '/'
  }
  if (to.meta.roles && !to.meta.roles.includes(rolId)) {
    return '/'
  }
  return true
})

export default router
