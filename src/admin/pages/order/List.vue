<template>
  <div class="order">
    <div class="page-header">
      <h1 class="wp-heading-inline">Goship - Đơn hàng</h1>
      <div class="page-actions">
        <a :href="configUrl" class="button">Cài đặt</a>
        <router-link :to="{ name: 'pickups' }" class="button">
          Điểm lấy hàng
        </router-link>
      </div>
    </div>
    <list-table
      :loading="loading"
      :rows="items"
      @pagination="goToPage"
      :columns="columns"
      :show-cb="true"
      :actions="actions"
      :bulk-actions="[]"
      action-column="shop_name"
      @action:click="onActionClick"
      @bulk:click="onBulkAction"
      :total-items="pagination.total"
      :total-pages="pagination.total_page"
      :per-page="pagination.items_per_page"
      :current-page="pagination.current_page"
    >
      <template slot="id" slot-scope="data"> {{ orderId(data.row) }} </template>
      <template slot="ship_address" slot-scope="data">
        {{ orderShipTo(data.row) }}
      </template>
      <template slot="date_created" slot-scope="data">
        {{ orderMeta(data.row)._gs_send_at }}
      </template>
      <template slot="total" slot-scope="data">
        <span v-if="orderMeta(data.row)._gs_fee">
          Phí:
          <span style="color: red">{{
            formatCurrency(orderMeta(data.row)._gs_fee)
          }}</span>
        </span>
        <br />
        <span v-if="orderMeta(data.row)._gs_cod">
          COD:
          <span style="color: red">{{
            formatCurrency(orderMeta(data.row)._gs_cod)
          }}</span>
        </span>
      </template>
      <template slot="carrier" slot-scope="data">
        {{ orderMeta(data.row)._gs_carrier }}
      </template>
      <template slot="tracking" slot-scope="data">
        <span v-if="orderMeta(data.row)._gs_code"
          >Mã Goship: {{ orderMeta(data.row)._gs_code }}</span
        >
        <br />
        <span v-if="orderMeta(data.row)._gs_tracking_number"
          >Mã tracking: {{ orderMeta(data.row)._gs_tracking_number }}</span
        >
      </template>
      <template slot="status" slot-scope="data">
        {{ orderMeta(data.row)._gs_status_name }} <br />
      </template>

      <template slot="action" slot-scope="data">
        <button
          class="button"
          @click="sendShipment(data.row)"
          v-if="
            !orderMeta(data.row)._gs_code ||
            (orderMeta(data.row)._gs_code &&
              orderMeta(data.row)._gs_status == 914)
          "
        >
          Gửi đơn
        </button>
        <button
          class="button"
          @click="detailShipment(data.row)"
          v-if="orderMeta(data.row)._gs_code"
        >
          Chi tiết
        </button>

        <button
          class="button"
          @click="printShipment(data.row)"
          v-if="orderMeta(data.row)._gs_code"
        >
          In
        </button>

        <button
          class="button delete-button"
          @click="cancelShipment(data.row)"
          v-if="
            orderMeta(data.row)._gs_code &&
            [901, 902, 900].includes(parseInt(orderMeta(data.row)._gs_status))
          "
        >
          Hủy
        </button>
      </template>

      <template slot="filters">
        <input type="text" v-model="filter.search" />
        <button class="button" @click="loadItems">Tìm kiếm</button>
      </template>
    </list-table>

    <div id="send-shipment-dialog" title="Gửi đơn hàng">
      <send-shipment-form
        ref="send-shipment-form"
        @success="submitSendShipment"
      />
    </div>

    <div id="shipment-delete-confirm" title="Hủy đơn hàng">
      <p>
        <span
          class="ui-icon ui-icon-alert"
          style="float: left; margin: 12px 12px 20px 0"
        ></span>
        Bạn chắc chắn muốn hủy đơn hàng
        <span v-if="current">{{ orderMeta(current)._gs_code }}</span
        >?
      </p>
    </div>

    <div id="detail-shipment-dialog" title="Chi tiết vận đơn">
      <detail-shipment ref="detail-shipment" />
    </div>
  </div>
</template>

<script>
import ListTable from 'vue-wp-list-table'
import order from 'admin/models/order'
import shipment from 'admin/models/shipment'
import SendShipmentForm from './SendShipmentForm.vue'
import DetailShipment from './DetailShipment.vue'

