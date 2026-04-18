@extends('layouts.public')

@section('title', $destination->name . ' - Travel Guide')

@section('content')

<script>
window.__destinationData = {
    id: {{ $destination->id }},
    name: @json($destination->name),
    country: @json($destination->country)
};
</script>

{{-- Hero Section --}}
<section class="info-hero" style="background: linear-gradient(160deg, rgba(10,20,30,0.7) 0%, rgba(59,31,43,0.6) 100%), url('{{ $destination->image_url ?: 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1800&q=80' }}') center/cover no-repeat; min-height:400px; display:flex; align-items:center; justify-content:center; color:#fff; text-align:center; padding:60px 20px;">
    <div style="max-width:800px;">
        <a href="{{ route('discover') }}" style="display:inline-flex;align-items:center;gap:8px;color:#fff;text-decoration:none;margin-bottom:20px;opacity:0.9;font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Discover
        </a>
        <h1 style="font-size:48px;margin:0 0 16px;font-weight:normal;letter-spacing:1px;">{{ $destination->name }}</h1>
        <p style="font-size:20px;opacity:0.95;margin:0;">
            <i class="fas fa-map-marker-alt"></i> {{ $destination->country }}
        </p>
        @if($destination->region)
        <p style="font-size:16px;opacity:0.8;margin-top:8px;">
            {{ ucwords(str_replace('_', ' ', $destination->region)) }}
        </p>
        @endif
    </div>
</section>

