<template>
    <div>
        <p-table
            :items="presentedFiles"
            :columns="fields"
            v-if="presentedFiles.length > 0"
            :editable="canUpdate"
            :deletable="canDelete"
            @delete="deleteFile($event)"
            @edit="editFile($event)"
        >
            <template slot="actions" slot-scope="slotProps">
                <a :href="downloadUrl(slotProps.row)" v-if="canDownload" class="text-primary hover:text-primary-dark">
                     <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 content="Download File" v-tippy="{ arrow: true, animation: 'fade', placement: 'top-start', arrow: true, interactive: true}"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span class="sr-only">Download</span>
                        </span>
                </a>
                <a href="#" @click="showComments(slotProps.row)" v-if="canSeeComments" class="text=primary hover:text-secondary-dark">
                    <span class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" content="View Comments" v-tippy="{ arrow: true, animation: 'fade', placement: 'top-start', arrow: true, interactive: true}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        ({{slotProps.row.comments.length}})
                        <span class="sr-only">Comments</span>
                    </span>
                </a>
            </template>

            <template #cell(uploaded_at)="{row}">
                <p-hover :activity-instance="row.uploaded_at">
                    <template #onHover>
                        {{ row.uploaded_at_formatted }}
                    </template>
                    {{ row.uploaded_at }}
                </p-hover>
            </template>
        </p-table>
        <div v-else>
            No files uploaded.
        </div>

        <p-modal id="editFileModal">
            <edit-file :file="fileBeingEdited" v-if="fileBeingEdited" @fileUpdated="markFileAsUpdated"></edit-file>
        </p-modal>

        <p-modal id="commentsModal" title="Comments">
            <comments :can-add-comments="canAddComments" :can-delete-comments="canDeleteComments"
                      :can-update-comments="canUpdateComments"
                      :file="fileBeingCommented" v-if="fileBeingCommented !== null"
                      @commentUpdated="updateComments"></comments>
        </p-modal>
    </div>
</template>

<script>
import moment from 'moment';
import EditFile from './EditFile';
import Comments from './Comments';

export default {
    name: "ViewTabContent",
    components: {Comments, EditFile},
    props: {
        files: {
            type: Array,
            default: function () {
                return [];
            }
        },
        canDownload: {
            type: Boolean,
            required: true,
            default: false
        },
        canUpdate: {
            type: Boolean,
            required: true,
            default: false
        },
        canDelete: {
            type: Boolean,
            required: true,
            default: false
        },
        canSeeComments: {
            type: Boolean,
            required: true,
            default: false
        },
        canAddComments: {
            type: Boolean,
            required: true,
            default: false
        },
        canDeleteComments: {
            type: Boolean,
            required: true,
            default: false
        },
        canUpdateComments: {
            type: Boolean,
            required: true,
            default: false
        },
        queryString: {
            type: String,
            required: true
        },
        isOldFiles: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            hover: null,
            fileBeingEdited: null,
            fileBeingCommented: null
        }
    },

    filters: {
        size(size) {
            let i = size == 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
            return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        }
    },

    methods: {

        downloadUrl(file) {
            return this.$tools.routes.query.addQueryStringToWebUrl(this.$tools.routes.module.moduleUrl() + '/' + (this.isOldFiles ? 'old-file' : 'file') + '/' + file.id + '/download');
        },

        deleteFile(file) {
            this.$ui.confirm.delete('Deleting file ' + file.title, 'Are you sure you want to delete this file?')
                .then(() => {
                    this.$http.delete('file/' + file.id)
                        .then(response => {
                            this.$notify.success('File deleted');
                            this.files.splice(this.files.indexOf(this.files.filter(f => f.id === file.id)[0]), 1);
                        })
                        .catch(error => this.$notify.alert('Could not delete file: ' + error.message));
                });
        },

        editFile(file) {
            this.fileBeingEdited = file;
            this.$ui.modal.show('editFileModal');
        },

        markFileAsUpdated(file) {
            this.files.splice(this.files.indexOf(this.files.filter(f => f.id === file.id)[0]), 1, file);
            this.$ui.modal.hide('editFileModal');
            this.fileBeingEdited = null;
        },

        showComments(file) {
            this.fileBeingCommented = file;
            this.$ui.modal.show('commentsModal');
        },

        updateComments(comments) {
            let file = this.fileBeingCommented;
            file.comments = comments;
            this.files.splice(this.files.indexOf(this.files.filter(f => f.id === file.id)[0]), 1, file);
        }
    },

    computed: {
        presentedFiles() {
            return this.files.map(file => {
                file.uploaded_by = file.uploaded_by.data.preferred_name ?? (file.uploaded_by.data.first_name + ' ' + file.uploaded_by.data.last_name);
                file.uploaded_at = moment(file.created_at).fromNow();
                file.uploaded_at_formatted = moment(file.created_at).format('lll');
                return file;
            })
        },
        fields() {
            return [
                {key: 'title', label: 'Title'},
                {key: 'description', label: 'Description'},
                {key: 'status', label: 'Status'},
                {key: 'uploaded_by', label: 'Uploaded By'},
                {key: 'uploaded_at', label: 'Uploaded At'}
            ];
        }
    }
}
</script>

<style scoped>

</style>
