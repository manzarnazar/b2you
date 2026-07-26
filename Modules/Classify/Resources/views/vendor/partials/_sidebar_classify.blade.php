<div id="sidebarMain" class="d-none">
    <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand" href="{{ route('vendor.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo initial--36"
                         src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
                         alt="Logo">
                </a>
            </div>
            <div class="navbar-vertical-content bg--005555" id="navbar-vertical-content">
                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('vendor-panel') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.dashboard') }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="text-truncate">{{ translate('messages.dashboard') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <small class="nav-subtitle">{{ translate('Classify') ?: 'Classify' }}</small>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('vendor-panel/classify*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.classify.listings.index') }}">
                            <i class="tio-premium-outlined nav-icon"></i>
                            <span class="text-truncate">{{ translate('My Listings') ?: 'My Listings' }}</span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('vendor-panel/classify/listings/create') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.classify.listings.create') }}">
                            <i class="tio-add nav-icon"></i>
                            <span class="text-truncate">{{ translate('Add Listing') ?: 'Add Listing' }}</span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('vendor-panel/message*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.message.list') }}">
                            <i class="tio-chat nav-icon"></i>
                            <span class="text-truncate">{{ translate('messages.chat') }}</span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('vendor-panel/profile*') || Request::is('vendor-panel/store*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('vendor.shop.view') }}">
                            <i class="tio-shop nav-icon"></i>
                            <span class="text-truncate">{{ translate('messages.my_shop') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
</div>
<div id="sidebarCompact" class="d-none"></div>