export default {
  name: 'orders',
  components: { ListTable, SendShipmentForm, DetailShipment },
  data() {
    return {
      loading: false,
      items: [],
      current: null,
      filter: { search: '' },
      pagination: {
        total: 1,
        items_per_page: 25,
        total_page: 1,
        current_page: 1,
      },
      columns: {
        id: {
          label: 'Đơn hàng',
        },
        ship_address: {
          label: 'Địa chỉ nhận hàng',
        },
        date_created: {
          label: 'Ngày gửi đơn',
        },
        carrier: {
          label: 'Hãng vận chuyển',
        },
        tracking: {
          label: 'Mã',
        },
        status: {
          label: 'Trạng thái',
        },
        total: { label: 'Tổng tiền ship' },
        action: { label: 'Hành động' },
      },
      actions: [
        {
          key: 'edit',
          label: 'Sửa',
        },
        {
          key: 'trash',
          label: 'Xóa',
        },
      ],
    }
  },

  created() {
    this.filter.search = this.$route.query.search
    this.loadItems()

    this.$nextTick(() => {
      jQuery('#send-shipment-dialog').dialog({
        dialogClass: 'wp-dialog',
        width: 800,
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        buttons: {
          'Gửi đơn': () => {
            this.$refs['send-shipment-form'].$emit('submit')
          },
          Đóng: function () {
            jQuery(this).dialog('close')
          },
        },
      })

      jQuery('#shipment-delete-confirm').dialog({
        dialogClass: 'wp-dialog',
        width: 400,
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        buttons: {
          Hủy: () => {
            this.submitDelete(this.current)
          },
          Đóng: function () {
            jQuery(this).dialog('close')
          },
        },
      })

      jQuery('#detail-shipment-dialog').dialog({
        dialogClass: 'wp-dialog',
        width: 1000,
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        buttons: {
          Đóng: function () {
            jQuery(this).dialog('close')
          },
        },
      })
    })
  },

  mounted() {
    this.jQuery = jQuery
  },

  methods: {
    loadItems() {
      let self = this

      self.loading = true

      order.getByQuery(
        {
          page: this.pagination.current_page,
          per_page: this.pagination.items_per_page,
          ...this.filter,
        },
        (response, status, xhr) => {
          self.loading = false
          self.items = response
          self.pagination.total = parseInt(xhr.getResponseHeader('X-Wp-Total'))
          self.pagination.total_page = parseInt(
            xhr.getResponseHeader('X-Wp-Totalpages')
          )
          self.$router.replace({
            query: {
              ...this.filter,
              ...{ page: self.pagination.current_page },
            },
          })
        }
      )
    },

    onActionClick(action, row) {},

    onBulkAction(action, items) {},

    goToPage(page) {
      this.pagination.current_page = page
      this.loadItems()
    },

    orderId(order) {
      return `#${order.id} ${order.shipping.first_name} ${
        order.shipping.last_name
      } - ${order.shipping.phone ? order.shipping.phone : order.billing.phone}`
    },

    orderMeta(order) {
      return order.meta_data.reduce((obj, item) => {
        return {
          ...obj,
          [item.key]: item.value,
        }
      }, {})
    },

    orderShipTo(order) {
      let meta = this.orderMeta(order)
      let addresses = [
        order.shipping.address_1,
        meta._billing_ward_name,
        meta._billing_district_name,
        meta._billing_city_name,
      ]
      return addresses.filter(Boolean).join(', ')
    },

    sendShipment(item) {
      order.getGoshipInfo(item.id, {}, (shipping) => {
        this.$refs['send-shipment-form'].$emit('init', shipping.shipment)
        jQuery('#send-shipment-dialog').dialog('open')
      })
    },
    submitSendShipment(data) {
      shipment.create(data, (response) => {
        jQuery('#send-shipment-dialog').dialog('close')
        this.loadItems()
      })
    },
    cancelShipment(item) {
      this.current = item
      jQuery('#shipment-delete-confirm').dialog('open')
    },
    submitDelete(item) {
      shipment.delete(
        item.id,
        {},
        () => {
          jQuery('#shipment-delete-confirm').dialog('close')
          this.loadItems()
        },
        (error) => {
          let errors = jQuery.parseJSON(error.responseText)
          alert(errors.message)
        }
      )
    },
    detailShipment(item) {
      this.current = item
      this.$refs['detail-shipment'].$emit('init', item)
      this.$nextTick(() => {
        jQuery('#detail-shipment-dialog').dialog('open')
      })
    },
    printShipment(item) {
      shipment.printShipment(this.orderMeta(item)._gs_code, {}, (response) => {
        window.open(response.url, '_blank').focus()
      })
    },
  },
}
</script>

<style scoped>
.delete-button {
  color: red;
  border-color: red;
}
.delete-button:hover {
  color: red;
  border-color: red;
}
.delete-button:enabled {
  color: red;
  border-color: red;
}
</style>
