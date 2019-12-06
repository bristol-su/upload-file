<template>
    <div>
        <b-tabs>
            <b-tab title="Upload" v-if="canUpload" active>
                <upload-tab-content @file-uploaded="pushFile"></upload-tab-content>
            </b-tab>
            <b-tab v-if="canView" title="View">
                <view-tab-content :download="canDownload" :files="files"></view-tab-content>
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
                this.files.push(file);
            },
            
            loadFiles() {
                this.$http.get('file')
                    .then(response => this.files = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving files: ' + error.message));
            }
        }
    }
</script>

<style scoped>

</style>