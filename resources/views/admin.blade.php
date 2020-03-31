@extends('uploadfile::layouts.app')

@section('title', settings('title', 'Upload a File'))

@section('module-content')
    <div class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12" style="text-align: center;">
                    <h2 class="">{{settings('title')}}</h2>
                    <p class="">{!! settings('description') !!}</p>

                    <upload-file-admin
                        :can-download="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.download')?'true':'false')}}"
                        :can-change-status="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.status.create')?'true':'false')}}"
                        :can-add-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.store')?'true':'false')}}"
                        :can-see-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.index')?'true':'false')}}"
                        :can-delete-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.destroy')?'true':'false')}}"
                        :can-update-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.update')?'true':'false')}}"
                        :statuses="{{json_encode(settings('statuses'))}}"
                        query-string="{{url()->getAuthQueryString()}}"></upload-file-admin>
                </div>
            </div>
        </div>
    </div>
@endsection