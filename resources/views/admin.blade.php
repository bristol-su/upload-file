@extends('uploadfile::layouts.app')

@section('title', settings('title', 'Upload a File'))

@section('module-content')
    <div class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12" style="text-align: center;">
                    <h2 class="">{{settings('title')}}</h2>
                    <p class="">{!! settings('description') !!}</p>

                    <admin-upload-file-page
                        :can-download="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.download')?'true':'false')}}"
                        :can-change-status="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.status.create')?'true':'false')}}"
                        :can-add-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.store')?'true':'false')}}"
                        :can-see-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.index')?'true':'false')}}"
                        :can-delete-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.destroy')?'true':'false')}}"
                        :can-update-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.update')?'true':'false')}}"
                        :can-view-files="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.index')?'true':'false')}}"
                        :can-upload-files="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.store')?'true':'false')}}"
                        :statuses="{{json_encode(settings('statuses'))}}"
                        default-document-title="{{settings('document_title')}}"
                        :multiple-files="{{(settings('multiple_files')?'true':'false')}}"
                        :allowed-extensions="{{json_encode((settings('allowed_extensions')??[]))}}"
                        query-string="{{url()->getAuthQueryString()}}"></admin-upload-file-page>
                </div>
            </div>
        </div>
    </div>
@endsection