{{-- Emits the <script type="text/x-template"> and the Vue registration for
     <x-bdgeo::address-fields />.

     This MUST be a sibling of a form's own x-template, never inside one: a nested
     </script> terminates the outer template and the page renders blank.

     The guard is request-scoped, not view-scoped: Blade component scopes are isolated and
     checkout renders the address form twice, for billing and shipping.

     Every control uses Bagisto's own control-group components, so the block inherits the
     form's borders, spacing, focus states and error styling. Postcode is optional because
     the geo dataset carries no post codes. `state` and `city` are submitted from the
     selection and re-derived by AddressObserver on save. A newer upazila request aborts the
     older one, so a slow response for a district the customer has left never overwrites
     the list on screen. --}}
@php
    $bdGeoEmit = ! app()->bound('bdgeo.scripts.emitted');
    if ($bdGeoEmit) {
        app()->instance('bdgeo.scripts.emitted', true);
    }
@endphp

@if ($bdGeoEmit)
    <script
        type="text/x-template"
        id="v-bd-address-fields-template"
    >
        <div>
            <!-- Country -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label>
                    @lang('shop::app.checkout.onepage.address.country')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    ::name="bdField('country_display')"
                    value="Bangladesh"
                    readonly
                    :label="trans('shop::app.checkout.onepage.address.country')"
                />

                <input
                    type="hidden"
                    :name="bdField('country')"
                    value="BD"
                />
            </x-shop::form.control-group>

            <div class="grid grid-cols-2 gap-x-5 max-md:grid-cols-1">
                <!-- Division -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required">
                        @lang('bdgeo::app.address.division')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::name="bdField('bd_division_id')"
                        v-model="divisionId"
                        rules="required"
                        :label="trans('bdgeo::app.address.division')"
                        :placeholder="trans('bdgeo::app.address.division')"
                        @change="onDivisionChange"
                    >
                        <option value="">@lang('bdgeo::app.address.select-division')</option>

                        <option
                            v-for="d in bootstrap.divisions"
                            :key="d.id"
                            :value="d.id"
                        >@{{ d.name }}</option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="bdField('bd_division_id')" />
                </x-shop::form.control-group>

                <!-- District -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required">
                        @lang('bdgeo::app.address.district')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::name="bdField('bd_district_id')"
                        v-model="districtId"
                        rules="required"
                        :label="trans('bdgeo::app.address.district')"
                        :placeholder="trans('bdgeo::app.address.district')"
                        ::disabled="! divisionId"
                        @change="onDistrictChange"
                    >
                        <option value="">
                            @{{ divisionId ? districtPlaceholder : divisionFirstText }}
                        </option>

                        <option
                            v-for="d in districtsForDivision"
                            :key="d.id"
                            :value="d.id"
                        >@{{ d.name }}</option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="bdField('bd_district_id')" />
                </x-shop::form.control-group>

                <!-- Upazila / Thana -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required">
                        @lang('bdgeo::app.address.upazila')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        ::name="bdField('bd_upazila_id')"
                        v-model="upazilaId"
                        rules="required"
                        :label="trans('bdgeo::app.address.upazila')"
                        :placeholder="trans('bdgeo::app.address.upazila')"
                        ::disabled="! districtId || loadingUpazilas"
                    >
                        <option value="">@{{ upazilaPlaceholder }}</option>

                        <option
                            v-for="u in upazilas"
                            :key="u.id"
                            :value="u.id"
                        >@{{ u.name }}</option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error ::name="bdField('bd_upazila_id')" />
                </x-shop::form.control-group>

                <!-- Postcode -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        @lang('shop::app.checkout.onepage.address.postcode')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        ::name="bdField('postcode')"
                        v-model="postcode"
                        :label="trans('shop::app.checkout.onepage.address.postcode')"
                        :placeholder="trans('bdgeo::app.address.postcode-placeholder')"
                    />
                </x-shop::form.control-group>
            </div>

            <!-- State and City -->
            <input type="hidden" :name="bdField('state')" :value="stateCode" />
            <input type="hidden" :name="bdField('city')" :value="cityName" />
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
                    postcode: this.initial.postcode ?? '',
                    upazilas: [],
                    loadingUpazilas: false,
                    upazilaRequest: null,
                    districtPlaceholder: @json(trans('bdgeo::app.address.select-district')),
                    divisionFirstText: @json(trans('bdgeo::app.address.select-division-first')),
                    districtFirstText: @json(trans('bdgeo::app.address.select-district-first')),
                    loadingText: @json(trans('bdgeo::app.address.loading')),
                    selectUpazilaText: @json(trans('bdgeo::app.address.select-upazila')),
                };
            },

            computed: {
                districtsForDivision() {
                    if (! this.divisionId) return [];
                    return this.bootstrap.districts.filter(
                        d => String(d.division_id) === String(this.divisionId)
                    );
                },

                selectedUpazila() {
                    return this.upazilas.find(u => String(u.id) === String(this.upazilaId)) || null;
                },

                upazilaPlaceholder() {
                    if (! this.districtId) return this.districtFirstText;
                    if (this.loadingUpazilas) return this.loadingText;
                    return this.selectUpazilaText;
                },

                stateCode() {
                    const d = this.bootstrap.districts.find(
                        x => String(x.id) === String(this.districtId)
                    );
                    return d ? d.code : '';
                },

                cityName() {
                    return this.selectedUpazila ? this.selectedUpazila.name : '';
                },
            },

            mounted() {
                if (this.districtId) {
                    this.fetchUpazilas();
                }
            },

            methods: {
                bdField(name) {
                    return this.namePrefix ? `${this.namePrefix}${name}` : name;
                },

                onDivisionChange() {
                    this.districtId = '';
                    this.upazilaId = '';
                    this.upazilas = [];
                },

                onDistrictChange() {
                    this.upazilaId = '';
                    this.fetchUpazilas();
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
