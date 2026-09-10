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

{{-- The x-template and component registration are emitted INLINE, not via @pushOnce.
     The checkout address form is itself rendered inside @push('scripts'), and Blade cannot
     push from within pushed content - the block would be silently dropped. A static guard
     keeps it to one emission per request even though the component renders twice on
     checkout (billing and shipping). --}}
@php
    // Request-scoped, not view-scoped: Blade component scopes are isolated, so a local
    // variable would not survive to the second instance (checkout renders billing AND
    // shipping). The container flag emits the template exactly once per request.
    $bdGeoEmit = ! app()->bound('bdgeo.template.emitted');
    if ($bdGeoEmit) {
        app()->instance('bdgeo.template.emitted', true);
    }
@endphp

@if ($bdGeoEmit)
    <script
        type="text/x-template"
        id="v-bd-address-fields-template"
    >
        <div>
            <!-- Country: the shop ships within Bangladesh only, so this is fixed rather than chosen. -->
            <x-shop::form.control-group class="!mb-4">
                <x-shop::form.control-group.label class="!mt-0">
                    @lang('shop::app.checkout.onepage.address.country')
                </x-shop::form.control-group.label>

                <div class="flex h-11 items-center rounded border border-[#E9E9E9] bg-gray-100 px-4 text-gray-600">
                    Bangladesh
                </div>

                <v-field
                    type="hidden"
                    :name="field('country')"
                    value="BD"
                />
            </x-shop::form.control-group>

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- Division -->
                <x-shop::form.control-group class="!mb-4">
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('bdgeo::app.address.division')
                    </x-shop::form.control-group.label>

                    <v-field
                        as="select"
                        :name="field('bd_division_id')"
                        v-model="divisionId"
                        rules="required"
                        :label="'@lang('bdgeo::app.address.division')'"
                        class="w-full rounded border px-4 py-2.5 text-sm"
                        @change="onDivisionChange"
                    >
                        <option value="">@lang('bdgeo::app.address.select-division')</option>
                        <option
                            v-for="d in bootstrap.divisions"
                            :key="d.id"
                            :value="d.id"
                        >@{{ d.name }}</option>
                    </v-field>

                    <v-error-message :name="field('bd_division_id')" class="mt-1 text-xs text-red-600" />
                </x-shop::form.control-group>

                <!-- District -> persisted as the native `state` -->
                <x-shop::form.control-group class="!mb-4">
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('bdgeo::app.address.district')
                    </x-shop::form.control-group.label>

                    <v-field
                        as="select"
                        :name="field('bd_district_id')"
                        v-model="districtId"
                        rules="required"
                        :label="'@lang('bdgeo::app.address.district')'"
                        class="w-full rounded border px-4 py-2.5 text-sm"
                        :disabled="! divisionId"
                        @change="onDistrictChange"
                    >
                        <option value="">
                            @{{ divisionId ? '@lang('bdgeo::app.address.select-district')' : '@lang('bdgeo::app.address.select-division-first')' }}
                        </option>
                        <option
                            v-for="d in districtsForDivision"
                            :key="d.id"
                            :value="d.id"
                        >@{{ d.name }}</option>
                    </v-field>

                    <v-error-message :name="field('bd_district_id')" class="mt-1 text-xs text-red-600" />
                </x-shop::form.control-group>

                <!-- Upazila / Thana -> persisted as the native `city` -->
                <x-shop::form.control-group class="!mb-4">
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('bdgeo::app.address.upazila')
                    </x-shop::form.control-group.label>

                    <v-field
                        as="select"
                        :name="field('bd_upazila_id')"
                        v-model="upazilaId"
                        rules="required"
                        :label="'@lang('bdgeo::app.address.upazila')'"
                        class="w-full rounded border px-4 py-2.5 text-sm"
                        :disabled="! districtId || loadingUpazilas"
                        @change="onUpazilaChange"
                    >
                        <option value="">
                            @{{ upazilaPlaceholder }}
                        </option>
                        <option
                            v-for="u in upazilas"
                            :key="u.id"
                            :value="u.id"
                        >@{{ u.name }}</option>
                    </v-field>

                    <v-error-message :name="field('bd_upazila_id')" class="mt-1 text-xs text-red-600" />
                </x-shop::form.control-group>

                <!-- Level 4 is a single free-text area for every address. Unions were dropped
                     from collection: they are rural-only, absent for metro thanas, and buyers
                     describe where they live by area and road rather than by union. -->
                <x-shop::form.control-group class="!mb-4" v-if="upazilaId">
                    <x-shop::form.control-group.label class="required !mt-0">
                        @lang('bdgeo::app.address.area')
                    </x-shop::form.control-group.label>

                    <v-field
                        type="text"
                        :name="field('bd_area_name')"
                        v-model="areaName"
                        rules="required"
                        :label="'@lang('bdgeo::app.address.area')'"
                        class="w-full rounded border px-4 py-2.5 text-sm"
                        placeholder="@lang('bdgeo::app.address.area-placeholder')"
                    />

                    <v-error-message :name="field('bd_area_name')" class="mt-1 text-xs text-red-600" />
                </x-shop::form.control-group>

                <!-- Postcode stays optional: the geo dataset carries no post codes. -->
                <x-shop::form.control-group class="!mb-4">
                    <x-shop::form.control-group.label class="!mt-0">
                        @lang('shop::app.checkout.onepage.address.postcode')
                    </x-shop::form.control-group.label>

                    <v-field
                        type="text"
                        :name="field('postcode')"
                        v-model="postcode"
                        :label="'@lang('shop::app.checkout.onepage.address.postcode')'"
                        class="w-full rounded border px-4 py-2.5 text-sm"
                        placeholder="@lang('bdgeo::app.address.postcode-placeholder')"
                    />
                </x-shop::form.control-group>
            </div>

            <!-- Derived server-side, but submitted so the request carries a coherent address
                 even before the observer runs. -->
            <v-field type="hidden" :name="field('state')" v-model="stateCode" />
            <v-field type="hidden" :name="field('city')" v-model="cityName" />
        </div>
    </script>

    <script type="module">
        app.component('v-bd-address-fields', {
            template: '#v-bd-address-fields-template',

            props: {
                namePrefix: { type: String, default: '' },
                bootstrap: { type: Object, required: true },
                initial: { type: Object, default: () => ({}) },
            },

            data() {
                return {
                    divisionId: this.initial.bd_division_id ?? '',
                    districtId: this.initial.bd_district_id ?? '',
                    upazilaId: this.initial.bd_upazila_id ?? '',
                    areaName: this.initial.bd_area_name ?? '',
                    postcode: this.initial.postcode ?? '',
                    upazilas: [],
                    loadingUpazilas: false,
                    // A slow response for a district the user has already moved on from must
                    // never overwrite the list they are looking at now.
                    upazilaRequest: null,
                };
            },

            computed: {
                districtsForDivision() {
                    if (! this.divisionId) return [];
                    return this.bootstrap.districts.filter(d => String(d.division_id) === String(this.divisionId));
                },

                selectedUpazila() {
                    return this.upazilas.find(u => String(u.id) === String(this.upazilaId)) || null;
                },

                upazilaPlaceholder() {
                    if (! this.districtId) return this.trans('select-district-first');
                    if (this.loadingUpazilas) return this.trans('loading');
                    return this.trans('select-upazila');
                },

                stateCode() {
                    const d = this.bootstrap.districts.find(x => String(x.id) === String(this.districtId));
                    return d ? d.code : '';
                },

                cityName() {
                    return this.selectedUpazila ? this.selectedUpazila.name : '';
                },
            },

            mounted() {
                // Edit mode: repopulate the level-3 list so the saved value shows its label.
                if (this.districtId) {
                    this.fetchUpazilas();
                }
            },

            methods: {
                field(name) {
                    return this.namePrefix ? `${this.namePrefix}${name}` : name;
                },

                trans(key) {
                    return {
                        'loading': "@lang('bdgeo::app.address.loading')",
                        'select-upazila': "@lang('bdgeo::app.address.select-upazila')",
                        'select-district-first': "@lang('bdgeo::app.address.select-district-first')",
                    }[key] || '';
                },

                onDivisionChange() {
                    this.districtId = '';
                    this.clearFromUpazila();
                    this.upazilas = [];
                },

                onDistrictChange() {
                    this.clearFromUpazila();
                    this.fetchUpazilas();
                },

                onUpazilaChange() {
                    this.areaName = '';
                },

                clearFromUpazila() {
                    this.upazilaId = '';
                    this.areaName = '';
                },

                fetchUpazilas() {
                    if (! this.districtId) return;

                    if (this.upazilaRequest) this.upazilaRequest.abort();
                    this.upazilaRequest = new AbortController();
                    this.loadingUpazilas = true;

                    fetch(`/api/bd-geo/upazilas?district_id=${encodeURIComponent(this.districtId)}`,
                        { signal: this.upazilaRequest.signal })
                        .then(r => r.json())
                        .then(json => { this.upazilas = json.data || []; })
                        .catch(e => { if (e.name !== 'AbortError') this.upazilas = []; })
                        .finally(() => { this.loadingUpazilas = false; });
                },

            },
        });
    </script>
@endif
