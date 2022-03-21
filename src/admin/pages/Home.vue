<template>
  <div class="home">
    <order />
  </div>
</template>

<script>
import Order from './order/List.vue'
export default {
  name: 'Home',
  components: { Order },

  data() {
    return {}
  },
  mounted() {},
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped></style>
