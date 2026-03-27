import { createRouter, createWebHistory } from 'vue-router'
import PacientesView from '../views/PacientesView.vue'
import LoginView from '../views/LoginView.vue'
import PersonalView from '../views/PersonalView.vue'
import NotFoundView from '../views/NotFoundView.vue'
import TurnosView from '../views/TurnosView.vue'
import ActividadesView from '../views/ActividadesView.vue'   // <-- Nueva importación

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
    path: '/actividades',                // <-- Nueva ruta
    name: 'actividades',
    component: ActividadesView,
    meta: { requiresAuth: true }
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

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  const requiresAuth = to.meta.requiresAuth

  if (requiresAuth && !token) {
    return '/login'
  }
  if (to.path === '/login' && token) {
    return '/'
  }
  return true
})

export default router
