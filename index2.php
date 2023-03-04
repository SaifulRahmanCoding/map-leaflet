<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tujuan</title>

    <!-- start cdn leaflet routing mechine -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <!-- end cdn -->
    <style>
        * {
            padding: 0;
            margin: 0;
        }

        #map {
            margin: auto;
            width: 90vw;
            height: 60vh;
        }
    </style>
</head>

<body>
    <!-- konten -->
    <div id="lokasi" class="container">
        <form method="post" id="form-lokasi">
            <div class="form-group mb-2">
                <input type="text" name="latSaya" class="form-control mb-2" id="latSaya" />
                <input type="text" name="lngSaya" class="form-control mb-5" id="lngSaya" />

                <input type="text" name="latTujuan" class="form-control mb-2" id="latTujuan" />
                <input type="text" name="lngTujuan" class="form-control mb-5" id="lngTujuan" />

                <input type="text" name="totalJarak" class="form-control mb-2" id="totalJarak" />
                <input type="text" name="Harga" class="form-control mb-2" id="Harga" />

            </div>
        </form>
    </div>
    <div id="map"></div>
    <!-- end konten -->




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- start cdn leaflet routing mechine -->
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <!-- end cdn -->

    <script>
        // buat variabel
        let map = L.map('map').setView([-7.749540941658048, 114.2177444085855], 16);

        let latSaya = $("input[name=latSaya]").val();
        let lngSaya = $("input[name=lngSaya]").val();

        // let lokasi1 = L.latLng(latSaya, lngSaya);
        // let lokasi2 = L.latLng(latTujuan, lngTujuan);


        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        getLocation();

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                x.innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        // menampilakn routing
        // L.Routing.control({
        //     waypoints: [lokasi1, lokasi2],
        //     routeWhileDragging: true,
        //     geocoder: L.Control.Geocoder.nominatim()

        // }).addTo(map);

        function showPosition(position) {

            $(document).ready(function() {
                setInterval(function() {
                    $("[name=latSaya]").val(position.coords.latitude);
                    $("[name=lngSaya]").val(position.coords.longitude);
                }, 2000);
            });
            L.marker([position.coords.latitude, position.coords.longitude], {
                draggable: false
            }).addTo(map).bindTooltip('<b>Lokasi Saya</b>');

        }

        //remember last position
        var rememberLat = document.getElementById('latTujuan').value;
        var rememberLong = document.getElementById('lngTujuan').value;
        if (!rememberLat || !rememberLong) {
            rememberLat = -7.749540941658048;
            rememberLong = 114.2177444085855;
        }
        var marker = L.marker([-7.749540941658048, 114.2177444085855], {
            draggable: true
        }).addTo(map).bindTooltip('<b>Lokasi Tujuan</b>');
        marker.on('dragend', function(e) {
            updateLatLng(marker.getLatLng().lat, marker.getLatLng().lng);
        });
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateLatLng(marker.getLatLng().lat, marker.getLatLng().lng);
        });

        function updateLatLng(lat, lng, reverse) {

            if (reverse) {
                marker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            } else {
                document.getElementById('latTujuan').value = marker.getLatLng().lat;
                document.getElementById('lngTujuan').value = marker.getLatLng().lng;
                map.panTo([lat, lng]);

                let latTujuan = marker.getLatLng().lat;
                let lngTujuan = marker.getLatLng().lng;

                let lokasi1 = L.latLng(latSaya, lngSaya);
                let lokasi2 = L.latLng(latTujuan, lngTujuan);

                let wp1 = new L.Routing.Waypoint(lokasi1);
                let wp2 = new L.Routing.Waypoint(lokasi2);

                // buat pperbandingan jarak terdekat
                let routeUs = L.Routing.osrmv1();
                routeUs.route([wp1, wp2], (err, routes) => {
                    if (!err) {

                        let best = 1000000000000000;
                        let bestRoute = 0;

                        for (i in routes) {
                            if (routes[i].summary.totalDistance < best) {
                                bestRoute = i;
                                best = (routes[i].summary.totalDistance / 1000);
                                $("[name=totalJarak]").val(best.toFixed(2) + " KM");

                                // penentuan harga
                                jarak = best.toFixed(2);
                                if (jarak <= 4) {
                                    $("[name=Harga]").val("Rp 6.000");
                                } else if (jarak >= 4.1 && jarak <= 8) {
                                    $("[name=Harga]").val("Rp 11.000");
                                } else if (jarak > 8) {
                                    j_awal = Math.round(jarak - 8);
                                    harga = ((j_awal * 2000) + 11000);
                                    hargaBulat = harga.toFixed(0);

                                    var number_string = hargaBulat.replace(/[^,\d]/g, '').toString(),
                                        split = number_string.split(','),
                                        sisa = split[0].length % 3,
                                        rupiah = split[0].substr(0, sisa),
                                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                                    // tambahkan titik jika yang di input sudah menjadi angka ribuan
                                    if (ribuan) {
                                        separator = sisa ? '.' : '';
                                        rupiah += separator + ribuan.join('.');
                                    }

                                    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

                                    $("[name=Harga]").val("Rp " + rupiah);
                                    console.log(parseInt(j_awal));
                                }
                            }
                        }

                        L.Routing.line(routes[bestRoute], {
                            styles: [{
                                color: 'green',
                                weight: '3'
                            }]
                        }).addTo(map);
                        L.Routing.line(routes[bestRoute]).removeTo(map);
                    }
                });
            }
        }
    </script>
</body>

</html>