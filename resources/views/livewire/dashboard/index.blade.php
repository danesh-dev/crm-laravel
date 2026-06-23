<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="card">
        <h3>Total Customers</h3>
        <p>{{ $stats['customers'] }}</p>
    </div>

    <div class="card">
        <h3>Active Subscribers</h3>
        <p>{{ $stats['active_subscribers'] }}</p>
    </div>

    <div class="card">
        <h3>Automations</h3>
        <p>{{ $stats['automations'] }}</p>
    </div>

    <div class="card">
        <h3>Emails Sent Today</h3>
        <p>{{ $stats['emails_sent_today'] }}</p>
    </div>

    <div class="card">
        <h3>Failed Today</h3>
        <p>{{ $stats['emails_failed_today'] }}</p>
    </div>

</div>