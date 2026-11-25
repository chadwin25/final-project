@extends('layouts.pos')

@section('title', 'Sales report · CampusBite')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@endpush

@section('content')
<div class="report-page">
    <div class="report-heading">
        <div>
            <p class="report-kicker">Reports</p>
            <h1>Sales report</h1>
            <p>Review completed sales for the selected date range.</p>
        </div>

        <div class="report-actions">
            <button type="button" onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>

    <form class="report-filters" method="GET" action="{{ route('reports.sales') }}">
        <div class="report-filter-field">
            <label for="start_date">From date</label>
            <input
                id="start_date"
                name="start_date"
                type="date"
                value="{{ $filters['start_date'] ?? '' }}"
            >
            @error('start_date')
                <p class="report-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="report-filter-field">
            <label for="end_date">To date</label>
            <input
                id="end_date"
                name="end_date"
                type="date"
                value="{{ $filters['end_date'] ?? '' }}"
            >
            @error('end_date')
                <p class="report-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="report-filter-button" type="submit">Apply filters</button>
        <a class="report-clear-button" href="{{ route('reports.sales') }}">Clear</a>
    </form>

    <div class="report-print-heading">
        <h2>CampusBite POS — Sales report</h2>
        <p>
            @if (! empty($filters['start_date']) || ! empty($filters['end_date']))
                Date range:
                {{ ! empty($filters['start_date']) ? \Illuminate\Support\Carbon::parse($filters['start_date'])->format('M j, Y') : 'Beginning' }}
                to
                {{ ! empty($filters['end_date']) ? \Illuminate\Support\Carbon::parse($filters['end_date'])->format('M j, Y') : 'Today' }}
            @else
                All recorded sales
            @endif
        </p>
        <p>Generated {{ now('Asia/Manila')->format('M j, Y · g:i A') }}</p>
    </div>

    <div class="report-summary-grid">
        <article class="report-summary-card">
            <span>Sales records</span>
            <strong>{{ number_format($summary['sales_count']) }}</strong>
        </article>

        <article class="report-summary-card">
            <span>Units sold</span>
            <strong>{{ number_format($summary['units_sold']) }}</strong>
        </article>

        <article class="report-summary-card">
            <span>Total sales</span>
            <strong>₱{{ number_format($summary['total_sales'], 2) }}</strong>
        </article>
    </div>

    @if ($sales->isEmpty())
        <div class="report-empty">
            <h2>No sales found</h2>
            <p>There are no sales records for the selected date range.</p>
        </div>
    @else
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th scope="col">Date and time</th>
                        <th scope="col">Product</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Staff</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)
                        @php($saleDate = $sale->created_at->copy()->timezone('Asia/Manila'))
                        <tr>
                            <td data-label="Date and time">
                                {{ $saleDate->format('M j, Y · g:i A') }}
                            </td>
                            <td data-label="Product">
                                {{ $sale->product?->name ?? 'Product unavailable' }}
                                @if ($sale->product?->trashed())
                                    <small class="report-note">Archived</small>
                                @endif
                            </td>
                            <td data-label="Customer">
                                {{ $sale->customer?->full_name ?? 'Walk-in customer' }}
                            </td>
                            <td data-label="Staff">
                                {{ $sale->staff?->full_name ?? 'Staff unavailable' }}
                                @if ($sale->staff?->trashed())
                                    <small class="report-note">Archived</small>
                                @endif
                            </td>
                            <td data-label="Quantity">{{ number_format($sale->quantity) }}</td>
                            <td class="report-money" data-label="Total">
                                ₱{{ number_format((float) $sale->total_price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Report total</th>
                        <th>{{ number_format($summary['units_sold']) }} units</th>
                        <th class="report-money">₱{{ number_format($summary['total_sales'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection