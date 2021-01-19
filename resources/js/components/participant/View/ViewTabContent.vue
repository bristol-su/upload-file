<template>
    <div>
        <b-table :fields="fields" :items="presentedFiles" v-if="presentedFiles.length > 0">
            <template v-slot:cell(created_at)="data">
                <span @mouseenter="hover = data.item.id" @mouseleave="hover = null"
                      style="margin: auto; width: 100%; height: 100%">{{(hover === data.item.id?data.item.uploadedAtFormatted:data.item.hrUploadedAt)}}</span>
            </template>
            <template v-slot:cell(actions)="data">
                <a :href="downloadUrl(data.item.id)" v-if="canDownload">
                    <b-button size="sm" variant="outline-info"><i class="fa fa-download"></i> Download</b-button>
                </a>
                <b-button @click="editFile(data.item.id)" size="sm" v-if="canUpdate" variant="outline-info"><i
                        class="fa fa-edit"></i> Edit
                </b-button>
                <b-button @click="showComments(data.item.id)" size="sm" v-if="canSeeComments" variant="outline-info"><i
                        class="fa fa-comments"></i> Comments
                    <b-badge variant="secondary">{{data.item.comments.length}} <span class="sr-only">comments</span>
                    </b-badge>
                </b-button>
                <b-button @click="deleteFile(data.item.id)" size="sm" v-if="canDelete" variant="outline-danger"><i
                        class="fa fa-trash"></i> Delete
                </b-button>

            </template>
        </b-table>
        <div v-else>
            No files uploaded.
        </div>

        <b-modal id="editFile">
            <edit-file :file-id="editingFileId" @fileUpdated="$emit('fileUpdated', $event)"
                       v-if="editingFileId !== null"></edit-file>

            <template slot="modal-footer">
                <b-btn @click="$bvModal.hide('editFile')" variant="secondary">
                    Cancel
                </b-btn>
            </template>
        </b-modal>

        <b-modal id="showComments" title="Comments" size="lg">
            <comments :can-add-comments="canAddComments" :can-delete-comments="canDeleteComments" :can-update-comments="canUpdateComments"
                      :file-id="commentingFileId" v-if="commentingFileId !== null"></comments>
        </b-modal>
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
                editingFileId: null,
                commentingFileId: null
            }
        },

        methods: {
            presentSize(size) {
                let i = size == 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
                return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
            },

            presentUploadedBy(user) {
                return user.data.first_name + ' ' + user.data.last_name;
            },

            downloadUrl(id) {
                return this.$url + '/' + (this.isOldFiles ? 'old-file' : 'file') + '/' + id + '/download?' + this.queryString;
            },

            deleteFile(id) {
                this.$bvModal.msgBoxConfirm('Are you sure you want to delete this file?', {
                    title: 'Deleting file',
                    size: 'sm',
                    buttonSize: 'sm',
                    okVariant: 'danger',
                    okTitle: 'Delete',
                    cancelTitle: 'Cancel',
                    footerClass: 'p-2',
                    hideHeaderClose: true,
                    centered: true
                })
                    .then(confirmed => {
                        if (confirmed) {
                            this.$http.delete('file/' + id)
                                .then(response => {
                                    this.$notify.success('File deleted');
                                    this.$emit('fileDeleted', response.data.id);
                                })
                                .catch(error => this.$notify.alert('Could not delete file: ' + error.message));
                        } else {
                            this.$notify.warning('No files deleted');
                        }
                    })
                    .catch(error => this.$notify.alert('Could not delete file: ' + error.message));
            },

            editFile(id) {
                this.editingFileId = id;
                this.$bvModal.show('editFile')
            },

            showComments(id) {
                this.commentingFileId = id;
                this.$bvModal.show('showComments');
            }

        },

        computed: {
            presentedFiles() {
                let newFiles = [];
                this.files.forEach(file => {
                    let newFile = JSON.parse(JSON.stringify(file));
                    newFile.hrSize = this.presentSize(newFile.size);
                    newFile.uploadedByName = this.presentUploadedBy(newFile.uploaded_by);
                    newFile.hrUploadedAt = moment(newFile.created_at).fromNow();
                    newFile.uploadedAtFormatted = moment(newFile.created_at).format('lll');
                    newFiles.push(newFile);
                });
                return newFiles;
            },
            fields() {
                return ['title', 'description', 'status', {
                    key: 'uploadedByName',
                    label: 'Uploaded By'
                }, {key: 'created_at', label: 'Uploaded At'}, 'actions'];
            }
        }
    }
</script>

<style scoped>

</style>