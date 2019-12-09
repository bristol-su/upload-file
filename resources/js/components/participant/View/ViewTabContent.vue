<template>
    <div>
        <b-table :fields="fields" :items="presentedFiles" v-if="presentedFiles.length > 0">
            <template v-slot:cell(created_at)="data">
                <span style="margin: auto; width: 100%; height: 100%" @mouseenter="hover = data.item.id" @mouseleave="hover = null">{{(hover === data.item.id?data.item.uploadedAtFormatted:data.item.hrUploadedAt)}}</span>
            </template>
            <template v-slot:cell(actions)="data">
                <a v-if="canDownload" :href="downloadUrl(data.item.id)">
                    <b-button size="sm" variant="outline-info"><i class="fa fa-download"></i> Download</b-button>
                </a>
                <b-button v-if="canUpdate" size="sm" variant="outline-info" @click="editFile(data.item.id)"><i class="fa fa-edit"></i> Edit</b-button>
                <b-button v-if="canSeeComments" size="sm" variant="outline-info" @click="showComments(data.item.id)"><i class="fa fa-comments"></i> Comments</b-button>
                <b-button v-if="canDelete" size="sm" variant="outline-danger" @click="deleteFile(data.item.id)"><i class="fa fa-trash"></i> Delete</b-button>

            </template>
        </b-table>
        <div v-else>
            No files uploaded.
        </div>
        
        <b-modal id="editFile">
            <edit-file v-if="editingFileId !== null" :file-id="editingFileId" @fileUpdated="$emit('fileUpdated', $event)"></edit-file>
        </b-modal>

        <b-modal id="showComments">
            <comments v-if="commentingFileId !== null" :file-id="commentingFileId"></comments>
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
                default: function() {
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
                let i = size == 0 ? 0 : Math.floor( Math.log(size) / Math.log(1024) );
                return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
            },
            
            presentUploadedBy(user) {
                return user.forename + ' ' + user.surname;
            },
            
            downloadUrl(id) {
                return this.$url + '/file/' + id + '/download';
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
                    if(confirmed) {
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
                return ['title', 'description', 'status', {key: 'uploadedByName', label: 'Uploaded By'}, {key: 'created_at', label: 'Uploaded At'}, 'actions'];
            }
        }
    }
</script>

<style scoped>

</style>