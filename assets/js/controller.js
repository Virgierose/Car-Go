const model = new DeliveryModel();
const view  = new DeliveryView();

let pinMode = null; // 'from' or 'to'

// Button: Set Pickup
document.getElementById('btnSetFrom').addEventListener('click', () => {
  pinMode = 'from';
  view.updateStatus('Click on the map to set the PICKUP location.');
});

// Button: Set Delivery
document.getElementById('btnSetTo').addEventListener('click', () => {
  pinMode = 'to';
  view.updateStatus('Click on the map to set the DELIVERY location.');
});

// Map click — place the correct pin
view.map.on('click', (e) => {
  const coords = { lat: e.latlng.lat, lon: e.latlng.lng };
  if (pinMode === 'from') {
    model.fromCoords = coords;
    view.setFromMarker(coords);
    view.updateStatus('Pickup set! Now set the delivery location.');
  } else if (pinMode === 'to') {
    model.toCoords = coords;
    view.setToMarker(coords);
    view.updateStatus('Delivery set! Click Calculate Route.');
  }
  pinMode = null;
});

// Button: Calculate Route
document.getElementById('btnRoute').addEventListener('click', async () => {
  if (!model.fromCoords || !model.toCoords) {
    view.updateStatus('Please set both pickup and delivery locations first!');
    return;
  }
  view.updateStatus('Calculating route...');
  const route = await model.getRoute(model.fromCoords, model.toCoords);
  view.displayRoute(model.fromCoords, model.toCoords, route);
  view.updateInfo(model.distanceKm, model.etaMinutes);
  view.updateStatus('Route calculated!');
});