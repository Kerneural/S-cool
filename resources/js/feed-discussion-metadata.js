export function feedDiscussionMetadata(initial) {
    return {
        commentsCount: initial.count,
        latestComment: initial.latestComment,
        updateComments(detail) {
            if (detail.communityId !== initial.communityId || detail.postId !== initial.postId) return;
            this.commentsCount = detail.count;
            this.latestComment = detail.latestComment;
        },
    };
}
