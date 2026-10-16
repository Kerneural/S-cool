<form method="POST"
      action="{{ $isSaved ? route('communities.posts.unsave', [$communitySlug, $postId]) : route('communities.posts.save', [$communitySlug, $postId]) }}"
      x-data="{
          saved: @js($isSaved),
          pending: false,
          message: @js($errorMessage),
          async submitBookmark() {
              if (this.pending) return;

              const previousSaved = this.saved;
              this.pending = true;
              this.message = null;
              this.saved = !this.saved;

              try {
                  const state = await this.$wire.toggleSave();
                  this.saved = state.isSaved;
                  this.message = state.errorMessage;
              } catch (error) {
                  this.saved = previousSaved;
                  this.message = @js(__('Unable to update bookmark. Please try again.'));
              } finally {
                  this.pending = false;
              }
          }
      }"
      @submit.prevent="submitBookmark"
      class="inline-flex items-center">
    @csrf
    @if ($isSaved)
        @method('DELETE')
    @endif

    <button type="submit"
            :disabled="pending"
            :aria-pressed="saved.toString()"
            :aria-label="saved ? @js(__('Remove from saved')) : @js(__('Save post for later'))"
            :title="saved ? @js(__('Remove from saved')) : @js(__('Save post for later'))"
            class="inline-flex items-center text-xs transition motion-reduce:transition-none active:scale-[0.98] motion-reduce:transform-none disabled:opacity-60 disabled:cursor-wait"
            :class="saved ? 'text-amber-600 hover:text-amber-700 font-semibold' : 'text-slate-500 hover:text-amber-600 font-medium'">
        <svg class="w-4 h-4 mr-1 transition-transform motion-reduce:transition-none"
             :class="[saved ? 'text-amber-600' : '', pending ? 'animate-pulse motion-reduce:animate-none' : '']"
             :fill="saved ? 'currentColor' : 'none'"
             stroke="currentColor"
             viewBox="0 0 24 24"
             aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
        </svg>
        <span x-text="saved ? @js(__('Saved')) : @js(__('Save'))">{{ $isSaved ? __('Saved') : __('Save') }}</span>
    </button>

    <span x-show="message"
          x-cloak
          x-text="message"
          class="ml-2 text-[11px] text-rose-600 font-medium"
          role="alert"></span>
</form>
