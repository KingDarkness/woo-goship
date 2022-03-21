import Vue from 'vue'
import App from './App.vue'
import router from './router'
import menuFix from './utils/admin-menu-fix'
import 'vue-wp-list-table/dist/vue-wp-list-table.css'
import 'select2'
import 'select2/dist/css/select2.css'
import './style.css'

Vue.config.productionTip = false
console.log(WEB_GLOBAL_JS_VARIABLES)

Vue.mixin({
  computed: {
    configUrl() {
      return `${WEB_GLOBAL_JS_VARIABLES.admin_url}/admin.php?page=wc-settings&tab=shipping&section=woo_goship`
    },
  },
  methods: {
    formatNumber(amount, decimalCount = 2, decimal = '.', thousands = ',') {
      decimalCount = Math.abs(decimalCount)
      decimalCount = isNaN(decimalCount) ? 2 : decimalCount

      const negativeSign = amount < 0 ? '-' : ''

      let i = parseInt(
        (amount = Math.abs(Number(amount) || 0).toFixed(decimalCount))
      ).toString()
      let j = i.length > 3 ? i.length % 3 : 0

      return (
        negativeSign +
        (j ? i.substr(0, j) + thousands : '') +
        i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousands) +
        (decimalCount
          ? decimal +
            Math.abs(amount - i)
              .toFixed(decimalCount)
              .slice(2)
          : '')
      )
    },
    formatCurrency(amount) {
      return this.formatNumber(amount, 0) + ' đ'
    },
  },
})

/* eslint-disable no-new */
new Vue({
  el: '#vue-admin-app',
  router,
  render: (h) => h(App),
})

// fix the admin menu for the slug "woo-goship"
menuFix(WEB_GLOBAL_JS_VARIABLES.text_domain)
