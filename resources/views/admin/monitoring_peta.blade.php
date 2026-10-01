@extends('admin.layout')

@section('title', 'Monitoring Kebun (Peta)')
@section('page_title', 'Monitoring Peta Interaktif Blok Kebun')

@section('content')
<div class="space-y-6">

    <!-- Header Stats & Legend -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-lg font-bold text-slate-800">{{ $kebun->nama ?? 'Kebun Cabai Agrocom' }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Radius Geofence: {{ $kebun->radius_meter }}m
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                <i class="fa-solid fa-location-dot text-agri-600 mr-1"></i> {{ $kebun->lokasi_text ?? 'Sambas, Kalimantan Barat' }} • 
                <span class="font-mono font-semibold text-slate-700">Lat: {{ $kebun->latitude }}, Lng: {{ $kebun->longitude }}</span>
            </p>
        </div>

        <!-- Status Legend Sesuai Mockup 10 -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-semibold">
            <span class="flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-200">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Normal (Subur)
            </span>
            <span class="flex items-center gap-1.5 text-amber-700 bg-amber-50 px-3 py-1.5 rounded-full border border-amber-200">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Perlu Perhatian
            </span>
            <span class="flex items-center gap-1.5 text-rose-700 bg-rose-50 px-3 py-1.5 rounded-full border border-rose-200">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Masalah / Hama
            </span>
        </div>
    </div>

    <!-- Map & Block Detail Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Interactive Map Column -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-col space-y-3">
            
            <!-- Map Control Header -->
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-xs font-bold text-slate-700">Geofence Radius {{ $kebun->radius_meter ?? 50 }}m</span>
                    <span id="mapEngineBadge" class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">
                        Memuat Peta...
                    </span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button id="btnTileSatellite" type="button" onclick="setMapLayer('satellite')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-agri-700 text-white shadow-sm transition">
                        Satelit
                    </button>
                    <button id="btnTileStreet" type="button" onclick="setMapLayer('street')"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Jalan
                    </button>
                    <button type="button" onclick="centerMapToKebun()"
                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition text-xs font-bold" title="Reset Fokus ke Kebun">
                        <i class="fa-solid fa-crosshairs"></i>
                    </button>
                </div>
            </div>

            <!-- The Real Interactive Map Canvas -->
            <div id="realMapContainer" class="w-full h-[500px] rounded-xl overflow-hidden border border-slate-200 relative shadow-inner">
                <!-- Leaflet / Google Map will render inside this container -->
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                <span>Klik pin atau blok pada peta untuk melihat informasi rinci.</span>
                <span class="font-mono">Lat: {{ $kebun->latitude }}, Lng: {{ $kebun->longitude }}</span>
            </div>
        </div>

        <!-- Block List Details Panel (Sesuai Storyboard 10) -->
        <div class="space-y-4">
            <h4 class="font-bold text-base text-slate-800 flex items-center justify-between">
                <span>Detail Lahan per Blok</span>
                <span class="text-xs text-slate-400 font-semibold">{{ count($bloks) }} Blok Terdaftar</span>
            </h4>

            <div class="space-y-3.5 max-h-[550px] overflow-y-auto pr-1">
                @forelse($bloks as $blok)
                    <div onclick="focusBlok({{ $blok->latitude ?? $kebun->latitude }}, {{ $blok->longitude ?? $kebun->longitude }}, '{{ $blok->kode_blok }}')"
                        class="bg-white rounded-2xl border p-4 shadow-sm hover:shadow-md transition cursor-pointer group {{ $blok->status_kondisi === 'normal' ? 'border-emerald-200 hover:border-emerald-400' : ($blok->status_kondisi === 'perhatian' ? 'border-amber-200 hover:border-amber-400' : 'border-rose-200 hover:border-rose-400') }}">
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $blok->status_kondisi === 'normal' ? 'bg-emerald-500' : ($blok->status_kondisi === 'perhatian' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                <h5 class="font-extrabold text-sm text-slate-800 group-hover:text-agri-700 transition">{{ $blok->kode_blok }}</h5>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $blok->status_kondisi === 'normal' ? 'bg-emerald-100 text-emerald-800' : ($blok->status_kondisi === 'perhatian' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ $blok->status_kondisi }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-500 mt-1">{{ $blok->nama_blok }}</p>

                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100 text-xs">
                            <div class="p-2 rounded-lg bg-slate-50">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Tanaman</span>
                                <strong class="text-slate-800">{{ number_format($blok->jumlah_tanaman) }} Pohon</strong>
                            </div>
                            <div class="p-2 rounded-lg bg-slate-50">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">Hama / Masalah</span>
                                <strong class="{{ $blok->jumlah_hama > 0 || $blok->jumlah_masalah > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $blok->jumlah_hama }} Hama • {{ $blok->jumlah_masalah }} Masalah
                                </strong>
                            </div>
                        </div>

                        @if($blok->keterangan)
                            <div class="mt-2.5 p-2 rounded-lg bg-amber-50/60 border border-amber-100 text-[11px] text-amber-800 italic">
                                <i class="fa-solid fa-note-sticky text-amber-600 mr-1"></i> {{ $blok->keterangan }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center text-slate-400 text-xs">
                        Belum ada blok kebun. Silakan tambahkan di menu <a href="{{ route('admin.master.lahan') }}" class="text-agri-600 underline font-bold">Master Lokasi & Blok</a>.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@push('scripts')
{{-- MarkerClustererPlus untuk Google Maps --}}
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
{{-- Leaflet.markercluster untuk fallback --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
@if(!empty($googleMapsApiKey))
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initGoogleMap" async defer></script>
@endif

<script>
    const kebunLat = {{ $kebun->latitude ?? -0.1234000 }};
    const kebunLng = {{ $kebun->longitude ?? 109.3456000 }};
    const radiusMeters = {{ $kebun->radius_meter ?? 50 }};
    const bloksData = @json($bloks);
    const googleApiKey = "{{ $googleMapsApiKey }}";

    let activeEngine = 'none';
    let leafletMap = null;
    let googleMap = null;
    let satelliteLayer = null;
    let streetLayer = null;
    let markers = [];

    // Catch Google Maps Authentication Failure (e.g. API not activated, Billing not enabled)
    window.gm_authFailure = function() {
        console.warn("Google Maps Auth Failure (API Key / Billing belum aktif). Beralih ke High-Res Esri Satellite.");
        const badge = document.getElementById('mapEngineBadge');
        if (badge) {
            badge.innerHTML = '<span class="text-amber-700 font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Google Maps Membutuhkan Aktivasi API &rarr; Menggunakan Satelit Esri</span>';
        }
        initLeafletMap();
    };

    // Initialize Map on Page Load
    document.addEventListener('DOMContentLoaded', function () {
        // If Google Maps API key is not provided, start Leaflet immediately
        if (!googleApiKey) {
            initLeafletMap();
        } else {
            // Give Google Maps script 2.5 seconds to load callback, otherwise fallback to Leaflet
            setTimeout(function () {
                if (activeEngine === 'none') {
                    console.log("Fallback to Leaflet (Google Maps timeout / script belum siap)");
                    initLeafletMap();
                }
            }, 2500);
        }
    });

    // 1. Google Maps Engine Initializer
    window.initGoogleMap = function() {
        try {
            if (activeEngine === 'google') return;
            activeEngine = 'google';

            const container = document.getElementById('realMapContainer');
            container.innerHTML = '';

            googleMap = new google.maps.Map(container, {
                center: { lat: kebunLat, lng: kebunLng },
                zoom: 18,
                mapTypeId: 'hybrid', // Real Google Satellite + Streets overlay
                tilt: 0,
                streetViewControl: false,
                mapTypeControl: false,
                fullscreenControl: true,
            });

            const badge = document.getElementById('mapEngineBadge');
            if (badge) {
                badge.innerHTML = '<span class="text-emerald-700 font-bold"><i class="fa-brands fa-google text-emerald-600 mr-1"></i> Google Maps Satelit Hybrid</span>';
            }

            // Geofence Circle
            new google.maps.Circle({
                strokeColor: '#16a34a',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#22c55e',
                fillOpacity: 0.25,
                map: googleMap,
                center: { lat: kebunLat, lng: kebunLng },
                radius: radiusMeters,
            });

            // Blok Markers — semua marker merah standar Google Maps, tanpa beda warna
            const rawMarkers = [];
            markers = [];
            bloksData.forEach((b, index) => {
                const bLat = b.latitude ? parseFloat(b.latitude)
                    : (kebunLat + (index % 2 === 0 ? 0.00015 : -0.00015) * Math.ceil((index + 1) / 2));
                const bLng = b.longitude ? parseFloat(b.longitude)
                    : (kebunLng + (index % 2 === 0 ? -0.00015 : 0.00018) * Math.ceil((index + 1) / 2));

                const statusColor = b.status_kondisi === 'normal' ? '#16a34a'
                    : (b.status_kondisi === 'perhatian' ? '#d97706' : '#dc2626');

                // Marker merah default standar (tidak pakai icon custom)
                const m = new google.maps.Marker({
                    position: { lat: bLat, lng: bLng },
                    title: `${b.kode_blok} - ${b.nama_blok || ''}`,
                });

                const info = new google.maps.InfoWindow({
                    maxWidth: 240,
                    content: (() => {
                        const keteranganHtml = b.keterangan
                            ? `<div style="margin-top:6px;padding-top:4px;font-style:italic;color:#64748b;font-size:11px;border-top:1px dashed #e2e8f0;">"${b.keterangan}"</div>`
                            : '';
                        return `<div style="font-family:inherit;font-size:12px;min-width:180px;padding:2px 0;">
                            <strong style="font-size:13px;color:#1e293b;display:block;margin-bottom:2px;">${b.kode_blok} - ${b.nama_blok || ''}</strong>
                            <span style="font-weight:bold;color:${statusColor};text-transform:uppercase;font-size:11px;">● ${b.status_kondisi}</span>
                            <hr style="margin:6px 0;border:0;border-top:1px solid #e2e8f0;">
                            <div style="line-height:1.6;">🌱 <b>${b.jumlah_tanaman}</b> Pohon Cabai</div>
                            <div style="line-height:1.6;">🐛 <b>${b.jumlah_hama || 0}</b> Hama Terdeteksi</div>
                            <div style="line-height:1.6;">⚠️ <b>${b.jumlah_masalah || 0}</b> Masalah Tanaman</div>
                            ${keteranganHtml}
                        </div>`;
                    })()
                });

                m.addListener('click', () => info.open(googleMap, m));
                rawMarkers.push(m);
                markers.push({ id: b.id, marker: m, lat: bLat, lng: bLng, infoWindow: info });
            });

            // Clustering otomatis saat zoom diperkecil
            markerCluster = new markerClusterer.MarkerClusterer({ map: googleMap, markers: rawMarkers });

        } catch (e) {
            console.error("Gagal inisialisasi Google Maps:", e);
            initLeafletMap();
        }
    };

    // 2. Leaflet High-Res Satellite Engine (Esri World Imagery)
    function initLeafletMap() {
        if (activeEngine === 'leaflet') return;
        activeEngine = 'leaflet';

        const container = document.getElementById('realMapContainer');
        container.innerHTML = '';

        const badge = document.getElementById('mapEngineBadge');
        if (badge && !badge.innerHTML.includes('Gagal Auth')) {
            badge.innerHTML = '<span class="text-slate-700 font-bold"><i class="fa-solid fa-satellite text-agri-600 mr-1"></i> Esri Satelit High-Res</span>';
        }

        // High resolution Esri World Imagery Satellite Tile layer
        satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri World Imagery',
            maxZoom: 19
        });

        // OpenStreetMap Streets layer
        streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        });

        leafletMap = L.map('realMapContainer', {
            center: [kebunLat, kebunLng],
            zoom: 17,
            layers: [satelliteLayer]
        });

        // Geofence Circle Overlay (50 meters radius)
        const geofenceCircle = L.circle([kebunLat, kebunLng], {
            color: '#16a34a',
            fillColor: '#22c55e',
            fillOpacity: 0.2,
            radius: radiusMeters,
            weight: 2
        }).addTo(leafletMap);
        geofenceCircle.bindPopup(`<strong>Batas Geofencing Presisi</strong><br>Radius: ${radiusMeters} meter untuk absensi pekerja.`);

        // Cluster Group dengan ikon cluster merah
        const clusterGroup = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: `<div style="background:#dc2626;color:white;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:13px;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.4);">${cluster.getChildCount()}</div>`,
                    className: '',
                    iconSize: [36, 36]
                });
            }
        });

        // Blok Markers — semua marker merah default Leaflet, tanpa beda warna
        markers = [];
        bloksData.forEach((b, index) => {
            const bLat = b.latitude ? parseFloat(b.latitude)
                : (kebunLat + (index % 2 === 0 ? 0.00015 : -0.00015) * Math.ceil((index + 1) / 2));
            const bLng = b.longitude ? parseFloat(b.longitude)
                : (kebunLng + (index % 2 === 0 ? -0.00015 : 0.00018) * Math.ceil((index + 1) / 2));

            const statusColor = b.status_kondisi === 'normal' ? '#16a34a'
                : (b.status_kondisi === 'perhatian' ? '#d97706' : '#dc2626');

            // Marker default Leaflet (merah standar, tidak pakai icon custom)
            const m = L.marker([bLat, bLng]);
            const keteranganHtmlL = b.keterangan
                ? `<div style="margin-top:6px;padding-top:4px;font-style:italic;color:#64748b;font-size:11px;border-top:1px dashed #e2e8f0;">"${b.keterangan}"</div>`
                : '';
            m.bindPopup(`
                <div style="font-family:inherit;font-size:12px;min-width:180px;padding:2px 0;">
                    <strong style="font-size:13px;color:#1e293b;display:block;margin-bottom:2px;">${b.kode_blok} - ${b.nama_blok || ''}</strong>
                    <span style="font-weight:bold;color:${statusColor};text-transform:uppercase;font-size:11px;">● ${b.status_kondisi}</span>
                    <hr style="margin:6px 0;border:0;border-top:1px solid #e2e8f0;">
                    <div style="line-height:1.6;">🌱 <b>${b.jumlah_tanaman}</b> Pohon Cabai</div>
                    <div style="line-height:1.6;">🐛 <b>${b.jumlah_hama || 0}</b> Hama Terdeteksi</div>
                    <div style="line-height:1.6;">⚠️ <b>${b.jumlah_masalah || 0}</b> Masalah Tanaman</div>
                    ${keteranganHtmlL}
                </div>
            `, { maxWidth: 240 });

            clusterGroup.addLayer(m);
            markers.push({ id: b.id, marker: m, lat: bLat, lng: bLng });
        });

        leafletMap.addLayer(clusterGroup);
    }

    function setMapLayer(type) {
        if (activeEngine === 'google' && googleMap) {
            googleMap.setMapTypeId(type === 'satellite' ? 'hybrid' : 'roadmap');
            document.getElementById('btnTileSatellite').className = type === 'satellite' ? 'px-2.5 py-1 rounded-lg text-xs font-bold bg-agri-700 text-white shadow-sm transition' : 'px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition';
            document.getElementById('btnTileStreet').className = type === 'street' ? 'px-2.5 py-1 rounded-lg text-xs font-bold bg-agri-700 text-white shadow-sm transition' : 'px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition';
        } else if (leafletMap) {
            if (type === 'satellite') {
                leafletMap.removeLayer(streetLayer);
                leafletMap.addLayer(satelliteLayer);
                document.getElementById('btnTileSatellite').className = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-agri-700 text-white shadow-sm transition';
                document.getElementById('btnTileStreet').className = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition';
            } else {
                leafletMap.removeLayer(satelliteLayer);
                leafletMap.addLayer(streetLayer);
                document.getElementById('btnTileStreet').className = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-agri-700 text-white shadow-sm transition';
                document.getElementById('btnTileSatellite').className = 'px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition';
            }
        }
    }

    function centerMapToKebun() {
        if (activeEngine === 'google' && googleMap) {
            googleMap.setCenter({ lat: kebunLat, lng: kebunLng });
            googleMap.setZoom(18);
        } else if (leafletMap) {
            leafletMap.setView([kebunLat, kebunLng], 17);
        }
    }

    function focusBlok(lat, lng, kodeBlok) {
        if (activeEngine === 'google' && googleMap) {
            googleMap.setCenter({ lat: lat, lng: lng });
            googleMap.setZoom(19);
            const item = markers.find(m => Math.abs(m.lat - lat) < 0.0001 && Math.abs(m.lng - lng) < 0.0001);
            if (item && item.infoWindow) {
                item.infoWindow.open(googleMap, item.marker);
            }
        } else if (leafletMap) {
            leafletMap.setView([lat, lng], 18, { animate: true });
            const item = markers.find(m => Math.abs(m.lat - lat) < 0.0001 && Math.abs(m.lng - lng) < 0.0001);
            if (item) item.marker.openPopup();
        }
    }
</script>
@endpush
@endsection

