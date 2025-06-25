import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import CourseView from '@/views/CourseView.vue'
import LessonView from '@/views/LessonView.vue'
import JumpView from '@/views/JumpView.vue'

const router = createRouter({ 
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
    },
    {
      path: '/detailcourse/:slug',
      name: 'course',
      component: CourseView,
    },
    {
      path: '/:slug/lessondetail/:id',
      name: 'lesson',
      component: LessonView,
    },
    {
      path: '/:slug/jump/:setId',
      name: 'jump',
      component: JumpView,
    },
  ],
})

export default router
