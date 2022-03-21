import Restful from './restful'

class Pickup extends Restful {
  getUrl() {
    return super.getUrl() + '/pickups'
  }
}

export default new Pickup()
