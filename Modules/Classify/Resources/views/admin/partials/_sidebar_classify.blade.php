<div id="sidebarMain" class="d-none">
    <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                @php($store_logo = \App\Models\BusinessSetting::where(['key' => 'logo'])->first())
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="Front">
                    <img class="navbar-brand-logo initial--36 onerror-image"
                         data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                         src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
                         alt="Logo">
                    <img class="navbar-brand-logo-mini initial--36 onerror-image"
                         data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}"
                         src="{{\App\CentralLogics\Helpers::get_full_url('business', $store_logo?->value?? '', $store_logo?->storage[0]?->value ?? 'public','favicon')}}"
                         alt="Logo">
                </a>
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>
            </div>

            <div class="navbar-vertical-content bg--005555" id="navbar-vertical-content">
                <form autocomplete="off" class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input autocomplete="false" name="qq" type="text" class="form-control form--control"
                               placeholder="{{ translate('Search Menu...') }}" id="search">
                        <div id="search-suggestions" class="flex-wrap mt-1"></div>
                    </div>
                </form>

                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin') || Request::is('admin/classify') ? 'show active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                           href="{{ route('admin.classify.dashboard') }}?module_id={{Config::get('module.current_module_id')}}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ translate('messages.dashboard') }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <small class="nav-subtitle">{{ translate('Classify') ?: 'Classify' }}</small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/classify/listings*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.classify.listings.index') }}">
                            <i class="tio-poi-outlined nav-icon"></i>
                            <span class="text-truncate">{{ translate('Listings') ?: 'Listings' }}</span>
                        </a>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/category*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                            <i class="tio-category-outlined nav-icon"></i>
                            <span class="text-truncate">{{ translate('Categories') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display:{{ Request::is('admin/category*') ? 'block' : 'none' }}">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.category.add',['position'=>0]) }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Category') }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.category.add',['position'=>1]) }}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{ translate('Sub Category') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/store*') || Request::is('admin/vendor*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.store.list') }}">
                            <i class="tio-poi nav-icon"></i>
                            <span class="text-truncate">{{ translate('Stores') ?: 'Sellers' }}</span>
                        </a>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/classify/reports*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.classify.reports.index') }}">
                            <i class="tio-report nav-icon"></i>
                            <span class="text-truncate">{{ translate('Reports') ?: 'Reports' }}</span>
                        </a>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/classify/settings*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{ route('admin.classify.settings') }}">
                            <i class="tio-settings nav-icon"></i>
                            <span class="text-truncate">{{ translate('Classify Settings') ?: 'Classify Settings' }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
</div>
<div id="sidebarCompact" class="d-none"></div>
