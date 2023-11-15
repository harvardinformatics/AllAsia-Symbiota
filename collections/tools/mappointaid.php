<?php
include_once('../../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
include_once($SERVER_ROOT.'/content/lang/collections/tools/mapaids.'.$LANG_TAG.'.php');

if($MAPPING_BOUNDARIES){
	$boundaryArr = explode(";",$MAPPING_BOUNDARIES);
	$latCenter = ($boundaryArr[0]>$boundaryArr[2]?((($boundaryArr[0]-$boundaryArr[2])/2)+$boundaryArr[2]):((($boundaryArr[2]-$boundaryArr[0])/2)+$boundaryArr[0]));
	$lngCenter = ($boundaryArr[1]>$boundaryArr[3]?((($boundaryArr[1]-$boundaryArr[3])/2)+$boundaryArr[3]):((($boundaryArr[3]-$boundaryArr[1])/2)+$boundaryArr[1]));
} else{
	$latCenter = 42.877742;
	$lngCenter = -97.380979;
}

$errMode = array_key_exists("errmode",$_REQUEST)?$_REQUEST["errmode"]:1;
?>
<html>
	<head>
		<title><?php echo $DEFAULT_TITLE; ?> - Point-Radius Aid</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

		<?php
			include_once($SERVER_ROOT.'/includes/leafletMap.php');
			include_once($SERVER_ROOT.'/includes/googleMap.php');
		?>

		<script type="text/javascript">
		var map;

		//Global temp Values
		let errRadius = 0;
		let latlng;

		//Map Center
		let latCenter;
		let lngCenter;

		//Inputs
		let radiusInput;
		let latInput;
		let lngInput;

		const checkCoord = (coord, range) => {
			return !isNaN(coord) && coord <= range && coord >= -range;
		}

		//Function for reading reading lat lng inputs
		function getLatLng() {
			const pos = [
				parseFloat(document.getElementById("latbox").value),
				parseFloat(document.getElementById("lngbox").value)
			];

			if(checkCoord(pos[0], 90) && checkCoord(pos[1], 180)) {
				return pos;
			}

			return false;
		}
		//Funciton for setting lat lng inputs
		function setLatLngForm(lat, lng) {
			document.getElementById("latbox").value = parseFloat(lat).toFixed(5);
			document.getElementById("lngbox").value = parseFloat(lng).toFixed(5);
		}

		//Function For refreshing markers based upon user input
		//Function For Submission
		function SubmitCoordinates(lat, lng) {
			opener.document.getElementById("decimallatitude").value = lat;
			opener.document.getElementById("decimallongitude").value = lng;
		}

		function errRadiusChanged(e) {
			try {
				errRadius = parseFloat(e.value);
			} catch {
				errRadius = 0;
			}
		}

		function getErrorRadius() {
			if(opener.document.getElementById("coordinateuncertaintyinmeters") && opener.document.getElementById("coordinateuncertaintyinmeters").value){
				errRadius = opener.document.getElementById("coordinateuncertaintyinmeters").value;
				document.getElementById("errRadius").value = opener.document.getElementById("coordinateuncertaintyinmeters").value;
			}
		}

		function leafletInit() {
			//Setup Map Canvas
			map = new LeafletMap('map_canvas', {
				center: [latCenter, lngCenter], 
				zoom: 7
			});

			var drawnItems = new L.FeatureGroup();
			map.mapLayer.addLayer(drawnItems);

			var drawControl = new L.Control.Draw({
				draw: {
					...map.DEFAULT_DRAW_OPTIONS,
					marker: true,
					polygon: false,
					circle: false,
					rectangle: false,
				},
				edit: {
					featureGroup: drawnItems,
				}
			});

			map.mapLayer.addControl(drawControl);
         const markerControl = document.querySelector(".leaflet-draw-draw-marker");
         if(markerControl) markerControl.click();

			let marker;

			function createMarker(lat, lng)  {
				drawnItems.clearLayers();
				if(marker) map.mapLayer.removeLayer(marker);

				latlng = [lat,lng];
				setLatLngForm(lat, lng);

				marker = L.marker([lat, lng])

				if(errRadius && errRadius > 0) {
					marker.addTo(map.mapLayer);
					L.circle([lat, lng], errRadius)
						.on('drag', e=> {
							const pos = e.target.getLatLng()

							setLatLngForm(pos.lat, pos.lng);

							map.mapLayer.removeLayer(marker)
							marker = L.marker([pos.lat, pos.lng])
								.addTo(map.mapLayer) 
						})
						.setStyle(map.DEFAULT_SHAPE_OPTIONS)
						.addTo(drawnItems);

					map.mapLayer.on('draw:deleted', e => {
						map.mapLayer.removeLayer(marker);
					})

					map.mapLayer.on('draw:editresize', e => {
						document.getElementById("errRadius").value = e.layer._mRadius.toFixed(5);
					})

					map.mapLayer.on('draw:edited', function(e) {
						latlng = getLatLng();
						errRadius = parseFloat(document.getElementById("errRadius").value);
					})

					map.mapLayer.on('draw:editstop', e => {
						setLatLngForm(latlng[0], latlng[1]);
						document.getElementById("errRadius").value = errRadius;
					})

					map.mapLayer.on('draw:editstop', e => {
						map.mapLayer.removeLayer(marker);
						marker = L.marker([latlng[0], latlng[1]])
							.addTo(map.mapLayer) 
					})

					map.mapLayer.fitBounds(drawnItems.getBounds());
				} else {
					marker.on('drag', e => {
						const pos = e.target.getLatLng();
						setLatLngForm(pos.lat, pos.lng);
					})

					map.mapLayer.on('draw:editstop', e => {
						setLatLngForm(latlng[0], latlng[1]);
					})

					map.mapLayer.on('draw:edited', function(e) {
						latlng = getLatLng();
					})

					marker.addTo(drawnItems);

					map.mapLayer.setView(latlng, map.mapLayer.getZoom());
				}
         } 

			onFormChange = (event) => { 
				errRadius = parseFloat(event.target.value);
				const pos = getLatLng();
				if(pos) createMarker(pos[0], pos[1]);
			}
			if(radiusInput) radiusInput.addEventListener("change", onFormChange);
			latInput.addEventListener("change", onFormChange);
			lngInput.addEventListener("change", onFormChange);

			map.mapLayer.on('draw:created', function(e) {
				//Clear between Draws
				if(e.layerType === "marker") {
					const lat = e.layer._latlng.lat;
					const lng = e.layer._latlng.lng;
					createMarker(lat, lng)
				} 
			})

			//Draw marker if one exists
			if(latlng) {
				createMarker(latlng[0], latlng[1]);
			}
		}

		function googleInit() {
			//Setup Map Canvas
			map = new GoogleMap('map_canvas', {
				center: new google.maps.LatLng(latCenter, lngCenter), 
				zoom: 7
			});

			let radiusInput = document.getElementById("errRadius");

			let polyOptions = {
				strokeWeight: 0,
				fillOpacity: 0.45,
				editable: false,
				draggable: false
			};

			let drawingManager = new google.maps.drawing.DrawingManager({
				drawingMode: null,
				drawingControl: false,
				circleOptions: polyOptions
			});

			drawingManager.setMap(map.mapLayer);

			let marker;
			let errCircle;

			function createMarker(lat, lng) {
				latlng = [lat, lng];
				setLatLngForm(lat, lng);
				//Clears Last Marker Off Map
				if(marker) marker.setMap();
				marker = new google.maps.Marker({
					position: new google.maps.LatLng(lat, lng),
					draggable: true,
					map: map.mapLayer
				});

				marker.addListener('position_changed', function(){
					let newLatLng = (marker.getPosition());
					setLatLngForm(newLatLng.lat(), newLatLng.lng());
				});
				drawError();
			}

			function drawError() {
				if(!marker || isNaN(errRadius) || errRadius <= 0) return;
				if(errCircle) errCircle.setMap();

				errCircle = new google.maps.Circle({
					center: new google.maps.LatLng(latlng[0], latlng[1]),
					//Convert to Meters
					radius: errRadius,
					strokeWeight: 0,
					fillOpacity: 0.45,
					editable: true,
					draggable: true,
					map: map.mapLayer
				});
				google.maps.event.addListener(errCircle, 'radius_changed', function(){
					var radius = (errCircle.getRadius());
					document.getElementById("errRadius").value = radius.toFixed(5);
				});

				errCircle.bindTo('center', marker, 'position');
			}


			onFormChange = (event) => { 
				errRadius = parseFloat(event.target.value);
				const pos = getLatLng();
				if(pos) createMarker(pos[0], pos[1]);
			}
			if(radiusInput) radiusInput.addEventListener("change", onFormChange);
			latInput.addEventListener("change", onFormChange);
			lngInput.addEventListener("change", onFormChange);

			//Draw marker if one exists
			if(latlng) {
				createMarker(latlng[0], latlng[1]);
				map.mapLayer.setCenter(marker.getPosition());
			}
			//Setup OnClick Listeners
			google.maps.event.addListener(map.mapLayer, 'click', function(e) {

				createMarker(e.latLng.lat(), e.latLng.lng());
				if(errRadius) {
					drawError();
				}
			}) 
		}

		function initialize() {
			let lat = opener.document.getElementById("decimallatitude").value;
			let lng = opener.document.getElementById("decimallongitude").value;

			const data = document.getElementById('service-container');
			latCenter = parseFloat(data.getAttribute('data-lat'));
			lngCenter = parseFloat(data.getAttribute('data-lng'));

			radiusInput = document.getElementById("errRadius");
			latInput = document.getElementById("latbox");
			lngInput = document.getElementById("lngbox");
         getErrorRadius();

			if(lat && lng) {
				if(checkCoord(lat, 90) && checkCoord(lng, 180)) {
					setLatLngForm(lat, lng);
					latlng = [lat, lng];
				} else {
					alert(`Error: Not Coordinates lat: ${lat}, lng: ${lng}`);
				}
			} 
			<?php if(empty($GOOGLE_MAP_KEY)) { ?> 
				leafletInit();
			<?php } else { ?> 
			googleInit();
		<?php } ?>
		 }

		function updateParentForm(f) {
			opener.document.getElementById("decimallatitude").value = f.latbox.value;
			opener.document.getElementById("decimallongitude").value = f.lngbox.value;
			try{
				if(opener.document.getElementById("coordinateuncertaintyinmeters")){
					opener.document.getElementById("coordinateuncertaintyinmeters").value = f.errRadius.value;
					opener.document.getElementById("coordinateuncertaintyinmeters").onchange();
				}
				if(opener.document.getElementById("geodeticdatum")){
					opener.document.getElementById("geodeticdatum").value = "WGS84";
					opener.document.getElementById("geodeticdatum").onchange();
				}
				opener.document.getElementById("decimallatitude").onchange();
				opener.document.getElementById("decimallongitude").onchange();
				opener.document.getElementById("saveEditsButton").disabled = false;
			}
			catch(myErr){
				//alert("Unable to trigger onchange");
			}
			finally{
				self.close();
				return false;
			}
		}
		</script>
	</head>
	<body style="background-color:#ffffff;" onload="initialize()">
		<div
			id="service-container" 
			class="service-container" 
			data-lat="<?= htmlspecialchars($latCenter)?>"
			data-lng="<?= htmlspecialchars($lngCenter)?>"
		>
		<div style="">
			<form name="coordform" action="" method="post" onsubmit="return false">
				<div style="float:right;margin:5px 20px">
					<button name="addcoords" type="button" onclick="updateParentForm(this.form);">Submit Coordinates</button><br/>
				</div>
				<div style="margin:3px 20px 3px 0px;">
					Click on the map to capture coordinates, or drag marker.
					<?php if($errMode) echo 'Enter uncertainty to create an error radius circle around the marker. '; ?>
					The Submit Coordinates button will transfer the information to form.
				</div>
				<div style="margin-right:10px;">
					<b>Latitude:</b> <input type="text" id="latbox" name="lat" style="width:100px" />
					<b>Longitude:</b> <input type="text" id="lngbox" name="lon" style="width:100px" />
				<?php
				if($errMode){
					?>
					<b>Uncertainty in Meters:</b> <input type="text" id="errRadius" name="errRadius" size="13" />
					<?php
				}
				?>
				</div>
			</form>
			<div id='map_canvas' style='width:100%; height:88%; clear:both;'></div>
		</div>
	</body>
</html>
