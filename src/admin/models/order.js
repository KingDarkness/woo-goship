import Restful from './restful'
const $ = jQuery

class Order extends Restful {
  getUrl() {
    return WEB_GLOBAL_JS_VARIABLES.res_url + 'wc/v3/orders'
  }

  getGoshipInfo(id, query = [], cb = null, errorCb = null) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/orders/' + id,
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

export default new Order()
