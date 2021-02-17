<template>
    <div>
        <b-container>
            <b-row>
                <!-- Filter & Search: -->
                <b-col lg="6">
                    <b-form-group
                            label="Filter"
                            label-for="filter-input"
                            label-cols-sm="3"
                            label-align-sm="right"
                            label-size="sm"
                            class="mb-0"
                    >
                        <b-input-group size="sm">
                            <b-form-input
                                    id="filter-input"
                                    v-model="filter"
                                    type="search"
                                    placeholder="Type to Search"
                            ></b-form-input>

                            <b-input-group-append>
                                <b-button :disabled="!filter" @click="search">Search</b-button>
                                <b-button :disabled="!filter" @click="filter = '' && loadFiles">Clear</b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form-group>
                </b-col>
                <b-col lg="6" class="my-1">
                    <b-form-group
                            v-model="sortDirection"
                            label="Filter On"
                            description="Leave all unchecked to filter on all data"
                            label-cols-sm="3"
                            label-align-sm="right"
                            label-size="sm"
                            class="mb-0"
                            v-slot="{ ariaDescribedby }"
                    >
                        <b-form-checkbox-group
                                v-model="filterOn"
                                :aria-describedby="ariaDescribedby"
                                class="mt-1"
                        >
                            <b-form-checkbox value="title">Title</b-form-checkbox>
                            <b-form-checkbox value="uploaded_for">Uploaded For</b-form-checkbox>
                            <b-form-checkbox value="uploaded_by">Uploaded By</b-form-checkbox>
                            <b-form-checkbox value="status">Status</b-form-checkbox>
                            <b-form-checkbox value="created_at">Created At</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-pagination
                            v-model="currentPage"
                            :total-rows="totalRows"
                            :per-page="perPage"
                            align="fill"
                            size="sm"
                            class="my-0"
                    ></b-pagination>
                </b-col>
            </b-row>
        </b-container>

        <b-table
                :fields="fields"
                :items="files"
                :tbody-tr-class="rowStyle"
                :busy="isBusy"
                ref="filetable"
        >
            <template v-slot:cell(uploaded_for)="data">
                <v-uploaded-for-name :activity-instance="data.item.activity_instance"></v-uploaded-for-name>
            </template>
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

            <template #table-busy>
                <div class="text-center text-danger my-2">
                    <b-spinner class="align-middle"></b-spinner>
                    <strong>Loading...</strong>
                </div>
            </template>
        </b-table>



        <b-modal id="file-status-change" :title="statusChangeTitle" hide-footer>
            <status-change :file="fileForStatusChange" v-if="fileForStatusChange !== null" :statuses="statuses" @statusAdded="addStatus">

            </status-change>
        </b-modal>

        <b-modal id="comments" :title="'Commenting On: ' + fileForComments.activity_instance.participant_name + ', Uploaded by: ' + fileForComments.uploaded_by" hide-footer size="lg" v-if="fileForComments !== null">
            <comments :file-id="fileForComments.id" v-if="fileForComments !== null"
                      :can-add-comments="canAddComments" :can-delete-comments="canDeleteComments" :can-update-comments="canUpdateComments"
                      v-on:updateCommentCount="updateCommentCount"
            ></comments>
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
    import VUploadedForName from './VUploadedForName';
    export default {
        name: "UploadFile",

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
                fields: [
                    {key: 'title', sortable: true}, {key: 'uploaded_for', sortable: true}, {key:'uploaded_by', sortable: true}, {key:'status', sortable: true}, {key:'created_at', sortable: true}, 'actions'],
                editingFileId: null,
                filter: null,
                filterOn: [],
                sortDirection: 'asc',
                currentPage: 1,
                perPage: 10,
                totalRows: 1,
                isBusy: false
            }
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
                alert(status);
                console.log(status);
                this.$http.get('/file/' + this.fileForStatusChange.id)
                    .then(response => {
                        console.log(response);
                        this.fileForStatusChange.status = response.data.status
                        this.fileForStatusChange.statuses.push(response.data);

                        let fileIndex = this.files.findIndex(f => f.id === this.fileForStatusChange.id);
                        console.log(this.fileForStatusChange);
                        Vue.set(this.files, fileIndex, this.fileForStatusChange)
                        this.$refs.filetable.refresh()
                    })
                    .catch(error => this.$notify.alert('Could not update files. Please refresh the page.'))
                    .then(() => {
                        this.$bvModal.hide('file-status-change');
                        this.fileForStatusChange = null;
                    });
            },

            pushFile(file) {
                this.files.push(file);
            },

            toggleBusy() {
              this.isBusy = !this.isBusy;
            },

            loadFiles() {
                this.toggleBusy();

                this.$http.get('file', {
                    params: {
                        page: this.currentPage,
                        per_page: this.perPage,

                    }
                })
                    .then(response => this.processFiles(response))
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message))
                    .then(() => {
                      this.$refs.filetable.refresh();
                      this.toggleBusy();
                });
            },

            searchFiles() {
                this.toggleBusy();

                this.$http.get('file/search', {
                    params: {
                        page: this.currentPage,
                        per_page: this.perPage,
                        filter: this.filter,
                        filter_on: this.filterOn
                    }
                })
                  .then(response => this.processFiles(response))
                  .catch(error => this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message))
                  .then(() => {
                      this.$refs.filetable.refresh();
                      this.toggleBusy();
                  });
            },

            processFiles(response) {
              this.files = response.data.data;

              this.files.map(file => {
                  file.size = this.presentSize(file.size);
                  file.uploaded_by = this.presentUploadedBy(file.uploaded_by);
                  return file;
              });

              this.totalRows = response.data.total;
              this.perPage = response.data.per_page;
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
            },

            updateCommentCount(data){
                let fileId = data.file;
                let Comment = data.comment;
                let index = this.files.findIndex(f => f.id === fileId);
                let file = this.files[index];
                let comments = file['comments'];

                if(data.action === 'Added') {
                    // Add new Comment to data:
                    comments.push(Comment);
                }

                if(data.action === 'Removed') {
                    // Find and remove Comment for Array:
                    comments.splice( comments.findIndex(c => c.id === Comment), 1);
                }
            },
            rowStyle(item, type)
            {
                if(!item || type !== 'row') { return; }
                // if(item.style === 'Awaiting Approval') { return 'table-warning'; }
                if(item.status === 'Approved') { return 'table-success'; }
                if(item.status === 'Approved Pending Comments') { return 'table-warning'; }
                if(item.status === 'Rejected') { return 'table-danger'; }
            }
        },

        computed: {
            processedFiles() {
                // return this.files.map(file => {
                //     file.size = this.presentSize(file.size);
                //     file.uploaded_by = this.presentUploadedBy(file.uploaded_by);
                //     return file;
                // })
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
