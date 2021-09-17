@extends('uploadfile::layouts.app')

@section('title', settings('title', 'Upload a File'))

@section('module-content')
    <p-page-content title="{{settings('title')}}" subtitle="{{settings('description')}}">
        <upload-file-root
            :show-old-files="{{(count(settings('tags_to_merge', [])) > 0 ? 'true' : 'false' )}}"
            :can-upload="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.file.store')?'true':'false')}}"
            :can-download="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.file.download')?'true':'false')}}"
            :can-view="{{(BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.file.index')?'true':'false')}}"
            default-document-title="{{settings('document_title')}}"
            :multiple-files="{{(settings('multiple_files')?'true':'false')}}"
            :allowed-extensions="{{json_encode((settings('allowed_extensions')??[]))}}"
            :can-update="{{(\BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.file.update')?'true':'false')}}"
            :can-delete="{{(\BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.file.destroy')?'true':'false')}}"
            :can-see-comments="{{(\BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.comment.index')?'true':'false')}}"
            :can-add-comments="{{(\BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.comment.store')?'true':'false')}}"
            :can-delete-comments="{{(\BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.comment.destroy')?'true':'false')}}"
            :can-update-comments="{{(\BristolSU\Support\Permissions\Facade\PermissionTester::evaluate('uploadfile.comment.update')?'true':'false')}}"
            query-string="{{url()->getAuthQueryString()}}"></upload-file-root>
    </p-page-content>
@endsection

