class DeliveryModel {
  constructor() {
    this.fromCoords = null;
    this.toCoords   = null;
    this.routeCoords = [];
    this.distanceKm  = 0;
    this.etaMinutes  = 0;
  }

  async getRoute(from, to) {
    const url = `https://router.project-osrm.org/route/v1/driving/` +
                `${from.lon},${from.lat};${to.lon},${to.lat}` +
                `?overview=full&geometries=geojson`;

    const res   = await fetch(url);
    const data  = await res.json();
    const route = data.routes[0];

    // Convert meters → km, seconds → minutes
    this.distanceKm = (route.distance / 1000).toFixed(2);
    this.etaMinutes = Math.ceil(route.duration / 60);

    // OSRM gives [lon, lat] — Leaflet needs [lat, lon]
    this.routeCoords = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);

    return this.routeCoords;
  }
}