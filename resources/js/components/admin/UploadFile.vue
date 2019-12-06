<template>
    <div>
        <b-table :fields="fields" :items="processedFiles">
            <template v-slot:cell(download)="data">
                <a :href="downloadUrl(data.item.id)">
                    <b-button variant="secondary">Download</b-button>
                </a>
            </template>

            <template v-slot:cell(change_status)="data">
                <b-button variant="secondary" @click="changeStatus(data.item)">Change Status</b-button>
            </template>
        </b-table>

        <b-modal id="file-status-change" :title="statusChangeTitle" hide-footer>
            <status-change :file="fileForStatusChange" v-if="fileForStatusChange !== null" :statuses="statuses">
                
            </status-change>
        </b-modal>
    </div>
</template>

<script>
    
    import StatusChange from './StatusChange';
    export default {
        name: "UploadFile",
        
        components: {
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
            statuses: {
                required: true,
                type: Array
            }
        },
        
        data() {
            return {
                files: [],
                fileForStatusChange: null
            }
        },
        
        created() {
            this.loadFiles();
        },
        
        methods: {
            pushFile(file) {
                this.files.push(file);
            },
            
            loadFiles() {
                this.$http.get('file')
                    .then(response => this.files = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message));
            },

            downloadUrl(id) {
                return this.$url + '/files/' + id + '/download';
            },

            presentSize(size) {
                let i = size == 0 ? 0 : Math.floor( Math.log(size) / Math.log(1024) );
                return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
            },

            presentUploadedBy(user) {
                return user.forename + ' ' + user.surname;
            },
            
            changeStatus(file) {
                this.fileForStatusChange = file;
                this.$bvModal.show('file-status-change');
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

            fields() {
                let fields = ['title', 'size', 'uploaded_by', 'status', 'created_at'];
                if(this.canDownload) {
                    fields.push('download');
                }
                if(this.canChangeStatus) {
                    fields.push('change_status');
                }
                return fields;
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