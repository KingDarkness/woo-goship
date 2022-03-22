<template>
  <div class="pickup">
    <div class="page-header">
      <div>
        <h1 class="wp-heading-inline">Goship - Điểm lấy hàng</h1>
        <button
          class="page-title-action"
          @click="
            () => {
              jQuery('#pickup-create-dialog').dialog('open')
            }
          "
        >
          Thêm mới
        </button>
      </div>
      <div class="page-actions">
        <router-link :to="{ name: 'home' }" class="button">
          Đơn hàng
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
      :bulk-actions="[
        {
          key: 'trash',
          label: 'Xóa',
        },
      ]"
      action-column="shop_name"
      @action:click="onActionClick"
      @bulk:click="onBulkAction"
      :total-items="pagination.total"
      :total-pages="pagination.total_page"
      :per-page="pagination.items_per_page"
      :current-page="pagination.current_page"
    >
      <template slot="default" slot-scope="data">
        <span
          v-if="data.row.default == 1"
          class="dashicons dashicons-yes"
        ></span>
        <span v-else> &nbsp; </span>
      </template>

      <template slot="filters"> </template>
    </list-table>

    <div id="pickup-create-dialog" title="Thêm mới điểm lấy hàng">
      <pickup-form ref="create-form" @success="submitCreate" />
    </div>

    <div id="pickup-update-dialog" title="Sửa điểm lấy hàng">
      <pickup-form ref="update-form" @success="submitUpdate" />
    </div>

    <div id="pickup-delete-confirm" title="Xóa điểm lấy hàng">
      <p>
        <span
          class="ui-icon ui-icon-alert"
          style="float: left; margin: 12px 12px 20px 0"
        ></span>
        Bạn chắc chắn muốn xóa?
      </p>
    </div>
  </div>
</template>

<script>
import ListTable from 'vue-wp-list-table'
import PickupForm from './Form.vue'
import pickup from 'admin/models/pickup'

export default {
  name: 'pickups',
  components: { ListTable, PickupForm },
  data() {
    return {
      loading: false,
      items: [],
      current: null,
      pagination: {
        total: 1,
        items_per_page: 25,
        total_page: 1,
        current_page: 1,
      },
      columns: {
        shop_name: {
          label: 'Tên shop',
        },
        name: {
          label: 'Người liên hệ',
        },
        phone: { label: 'Điện thoại liên hệ' },
        full_street: { label: 'Địa chỉ' },
        default: { label: 'Mặc định' },
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
    this.loadItems()
  },

  mounted() {
    this.jQuery = jQuery
    this.$nextTick(() => {
      jQuery('#pickup-create-dialog').dialog({
        dialogClass: 'wp-dialog',
        width: 400,
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        buttons: {
          'Tạo mới': () => {
            this.$refs['create-form'].$emit('submit')
          },
          Đóng: function () {
            jQuery(this).dialog('close')
          },
        },
      })

      jQuery('#pickup-update-dialog').dialog({
        dialogClass: 'wp-dialog',
        width: 400,
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        buttons: {
          'Cập nhật': () => {
            this.$refs['update-form'].$emit('submit')
          },
          Đóng: function () {
            jQuery(this).dialog('close')
          },
        },
      })

      jQuery('#pickup-delete-confirm').dialog({
        dialogClass: 'wp-dialog',
        width: 400,
        modal: true,
        autoOpen: false,
        closeOnEscape: true,
        buttons: {
          Xóa: () => {
            this.submitDelete(this.current)
          },
          Đóng: function () {
            jQuery(this).dialog('close')
          },
        },
      })
    })
  },

  methods: {
    loadItems() {
      let self = this

      self.loading = true

      pickup.getByQuery(
        {
          page: this.pagination.current_page,
          per_page: this.pagination.items_per_page,
        },
        (response) => {
          self.loading = false
          self.items = response.data
          self.pagination = response.pagination
        }
      )
    },

    submitCreate(data) {
      pickup.create(data, (response) => {
        jQuery('#pickup-create-dialog').dialog('close')
        this.pagination.current_page = 1
        this.loadItems()
      })
    },

    submitUpdate(data) {
      if (!data.id) {
        return
      }
      pickup.update(data.id, data, (response) => {
        jQuery('#pickup-update-dialog').dialog('close')
        this.loadItems()
      })
    },

    submitDelete(data) {
      pickup.delete(data.id, [], (response) => {
        jQuery('#pickup-delete-confirm').dialog('close')
        this.pagination.current_page = 1
        this.loadItems()
      })
    },

    doEdit(item) {
      this.current = item
      this.$refs['update-form'].$emit('init', item)
      jQuery('#pickup-update-dialog').dialog('open')
    },

    doDelete(item) {
      this.current = item
      jQuery('#pickup-delete-confirm').dialog('open')
    },

    onActionClick(action, row) {
      switch (action) {
        case 'edit':
          this.doEdit(row)
          break
        case 'trash':
          this.doDelete(row)
          break
        default:
          return
      }
    },

    onBulkAction(action, items) {
      if (action === 'trash') {
        let requests = []
        items.forEach((item) => {
          requests.push(
            jQuery.ajax({
              url:
                WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/pickups/' + item,
              type: 'DELETE',
            })
          )
        })
        jQuery.when(...requests).then((response) => {
          this.pagination.current_page = 1
          this.loadItems()
        })
      }
    },

    goToPage(page) {
      this.pagination.current_page = page
      this.loadItems()
    },
  },
}
</script>
