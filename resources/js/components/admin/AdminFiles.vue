<template>
    <div>
        <div v-if="loading">
            Loading files
        </div>
        <p-table
            v-else
            :columns="columns"
            :items="processedFiles"
            :editable="canUpdateFiles"
            :deletable="canDeleteFiles"
            @delete="deleteFile($event)"
            @edit="editFile($event)">
            <template #cell(uploaded_for)="{row}">
                <v-uploaded-for-name :activity-instance="row.activity_instance"></v-uploaded-for-name>
            </template>

            <template #cell(uploaded_at_name)="{row}">
                <p-hover :activity-instance="row.uploaded_at">
                    <template #onHover>
                        {{ row.uploaded_at_formatted }}
                    </template>
                    {{ row.uploaded_at }}
                </p-hover>
            </template>

            <template #actions="{row}">
                <a :href="downloadUrl(row.id)" v-if="canDownload" class="text-primary hover:text-primary-dark">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 content="Download File" v-tippy="{ arrow: true, animation: 'fade', placement: 'top-start', arrow: true, interactive: true}"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span class="sr-only">Download</span>
                        </span>
                </a>
                <a href="#" @click.prevent="changeStatus(row)" v-if="canChangeStatus" class="text-success hover:text-success-dark" role="button">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             content="Change Status" v-tippy="{ arrow: true, animation: 'fade', placement: 'top-start', arrow: true, interactive: true}"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="sr-only">Change Status</span>
                </a>
                <a href="#" @click.prevent="showComments(row)" v-if="canSeeComments" class="text-primary hover:text-secondary-dark" role="button">
                    <span class="flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" content="View Comments" v-tippy="{ arrow: true, animation: 'fade', placement: 'top-start', arrow: true, interactive: true}">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        ({{ row.comments.length }})
                        <span class="sr-only">Comments</span>
                    </span>
                </a>
            </template>

        </p-table>


        <p-modal id="commentsModal" title="Comments">
            <comments :can-add-comments="canAddComments" :can-delete-comments="canDeleteComments"
                      :can-update-comments="canUpdateComments"
                      :file="fileBeingCommented" v-if="fileBeingCommented !== null"
                      @commentUpdated="updateComments"></comments>
        </p-modal>

        <p-modal id="changeStatusModal" :title="changeStatusModalTitle">
            <status-change :file="fileForStatusChange" v-if="fileForStatusChange !== null" :statuses="statuses"
                           @statusAdded="addNewStatusToFile">

            </status-change>
        </p-modal>

        <p-modal id="editFileModal" title="Edit file">
            <edit-file :file="fileBeingEdited" v-if="fileBeingEdited !== null" @fileUpdated="markFileAsUpdated"></edit-file>
        </p-modal>
    </div>
</template>

<script>

import StatusChange from './StatusChange';
import Comments from '../participant/View/Comments';
import EditFile from './EditFile';
import VUploadedForName from './VUploadedForName';
import moment from 'moment';

export default {
    name: "AdminFiles",

    components: {
        VUploadedForName,
        EditFile,
        Comments,
        StatusChange
    },

    props: {
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
        }
    },

    data() {
        return {
            files: [],
            fileForStatusChange: null,
            fileBeingCommented: null,
            columns: [
                {key: 'title', label: 'Title'},
                {key: 'uploaded_for', label: 'Uploaded For'},
                {key: 'uploaded_by_name', label: 'Uploaded By'},
                {key: 'status', label: 'Status'},
                {key: 'uploaded_at', label: 'Uploaded At'},
            ],
            fileBeingEdited: null,
            loading: false
        }
    },

    created() {
        this.loadFiles();
    },

    methods: {
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

        changeStatus(file) {
            this.fileForStatusChange = file;
            this.$ui.modal.show('changeStatusModal');
        },

        addNewStatusToFile(status) {
            this.fileForStatusChange.status = status.status;
            this.fileForStatusChange.statuses.push(status);
            this.files.splice(this.files.indexOf(this.files.filter(f => f.id === this.fileForStatusChange.id)[0]), 1, this.fileForStatusChange);
            this.$ui.modal.hide('changeStatusModal');
            this.fileForStatusChange = null;
            this.$notify.success('The status of the file has been updated');
        },

        loadFiles() {
            this.loading = true;
            this.$http.get('file')
                .then(response => this.files = response.data)
                .catch(error => console.log(error) && this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message))
                .then(() => this.loading = false);
        },

        downloadUrl(id) {
            return this.$tools.routes.query.addQueryStringToWebUrl(this.$tools.routes.module.moduleUrl() + '/file/' + id + '/download');
        },

        presentSize(size) {
            let i = size == 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
            return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        },

        presentUploadedBy(user) {
            return user.data.preferred_name ?? user.data.first_name + ' ' + user.data.last_name;
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
        processedFiles() {
            return this.files.map(file => {
                file.uploaded_for = null;
                file.uploaded_by_name = this.presentUploadedBy(file.uploaded_by);
                file.uploaded_at = moment(file.created_at).fromNow();
                file.uploaded_at_formatted = moment(file.created_at).format('lll');
                return file;
            })
        },

        changeStatusModalTitle() {
            if (this.fileForStatusChange === null) {
                return 'No file selected.';
            }
            return 'Status of ' + this.fileForStatusChange.title
        },
        editFileModalTitle() {
            if (this.fileBeingEdited === null) {
                return 'No file selected.';
            }
            return 'Editing file ' + this.fileBeingEdited.title
        }
    }
}
</script>

<style scoped>

</style>