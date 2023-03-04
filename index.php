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
            width: 100%;
            /* margin: auto; */
            /* width: 90vw; */
            height: 60vh;
        }
    </style>
</head>

<body>
    <!-- konten -->
    <div id="peta" class="my-5">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-6">
                    <form method="post" id="form-lokasi">
                        <div class="form-group">
                            <label>Titik Lokasi Saya</label>
                            <input type="text" name="latSaya" class="form-control mb-2" id="latSaya" />
                            <input type="text" name="lngSaya" class="form-control mb-5" id="lngSaya" />

                            <label>Titik Lokasi Tujuan</label>
                            <input type="text" name="latTujuan" class="form-control mb-2" id="latTujuan" />
                            <input type="text" name="lngTujuan" class="form-control mb-5" id="lngTujuan" />

                            <label>Jarak Tempuh</label>
                            <input type="text" name="totalJarak" class="form-control mb-2" id="totalJarak" />
                            <label>Harga</label>
                            <input type="text" name="Harga" class="form-control mb-2" id="Harga" />

                        </div>
                    </form>
                </div>

                <div class="col-12 col-sm-6">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- end konten -->


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <!-- start cdn leaflet routing mechine -->
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <!-- end cdn -->

    <script>
        // buat map dengan titik koordinat default asembagus
        let map = L.map('map').setView([-7.749540941658048, 114.2177444085855], 16);

        // ambil data dari form
        let latSaya = $("input[name=latSaya]").val();
        let lngSaya = $("input[name=lngSaya]").val();

        // buat titik lokasi sekarang
        let lokasi1 = L.latLng(latSaya, lngSaya);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // dapatkan lokasi
        getLocation();

        // fungsi dapatkan lokasi
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                x.innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        // fungsi munculkan posisi dan atur isi inputan lokasi saya dengan posisi sekarang
        function showPosition(position) {

            $(document).ready(function() {
                $("[name=latSaya]").val(position.coords.latitude);
                $("[name=lngSaya]").val(position.coords.longitude);
            });
        }

        // fungsi buat tombol
        function createButton(label, container) {
            var btn = L.DomUtil.create('button', '', container);
            btn.setAttribute('type', 'button');
            btn.innerHTML = label;
            return btn;
        }

        // klik lokasi tujuan agar muncul marker tujuan
        var ReversablePlan = L.Routing.Plan.extend({
            createGeocoders: function() {
                var container = L.Routing.Plan.prototype.createGeocoders.call(this),
                    reverseButton = createButton('↑↓', container);
                L.DomEvent.on(reverseButton, 'click', function() {
                    var waypoints = this.getWaypoints();
                    this.setWaypoints(waypoints.reverse());
                }, this);
                return container;
            }
        });

        map.on('click', function(e) {

            // buat tombol balikkan rute serta buat rutenya
            var plan = new ReversablePlan([
                    lokasi1,
                    L.latLng(e.latlng.lat, e.latlng.lng)
                ], {
                    geocoder: L.Control.Geocoder.nominatim(),
                    routeWhileDragging: true
                }),
                control = L.Routing.control({
                    routeWhileDragging: true,
                    plan: plan
                }).on('routesfound', function(e) {

                    var titik = e.routes[0].coordinates;
                    var jml_jalur = titik.length - 1;

                    // inputkan nilai ke tag input pada form
                    $("[name=latSaya]").val(e.routes[0].coordinates[0].lat);
                    $("[name=lngSaya]").val(e.routes[0].coordinates[0].lng);

                    $("[name=latTujuan]").val(e.routes[0].coordinates[jml_jalur].lat);
                    $("[name=lngTujuan]").val(e.routes[0].coordinates[jml_jalur].lng);

                    // console.log(e.routes[0]);

                    best = e.routes[0].summary.totalDistance / 1000;
                    jarak = best.toFixed(2);
                    $("[name=totalJarak]").val(jarak + " Km");

                    if (jarak <= 4) {
                        $("[name=Harga]").val("Rp 6.000");
                    } else if (jarak >= 4.1 && jarak <= 8) {
                        $("[name=Harga]").val("Rp 11.000");
                    } else if (jarak > 8) {

                        // hitung harga dengan jarak yang ditempuh
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
                        
                        // update value input harga dengan harga yang sudah di hitung berdasarkan jarak
                        $("[name=Harga]").val("Rp " + rupiah);
                    }

                }).addTo(map);

            // console.log(e);
        });
    </script>
</body>

</html>