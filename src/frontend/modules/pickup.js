const $ = jQuery
class Pickup {
  pickups(cb) {
    $.ajax({
      url: WEB_GLOBAL_JS_VARIABLES.res_url + 'goship/v1/pickups',
      type: 'GET',
      data: { per_page: 100000 },
      success: function (response) {
        cb(response)
      },
    })
  }
}

export default new Pickup()
