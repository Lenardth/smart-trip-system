@extends('layouts.authenticated')

@section('title', 'Book Accommodation — Smart Booking')
@section('page-class', 'main-content')

@section('content')
<div style="max-width:600px;margin:40px auto;padding:0 20px;">

    <div style="margin-bottom:24px;">
        <a href="/accommodations" style="color:var(--gold);text-decoration:none;font-size:14px;">
            <i class="fas fa-arrow-left"></i> Back to Accommodations
        </a>
    </div>

    @if($accommodation)
    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:12px;overflow:hidden;">

        @if($accommodation->image_url)
        <div style="height:200px;background-image:url('{{ $accommodation->image_url }}');background-size:cover;background-position:center;"></div>
        @endif

        <div style="padding:28px;">
            <h2 style="margin:0 0 4px;color:var(--deep);font-size:22px;">{{ $accommodation->name }}</h2>
            <p style="color:var(--text-muted);margin:0 0 20px;font-size:14px;">
                <i class="fas fa-map-marker-alt"></i>
                {{ $accommodation->city }}{{ $accommodation->country ? ', ' . $accommodation->country : '' }}
            </p>

            @if($accommodation->nightly_rate)
            <div style="background:rgba(201,169,110,0.1);border:1px solid var(--gold);border-radius:8px;padding:14px 18px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;">
                <span style="color:var(--text-muted);font-size:13px;">Nightly rate</span>
                <span style="font-size:20px;font-weight:700;color:var(--deep);">${{ number_format($accommodation->nightly_rate) }}<span style="font-size:13px;font-weight:normal;color:var(--text-muted);">/night</span></span>
            </div>
            @endif

            <form id="bookingForm">
                @csrf
                <input type="hidden" name="accommodation_id" value="{{ $accommodation->id }}">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:13px;color:var(--text-muted);margin-bottom:6px;">Check-in</label>
                        <input type="date" name="check_in" id="checkIn" required
                               min="{{ date('Y-m-d') }}"
                               style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;background:var(--card-bg);color:var(--deep);font-size:14px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;color:var(--text-muted);margin-bottom:6px;">Check-out</label>
                        <input type="date" name="check_out" id="checkOut" required
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;background:var(--card-bg);color:var(--deep);font-size:14px;">
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:13px;color:var(--text-muted);margin-bottom:6px;">Guests</label>
                    <select name="guests" id="guests"
                            style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;background:var(--card-bg);color:var(--deep);font-size:14px;">
                        @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }} guest{{ $i > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>

                <div id="priceSummary" style="display:none;background:#f8f4f0;border-radius:8px;padding:16px;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;">
                        <span style="color:var(--text-muted);" id="nightsLabel">— nights</span>
                        <span id="subtotalLabel" style="color:var(--deep);font-weight:600;"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:1px solid var(--border);padding-top:10px;margin-top:4px;">
                        <span style="color:var(--deep);">Total</span>
                        <span id="totalLabel" style="color:var(--gold);"></span>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="primary-button" style="width:100%;padding:14px;font-size:15px;">
                    <i class="fas fa-calendar-check"></i> Confirm Booking
                </button>
            </form>
        </div>
    </div>

    @else
    <div style="text-align:center;padding:60px 20px;">
        <i class="fas fa-exclamation-triangle" style="font-size:40px;color:var(--gold);opacity:0.6;"></i>
        <h3 style="margin:16px 0 8px;color:var(--deep);">Accommodation not found</h3>
        <a href="/accommodations" class="primary-button" style="text-decoration:none;display:inline-flex;margin-top:16px;">Browse Accommodations</a>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const rate    = {{ $accommodation?->nightly_rate ?? 0 }};
    const checkIn = document.getElementById('checkIn');
    const checkOut= document.getElementById('checkOut');
    const guests  = document.getElementById('guests');
    const summary = document.getElementById('priceSummary');
    const form    = document.getElementById('bookingForm');

    function updatePrice() {
        if (!checkIn.value || !checkOut.value) { summary.style.display = 'none'; return; }
        const d1 = new Date(checkIn.value), d2 = new Date(checkOut.value);
        const nights = Math.round((d2 - d1) / 86400000);
        if (nights <= 0) { summary.style.display = 'none'; return; }
        const g = parseInt(guests.value) || 1;
        const total = rate * nights * g;
        document.getElementById('nightsLabel').textContent  = nights + ' night' + (nights > 1 ? 's' : '') + ' × ' + g + ' guest' + (g > 1 ? 's' : '');
        document.getElementById('subtotalLabel').textContent = '$' + (rate * nights * g).toLocaleString();
        document.getElementById('totalLabel').textContent    = '$' + total.toLocaleString();
        summary.style.display = 'block';
        checkOut.min = new Date(d1.getTime() + 86400000).toISOString().split('T')[0];
    }

    checkIn.addEventListener('change', updatePrice);
    checkOut.addEventListener('change', updatePrice);
    guests.addEventListener('change', updatePrice);

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking…';

            const body = {
                accommodation_id: form.querySelector('[name=accommodation_id]').value,
                check_in:  checkIn.value,
                check_out: checkOut.value,
                guests:    guests.value,
            };

            try {
                const res  = await fetch('/api/bookings/accommodation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        title: 'Booking Confirmed!',
                        html: '<p>Reference: <strong>' + data.booking_reference + '</strong></p><p>Total: <strong>$' + Number(data.total_price).toLocaleString() + '</strong></p>',
                        icon: 'success',
                        confirmButtonColor: '#c9a96e',
                        confirmButtonText: 'View My Bookings',
                        showCancelButton: true,
                        cancelButtonText: 'Stay Here',
                    }).then(r => { if (r.isConfirmed) window.location.href = '/bookings'; });
                } else {
                    throw new Error(data.message || 'Booking failed');
                }
            } catch (err) {
                Swal.fire({ title: 'Error', text: err.message, icon: 'error', confirmButtonColor: '#c9a96e' });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirm Booking';
            }
        });
    }
})();
</script>
@endpush