<div style="max-width:1200px;margin:40px auto;padding:0 20px;">
    
    {{-- About & Fun Facts --}}
    <div style="background:#fff;border-radius:8px;padding:40px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:1px solid var(--border);">
        <h2 style="font-size:28px;margin:0 0 20px;color:var(--deep);display:flex;align-items:center;gap:12px;">
            <i class="fas fa-info-circle" style="color:var(--gold);"></i>
            About {{ $destination->name }}
        </h2>
        <p style="font-size:16px;line-height:1.8;color:var(--text);margin-bottom:24px;">
            {{ $destination->description ?: 'Discover the beauty and charm of this amazing destination.' }}
        </p>
        
        @if(isset($enrichedData['fun_facts']['summary']) && $enrichedData['fun_facts']['summary'])
        <div style="background:#fef9f3;border-left:4px solid var(--gold);padding:20px;border-radius:4px;margin-top:24px;">
            <h3 style="font-size:18px;margin:0 0 12px;color:var(--deep);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-lightbulb"></i> Did You Know?
            </h3>
            <p style="font-size:15px;line-height:1.7;color:var(--text);margin:0;">
                {{ $enrichedData['fun_facts']['summary'] }}
            </p>
        </div>
        @endif
    </div>

    {{-- Things to Do --}}
    @if(isset($enrichedData['activities']) && count($enrichedData['activities']) > 0)
    <div style="background:#fff;border-radius:8px;padding:40px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:1px solid var(--border);">
        <h2 style="font-size:28px;margin:0 0 24px;color:var(--deep);display:flex;align-items:center;gap:12px;">
            <i class="fas fa-map-marked-alt" style="color:var(--gold);"></i>
            Things to Do
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;">
            @foreach($enrichedData['activities'] as $activity)
            <div style="padding:24px;border:1px solid var(--border);border-radius:8px;background:#fafafa;transition:all 0.3s;" class="activity-card">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:48px;height:48px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;">
                        <i class="fas {{ $activity['icon'] }}"></i>
                    </div>
                    <h3 style="font-size:17px;margin:0;color:var(--deep);">{{ $activity['name'] }}</h3>
                </div>
                <p style="font-size:14px;color:var(--text-muted);margin:0;line-height:1.6;">{{ $activity['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Top Attractions from Geoapify --}}
    @if(isset($enrichedData['attractions']) && count($enrichedData['attractions']) > 0)
    <div style="background:#fff;border-radius:8px;padding:40px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:1px solid var(--border);">
        <h2 style="font-size:28px;margin:0 0 12px;color:var(--deep);display:flex;align-items:center;gap:12px;">
            <i class="fas fa-star" style="color:var(--gold);"></i>
            Top Attractions
        </h2>
        <p style="font-size:14px;color:var(--text-muted);margin:0 0 24px;">Popular places and points of interest in {{ $destination->name }}</p>
        <div style="display:grid;gap:16px;">
            @foreach($enrichedData['attractions'] as $attraction)
            <div style="display:flex;gap:16px;padding:20px;border:1px solid var(--border);border-radius:8px;background:#fafafa;transition:all 0.3s;" class="attraction-card">
                <div style="width:60px;height:60px;flex-shrink:0;border-radius:8px;background:linear-gradient(135deg,var(--gold),#d4a574);display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;">
                    <i class="fas {{ $attraction['icon'] }}"></i>
                </div>
                <div style="flex:1;">
                    <h3 style="font-size:18px;margin:0 0 6px;color:var(--deep);">{{ $attraction['name'] }}</h3>
                    <div style="display:flex;gap:12px;font-size:13px;color:var(--text-muted);margin-bottom:4px;">
                        <span><i class="fas fa-tag"></i> {{ $attraction['category'] }}</span>
                        @if($attraction['address'])
                        <span><i class="fas fa-map-marker-alt"></i> {{ $attraction['address'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Local Food & Culture --}}
    @if(isset($enrichedData['food']) || isset($enrichedData['culture']))
    <div style="background:#fff;border-radius:8px;padding:40px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:1px solid var(--border);">
        <h2 style="font-size:28px;margin:0 0 24px;color:var(--deep);display:flex;align-items:center;gap:12px;">
            <i class="fas fa-utensils" style="color:var(--gold);"></i>
            Local Food & Culture
        </h2>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;">
            @if(isset($enrichedData['food']['popular_dishes']) && count($enrichedData['food']['popular_dishes']) > 0)
            <div>
                <h3 style="font-size:20px;margin:0 0 16px;color:var(--deep);display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-drumstick-bite" style="color:var(--gold);"></i>
                    Must-Try Dishes
                </h3>
                <ul style="list-style:none;padding:0;margin:0;">
                    @foreach($enrichedData['food']['popular_dishes'] as $dish)
                    <li style="padding:12px 0;border-bottom:1px solid var(--border-light);color:var(--text);display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-check-circle" style="color:var(--gold);"></i>
                        <span>{{ $dish }}</span>
                    </li>
                    @endforeach
                </ul>
                @if(isset($enrichedData['food']['dining_tips']))
                <p style="margin-top:16px;font-size:14px;color:var(--text-muted);font-style:italic;padding:12px;background:#fef9f3;border-radius:4px;">
                    <i class="fas fa-info-circle"></i> {{ $enrichedData['food']['dining_tips'] }}
                </p>
                @endif
            </div>
            @endif

            @if(isset($enrichedData['culture']['tips']) && count($enrichedData['culture']['tips']) > 0)
            <div>
                <h3 style="font-size:20px;margin:0 0 16px;color:var(--deep);display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-users" style="color:var(--gold);"></i>
                    Cultural Tips
                </h3>
                <ul style="list-style:none;padding:0;margin:0;">
                    @foreach($enrichedData['culture']['tips'] as $tip)
                    <li style="padding:12px 0;border-bottom:1px solid var(--border-light);color:var(--text);display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-lightbulb" style="color:var(--gold);"></i>
                        <span>{{ $tip }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Latest News & Headlines --}}
    <div style="background:#fff;border-radius:8px;padding:40px;margin-bottom:30px;box-shadow:0 2px 8px rgba(0,0,0,0.08);border:1px solid var(--border);">
        <h2 style="font-size:28px;margin:0 0 12px;color:var(--deep);display:flex;align-items:center;gap:12px;">
            <i class="fas fa-newspaper" style="color:var(--gold);"></i>
            Latest News & Headlines
        </h2>
        <p style="font-size:14px;color:var(--text-muted);margin:0 0 24px;">Current affairs and travel updates about {{ $destination->name }}</p>
        
        <div id="newsContent">
            <div style="text-align:center;padding:40px;color:var(--text-muted);">
                <i class="fas fa-spinner fa-spin" style="font-size:32px;color:var(--gold);"></i>
                <p style="margin-top:16px;font-size:15px;">Loading latest news...</p>
            </div>
        </div>
    </div>

    {{-- Call to Action --}}
    <div style="background:linear-gradient(135deg, var(--deep), var(--deep-alt));border-radius:8px;padding:50px 40px;text-align:center;color:#fff;box-shadow:0 4px 16px rgba(59,31,43,0.2);">
        <h2 style="font-size:32px;margin:0 0 16px;font-weight:normal;">Ready to Visit {{ $destination->name }}?</h2>
        <p style="font-size:16px;opacity:0.9;margin:0 0 30px;">Start planning your perfect trip today</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('plan-trip') }}?destination={{ urlencode($destination->name) }}" 
               style="background:var(--gold);color:var(--deep);padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:16px;display:inline-flex;align-items:center;gap:10px;transition:all 0.3s;">
                <i class="fas fa-route"></i> Plan Your Trip
            </a>
            <a href="{{ route('destinations.show', $destination->id) }}" 
               style="background:rgba(255,255,255,0.15);color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:16px;display:inline-flex;align-items:center;gap:10px;border:2px solid rgba(255,255,255,0.3);transition:all 0.3s;">
                <i class="fas fa-calculator"></i> View Pricing
            </a>
        </div>
    </div>

</div>

<style>
.activity-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(59,31,43,0.12);
    border-color: var(--gold);
}

.attraction-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 16px rgba(59,31,43,0.1);
    border-color: var(--gold);
}
</style>

@endsection

@push('scripts')
@vite('resources/js/blade/destination-info/show.js')
@endpush
