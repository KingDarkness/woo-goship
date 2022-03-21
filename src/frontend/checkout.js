const $ = jQuery
import location from './modules/location'
import pickup from './modules/pickup'

class CheckOut {
  init() {
    $('.select2 select').prop('disabled', true)
    this.loadCities('#billing_city')
    this.loadPickups('#billing_pickup')
    this.registerHooks()
  }

  registerHooks() {
    $(document)
      .on('change', '#billing_city', () => {
        let city = $('#billing_city').val()
        if (city) {
          this.loadDistricts('#billing_district', city)
        }
        if (!$('[name="ship_to_different_address"]').prop('checked')) {
          $('#gs-city-name').val($('#billing_city :selected').text())
        }
      })
      .on('change', '#billing_district', () => {
        let district = $('#billing_district').val()
        if (district) {
          this.loadWards('#billing_ward', district)
        }
        if (!$('[name="ship_to_different_address"]').prop('checked')) {
          $('#gs-district-name').val($('#billing_district :selected').text())
        }
      })
      .on('change', '#billing_ward', () => {
        if (!$('[name="ship_to_different_address"]').prop('checked')) {
          $('#gs-ward-name').val($('#billing_ward :selected').text())
        }
      })
      .on('change', '[name="ship_to_different_address"]', () => {
        let checked = $('[name="ship_to_different_address"]').prop('checked')
        if (checked) {
          this.loadCities('#shipping_city')
        }
      })
      .on('change', '#shipping_city', () => {
        let city = $('#shipping_city').val()
        if (city) {
          this.loadDistricts('#shipping_district', city)
        }
        if ($('[name="ship_to_different_address"]').prop('checked')) {
          $('#gs-city-name').val($('#shipping_city :selected').text())
        }
      })
      .on('change', '#shipping_district', () => {
        let district = $('#shipping_district').val()
        if (district) {
          this.loadWards('#shipping_ward', district)
        }
        if ($('[name="ship_to_different_address"]').prop('checked')) {
          $('#gs-district-name').val($('#shipping_district :selected').text())
        }
      })
      .on('change', '#shipping_ward', () => {
        if ($('[name="ship_to_different_address"]').prop('checked')) {
          $('#gs-ward-name').val($('#shipping_ward :selected').text())
        }
      })
      .on('change', '.shipping_method', () => {
        $('#gs-rate-id').val($('.shipping_method:checked').val())
      })
  }

  loadPickups(selector) {
    $(selector).empty()
    $(selector).select2({ disabled: true, data: [] })

    let pickupDefault = null

    pickup.pickups((response) => {
      let pickups = $.map(response.data, function (obj) {
        obj.text = obj.text || obj.shop_name
        if (obj.default == 1) {
          pickupDefault = obj
        }
        return obj
      })
      pickups.unshift({ id: '', text: 'Chọn điểm lấy hàng' })
      $(selector).select2({ disabled: false, data: pickups })
      if (pickupDefault) {
        $(selector).val(pickupDefault.id).trigger('change')
      }
    })
  }

  loadCities(selector) {
    $(selector).empty()
    $(selector).select2({ disabled: true, data: [] })
    location.cities((cities) => {
      cities = $.map(cities, function (obj) {
        obj.text = obj.text || obj.name
        return obj
      })
      cities.unshift({ id: '', text: 'Chọn tỉnh thành' })
      $(selector).select2({ disabled: false, data: cities })
    })
  }

  loadDistricts(selector, cityCode) {
    $(selector).empty()
    $(selector).select2({ disabled: true, data: [] })
    location.districts(cityCode, (districts) => {
      districts = $.map(districts, function (obj) {
        obj.text = obj.text || obj.name
        return obj
      })
      districts.unshift({ id: '', text: 'Chọn quận huyện' })
      $(selector).select2({ disabled: false, data: districts })
    })
  }

  loadWards(selector, districtCode) {
    $(selector).empty()
    $(selector).select2({ disabled: true, data: [] })
    location.wards(districtCode, (wards) => {
      wards = $.map(wards, function (obj) {
        obj.text = obj.text || obj.name
        return obj
      })
      wards.unshift({ id: '', text: 'Chọn phường xã' })
      $(selector).select2({ disabled: false, data: wards })
    })
  }
}

export default new CheckOut()
