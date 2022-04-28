<template>
    <div>
        <div class="flex justify-center" v-if="$isLoading('deleting-comment-' + comment.id)">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Deleting Comment
        </div>
        <div class="comment-wrapper" v-else>
            <div class="commentText">
                <span class="commenterName">{{ commenterName }}</span> <span
                class="date sub-text">{{ comment.created_at | datetime }}</span>
                <p-api-form :schema="form" @submit="updateComment" v-if="editing" button-text="Update Comment"
                            :busy="$isLoading('updating-comment-' + comment.id)" busy-text="Updating Comment">

                </p-api-form>

                <p v-else v-html="comment.comment"></p>

            </div>
            <div>
                <a role="button" href="#" @click="deleteComment" v-if="ownsComment && canDeleteComments"><i
                    class="fa fa-trash"></i><span class="sr-only">Delete Comment</span></a>
                <a role="button" href="#" @click="editComment" v-if="ownsComment && canUpdateComments"><i
                    class="fa fa-edit"></i><span class="sr-only">Edit Comment</span></a>
            </div>
        </div>
    </div>

</template>

<script>
import moment from 'moment';

export default {
    name: "Comment",

    props: {
        comment: {
            required: true,
            type: Object
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
    },

    data() {
        return {
            editing: false,
            fieldId: Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 5)
        }
    },

    filters: {
        datetime(val) {
            return moment(val).format('lll')
        }
    },

    methods: {
        deleteComment() {
            this.$ui.confirm.delete('Deleting comment', 'Are you sure you want to delete this comment?')
                .then(() => {
                    this.$http.delete('/comment/' + this.comment.id, {name: 'deleting-comment-' + this.comment.id})
                        .then(response => {
                            this.$notify.success('Comment deleted');
                            this.$emit('deleted')
                        })
                        .catch(error => this.$notify.alert('Could not delete comment: ' + error.message));
                })
        },
        editComment() {
            this.editing = true;
        },
        updateComment(data) {
            this.$http.patch('/comment/' + this.comment.id, {
                comment: data[this.fieldId + '-comment']
            }, {name: 'updating-comment-' + this.comment.id})
                .then(response => {
                    this.$notify.success('Comment updated');
                    this.editing = false;
                    this.$emit('updated', response.data);
                })
                .catch(error => this.$notify.alert('Could not update the comment: ' + error.message));
        }
    },

    computed: {
        commenterName() {
            return this.comment.posted_by.data.first_name + ' ' + this.comment.posted_by.data.last_name
        },
        ownsComment() {
            return portal.user.id === this.comment.posted_by.id;
        },
        form() {
            return this.$tools.generator.form.newForm()
                .withGroup(this.$tools.generator.group.newGroup()
                    .withField(
                        this.$tools.generator.field.text(this.fieldId + '-comment')
                            .label('Your comment')
                            .required(true)
                            .value(this.comment.comment)
                            .errorKey('comment')
                    )
                );
        }
    }
}
</script>

<style scoped>

.comment-wrapper {
    width: 100%
}

.commenterImage {
    width: 30px;
    margin-right: 5px;
    height: 100%;
    float: left;
}

.commenterImage.owner {
    float: right;
}

.commentText p {
    margin: 0;
}

.sub-text {
    color: #aaa;
    font-family: verdana;
    font-size: 11px;
}

.commenterName {
    font-weight: bold;
}
</style>
