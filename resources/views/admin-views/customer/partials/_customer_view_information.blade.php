@if($customer)
    <div class="card-body">
        <div class="media gap-3 flex-wrap">
            <div class="avatar avatar-circle avatar-70">
                <img class="avatar-img onerror-image" width="70" height="70" data-onerror-image="{{asset('public/assets/admin/img/160x160/img1.jpg')}}" src="{{ $customer->image_full_url }}"
                alt="Image Description">
            </div>
            <div class="media-body">
                <div class="key-value-list d-flex flex-column gap-2 text-dark" style="--min-width: 60px">
                    <div class="key-val-list-item d-flex gap-3">
                        <div>{{ translate('name') }}</div>:
                        <div class="font-semibold">{{$customer['f_name']? $customer['f_name'].' '.$customer['l_name'] : translate('messages.Incomplete_Profile')}}</div>
                    </div>
                    <div class="key-val-list-item d-flex gap-3">
                        <div>{{ translate('contact') }}</div>:
                        <a href="tel:{{ $customer['phone'] }}" class="text-dark font-semibold">{{$customer['phone'] ?? translate('messages.N/A')}}</a>
                    </div>
                    <div class="key-val-list-item d-flex gap-3">
                        <div>{{ translate('email') }}</div>:
                        <a href="mailto:{{ $customer['email'] }}" class="text-dark font-semibold">{{$customer['email'] ?? translate('messages.N/A')}}</a>
                    </div>
                    <div class="key-val-list-item d-flex gap-3">
                        <div>{{ translate('messages.date_of_birth') }}</div>:
                        <div class="font-semibold">{{ $customer->date_of_birth ? \Carbon\Carbon::parse($customer->date_of_birth)->format('d M Y') : translate('messages.N/A') }}</div>
                    </div>
                    <div class="key-val-list-item d-flex gap-3 align-items-center flex-wrap">
                        <div>{{ translate('messages.age_verified') }}</div>:
                        @if((int) $customer->is_age_verified === 1)
                            <span class="badge badge-soft-success">{{ translate('messages.age_verified') }}</span>
                            @if($customer->age_verified_by)
                                <span class="text-muted fs-12">({{ $customer->age_verified_by }})</span>
                            @endif
                            <a href="{{ route('admin.users.customer.age-verify', [$customer->id, 0]) }}"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('{{ translate('messages.revoke_age_verification') }}?')">
                                {{ translate('messages.revoke_age_verification') }}
                            </a>
                        @else
                            <span class="badge badge-soft-danger">{{ translate('messages.not_age_verified') }}</span>
                            <a href="{{ route('admin.users.customer.age-verify', [$customer->id, 1]) }}"
                               class="btn btn-sm btn-outline-success"
                               onclick="return confirm('{{ translate('messages.mark_as_18_plus') }}?')">
                                {{ translate('messages.mark_as_18_plus') }}
                            </a>
                        @endif
                    </div>
                    @if($customer->age_verification_document_type)
                        <div class="key-val-list-item d-flex gap-3">
                            <div>{{ translate('messages.age_verification_document') }}</div>:
                            <div class="font-semibold">{{ translate('messages.'.$customer->age_verification_document_type) }}</div>
                        </div>
                    @endif
                    @if($customer->age_verification_document_full_url)
                        <div class="key-val-list-item d-flex gap-3 align-items-start">
                            <div>{{ translate('messages.age_verification_document') }}</div>:
                            <a href="{{ $customer->age_verification_document_full_url }}" target="_blank">
                                <img src="{{ $customer->age_verification_document_full_url }}" alt="document" width="80" height="80" class="rounded border" style="object-fit:cover">
                            </a>
                        </div>
                    @endif
                    @foreach($customer->addresses as $address)
                        <div class="key-val-list-item d-flex gap-3">
                            <div>{{ translate('address') }}</div>:
                            <a href="https://www.google.com/maps/search/?api=1&query={{ data_get($address,'latitude',0)}},{{ data_get($address,'longitude',0)}}" target="_blank">{{ $address['address'] }}</a>
                        </div>
                    @endforeach
                </div>

                {{-- <ul class="list-unstyled m-0">
                    <li class="pb-1 d-flex align-items-center">
                        <i class="tio-shopping-basket-outlined mr-2"></i>
                        <span>{{$customer->order_count}} {{translate('messages.Completed_orders')}}</span>
                    </li>
                </ul> --}}
            </div>
        </div>


        {{-- @foreach($customer->addresses as $address)
            <div class="d-flex justify-content-between align-items-center">
                <h5>{{translate('messages.addresses')}}</h5>
            </div>
            <ul class="list-unstyled list-unstyled-py-2">
                <li class="d-flex align-items-center">
                    <i class="tio-tab mr-2"></i>
                    <span>{{translate($address['address_type'])}}</span>
                </li>
                @if($address['contact_person_umber'])
                <li class="d-flex align-items-center">
                    <i class="tio-android-phone-vs mr-2"></i>
                    <span>{{$address['contact_person_number']}}</span>
                </li>
                @endif
                <li>
                    <a target="_blank" href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}" class="d-flex align-items-center">
                        <i class="tio-poi mr-2"></i>
                        {{$address['address']}}
                    </a>
                </li>
            </ul>
            <hr>
        @endforeach --}}

    </div>
@endif