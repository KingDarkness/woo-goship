<template>
  <div class="pickup-form">
    <div class="form-wrap">
      <ValidationObserver v-slot="{ invalid, passes }" ref="observer">
        <form class="validate" @submit.prevent="passes(submit)">
          <div style="display: flex">
            <div class="receiver-info" style="flex: 1">
              <div class="form-field">
                <label>Điểm lấy hàng</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <select
                    v-model="data.pickup_id"
                    style="width: 100%"
                    @change="changePickup"
                    name="Điểm lấy hàng"
                  >
                    <option value="">Chọn điểm lấy hàng</option>
                    <option
                      v-for="pickup in pickups"
                      :key="pickup.id"
                      :value="pickup.id"
                    >
                      {{ pickup.shop_name }}
                    </option>
                  </select>
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Người nhận</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <input
                    name="Người nhận"
                    type="text"
                    v-model="data.receiver_name"
                  />
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>SĐT người nhận</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <input
                    type="text"
                    v-model="data.receiver_phone"
                    name="SĐT người nhận"
                  />
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Tỉnh thành đến</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <select
                    v-model="data.to_city"
                    style="width: 100%"
                    @change="loadDistrict"
                    ref="to_city"
                    name="Tỉnh thành đến"
                  >
                    <option value="">Chọn tỉnh thành</option>
                    <option
                      v-for="city in toCities"
                      :key="city.id"
                      :value="city.id"
                    >
                      {{ city.name }}
                    </option>
                  </select>
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Quận huyện đến</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <select
                    v-model="data.to_district"
                    style="width: 100%"
                    @change="loadWard"
                    :disabled="!toDistricts.length"
                    ref="to_district"
                    name="Quận huyện đến"
                  >
                    <option value="">Chọn quận huyện</option>
                    <option
                      v-for="district in toDistricts"
                      :key="district.id"
                      :value="district.id"
                    >
                      {{ district.name }}
                    </option>
                  </select>
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Phường xã đến</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <select
                    v-model="data.to_ward"
                    style="width: 100%"
                    :disabled="!toWards.length"
                    ref="to_ward"
                    name="Phường xã đến"
                  >
                    <option value="">Chọn phường xã</option>
                    <option
                      v-for="ward in toWards"
                      :key="ward.id"
                      :value="ward.id"
                    >
                      {{ ward.name }}
                    </option>
                  </select>
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Địa chỉ đến</label>
                <ValidationProvider rules="required" v-slot="{ errors }">
                  <input
                    type="text"
                    v-model="data.to_street"
                    name="Địa chỉ đến"
                  />
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>
            </div>

            <div class="package-info" style="flex: 1">
              <div class="form-field">
                <label>Cân nặng(g)</label>
                <ValidationProvider
                  rules="required|numeric"
                  v-slot="{ errors }"
                >
                  <input
                    name="Cân nặng"
                    type="text"
                    v-model="data.weight"
                    @change="getRates"
                  />
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Dài x Rộng x Cao (cm)</label>
                <div style="display: flex; align-items: center">
                  <ValidationProvider rules="numeric" v-slot="{ errors }">
                    <input
                      type="text"
                      v-model="data.length"
                      @change="getRates"
                      name="Dài"
                    />
                    <span class="validate-error">{{ errors[0] }}</span>
                  </ValidationProvider>
                  <span>x</span>
                  <ValidationProvider rules="numeric" v-slot="{ errors }">
                    <input
                      type="text"
                      v-model="data.width"
                      @change="getRates"
                      name="rộng"
                    />
                    <span class="validate-error">{{ errors[0] }}</span>
                  </ValidationProvider>
                  <span>x</span>
                  <ValidationProvider rules="numeric" v-slot="{ errors }">
                    <input
                      type="text"
                      v-model="data.height"
                      @change="getRates"
                      name="cao"
                    />
                    <span class="validate-error">{{ errors[0] }}</span>
                  </ValidationProvider>
                </div>
              </div>

              <div class="form-field">
                <label>Tên hàng hóa</label>
                <input type="text" v-model="data.package_name" />
              </div>

              <div class="form-field">
                <label>Ghi chú</label>
                <textarea rows="3" v-model="data.metadata" />
              </div>

              <div class="form-field">
                <label>COD (đ)</label>
                <ValidationProvider rules="numeric" v-slot="{ errors }">
                  <input
                    type="text"
                    v-model="data.cod"
                    @change="getRates"
                    name="Tiền thu hộ"
                  />
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Khai giá (đ)</label>
                <ValidationProvider rules="numeric" v-slot="{ errors }">
                  <input
                    type="text"
                    v-model="data.amount"
                    @change="getRates"
                    name="Giá trị khai giá"
                  />
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>

              <div class="form-field">
                <label>Người trả phí</label>
                <ValidationProvider
                  rules="required"
                  v-slot="{ errors }"
                  name="Người trả phí"
                >
                  <input
                    type="radio"
                    name="payer"
                    :value="0"
                    v-model="data.payer"
                    @change="getRates"
                  />
                  Người nhận &nbsp;&nbsp;
                  <input
                    type="radio"
                    name="payer"
                    :value="1"
                    v-model="data.payer"
                    @change="getRates"
                  />
                  Người gửi
                  <span class="validate-error">{{ errors[0] }}</span>
                </ValidationProvider>
              </div>
            </div>
          </div>
          <div class="rate">
            <div class="rate-header">
              <div class="select-input">&nbsp;</div>
              <label>
                <div style="width: 40%; text-align: center">
                  Hãng vận chuyển
                </div>
                <div style="width: 30%; text-align: center">Dịch vụ</div>
                <div style="width: 30%; text-align: end">Tổng phí</div>
              </label>
            </div>
            <div
              v-if="loadRates"
              style="
                display: flex;
                align-items: center;
                color: red;
                justify-content: center;
              "
            >
              Đang tải bảng giá vui lòng đợi...
            </div>
            <div
              v-if="!loadRates && !rates.length"
              style="
                display: flex;
                align-items: center;
                color: red;
                justify-content: center;
              "
            >
              Không có bảng giá phù hợp với tuyến đường...
            </div>

            <div v-if="!loadRates && rates.length">
              <ValidationProvider
                rules="required"
                v-slot="{ errors }"
                name="Đơn vị vận chuyển"
              >
                <div class="rate-item" v-for="rate in rates" :key="rate.id">
                  <div class="select-input">
                    <input
                      type="radio"
                      name="rate"
                      :id="`rate-${rate.id}`"
                      v-model="data.rate"
                      :value="rate.id"
                    />
                  </div>

                  <label :for="`rate-${rate.id}`">
                    <div
                      class="rate-carrier"
                      style="width: 40%; text-align: center"
                    >
                      <img
                        width="65"
                        :src="rate.carrier_logo"
                        :alt="rate.carrier_name"
                      />
                      &nbsp;
                      {{ rate.carrier_name }}
                    </div>
                    <div style="width: 30%; text-align: center">
                      {{ rate.service }}
                    </div>
                    <div style="width: 30%; text-align: end">
                      {{ rate.total_fee }}
                    </div>
                  </label>
                </div>
                <span class="validate-error">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>
        </form>
      </ValidationObserver>
    </div>
  </div>
