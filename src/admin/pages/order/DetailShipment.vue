<template>
  <div class="shipment-detail">
    <div
      v-if="loadShipment"
      style="
        height: 500px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: red;
      "
    >
      Đang tải dư liệu vui lòng đợi...
    </div>
    <div
      v-if="shipment && shipment.id && !loadShipment"
      class="d-flex justify-content-space-between"
    >
      <div class="status-history col">
        <ul>
          <li
            v-for="(history, index) in shipment.history.reverse()"
            :key="index"
          >
            <p>{{ history.updated_at }}</p>
            <div>
              <p>{{ history.status_text }}</p>
              <p>{{ history.status_desc }}</p>
              <p v-if="history.message">{{ history.message }}</p>
            </div>
          </li>
        </ul>
        <div>
          <h3 class="title">Thời gian</h3>
          <div class="info-tracking">
            <div class="d-flex justify-content-space-between">
              <div class="">
                <p class="text-bold">Ngày tạo đơn:</p>
              </div>
              <div class="">
                <p>{{ shipment.created_at }}</p>
              </div>
            </div>
            <div class="d-flex justify-content-space-between">
              <div class="">
                <p class="text-bold">Dự kiến giao:</p>
              </div>
              <div class="">
                <p>{{ shipment.expected_delivery_date }}</p>
              </div>
            </div>
          </div>
          <h3 class="title">
            Lưu ý: Đối với những đơn đi huyện, xã, vùng sâu, vùng xa thời gian
            giao hàng sẽ cộng thêm từ 1-3 ngày
          </h3>
        </div>
      </div>
      <div class="shipment-info col" style="flex-grow: 1.8">
        <h3 class="title">Thông tin vận đơn</h3>
        <div class="info-tracking">
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Hãng vận chuyển:</p>
            </div>
            <div class="">
              <p>{{ shipment.carrier_name }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Mã tracking:</p>
            </div>
            <div class="d-flex justify-content-space-between">
              <p class="text-green">{{ shipment.carrier_code }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Mã Goship:</p>
            </div>
            <div class="">
              <p class="text-green">{{ shipment.id }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Gói dịch vụ:</p>
            </div>
            <div class="">
              <p>{{ shipment.service_name }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">COD:</p>
            </div>
            <div class="">
              <p style="color: red">
                {{ formatCurrency(shipment.parcel.cod_amount) }}
              </p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Tổng phí:</p>
            </div>
            <div class="">
              <p style="color: red">{{ formatCurrency(shipment.total_fee) }}</p>
            </div>
          </div>
        </div>
        <h3 class="title">Người gửi</h3>
        <div class="info-tracking">
          <div class="d-flex justify-content-space-between">
            <div class=""><p class="text-bold">Tên</p></div>
            <div class="">
              <p>{{ shipment.address_from.name }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class=""><p class="text-bold">Điện thoại</p></div>
            <div class="">
              <p>{{ shipment.address_from.phone }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class=""><p class="text-bold">Địa chỉ</p></div>
            <div class="">
              <p>
                {{ shipment.address_from.street }},
                {{ shipment.address_from.ward }},
                {{ shipment.address_from.district }},
                {{ shipment.address_from.city }}
              </p>
            </div>
          </div>
        </div>
        <h3 class="title">Người nhận</h3>
        <div class="info-tracking">
          <div class="d-flex justify-content-space-between">
            <div class=""><p class="text-bold">Tên</p></div>
            <div class="">
              <p>{{ shipment.address_to.name }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class=""><p class="text-bold">Điện thoại</p></div>
            <div class="">
              <p>{{ shipment.address_to.phone }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class=""><p class="text-bold">Địa chỉ</p></div>
            <div class="">
              <p>
                {{ shipment.address_to.street }},
                {{ shipment.address_to.ward }},
                {{ shipment.address_to.district }},
                {{ shipment.address_to.city }}
              </p>
            </div>
          </div>
        </div>

        <h3 class="title">Thông tin hàng hóa</h3>
        <div class="info-tracking">
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Gói hàng:</p>
            </div>
            <div class="">
              <p>{{ shipment.parcel.name }}</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Cân nặng:</p>
            </div>
            <div class="">
              <p>{{ formatNumber(shipment.parcel.weight, 0) }} g</p>
            </div>
          </div>
          <div
            class="d-flex justify-content-space-between"
            v-if="shipment.parcel.cweight"
          >
            <div class="">
              <p class="text-bold">Cân nặng quy đổi:</p>
            </div>
            <div class="">
              <p>{{ formatNumber(shipment.parcel.cweight, 0) }} g</p>
            </div>
          </div>
          <div class="d-flex justify-content-space-between">
            <div class="">
              <p class="text-bold">Kích thước:</p>
            </div>
            <div class="d-flex justify-content-space-between">
              <p>
                {{ shipment.parcel.length }} x {{ shipment.parcel.width }} x
                {{ shipment.parcel.height }} cm
              </p>
            </div>
          </div>
          <div
            class="d-flex justify-content-space-between"
            v-if="shipment.parcel.metadata"
          >
            <div class="">
              <p class="text-bold">Ghi chú:</p>
            </div>
            <div class="">
              <p>{{ shipment.parcel.metadata }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import shipment from 'admin/models/shipment'
export default {
  name: 'detail-shipment',
  data() {
    return { order: {}, shipment: {}, loadShipment: false }
  },
  methods: {
    orderMeta(order) {
      return order.meta_data.reduce((obj, item) => {
        return {
          ...obj,
          [item.key]: item.value,
        }
      }, {})
    },
    fetchShipment(shipmentCode) {
      this.loadShipment = true
      shipment.getDetail(shipmentCode, {}, (response) => {
        this.shipment = response
        this.loadShipment = false
      })
    },
  },
  created() {
    this.$on('init', (order) => {
      this.order = order
      this.fetchShipment(this.orderMeta(order)._gs_code)
    })
  },
}
</script>
<style scoped>
.shipment-detail p {
  padding: 0;
  margin: 0;
  line-height: 2em;
}
.d-flex {
  display: flex;
}
.col {
  flex: 1;
  padding-left: 10px;
  padding-right: 10px;
}
.justify-content-space-between {
  justify-content: space-between;
}
.status-history ul {
  padding: 25px 0px;
}
.status-history ul li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}
.status-history ul li > p {
  margin-right: 5px;
  flex: 1;
  position: relative;
}
.status-history ul li > p:before {
  position: absolute;
  content: '';
  width: 1px;
  height: 25px;
  border-left: 2px solid #979797;
  left: 26px;
  top: -27px;
}
.status-history ul li > p:after {
  position: absolute;
  content: '';
  width: 1px;
  height: 25px;
  border-left: 2px solid #979797;
  left: 26px;
  top: 42px;
}
.status-history ul li div {
  margin-left: 5px;
  flex: 2;
}
.status-history ul li div * {
  text-align: right;
}
</style>
