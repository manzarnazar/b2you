@extends('layouts.admin.app')

@section('title',translate('Update Zone'))

@push('css_or_js')

@endpush

@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                   {{ translate('edit_zone')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <form action="{{route('admin.business-settings.zone.update', $zone->id)}}" method="post" id="zone_form" class="shadow--card">
            @csrf
            <div class="row">
                <div class="col-md-5">
                    <div class="zone-setup-instructions">
                        <div class="zone-setup-top">
                            <h6 class="subtitle">{{ translate('Instructions') }}</h6>
                            <p>
                                {{ translate('Create_&_connect_dots_in_a_specific_area_on_the_map_to_add_a_new_business_zone.') }}
                            </p>
                        </div>
                        <div class="zone-setup-item">
                            <div class="zone-setup-icon">
                                <i class="tio-hand-draw"></i>
                            </div>
                            <div class="info">
                                {{ translate('Use_this_‘Hand_Tool’_to_find_your_target_zone.') }}
                            </div>
                        </div>
                        <div class="zone-setup-item">
                            <div class="zone-setup-icon">
                                <i class="tio-free-transform"></i>
                            </div>
                            <div class="info">
                                {{ translate('Use_this_‘Shape_Tool’_to_point_out_the_areas_and_connect_the_dots._Minimum_3_points/dots_are_required.') }}
                            </div>
                        </div>
                        <div class="instructions-image mt-4">
                            <img src="{{asset('public/assets/admin/img/instructions.gif')}}" alt="instructions">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-7 zone-setup">
                    <div class="form-group">
                        @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{translate('messages.default')}}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="pl-xl-5 pl-xxl-0">
                        @if($language)
                            <div class="row lang_form" id="default-form">
                                <div class="form-group col-6">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{ translate('messages.default') }})</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_zone')}}" maxlength="191" value="{{$zone?->getRawOriginal('name')}}"  >
                                </div>
                                <div class="form-group col-6">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.display_name')}} ({{ translate('messages.default') }})</label>
                                    <input type="text" name="display_name[]" class="form-control" placeholder="{{translate('messages.display_name')}}" maxlength="191" value="{{$zone?->getRawOriginal('display_name')}}"  >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                                @foreach($language as $lang)
                                    <?php
                                        if(count($zone['translations'])){
                                            $translate = [];
                                            foreach($zone['translations'] as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="name"){
                                                    $translate[$lang]['name'] = $t->value;
                                                }
                                                if($t->locale == $lang && $t->key=="display_name"){
                                                    $translate[$lang]['display_name'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                <div class="row lang_form d-none" id="{{$lang}}-form">
                                    <div class="form-group col-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" placeholder="{{translate('messages.new_zone')}}" maxlength="191" value="{{$translate[$lang]['name']??''}}"  >
                                    </div>
                                    <div class="form-group col-6">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.display_name')}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="display_name[]" class="form-control" placeholder="{{translate('messages.display_name')}}" maxlength="191" value="{{$translate[$lang]['display_name']??''}}"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                                @endforeach
                            @endif
                        <div class="form-group d-none">
                            <label class="input-label" for="exampleFormControlInput1">{{ translate('messages.Coordinates') }}
                                <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{translate('messages.draw_your_zone_on_the_map')}}">
                                    {{translate('messages.draw_your_zone_on_the_map')}}
                                </span>
                            </label>
                                <textarea type="text" rows="8" name="coordinates" id="coordinates" class="form-control" readonly>@foreach($zone->coordinates[0]->toArray()['coordinates'] as $key=>$coords)<?php if(count($zone->coordinates[0]->toArray()['coordinates']) != $key+1) {if($key != 0) echo(','); ?>({{$coords[1]}}, {{$coords[0]}})<?php } ?>@endforeach</textarea>
                        </div>


                        <div class="map-warper rounded mt-0">
                            <input id="pac-input" class="controls rounded initial--33" title="{{translate('messages.search_your_location_here')}}" type="text" placeholder="{{translate('messages.search_here')}}"/>
                            <div id="map-canvas" class="initial--34"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container mt-3 justify-content-end">
                <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                <button type="submit" class="btn btn--primary">{{translate('messages.Save_changes')}}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
<script src="https://maps.googleapis.com/maps/api/js?key={{\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value}}&libraries=places,marker&v=3.61"></script>
<script>
    "use strict";
    auto_grow();
    function auto_grow() {
        let element = document.getElementById("coordinates");
        element.style.height = "5px";
        element.style.height = (element.scrollHeight)+"px";
    }

    let map; // Global declaration of the map
    let lat_longs = new Array();
    let drawingPolygon = null; // editable polygon used to draw/edit the zone
    let lastpolygon = null; // kept for backward compatibility with existing handlers
    let bounds = new google.maps.LatLngBounds();
    let polygons = [];
    let drawingMode = false; // true = Shape tool (draw), false = Hand tool (pan)
    let vertexMarkers = []; // visible dot at each polygon vertex (drawing feedback)

    function vertexIcon() {
        return {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 6,
            fillColor: '#FF0000',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
        };
    }

    function syncVertexMarkers() {
        vertexMarkers.forEach(function (m) { m.setMap(null); });
        vertexMarkers = [];
        if (!drawingPolygon) return;
        drawingPolygon.getPath().forEach(function (latLng) {
            vertexMarkers.push(new google.maps.Marker({
                position: latLng,
                map: map,
                icon: vertexIcon(),
                clickable: false,
                zIndex: 9999,
            }));
        });
    }

    function clearDrawing() {
        if (drawingPolygon) drawingPolygon.getPath().clear();
        vertexMarkers.forEach(function (m) { m.setMap(null); });
        vertexMarkers = [];
        $('#coordinates').val('');
    }

    function updateCoordinates() {
        if (!drawingPolygon) return;
        const path = drawingPolygon.getPath().getArray();
        $('#coordinates').val(path.length ? path.toString() : '');
        auto_grow();
        syncVertexMarkers();
    }

    // On-map Hand/Shape toolbar (custom map control replacing the removed DrawingManager).
    let handToolEl = null;
    let shapeToolEl = null;

    function setDrawingMode(drawing) {
        drawingMode = drawing;
        if (map) {
            map.setOptions({ draggableCursor: drawing ? "crosshair" : null });
        }
        if (shapeToolEl) {
            shapeToolEl.style.backgroundColor = drawing ? "#e7f0ff" : "#fff";
            shapeToolEl.style.color = drawing ? "#050df2" : "#444";
        }
        if (handToolEl) {
            handToolEl.style.backgroundColor = drawing ? "#fff" : "#e7f0ff";
            handToolEl.style.color = drawing ? "#444" : "#050df2";
        }
    }

    function buildDrawingControl() {
        const wrapper = document.createElement("div");
        wrapper.style.cssText = "margin:10px;display:flex;border-radius:4px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.3);background:#fff;font-family:Roboto,Arial,sans-serif;";

        handToolEl = document.createElement("div");
        handToolEl.title = "Hand Tool — pan the map";
        handToolEl.style.cssText = "cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px;font-size:18px;color:#444;";
        handToolEl.innerHTML = `<i class="tio-hand-draw"></i>`;

        shapeToolEl = document.createElement("div");
        shapeToolEl.title = "Shape Tool — click the map to connect the dots";
        shapeToolEl.style.cssText = "cursor:pointer;display:flex;align-items:center;justify-content:center;width:36px;height:36px;font-size:18px;color:#444;border-left:1px solid #e6e6e6;";
        shapeToolEl.innerHTML = `<i class="tio-free-transform"></i>`;

        handToolEl.addEventListener("click", function () { setDrawingMode(false); });
        shapeToolEl.addEventListener("click", function () { setDrawingMode(true); });

        wrapper.appendChild(handToolEl);
        wrapper.appendChild(shapeToolEl);
        return wrapper;
    }


    function resetMap(controlDiv) {
        // Set CSS for the control border.
        const controlUI = document.createElement("div");
        controlUI.style.backgroundColor = "#fff";
        controlUI.style.border = "2px solid #fff";
        controlUI.style.borderRadius = "3px";
        controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
        controlUI.style.cursor = "pointer";
        controlUI.style.marginTop = "8px";
        controlUI.style.marginBottom = "22px";
        controlUI.style.textAlign = "center";
        controlUI.title = "Reset map";
        controlDiv.appendChild(controlUI);
        // Set CSS for the control interior.
        const controlText = document.createElement("div");
        controlText.style.color = "rgb(25,25,25)";
        controlText.style.fontFamily = "Roboto,Arial,sans-serif";
        controlText.style.fontSize = "10px";
        controlText.style.lineHeight = "16px";
        controlText.style.paddingLeft = "2px";
        controlText.style.paddingRight = "2px";
        controlText.innerHTML = "X";
        controlUI.appendChild(controlText);
        // Setup the click event listeners: simply set the map to Chicago.
        controlUI.addEventListener("click", () => {
            clearDrawing();
        });
    }

    function initialize() {
        let myLatlng = new google.maps.LatLng({{trim(explode(' ',$zone->center)[1], 'POINT()')}}, {{trim(explode(' ',$zone->center)[0], 'POINT()')}});
        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}"

        let myOptions = {
            zoom: 13,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapId:mapId
        };
        map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

        const polygonCoords = [

            @foreach($area['coordinates'] as $coords)
             { lat: {{$coords[1]}}, lng: {{$coords[0]}} },
            @endforeach
        ];

        // Existing zone — shown read-only as a reference (blue).
        let existingZone = new google.maps.Polygon({
            map: map,
            paths: polygonCoords,
            editable: false,
            clickable: false,
            strokeColor: "#050df2",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#050df2",
            fillOpacity: 0.1,
        });

        existingZone.getPaths().forEach(function(path) {
            path.forEach(function(latlng) {
                bounds.extend(latlng);
                map.fitBounds(bounds);
            });
        });

        // New drawing surface — starts empty. Use the Shape Tool and click the
        // map to draw a fresh zone; it replaces the existing one on save.
        // (Google removed DrawingManager as of Maps JS v3.65.)
        // Create the polygon, then set ONE empty path so getPath() returns a real
        // MVCArray of points. (`paths: []` -> getPath() undefined; an empty MVCArray
        // is treated as a list of paths -> getPath() returns a LatLng, breaking
        // getArray(). setPath([]) unambiguously creates a single empty path.)
        drawingPolygon = new google.maps.Polygon({
            map: map,
            editable: true,
            clickable: false, // so map clicks keep adding points even over the fill
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.1,
        });
        drawingPolygon.setPath([]);
        const drawingPath = drawingPolygon.getPath();
        lastpolygon = drawingPolygon;

        google.maps.event.addListener(drawingPath, "set_at", updateCoordinates);
        google.maps.event.addListener(drawingPath, "insert_at", updateCoordinates);
        google.maps.event.addListener(drawingPath, "remove_at", updateCoordinates);

        google.maps.event.addListener(map, "click", function (event) {
            if (!drawingMode) return; // Hand tool active — just pan
            drawingPath.push(event.latLng);
            updateCoordinates();
        });

        map.controls[google.maps.ControlPosition.LEFT_TOP].push(buildDrawingControl());
        setDrawingMode(false); // start with Hand tool; existing zone is editable

        const resetDiv = document.createElement("div");
        resetMap(resetDiv, lastpolygon);
        map.controls[google.maps.ControlPosition.RIGHT_TOP].push(resetDiv);

        // Create the search box and link it to the UI element.
        const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            // Bias the SearchBox results towards current map's viewport.
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });
            let markers = [];
            // Listen for the event fired when the user selects a prediction and retrieve
            // more details for that place.
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                return;
                }
                // Clear out the old markers.
                markers.forEach((marker) => {
                marker.map = null;
                });
                markers = [];
                // For each place, get the name and location.
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                if (!place.geometry || !place.geometry.location) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                const { AdvancedMarkerElement } = google.maps.marker;

                // Create a marker for each place (AdvancedMarkerElement has no `icon`).
                markers.push(
                    new AdvancedMarkerElement({
                    map,
                    title: place.name,
                    position: place.geometry.location,
                    })
                );

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
                });
                map.fitBounds(bounds);
            });
    }
    google.maps.event.addDomListener(window, 'load', initialize);

    function set_all_zones()
    {
        $.get({
            url: '{{route('admin.zone.zoneCoordinates')}}/{{$zone->id}}',
            dataType: 'json',
            success: function (data) {

                console.log(data);
                for(let i=0; i<data.length;i++)
                {
                    polygons.push(new google.maps.Polygon({
                        paths: data[i],
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#FF0000",
                        fillOpacity: 0.1,
                        clickable: false, // let clicks pass through to the map for drawing
                    }));
                    polygons[i].setMap(map);
                }

            },
        });
    }
    $(document).on('ready', function(){
        set_all_zones();
        $("#zone_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })
    });

    $('#reset_btn').click(function(){
        location.reload(true);
    })

</script>
@endpush
