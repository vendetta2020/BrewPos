@extends('layouts.admin')

@section('title', 'Manajemen Resep - BrewPos')
@section('page_title', 'Manajemen Resep')

@section('content')
    <!-- Redirect to actual recipes.index route -->
    <script>
        window.location.href = "{{ route('admin.recipes.index') }}";
    </script>
@endsection