</template>
<script>
import location from 'admin/models/location'
import pickup from 'admin/models/pickup'
import shipmentModel from 'admin/models/shipment'
import Select2 from 'admin/components/Select2.vue'
import {
  ValidationProvider,
  ValidationObserver,
  extend,
  localize,
} from 'vee-validate'
import { required, numeric } from 'vee-validate/dist/rules'
import vi from 'vee-validate/dist/locale/vi.json'
extend('required', required)
extend('numeric', numeric)
localize('vi', vi)
const shipment = {
  rate: '',
  from_city: '',
  from_district: '',
  from_ward: '',
  from_street: '',
  sender_name: '',
  sender_phone: '',
  to_city: '',
  to_district: '',
  to_ward: '',
  to_street: '',
  receiver_name: '',
  receiver_phone: '',
  cod: 0,
  amount: 0,
  weight: 500,
  payer: 0,
  package_name: 'test package',
  metadata: 'ghi chú',
  order_id: '',
  node_code: 'KHONGCHOXEMHANG',
  length: 0,
  width: 0,
  height: 0,
  pickup_id: '',
}
export default {
  name: 'PickupForm',
  props: [],
  components: { Select2, ValidationProvider, ValidationObserver },
  data() {
    return {
      data: { ...shipment },
      pickups: [],
      toCities: [],
      toDistricts: [],
      toWards: [],
      rates: [],
      loadRates: false,
      delayLoadRates: 500,
    }
  },
  methods: {
    async submit() {
      const isValid = await this.$refs.observer.validate()
      if (!isValid) {
        alert('Vui lòng kiểm tra thông tin cần nhập!')
        return
      }
      this.data.to_city_name = this.$refs.to_city.options[
        this.$refs.to_city.selectedIndex
      ].text
      this.data.to_district_name = this.$refs.to_district.options[
        this.$refs.to_district.selectedIndex
      ].text
      this.data.to_ward_name = this.$refs.to_ward.options[
        this.$refs.to_ward.selectedIndex
      ].text
      this.$emit('success', this.data)
    },
    loadDistrict() {
      if (!this.data.to_city) {
        this.toDistricts = []
        return
      }
      location.districts(this.data.to_city, (toDistricts) => {
        this.toDistricts = toDistricts
      })
    },
    loadCity() {
      location.cities((toCities) => {
        this.toCities = toCities
      })
    },
    loadWard() {
      if (!this.data.to_district) {
        this.toWards = []
        return
      }
      location.wards(this.data.to_district, (toWards) => {
        this.toWards = toWards
      })
      this.getRates()
    },
    loadPickup() {
      pickup.getByQuery({ per_page: 10000 }, (pickups) => {
        this.pickups = pickups.data
      })
    },
    changePickup() {
      if (this.data.pickup_id) {
        let pickups = this.pickups.reduce((obj, item) => {
          return {
            ...obj,
            [item.id]: item,
          }
        }, {})
        let pickup = pickups[this.data.pickup_id]
        this.data.from_city = pickup.city_code
        this.data.from_district = pickup.district_code
        this.data.from_ward = pickup.ward_id
        this.data.from_street = pickup.street
        this.data.sender_name = pickup.name
        this.data.sender_phone = pickup.phone
        let fullStreet = pickup.full_street
        fullStreet = fullStreet.split(',')
        this.data.from_city_name = fullStreet.pop().trim()
        this.data.from_district_name = fullStreet.pop().trim()
        this.data.from_ward_name = fullStreet.pop().trim()
        this.getRates()
      }
    },
    getRates() {
      clearTimeout(this.delayLoadRates)
      this.delayLoadRates = setTimeout(() => {
        if (
          this.data.from_city &&
          this.data.from_district &&
          this.data.to_city &&
          this.data.to_district &&
          this.data.weight
        ) {
          this.loadRates = true
          shipmentModel.getRates(this.data, (response) => {
            this.rates = response
            this.loadRates = false
          })
        }
      }, 500)
    },
  },
  created() {
    this.loadCity()
    this.loadPickup()
    this.$on('submit', () => {
      this.submit()
    })
    this.$on('init', (data) => {
      data.default = data.default == 1
      this.data = { ...data }
      if (this.data.to_district) {
        this.loadDistrict()
      }
      if (this.data.to_district) {
        this.loadWard()
      }
      this.getRates()
    })
    this.$on('reset', () => {
      this.data = { ...shipment }
      this.toDistricts = []
      this.toWards = []
    })
  },
}
</script>
<style scoped>
.rate-item {
  display: flex;
}
.rate-item .select-input {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80px;
  width: 10%;
}
.rate-item label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80px;
  width: 90%;
}
.rate-item img {
  border: 1px solid #999;
  border-radius: 100%;
}
.rate-item .rate-carrier {
  display: flex;
  align-items: center;
}
.rate-item:not(:last-child) {
  border-bottom: 1px solid #8d8f95;
}
.rate-header {
  display: flex;
  border-bottom: 1px solid #8d8f95;
}
.rate-header .select-input {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 30px;
  width: 10%;
}
.rate-header label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 30px;
  width: 90%;
}
.validate-error {
  color: red;
}
</style>
