@extends('layouts.public')

@section('title', 'Active Members — Smart Booking')

@push('styles')
<script>
window.__dashboardConfig = window.__dashboardConfig || {};
@auth
window.__dashboardConfig.userId    = {{ Auth::id() }};
window.__dashboardConfig.user      = { id: {{ Auth::id() }}, name: @json(Auth::user()->name), avatar: @json(Auth::user()->avatar ?? '') };
@endauth
</script>
@endpush

@section('content')
<section class="page-hero" style="background: linear-gradient(160deg, rgba(20,10,30,0.72) 0%, rgba(59,31,43,0.55) 100%), url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=1920&q=90'); background-size: cover; background-position: center; min-height: 450px; display: flex; align-items: center;">
    <div>
        <h1 style="margin-bottom: 16px;"><i class="fas fa-user-friends"></i> Active Members</h1>
        <p style="font-size: 15px; max-width: 600px; margin: 0 auto;">Connect with travelers from around the world</p>
    </div>
</section>

<div class="members-container">
    <div class="members-layout">
        <!-- Sidebar Filters -->
        <div class="members-sidebar">
            <div class="filter-card">
                <h3><i class="fas fa-filter"></i> Filters</h3>
                
                <div class="filter-group">
                    <label>Search Members</label>
                    <input type="text" id="searchMembers" placeholder="Search by name..." class="filter-input">
                </div>

                <div class="filter-group">
                    <label>Sort By</label>
                    <select id="sortBy" class="filter-select" onchange="Members.sortBy(this.value)">
                        <option value="newest">Newest Members</option>
                        <option value="active">Most Active</option>
                        <option value="popular">Most Popular</option>
                        <option value="name">Name (A-Z)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Member Type</label>
                    <div class="filter-checkboxes">
                        <label class="checkbox-label">
                            <input type="checkbox" value="all" checked onchange="Members.filterType(this)"> All Members
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" value="travelers" onchange="Members.filterType(this)"> Travelers
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" value="agencies" onchange="Members.filterType(this)"> Agencies
                        </label>
                    </div>
                </div>

                <button class="primary-button" style="width:100%;margin-top:16px;" onclick="Members.resetFilters()">
                    <i class="fas fa-redo"></i> Reset Filters
                </button>
            </div>

            <div class="stats-card">
                <h3><i class="fas fa-chart-bar"></i> Community Stats</h3>
                <div class="stat-item">
                    <span>Total Members</span>
                    <strong id="totalMembers">-</strong>
                </div>
                <div class="stat-item">
                    <span>Online Now</span>
                    <strong id="onlineMembers">-</strong>
                </div>
                <div class="stat-item">
                    <span>New This Week</span>
                    <strong id="newMembers">-</strong>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="members-main">
            <div class="members-header">
                <div>
                    <h2>All Members</h2>
                    <p id="memberCount">Loading members...</p>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" onclick="Members.changeView('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn" data-view="list" onclick="Members.changeView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <div id="membersGrid" class="members-grid">
                <!-- Loading skeleton -->
                @for ($i = 0; $i < 12; $i++)
                <div class="member-card skeleton-card">
                    <div class="sk-circle skeleton" style="width:80px;height:80px;margin:0 auto 12px;"></div>
                    <div class="sk-line skeleton" style="width:120px;height:18px;margin:0 auto 8px;"></div>
                    <div class="sk-line skeleton" style="width:80px;height:14px;margin:0 auto;"></div>
                </div>
                @endfor
            </div>

            <div id="loadMoreContainer" style="text-align:center;margin-top:30px;display:none;">
                <button class="secondary-button" onclick="Members.loadMore()">
                    <i class="fas fa-chevron-down"></i> Load More Members
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Member Profile Quick View Modal -->
<div class="modal-overlay" id="quickViewModal">
    <div class="modal quick-view-modal">
        <button class="modal-close" onclick="Members.closeQuickView()">&#x2715;</button>
        <div id="quickViewContent">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMsg"></span>
</div>
@endsection
