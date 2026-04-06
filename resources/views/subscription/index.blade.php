@extends('layouts.authenticated')
@section('title', 'Premium — Smart Booking')
@section('page-class', 'main-content')

@section('content')
<div style="max-width:700px;margin:40px auto;padding:0 20px;">

    @if($status['is_premium'])
    <div style="background:linear-gradient(135deg,var(--deep),#4d2a3a);border-radius:14px;padding:32px;margin-bottom:28px;text-align:center;">
        <div style="font-size:40px;margin-bottom:12px;">⭐</div>
        <h2 style="color:var(--gold);margin:0 0 8px;font-size:24px;">You're a Premium Member</h2>
        <p style="color:rgba(255,255,255,.7);margin:0;">Active until <strong style="color:var(--gold);">{{ $status['premium_until'] }}</strong>
            ({{ $status['subscription']['days_left'] ?? 0 }} days left)</p>
        <button onclick="cancelSubscription()" style="margin-top:20px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);border:1px solid rgba(255,255,255,.2);padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px;">
            Cancel Subscription
        </button>
    </div>
    @else
    <div style="background:linear-gradient(135deg,var(--deep),#4d2a3a);border-radius:14px;padding:36px;margin-bottom:28px;text-align:center;">
        <div style="font-size:44px;margin-bottom:14px;">✈️</div>
        <h2 style="color:var(--gold);margin:0 0 8px;font-size:26px;">Smart Booking Premium</h2>
        <p style="color:rgba(255,255,255,.75);margin:0 0 20px;font-size:15px;">Unlock the full travel experience</p>
        <div style="font-size:42px;font-weight:700;color:#fff;margin-bottom:4px;">$9.99<span style="font-size:16px;font-weight:400;color:rgba(255,255,255,.6);">/month</span></div>
        <p style="color:rgba(255,255,255,.5);font-size:12px;margin:0 0 24px;">Cancel anytime</p>
        <button onclick="openSubscribeModal()" class="primary-button" style="font-size:16px;padding:14px 40px;background:var(--gold);color:var(--deep);">
            <i class="fas fa-crown"></i> Upgrade to Premium
        </button>
    </div>
    @endif

    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:28px;">
        <h3 style="margin:0 0 20px;color:var(--deep);font-size:17px;"><i class="fas fa-star" style="color:var(--gold);margin-right:8px;"></i>Premium Benefits</h3>
        <div style="display:grid;gap:14px;">
            @foreach($status['benefits'] as $benefit)
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:28px;height:28px;background:rgba(201,169,110,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-check" style="color:var(--gold);font-size:12px;"></i>
                </div>
                <span style="color:var(--deep);font-size:14px;">{{ $benefit }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:28px;margin-top:20px;">
        <h3 style="margin:0 0 16px;color:var(--deep);font-size:17px;"><i class="fas fa-tag" style="color:var(--gold);margin-right:8px;"></i>How We Make Money</h3>
        <div style="display:grid;gap:12px;">
            <div style="background:#f8f4f0;border-radius:8px;padding:14px 16px;">
                <div style="font-weight:600;color:var(--deep);margin-bottom:4px;">5% Service Fee</div>
                <div style="font-size:13px;color:var(--text-muted);">Applied to all bookings. Premium members pay zero service fees.</div>
            </div>
            <div style="background:#f8f4f0;border-radius:8px;padding:14px 16px;">
                <div style="font-weight:600;color:var(--deep);margin-bottom:4px;">10% Agency Commission</div>
                <div style="font-size:13px;color:var(--text-muted);">We earn a commission on bookings made through agency-listed services.</div>
            </div>
            <div style="background:#f8f4f0;border-radius:8px;padding:14px 16px;">
                <div style="font-weight:600;color:var(--deep);margin-bottom:4px;">$9.99/month Premium</div>
                <div style="font-size:13px;color:var(--text-muted);">Monthly subscription for premium features and fee-free bookings.</div>
            </div>
        </div>
    </div>
</div>

<div id="subscribeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:var(--card-bg);border-radius:12px;padding:32px;max-width:440px;width:100%;border:2px solid var(--gold);">
        <h3 style="margin:0 0 6px;color:var(--deep);">Upgrade to Premium</h3>
        <p style="color:var(--text-muted);font-size:13px;margin:0 0 24px;">$9.99/month — cancel anytime</p>

        <div style="background:#f8f4f0;border-radius:8px;padding:16px;margin-bottom:20px;">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:8px;">Demo Payment Reference</div>
            <input type="text" id="paymentRef" placeholder="e.g. PAY-DEMO-12345"
                   style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-size:14px;background:#fff;color:var(--deep);">
            <p style="font-size:11px;color:var(--text-muted);margin:8px 0 0;">In production, this integrates with Stripe/PayPal. Enter any reference to demo.</p>
        </div>

        <div style="display:flex;gap:10px;">
            <button onclick="submitSubscription()" class="primary-button" style="flex:1;">
                <i class="fas fa-crown"></i> Confirm Payment
            </button>
            <button onclick="document.getElementById('subscribeModal').style.display='none'" class="secondary-button" style="flex:1;">
                Cancel
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openSubscribeModal() {
    document.getElementById('subscribeModal').style.display = 'flex';
}

async function submitSubscription() {
    const ref = document.getElementById('paymentRef').value.trim();
    if (!ref) { Swal.fire('Required', 'Please enter a payment reference.', 'warning'); return; }

    try {
        const res = await fetch('/api/subscription/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ payment_reference: ref }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ title: 'Welcome to Premium! ⭐', text: data.message, icon: 'success', confirmButtonColor: '#c9a96e' })
                .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Something went wrong.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Network error. Please try again.', 'error');
    }
}

async function cancelSubscription() {
    const result = await Swal.fire({
        title: 'Cancel Premium?',
        text: 'You will lose all premium benefits immediately.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f44336',
        confirmButtonText: 'Yes, cancel',
    });
    if (!result.isConfirmed) return;

    const res = await fetch('/api/subscription/cancel', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    });
    const data = await res.json();
    if (data.success) location.reload();
}
</script>
@endpush
