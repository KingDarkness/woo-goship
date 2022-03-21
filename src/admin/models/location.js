const $ = jQuery
class Location {
  cities(cb, errorCb = null) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/cities',
      type: 'GET',
      success: function (response) {
        cb(response)
      },
      error: function (data) {
        if (errorCb) {
          errorCb(data)
        }
      },
    })
  }

  districts(cityCode, cb, errorCb = null) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/districts/' + cityCode,
      type: 'GET',
      success: function (response) {
        cb(response)
      },
      error: function (data) {
        if (errorCb) {
          errorCb(data)
        }
      },
    })
  }

  wards(districtCode, cb, errorCb = null) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/wards/' + districtCode,
      type: 'GET',
      success: function (response) {
        cb(response)
      },
      error: function (data) {
        if (errorCb) {
          errorCb(data)
        }
      },
    })
  }
}

export default new Location()
