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

            <template #cell(uploaded_at)="{row}">
                <p-hover :activity-instance="row.uploaded_at">
                    <template #onHover>
                        {{ row.uploaded_at_formatted }}
                    </template>
                    {{ row.uploaded_at }}
                </p-hover>
            </template>

            <template #actions="{row}">
                <a :href="downloadUrl(row.id)" v-if="canDownload">Download</a>
                <a href="#" @click.prevent="changeStatus(row)" v-if="canChangeStatus">Change Status</a>
                <a href="#" @click.prevent="showComments(row)" v-if="canSeeComments">
                    Comments ({{ row.comments.length }})
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
                {key: 'uploaded_by', label: 'Uploaded By'},
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
            return user.data.first_name + ' ' + user.data.last_name;
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
                file.uploaded_by = this.presentUploadedBy(file.uploaded_by);
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
