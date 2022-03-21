const $ = jQuery
class Restful {
  getUrl() {
    return WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1'
  }

  getByQuery(query = [], cb = null, errorCb = null) {
    $.ajax({
      url: this.getUrl(),
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

  getDetail(id, query = [], cb = null, errorCb = null) {
    $.ajax({
      url: this.getUrl() + '/' + id,
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

  create(data = [], cb = null, errorCb = null) {
    $.ajax({
      url: this.getUrl(),
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

  delete(id, query = [], cb = null, errorCb = null) {
    $.ajax({
      url: this.getUrl() + '/' + id,
      type: 'delete',
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

  update(id, data, cb = null, errorCb = null) {
    $.ajax({
      url: this.getUrl() + '/' + id,
      type: 'PUT',
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
}

export default Restful
