@extends('layouts.vendor')

@section('page-title', 'My Customers')

@section('content')

    <div class="sm:px-8">

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-xl md:text-2xl font-semibold text-gray-900">
                My Customers
            </h1>

            <p class="text-gray-600 mt-1 text-sm">
                Customers added by you
            </p>
        </div>

        <!-- Customers Card -->
        <div class="bg-white rounded-xl shadow overflow-hidden">

            <!-- Card Header -->
            <div class="p-4 border-b border-gray-200">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            Customers
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            {{ $totalCustomers ?? $customers->count() }} customers found
                        </p>
                    </div>

                    <!-- Search -->
                    <div class="w-full sm:w-72">
                        <div class="relative">

                            <input id="customerSearch" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 pl-9 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-[#00995c] focus:border-[#00995c]"
                                placeholder="Search customers...">

                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0z" />
                            </svg>

                        </div>
                    </div>

                </div>

            </div>

            <!-- Table -->
            <div class="overflow-x-auto">

                <table class="w-full min-w-[650px]">

                    <thead class="bg-gray-50 border-b border-gray-200">

                        <tr>

                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                S.N.
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                Full Name
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                Email
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">
                                Mobile Number
                            </th>

                        </tr>

                    </thead>

                    <tbody id="customersBody" class="divide-y divide-gray-200">

                        @forelse($customers as $customer)

                            <tr class="customer-row hover:bg-gray-50 transition">

                                <!-- S.N. -->
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $loop->iteration }}
                                </td>

                                <!-- Full Name -->
                                <td class="px-4 py-3">

                                    <div class="flex items-center gap-3">

                                        <div class="w-9 h-9 rounded-full bg-emerald-50
                                                        flex items-center justify-center
                                                        text-[#00995c] font-semibold text-sm">

                                            {{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}

                                        </div>

                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $customer->name ?? '-' }}
                                        </span>

                                    </div>

                                </td>

                                <!-- Email -->
                                <td class="px-4 py-3 text-sm text-gray-600">

                                    @if($customer->email)

                                        {{ $customer->email }}

                                    @else

                                        <span class="text-gray-400">-</span>

                                    @endif

                                </td>

                                <!-- Mobile -->
                                <td class="px-4 py-3 text-sm text-gray-600">

                                    @if($customer->phone)

                                        {{ $customer->phone }}

                                    @else

                                        <span class="text-gray-400">-</span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4" class="px-4 py-10 text-center">

                                    <div class="flex flex-col items-center">

                                        <div class="w-12 h-12 rounded-full bg-gray-100
                                                        flex items-center justify-center mb-3">

                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m8-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm6-3v6m3-3h-6" />
                                            </svg>

                                        </div>

                                        <p class="text-sm font-medium text-gray-700">
                                            No customers found
                                        </p>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Customers added by you will appear here.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        <!-- No Search Results -->
                        <tr id="noSearchResults" class="hidden">

                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                No customers match your search.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection


@push('scripts')

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const searchInput = document.getElementById('customerSearch');
            const rows = document.querySelectorAll('.customer-row');
            const noResults = document.getElementById('noSearchResults');

            if (!searchInput) {
                return;
            }

            searchInput.addEventListener('input', function () {

                const search = this.value.toLowerCase().trim();

                let visibleRows = 0;

                rows.forEach(function (row) {

                    const text = row.textContent.toLowerCase();

                    if (text.includes(search)) {

                        row.classList.remove('hidden');
                        visibleRows++;

                    } else {

                        row.classList.add('hidden');

                    }

                });

                if (noResults) {

                    if (search && visibleRows === 0) {

                        noResults.classList.remove('hidden');

                    } else {

                        noResults.classList.add('hidden');

                    }

                }

            });

        });

    </script>

@endpush