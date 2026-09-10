{{-- Renders ONLY the component element. It must emit no <script> tag: every form that
     uses it (checkout and both account forms) places it inside a
     <script type="text/x-template"> block, and a nested </script> would terminate that
     outer template early and leave the page blank. The template and Vue registration
     live in <x-bdgeo::address-fields-scripts />, emitted as a SIBLING of the template. --}}
@props([
    // Static prefix, for the flat account/admin forms (usually '').
    'namePrefix' => '',
    // Vue expression, for checkout where the prefix is a runtime value such as
    // `controlName + '.'` and differs between the billing and shipping copies.
    'prefixExpr' => null,
    'initial' => [],
    // Checkout keeps its address in a Vue object; pass its expression to seed edit mode.
    'initialExpr' => null,
])

@php
    $bdGeoBootstrap = \Illuminate\Support\Facades\Cache::rememberForever('bd_geo.bootstrap', fn () => [
        'divisions' => \Local\BangladeshGeo\Models\BdDivision::active()->orderBy('name')
            ->get(['id', 'name', 'bn_name'])->toArray(),
        'districts' => \Local\BangladeshGeo\Models\BdDistrict::active()->orderBy('name')
            ->get(['id', 'division_id', 'code', 'name', 'bn_name', 'has_city_corporation'])->toArray(),
    ]);
@endphp

<v-bd-address-fields
    @if ($prefixExpr) :name-prefix="{{ $prefixExpr }}" @else name-prefix="{{ $namePrefix }}" @endif
    :bootstrap='@json($bdGeoBootstrap)'
    @if ($initialExpr) :initial="{{ $initialExpr }}" @else :initial='@json((object) $initial)' @endif
></v-bd-address-fields>
