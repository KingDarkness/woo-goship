import Restful from './restful'
const $ = jQuery

class Shipment extends Restful {
  getUrl() {
    return super.getUrl() + '/shipments'
  }

  getRates(data = [], cb = null, errorCb = null) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/rates',
      type: 'POST',
      data: data,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', WEB_GLOBAL_JS_VARIABLES.nonce)
      },
      success: function (response, status, xhr) {
        if (cb) {
          cb(response, status, xhr)
        }
      },
      error: function (data) {
        if (errorCb) {
          errorCb(data)
        }
      },
    })
  }

  printShipment(id, query = [], cb = null, errorCb = null) {
    $.ajax({
      url: this.getUrl() + '/' + id + '/print',
      type: 'GET',
      data: query,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', WEB_GLOBAL_JS_VARIABLES.nonce)
      },
      success: function (response, status, xhr) {
        if (cb) {
          cb(response, status, xhr)
        }
      },
      error: function (data) {
        if (errorCb) {
          errorCb(data)
        }
      },
    })
  }
}

export default new Shipment()
