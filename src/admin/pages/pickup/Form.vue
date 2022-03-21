<template>
  <div class="pickup-form">
    <div class="form-wrap">
      <ValidationObserver v-slot="{ invalid, passes }" ref="observer">
        <form class="validate" @submit.prevent="passes(submit)">
          <div class="form-field">
            <label>Tên shop</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <input name="Tên shop" type="text" v-model="data.shop_name" />
              <span class="validate-error">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-field">
            <label>Người liên hệ</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <input name="Tên người liên hệ" type="text" v-model="data.name" />
              <span class="validate-error">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-field">
            <label>Điện thoại liên hệ</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <input
                name="Số điện thoại người liên hệ"
                type="text"
                v-model="data.phone"
              />
              <span class="validate-error">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-field">
            <label>Tỉnh thành</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <select
                v-model="data.city_code"
                style="width: 100%"
                @change="loadDistrict"
                ref="city_code"
                name="Tỉnh thành"
              >
                <option value="">Chọn tỉnh thành</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }}
                </option>
              </select>
              <span class="validate-error">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-field">
            <label>Quận huyện</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <select
                v-model="data.district_code"
                style="width: 100%"
                @change="loadWard"
                :disabled="!districts.length"
                ref="district_code"
                name="Quận huyện"
              >
                <option value="">Chọn quận huyện</option>
                <option
                  v-for="district in districts"
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
            <label>Phường xã</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <select
                v-model="data.ward_id"
                style="width: 100%"
                :disabled="!wards.length"
                ref="ward_id"
                name="Phường xã"
              >
                <option value="">Chọn phường xã</option>
                <option v-for="ward in wards" :key="ward.id" :value="ward.id">
                  {{ ward.name }}
                </option>
              </select>
              <span class="validate-error">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-field">
            <label>Địa chỉ</label>
            <ValidationProvider rules="required" v-slot="{ errors }">
              <input name="Địa chỉ" type="text" v-model="data.street" />
              <span class="validate-error">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>

          <div class="form-field">
            <label>Mặc định</label>
            <input type="checkbox" v-model="data.default" />
          </div>
        </form>
      </ValidationObserver>
    </div>
  </div>
</template>
<script>
import location from 'admin/models/location'
import Select2 from 'admin/components/Select2.vue'
import {
  ValidationProvider,
  ValidationObserver,
  extend,
  localize,
} from 'vee-validate'
import vi from 'vee-validate/dist/locale/vi.json'
import { required } from 'vee-validate/dist/rules'
extend('required', required)
localize('vi', vi)

const pickup = {
  shop_name: '',
  name: '',
  phone: '',
  city_code: '',
  district_code: '',
  ward_id: '',
  street: '',
  full_street: '',
  default: true,
}
export default {
  name: 'PickupForm',
  props: [],
  components: { Select2, ValidationProvider, ValidationObserver },
  data() {
    return {
      data: { ...pickup },
      cities: [],
      districts: [],
      wards: [],
    }
  },
  methods: {
    async submit() {
      const isValid = await this.$refs.observer.validate()
      if (!isValid) {
        alert('Vui lòng kiểm tra thông tin cần nhập!')
        return
      }

      let cityName = this.$refs.city_code.options[
        this.$refs.city_code.selectedIndex
      ].text
      let districtName = this.$refs.district_code.options[
        this.$refs.district_code.selectedIndex
      ].text
      let wardName = this.$refs.ward_id.options[
        this.$refs.ward_id.selectedIndex
      ].text
      this.data.full_street = `${this.data.street}, ${wardName}, ${districtName}, ${cityName}`
      this.$emit('success', this.data)
    },
    loadDistrict() {
      if (!this.data.city_code) {
        this.districts = []
        return
      }
      location.districts(this.data.city_code, (districts) => {
        this.districts = districts
      })
    },
    loadCity() {
      location.cities((cities) => {
        this.cities = cities
      })
    },
    loadWard() {
      if (!this.data.district_code) {
        this.wards = []
        return
      }
      location.wards(this.data.district_code, (wards) => {
        this.wards = wards
      })
    },
  },
  created() {
    this.loadCity()
    this.$on('submit', () => {
      this.submit()
    })
    this.$on('init', (data) => {
      data.default = data.default == 1
      this.data = { ...data }
      if (this.data.district_code) {
        this.loadDistrict()
      }
      if (this.data.district_code) {
        this.loadWard()
      }
    })
    this.$on('reset', () => {
      this.data = { ...pickup }
      this.districts = []
      this.wards = []
    })
  },
}
</script>
<style scoped>
.validate-error {
  color: red;
}
</style>
