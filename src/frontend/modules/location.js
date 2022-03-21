const $ = jQuery
class Location {
  cities(cb) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/cities',
      type: 'GET',
      success: function (response) {
        cb(response)
      },
    })
  }

  districts(cityCode, cb) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/districts/' + cityCode,
      type: 'GET',
      success: function (response) {
        cb(response)
      },
    })
  }

  wards(districtCode, cb) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/wards/' + districtCode,
      type: 'GET',
      success: function (response) {
        cb(response)
      },
    })
  }
}

export default new Location()
