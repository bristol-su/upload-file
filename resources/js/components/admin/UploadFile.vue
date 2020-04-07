<template>
    <div>
        <b-table :fields="fields" :items="processedFiles">

            <template v-slot:cell(actions)="data">
                <a :href="downloadUrl(data.item.id)" v-if="canDownload">
                    <b-button size="sm" variant="outline-info"><i class="fa fa-download"></i> Download</b-button>
                </a>
                <b-button @click="changeStatus(data.item)" size="sm" v-if="canChangeStatus" variant="outline-info"><i
                        class="fa fa-check"></i> Status
                </b-button>
                <b-button @click="showComments(data.item)" size="sm" v-if="canSeeComments" variant="outline-info"><i
                        class="fa fa-comments"></i> Comments <b-badge variant="secondary">{{data.item.comments.length}} <span class="sr-only">comments</span></b-badge>
                </b-button>
                <b-button @click="editFile(data.item.id)" size="sm" v-if="canUpdateFiles" variant="outline-info"><i
                        class="fa fa-edit"></i> Edit
                </b-button>
                <b-button @click="deleteFile(data.item.id)" size="sm" v-if="canDeleteFiles" variant="outline-danger"><i
                        class="fa fa-trash"></i> Delete
                </b-button>
            </template>
            
            <template v-slot:cell(change_status)="data">
                <b-button variant="secondary" @click="changeStatus(data.item)">Change Status</b-button>
            </template>
        </b-table>
        
        

        <b-modal id="file-status-change" :title="statusChangeTitle" hide-footer>
            <status-change :file="fileForStatusChange" v-if="fileForStatusChange !== null" :statuses="statuses" @statusAdded="addStatus">
                
            </status-change>
        </b-modal>

        <b-modal id="comments" title="Comments" hide-footer >
            <comments :file-id="fileForComments.id" v-if="fileForComments !== null"
                      :can-add-comments="canAddComments" :can-delete-comments="canDeleteComments" :can-update-comments="canUpdateComments"></comments>
        </b-modal>

        <b-modal id="editFile">
            <edit-file :file-id="editingFileId" v-if="editingFileId !== null"></edit-file>

            <template slot="modal-footer">
                <b-btn @click="$bvModal.hide('editFile')" variant="secondary">
                    Cancel
                </b-btn>
            </template>
        </b-modal>
    </div>
</template>

<script>
    
    import StatusChange from './StatusChange';
    import Comments from '../participant/View/Comments';
    import EditFile from './EditFile';
    export default {
        name: "UploadFile",
        
        components: {
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
            queryString: {
                type: String,
                required: true
            },
            canUpdateFiles: {
                type: Boolean,
                required: false,
                default:  false
            },
            canDeleteFiles: {
                type: Boolean,
                required: false,
                default:  false
            }
        },
        
        data() {
            return {
                files: [],
                fileForStatusChange: null,
                fileForComments: null,
                fields: ['title', 'size', 'uploaded_by', 'status', 'created_at', 'actions'],
                editingFileId: null
            }
        },
        
        created() {
            this.loadFiles();
        },
        
        methods: {
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
                                    window.location.reload();
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

            addStatus(status) {
                this.$http.get('/file/' + this.fileForStatusChange.id)
                    .then(response => Vue.set(this.files, this.files.indexOf(this.fileForStatusChange), response.data))
                    .catch(error => this.$notify.alert('Could not update files. Please refresh the page.'))
                    .then(() => this.$bvModal.hide('file-status-change'));
                
            },
            
            pushFile(file) {
                this.files.push(file);
            },
            
            loadFiles() {
                this.$http.get('file')
                    .then(response => this.files = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message));
            },

            downloadUrl(id) {
                return this.$url + '/file/' + id + '/download?' + this.queryString;
            },

            presentSize(size) {
                let i = size == 0 ? 0 : Math.floor( Math.log(size) / Math.log(1024) );
                return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
            },

            presentUploadedBy(user) {
                return user.data.first_name + ' ' + user.data.last_name;
            },
            
            changeStatus(file) {
                this.fileForStatusChange = file;
                this.$bvModal.show('file-status-change');
            },

            showComments(file) {
                this.fileForComments = file;
                this.$bvModal.show('comments');
            }
        },
        
        computed: {
            processedFiles() {
                return this.files.map(file => {
                    file.size = this.presentSize(file.size);
                    file.uploaded_by = this.presentUploadedBy(file.uploaded_by);
                    return file;
                })
            },

            statusChangeTitle() {
                if(this.fileForStatusChange === null) {
                    return 'No file selected.';
                }
                return 'Status of ' + this.fileForStatusChange.title
            }
        }
    }
</script>

<style scoped>

</style>