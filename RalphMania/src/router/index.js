import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AboutView from '../views/AboutView.vue'
import ContactsView from '../views/ContactsView.vue'
import ShopView from '../views/ShopView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { title: 'HOME | RalphMania' }, // Add meta title
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
      meta: { title: 'ABOUT | RalphMania' }, // Add meta title
    },
    {
      path: '/contacts',
      name: 'contacts',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/ContactsView.vue'),
      meta: { title: 'CONTACTS | RalphMania' }, // Add meta title
    },
    {
      path: '/shop',
      name: 'shop',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/ShopView.vue'),
      meta: { title: 'SHOP | RalphMania' }, // Add meta title
    }
  ],
})

// Navigation guard for setting the document title
router.beforeEach((to, from, next) => {
  document.title = to.meta.title || 'RalphMania';
  next();
});

// Change the background based on the route
router.beforeEach((to, from, next) => {
  const body = document.body;

  if (to.name === 'home') {
    body.style.backgroundImage = "url('../../public/img/Coder_RoltonsLV.png')";
  } else if (to.name === 'about') {
    body.style.backgroundImage = "url('../../public/img/Hostage_Adventure.png')";
  } else if (to.name === 'contacts') {
    body.style.backgroundImage = "url('../../public/img/Coder_RoltonsLV.png')";
  } else if (to.name === 'shop') {
    body.style.backgroundImage = "url('../../public/img/Coder_RoltonsLV.png')";
  } else {
    body.style.backgroundImage = '';  // Default background
  }

  next();
});

export default router
