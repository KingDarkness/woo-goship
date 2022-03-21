import Vue from 'vue'
import Router from 'vue-router'

import Home from 'admin/pages/Home.vue'
import Pickups from 'admin/pages/pickup/List.vue'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
    },
    {
      path: '/pickups',
      name: 'pickups',
      component: Pickups,
    },
  ],
})
