<form method="POST"
      action="{{ $isLiked ? route('communities.posts.unlike', [$communitySlug, $postId]) : route('communities.posts.like', [$communitySlug, $postId]) }}"
      x-data="{
          liked: @js($isLiked),
          count: @js($likesCount),
          pending: false,
          message: @js($errorMessage),
          async submitReaction() {
              if (this.pending) return;

              const previousLiked = this.liked;
              const previousCount = this.count;

              this.pending = true;
              this.message = null;
              this.liked = !this.liked;
              this.count = Math.max(0, this.count + (this.liked ? 1 : -1));

              try {
                  const state = await this.$wire.toggleLike();
                  this.liked = state.isLiked;
                  this.count = state.likesCount;
                  this.message = state.errorMessage;
              } catch (error) {
                  this.liked = previousLiked;
                  this.count = previousCount;
                  this.message = @js(__('Unable to update like. Please try again.'));
              } finally {
                  this.pending = false;
              }
          }
      }"
      @submit.prevent="submitReaction"
      class="inline-flex items-center">
    @csrf
    @if ($isLiked)
        @method('DELETE')
    @endif

    @if ($isOwnPost)
        <span class="inline-flex items-center text-slate-400 cursor-not-allowed select-none text-xs"
              title="{{ __('You cannot like your own post') }}">
            <svg class="w-4 h-4 mr-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
            </svg>
            <span>{{ $likesCount }} {{ __('likes') }}</span>
        </span>
    @else
        <button type="submit"
                :disabled="pending"
                :aria-pressed="liked.toString()"
                :aria-label="liked ? @js(__('Unlike post')) : @js(__('Like post'))"
                :title="liked ? @js(__('Unlike post')) : @js(__('Like post'))"
                class="inline-flex items-center text-xs transition motion-reduce:transition-none active:scale-[0.98] motion-reduce:transform-none disabled:opacity-60 disabled:cursor-wait"
                :class="liked ? 'text-rose-600 hover:text-rose-700 font-semibold' : 'text-slate-500 hover:text-rose-600 font-medium'">
            <svg class="w-4 h-4 mr-1 transition-transform motion-reduce:transition-none"
                 :class="[liked ? 'text-rose-600' : '', pending ? 'opacity-60' : '']"
                 :fill="liked ? 'currentColor' : 'none'"
                 stroke="currentColor"
                 viewBox="0 0 24 24"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
            </svg>
            <span x-text="`${count} ${@js(__('likes'))}`">{{ $likesCount }} {{ __('likes') }}</span>
        </button>
    @endif

    <span x-show="message"
          x-cloak
          x-text="message"
          class="ml-2 text-[11px] text-rose-600 font-medium"
          role="alert"></span>
</form>
