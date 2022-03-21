import 'select2'
import 'select2/dist/css/select2.css'
import checkOut from './checkout'

const $ = jQuery
$(document).ready(() => {
  checkOut.init()
})
