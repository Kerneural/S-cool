import test from 'node:test';
import assert from 'node:assert/strict';
import { feedDiscussionMetadata } from '../../resources/js/feed-discussion-metadata.js';

test('reply metadata updates only its own community and card; deletion clears preview', () => {
    const card = feedDiscussionMetadata({ communityId: 2, postId: 7, count: 0, latestComment: null });
    const reply = { id: 9, author: 'Learner', body: '<script>plain text</script>' };
    card.updateComments({ communityId: 3, postId: 7, count: 1, latestComment: reply });
    card.updateComments({ communityId: 2, postId: 8, count: 1, latestComment: reply });
    assert.equal(card.commentsCount, 0);
    assert.equal(card.latestComment, null);
    card.updateComments({ communityId: 2, postId: 7, count: 1, latestComment: reply });
    assert.equal(card.commentsCount, 1);
    assert.deepEqual(card.latestComment, reply);
    card.updateComments({ communityId: 2, postId: 7, count: 0, latestComment: null });
    assert.equal(card.commentsCount, 0);
    assert.equal(card.latestComment, null);
});
