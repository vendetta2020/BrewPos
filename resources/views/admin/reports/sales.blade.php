@extends('layouts.admin')

@section('title', 'Laporan Penjualan - BrewPos')
@section('page_title', 'Laporan Penjualan')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Laporan Penjualan</h2>
            <p class="text-sm text-gray-600 mt-1">Analisis data penjualan per periode</p>
        </div>
        <div class="p-6">
            <p class="text-gray-600">Belum ada data penjualan untuk periode ini.</p>
        </div>
    </div>
@endsection
