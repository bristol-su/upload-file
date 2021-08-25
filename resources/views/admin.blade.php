@extends('uploadfile::layouts.app')

@section('title', settings('title', 'Upload a File'))

@section('module-content')
    <p-page-content title="{{settings('title')}}" subtitle="{{settings('description')}}">
        <p-tabs>
            <p-tab title="Uploaded Files" v-if="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.index')?'true':'false')}}">
                <admin-files
                    :can-see-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.index')?'true':'false')}}"
                    :can-add-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.store')?'true':'false')}}"
                    :can-change-status="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.status.create')?'true':'false')}}"
                    :can-delete-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.destroy')?'true':'false')}}"
                    :can-update-comments="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.comment.update')?'true':'false')}}"
                    :can-download="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.download')?'true':'false')}}"
                    :can-delete-files="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.destroy')?'true':'false')}}"
                    :can-update-files="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.update')?'true':'false')}}"
                    :statuses="{{json_encode(settings('statuses'))}}"></admin-files>
            </p-tab>
            <p-tab title="Upload a File" v-if="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.admin.file.store')?'true':'false')}}">
                <admin-upload-file
                    default-document-title="{{settings('document_title')}}"
                    :multiple-files="{{(settings('multiple_files')?'true':'false')}}"
                    :allowed-extensions="{{json_encode((settings('allowed_extensions')??[]))}}"
                ></admin-upload-file>
            </p-tab>
        </p-tabs>
    </p-page-content>
@endsection
