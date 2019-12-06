<template>
    <div>
        <b-tabs>
            <b-tab title="Upload" v-if="canUpload" active>
                <upload-tab-content @file-uploaded="pushFile"></upload-tab-content>
            </b-tab>
            <b-tab title="View">
                <view-tab-content :files="files"></view-tab-content>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    import UploadTabContent from './Upload/UploadTabContent';
    import ViewTabContent from './View/ViewTabContent';
    
    export default {
        name: "UploadFileRoot",
        
        components: {
            UploadTabContent,
            ViewTabContent,
        },
        
        props: {
            canUpload: {
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
                this.$http.get('/files')
                    .then(response => this.files = response.data)
                    .catch(error => this.$notify.alert('Sorry, something went wrong retrieving your files: ' + error.message));
            }
        }
    }
</script>

<style scoped>

</style>