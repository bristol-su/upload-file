<template>
    <div>
        <b-tabs>
            <b-tab title="New File" v-if="canUpload" active>
                <upload-tab-content :allowed-extensions="allowedExtensions" :multiple-files="multipleFiles" :default-document-title="defaultDocumentTitle" @file-uploaded="pushFile"></upload-tab-content>
            </b-tab>
            <b-tab v-if="canView" title="Saved Files">
                <view-tab-content :can-update="canUpdate" :can-see-comments="canSeeComments" :can-delete="canDelete" :can-download="canDownload" @fileDeleted="popFile" :files="files" @fileUpdated="replaceFile"></view-tab-content>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    import UploadTabContent from './Upload/UploadTabContent';
    import ViewTabContent from './View/ViewTabContent';
    
    export default {
        name: "UploadFile",
        
        components: {
            UploadTabContent,
            ViewTabContent,
        },
        
        props: {
            canUpload: {
                type: Boolean,
                required: true,
                default: false
            },
            canView: {
                type: Boolean,
                required: true,
                default: false
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
            defaultDocumentTitle: {
                type: String,
                default: ''
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
            pushFile(file) {
                this.$http.get('file/'+file.id)
                    .then(response => this.files.push(response.data))
                    .catch(error => window.location.reload());
            },
            
            loadFiles() {
                this.$http.get('file')
                    .then(response => this.files = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving files: ' + error.message));
            },
            
            popFile(id) {
                this.files = this.files.filter(file => file.id !== id);
            },
            
            replaceFile(file) {
                this.$http.get('file/' + file.id)
                    .then(response => {
                        let index = this.files.map(data => data.id).indexOf(file.id);
                        this.files[index] = response.data;  
                    })
                    .catch(error => window.location.reload());
            }
        }
    }
</script>

<style scoped>

</style>