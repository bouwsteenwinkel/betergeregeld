<?php

return [
	'city_mmdb' => env('GEOIP_CITY_MMDB', storage_path('geoip/GeoLite2-City.mmdb')),
	'asn_mmdb' => env('GEOIP_ASN_MMDB', storage_path('geoip/GeoLite2-ASN.mmdb')),
];
