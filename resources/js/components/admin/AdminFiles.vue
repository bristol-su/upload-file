<template>
    <div>
        <p-text-input id="search" label="Search" hint="Search for a group or file name" @input="$emit('search', $event)" :value="searchString"></p-text-input>
        <p-button button-text="Search" variant="secondary" @click="$emit('searchNow')"></p-button>
        <p-table
            :busy="loading"
            :columns="columns"
            :items="processedFiles"
            :editable="canUpdateFiles"
            :actions="true"
            :total-count="totalFileCount"
            :deletable="canDeleteFiles"
            @changePage="$emit('changePage', $event)"
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
        searchString: {type: String, default: null, required: false},
        loading: {type: Boolean, default: false},
        files: {
            required: false,
            type: Array,
            default: () => []
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
        totalFileCount: {
            type: Number, required: false, default: 0
        }
    },

    data() {
        return {
            fileForStatusChange: null,
            fileBeingCommented: null,
            columns: [
                {key: 'title', label: 'Title'},
                {key: 'description', label: 'Description', truncateCell: 20},
                {key: 'uploaded_for', label: 'Uploaded For'},
                {key: 'uploaded_by_name', label: 'Uploaded By'},
                {key: 'status', label: 'Status'},
                {key: 'uploaded_at', label: 'Uploaded At'},
            ],
            fileBeingEdited: null
        }
    },



    methods: {
        deleteFile(file) {
            this.$ui.confirm.delete('Deleting file ' + file.title, 'Are you sure you want to delete this file?')
                .then(() => {
                    this.$http.delete('file/' + file.id, {name: 'deleting-file-' + file.id})
                        .then(response => {
                            this.$notify.success('File deleted');
                            this.$emit('delete', file);
                        })
                        .catch(error => this.$notify.alert('Could not delete file: ' + error.message));
                });
        },

        editFile(file) {
            this.fileBeingEdited = file;
            this.$ui.modal.show('editFileModal');
        },

        markFileAsUpdated(file) {
            this.$emit('update', this.files.filter(f => f.id === file.id)[0], file)
            this.$ui.modal.hide('editFileModal');
            this.fileBeingEdited = null;
        },

        changeStatus(file) {
            this.fileForStatusChange = file;
            this.$ui.modal.show('changeStatusModal');
        },

        addNewStatusToFile(status) {
            let updatedFile = _.cloneDeep(this.fileForStatusChange);
            updatedFile.status = status.status;
            updatedFile.statuses.push(status);
            this.$emit('update', this.files.filter(f => f.id === updatedFile.id)[0], updatedFile);
            this.$ui.modal.hide('changeStatusModal');
            this.fileForStatusChange = null;
            this.$notify.success('The status of the file has been updated');
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
            this.$emit('update', this.files.indexOf(this.files.filter(f => f.id === file.id)[0]), file);
        }
    },

    computed: {
        processedFiles() {
            return this.files.map(file => {
                if(!file.hasOwnProperty('_table')) {
                    file._table = {}
                }
                file._table.isDeleting = this.$isLoading('deleting-file-' + file.id);
                file.uploaded_by_name = file.uploaded_by.data.preferred_name ?? (file.uploaded_by.data.first_name + ' ' + file.uploaded_by.data.last_name);
                file.created_at_datetime = moment(file.created_at);
                file.uploaded_at = file.created_at_datetime.fromNow();
                file.uploaded_at_formatted = file.created_at_datetime.format('lll');
                return file;
            }).sort((a,b) => b.created_at_datetime - a.created_at_datetime)
        },

        changeStatusModalTitle() {
            if (this.fileForStatusChange === null) {
                return 'No file selected.';
            }
            return this.fileForStatusChange.title + ' - ' + this.fileForStatusChange.status
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
