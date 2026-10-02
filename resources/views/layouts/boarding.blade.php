@extends('layouts.base')
@section('body')
    <main class="app-canvas relative isolate min-h-screen flex items-center justify-center bg-app p-4">
        {{ $slot }}
    </main>
    @parent
@endsection
