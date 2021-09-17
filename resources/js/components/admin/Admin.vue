<template>
    <p-tabs>
        <p-tab title="Uploaded Files" v-if="canSeeFiles">
            <admin-files
                :can-see-comments="canSeeComments"
                :can-add-comments="canAddComments"
                :can-change-status="canChangeStatus"
                :can-delete-comments="canDeleteComments"
                :can-update-comments="canUpdateComments"
                :can-download="canDownload"
                :can-delete-files="canDeleteFiles"
                :can-update-files="canUpdateFiles"
                :loading="$isLoading('loading-files')"
                @delete="deleteFile"
                @update="updateFile"
                :files="files
"
                :statuses="statuses"></admin-files>
        </p-tab>
        <p-tab title="Upload a File" v-if="canAddFile">
            <admin-upload-file
                @newFiles="pushFiles"
                :default-document-title="defaultDocumentTitle"
                :multiple-files="multipleFiles"
                :allowed-extensions="allowedExtensions"
            ></admin-upload-file>
        </p-tab>
    </p-tabs>
</template>

<script>
import AdminFiles from './AdminFiles';
import AdminUploadFile from './AdminUploadFile';

export default {
    name: "Admin",
    components: {
        AdminFiles, AdminUploadFile
    },
    props: {
        canAddFile: {
            required: true,
            type: Boolean,
            default: false
        },
        canSeeFiles: {
            required: true,
            type: Boolean,
            default: false
        },
        canDownload: {
            required: true,
            type: Boolean,
            default: false
        },
        canChangeStatus: {
            required: true,
            type: Boolean,
            default: false
        },
        canAddComments: {
            required: true,
            type: Boolean,
            default: false
        },
        canDeleteComments: {
            required: true,
            type: Boolean,
            default: false
        },
        canUpdateComments: {
            required: true,
            type: Boolean,
            default: false
        },
        canSeeComments: {
            required: true,
            type: Boolean,
            default: false
        },
        statuses: {
            required: true,
            type: Array
        },
        canUpdateFiles: {
            type: Boolean,
            required: false,
            default: false
        },
        canDeleteFiles: {
            type: Boolean,
            required: false,
            default: false
        },
        defaultDocumentTitle: {
            required: false,
            default: '',
            type: String
        },
        multipleFiles: {
            required: false,
            default: true,
            type: Boolean
        },
        allowedExtensions: {
            required: false,
            type: Array,
            default: function() {
                return [];
            }
        }
    },
    data() {
        return {
            files: []
        }
    },
    created() {
        this.loadFiles();
    },

    methods: {
        loadFiles() {
            this.$http.get('file', {name: 'loading-files'})
                .then(response => this.files = response.data)
                .catch(error => this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message));
        },
        deleteFile(file) {
            this.files.splice(
                this.files.indexOf(file),
                1
            )
        },
        updateFile(file,update) {
            this.files.splice(
                this.files.indexOf(file),
                1,
                update
            )
        },
        pushFiles(files) {
            files.forEach(file => this.files.push(file));
        },
    }
}
</script>

<style scoped>

</style>
