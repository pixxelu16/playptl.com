@extends('layouts.admin')

@section('title', 'Admin Dashboard | '.config('app.name', 'playptl'))
@section('meta_description', 'Admin dashboard for managing users, organisers, players, and platform settings.')

@section('content')
    <section class="admin-card" style="margin-bottom: 24px;">
        <div class="admin-dashboard-hero">
            <div>
                <h1 class="admin-card-title">
                    @if(auth()->user()->hasRole('Organiser') && !auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Admin'))
                        Organiser Dashboard
                    @else
                        Admin Dashboard
                    @endif
                </h1>
                <p class="admin-card-text">Welcome, {{ auth()->user()->name }}. Use the stats, charts, and activity listings below for full oversight.</p>
            </div>
        </div>
    </section>

    {{-- Tournament Overview (Visible if user has tournament or player management permissions) --}}
    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->can('manage leagues') || auth()->user()->can('manage players'))
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #ecfdf5; color: #059669; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Tournaments</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $leaguesCount }}</strong>
            </div>
        </div>
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #e0f2fe; color: #0284c7; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Players</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $playersCount }}</strong>
            </div>
        </div>
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #faf5ff; color: #9333ea; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-table-cells-large"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Tournament Groups</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $groupCardsCount }}</strong>
            </div>
        </div>
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #fff7ed; color: #ea580c; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-users-line"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Subgroups</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $groupsCount }}</strong>
            </div>
        </div>
    </div>
    @endif

    {{-- User Statistics Grid (Visible if user has user management permissions) --}}
    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->can('manage users'))
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #e0f2fe; color: #0284c7; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Students</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $studentsCount }}</strong>
            </div>
        </div>
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #eef2ff; color: #4f46e5; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Coaches</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $coachesCount }}</strong>
            </div>
        </div>
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #faf5ff; color: #9333ea; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-handshake-angle"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Mentors</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $mentorsCount }}</strong>
            </div>
        </div>
        <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; display:flex; align-items:center; gap: 12px;">
            <div style="background: #fdf2f8; color: #db2777; height: 48px; width: 48px; border-radius: 50%; display:flex; align-items:center; justify-content:center; font-size:20px;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Users</span>
                <strong style="display:block; font-size:22px; color:#1f2937; font-weight:800; line-height:1.2;">{{ $totalUsers }}</strong>
            </div>
        </div>
    </div>
    @endif

    {{-- Financial Statistics Grid (Visible if user has payment / finance management permissions) --}}
    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->can('manage payment history') || auth()->user()->can('manage payments'))
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #059669; color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <span style="font-size:11px; text-transform:uppercase; font-weight:700; opacity:0.8; letter-spacing:0.05em;">Total Platform Revenue</span>
            <strong style="display:block; font-size:28px; font-weight:900; margin-top:4px;">${{ number_format($totalPlatformRevenue, 2) }}</strong>
            <p style="font-size:11px; opacity:0.7; margin-top:6px; font-weight:500;">Includes bookings, tournaments & charity</p>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Tournament Entry Revenue</span>
            <strong style="display:block; font-size:24px; font-weight:800; color:#1f2937; margin-top:4px;">${{ number_format($tournamentRevenue, 2) }}</strong>
            <p style="font-size:11px; color:#9ca3af; margin-top:6px; font-weight:500;">From player registrations</p>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Admin Payout to Mentor/Coach</span>
            <strong style="display:block; font-size:24px; font-weight:800; color:#1f2937; margin-top:4px;">${{ number_format($totalPayoutPaid, 2) }}</strong>
            <p style="font-size:11px; color:#9ca3af; margin-top:6px; font-weight:500;">Paid payouts to providers</p>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Total Platform Commissions</span>
            <strong style="display:block; font-size:24px; font-weight:800; color:#1f2937; margin-top:4px;">${{ number_format($totalCommission, 2) }}</strong>
            <p style="font-size:11px; color:#9ca3af; margin-top:6px; font-weight:500;">Deducted session commissions</p>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <span style="font-size:11px; text-transform:uppercase; font-weight:700; color:#9ca3af; letter-spacing:0.05em;">Charity Donation Revenue</span>
            <strong style="display:block; font-size:24px; font-weight:800; color:#059669; margin-top:4px;">${{ number_format($charityRevenue, 2) }}</strong>
            <p style="font-size:11px; color:#9ca3af; margin-top:6px; font-weight:500;">From charity donations</p>
        </div>
    </div>

    {{-- Charts Section --}}
    <section class="admin-card" style="margin-bottom: 24px; padding: 24px;">
        <div style="display:flex; justify-content:between; align-items:center; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; margin-bottom: 20px; flex-wrap:wrap; gap:10px;">
            <h2 style="font-size:18px; font-weight:700; color:#374151;">Financial Analytics & Trends</h2>
            <div style="display:flex; gap: 8px;" id="chart-tabs">
                <button onclick="switchChart('daily')" id="btn-daily" style="background:#5DA44E; color:white; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.15s;">Daily</button>
                <button onclick="switchChart('weekly')" id="btn-weekly" style="background:#f3f4f6; color:#4b5563; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.15s;">Weekly</button>
                <button onclick="switchChart('monthly')" id="btn-monthly" style="background:#f3f4f6; color:#4b5563; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.15s;">Monthly</button>
                <button onclick="switchChart('yearly')" id="btn-yearly" style="background:#f3f4f6; color:#4b5563; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; transition:all 0.15s;">Yearly</button>
            </div>
        </div>
        <div style="position:relative; height: 320px; width:100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </section>
    @endif

    {{-- Recent Users List (Visible if user has user management permissions) --}}
    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->can('manage users'))
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 24px;">
        {{-- Recent Students --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 18px; display:flex; flex-direction: column; gap: 12px;">
            <h3 style="font-size:14px; font-weight:700; color:#374151; border-bottom:1px solid #f3f4f6; padding-bottom:8px; display:flex; align-items:center; gap:6px; margin: 0;">
                <i class="fa-solid fa-graduation-cap text-[#5DA44E]"></i>
                <span>Recent Joined Students</span>
            </h3>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:8px;">
                @forelse($recentStudents as $s)
                    <div style="display:flex; align-items:center; justify-content:space-between; font-size:13px;">
                        <div>
                            <span style="font-weight:600; color:#374151; display:block;">{{ $s->name }}</span>
                            <span style="font-size:11px; color:#9ca3af;">{{ $s->email }}</span>
                        </div>
                        <span style="font-size:11px; color:#6b7280; font-weight:500;">{{ $s->created_at->format('M d') }}</span>
                    </div>
                @empty
                    <p style="font-size:13px; color:#9ca3af; font-style: italic; text-align:center; margin: 0;">No students found.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Coaches --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 18px; display:flex; flex-direction: column; gap: 12px;">
            <h3 style="font-size:14px; font-weight:700; color:#374151; border-bottom:1px solid #f3f4f6; padding-bottom:8px; display:flex; align-items:center; gap:6px; margin: 0;">
                <i class="fa-solid fa-chalkboard-user text-[#4f46e5]"></i>
                <span>Recent Joined Coaches</span>
            </h3>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:8px;">
                @forelse($recentCoaches as $c)
                    <div style="display:flex; align-items:center; justify-content:space-between; font-size:13px;">
                        <div>
                            <span style="font-weight:600; color:#374151; display:block;">{{ $c->name }}</span>
                            <span style="font-size:11px; color:#9ca3af;">{{ $c->email }}</span>
                        </div>
                        <span style="font-size:11px; color:#6b7280; font-weight:500;">{{ $c->created_at->format('M d') }}</span>
                    </div>
                @empty
                    <p style="font-size:13px; color:#9ca3af; font-style: italic; text-align:center; margin: 0;">No coaches found.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Mentors --}}
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 18px; display:flex; flex-direction: column; gap: 12px;">
            <h3 style="font-size:14px; font-weight:700; color:#374151; border-bottom:1px solid #f3f4f6; padding-bottom:8px; display:flex; align-items:center; gap:6px; margin: 0;">
                <i class="fa-solid fa-handshake-angle text-[#9333ea]"></i>
                <span>Recent Joined Mentors</span>
            </h3>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:8px;">
                @forelse($recentMentors as $m)
                    <div style="display:flex; align-items:center; justify-content:space-between; font-size:13px;">
                        <div>
                            <span style="font-weight:600; color:#374151; display:block;">{{ $m->name }}</span>
                            <span style="font-size:11px; color:#9ca3af;">{{ $m->email }}</span>
                        </div>
                        <span style="font-size:11px; color:#6b7280; font-weight:500;">{{ $m->created_at->format('M d') }}</span>
                    </div>
                @empty
                    <p style="font-size:13px; color:#9ca3af; font-style: italic; text-align:center; margin: 0;">No mentors found.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin') || auth()->user()->can('manage payment history') || auth()->user()->can('manage payments'))
    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = {
            daily: {
                labels: @json($dailyLabels),
                values: @json($dailyValues)
            },
            weekly: {
                labels: @json($weeklyLabels),
                values: @json($weeklyValues)
            },
            monthly: {
                labels: @json($monthlyLabels),
                values: @json($monthlyValues)
            },
            yearly: {
                labels: @json($yearlyLabels),
                values: @json($yearlyValues)
            }
        };

        let activeChartType = 'daily';
        let revenueChart;

        function renderChart(type) {
            const chartCanvas = document.getElementById('revenueChart');
            if (!chartCanvas) return;
            const ctx = chartCanvas.getContext('2d');
            const data = chartData[type];

            if (revenueChart) {
                revenueChart.destroy();
            }

            revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Revenue ($)',
                        data: data.values,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.05)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#059669',
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                },
                                font: {
                                    family: 'sans-serif',
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'sans-serif',
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });
        }

        function switchChart(type) {
            // Update button styles
            const types = ['daily', 'weekly', 'monthly', 'yearly'];
            types.forEach(t => {
                const btn = document.getElementById('btn-' + t);
                if (btn) {
                    if (t === type) {
                        btn.style.background = '#059669';
                        btn.style.color = 'white';
                    } else {
                        btn.style.background = '#f3f4f6';
                        btn.style.color = '#4b5563';
                    }
                }
            });

            activeChartType = type;
            renderChart(type);
        }

        // Init daily chart
        window.addEventListener('DOMContentLoaded', () => {
            renderChart('daily');
        });
    </script>
    @endif
@endsection
