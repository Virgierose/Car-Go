class DeliveryView {
  constructor() {
    // Default center: Bacolod City (change to your city's coords)
    this.map = L.map('map').setView([10.6765, 122.9509], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(this.map);

    this.fromMarker = null;
    this.toMarker   = null;
    this.routeLine  = null;
  }

  setFromMarker(coords) {
    if (this.fromMarker) this.map.removeLayer(this.fromMarker);
    this.fromMarker = L.marker([coords.lat, coords.lon])
      .addTo(this.map)
      .bindPopup('📦 Pickup Location').openPopup();
  }

  setToMarker(coords) {
    if (this.toMarker) this.map.removeLayer(this.toMarker);
    this.toMarker = L.marker([coords.lat, coords.lon])
      .addTo(this.map)
      .bindPopup('🏠 Delivery Location').openPopup();
  }

  displayRoute(from, to, routeCoords) {
    if (this.routeLine) this.map.removeLayer(this.routeLine);
    this.routeLine = L.polyline(routeCoords, {
      color: '#ee4d2d',   // Shopee-style orange-red
      weight: 5
    }).addTo(this.map);
    this.map.fitBounds(this.routeLine.getBounds());
  }

  updateInfo(distanceKm, etaMinutes) {
    document.getElementById('distance').textContent = distanceKm + ' km';
    document.getElementById('eta').textContent      = etaMinutes + ' mins';
  }

  updateStatus(msg) {
    document.getElementById('status').textContent = msg;
  }
}