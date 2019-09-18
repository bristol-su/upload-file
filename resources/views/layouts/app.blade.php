@extends('bristolsu::base')



@section('content')
    <div id="uploadfile-root">
        @yield('module-content')
    </div>
@endsection




@push('styles')
    <link href="{{ asset('modules/uploadfile/css/app.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('modules/uploadfile/js/app.js') }}"></script>
@endpush
