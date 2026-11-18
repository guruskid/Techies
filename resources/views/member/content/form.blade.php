@php
  $editing = $article->exists;
  $inputClass = 'w-full border border-slate-200 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#FF5C3D]';
  $labelClass = 'block text-sm font-medium text-slate-700 mb-1';
  $errorClass = 'mt-1 text-xs text-red-600';
  $seoScore = $editing ? $article->seoScore() : 0;
@endphp

<style>[x-cloak]{display:none !important}</style>

<form method="POST" action="{{ $editing ? route('member.content.update', $article) : route('member.content.store') }}" class="grid lg:grid-cols-3 gap-6">
  @csrf
  @if($editing)
    @method('PATCH')
  @endif

  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
      <div>
        <label for="title" class="{{ $labelClass }}">Title</label>
        <div x-data="{ count: {{ mb_strlen(old('title', $article->title ?? '')) }} }">
          <input id="title" name="title" value="{{ old('title', $article->title) }}" maxlength="160" required
                 @input="count = $event.target.value.length"
                 placeholder="e.g. How I built my first production Laravel app"
                 class="{{ $inputClass }}">
          <div class="mt-1 flex justify-between text-xs text-slate-400">
            <span>A clear, specific headline works best.</span>
            <span><span x-text="count"></span>/160</span>
          </div>
        </div>
        @error('title')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="topic" class="{{ $labelClass }}">Topic</label>
        <select id="topic" name="topic" required class="{{ $inputClass }}">
          <option value="">Choose a topic</option>
          @foreach($topics as $key => $topic)
            <option value="{{ $key }}" @selected(old('topic', $article->topic) === $key)>
              {{ $topic['label'] }}{{ $topic['approval'] === 'super_admin' ? ' — super admin review' : '' }}
            </option>
          @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-400">Topics marked “super admin review” can only be approved by a super administrator.</p>
        @error('topic')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="excerpt" class="{{ $labelClass }}">Excerpt</label>
        <div x-data="{ count: {{ mb_strlen(old('excerpt', $article->excerpt ?? '')) }} }">
          <textarea id="excerpt" name="excerpt" rows="3" maxlength="300"
                    @input="count = $event.target.value.length"
                    placeholder="A short summary shown on listing pages and social previews."
                    class="{{ $inputClass }}">{{ old('excerpt', $article->excerpt) }}</textarea>
          <div class="mt-1 text-right text-xs text-slate-400"><span x-text="count"></span>/300</div>
        </div>
        @error('excerpt')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="body" class="{{ $labelClass }}">Article body</label>
        <div x-data="{ count: {{ mb_strlen(old('body', $article->body ?? '')) }} }">
          <textarea id="body" name="body" rows="18" required
                    @input="count = $event.target.value.length"
                    placeholder="Write your article. Use blank lines to separate paragraphs."
                    class="{{ $inputClass }} font-mono text-sm leading-relaxed">{{ old('body', $article->body) }}</textarea>
          <div class="mt-1 text-right text-xs text-slate-400"><span x-text="count"></span> characters</div>
        </div>
        @error('body')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="cover_image" class="{{ $labelClass }}">Cover image URL</label>
          <input id="cover_image" name="cover_image" type="url" value="{{ old('cover_image', $article->cover_image) }}"
                 placeholder="https://example.com/cover.jpg" class="{{ $inputClass }}">
          @error('cover_image')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="slug" class="{{ $labelClass }}">URL slug <span class="text-slate-400 font-normal">(optional)</span></label>
          <input id="slug" name="slug" value="{{ old('slug', $article->slug) }}"
                 placeholder="auto-generated-from-title" class="{{ $inputClass }}">
          @error('slug')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
      <div>
        <h2 class="text-lg font-bold text-[#101E33]">SEO settings</h2>
        <p class="text-sm text-slate-500">Articles need a SEO score of at least {{ \App\Models\Article::MINIMUM_SEO_SCORE }}% before they can be submitted for review.</p>
      </div>

      <div>
        <label for="seo_title" class="{{ $labelClass }}">SEO title</label>
        <input id="seo_title" name="seo_title" value="{{ old('seo_title', $article->seo_title) }}" maxlength="70"
               placeholder="30–60 characters, includes your focus keyword" class="{{ $inputClass }}">
        @error('seo_title')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="seo_description" class="{{ $labelClass }}">Meta description</label>
        <textarea id="seo_description" name="seo_description" rows="3" maxlength="200"
                  placeholder="120–160 characters describing what the reader will learn."
                  class="{{ $inputClass }}">{{ old('seo_description', $article->seo_description) }}</textarea>
        @error('seo_description')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="seo_keywords" class="{{ $labelClass }}">Focus keyword(s)</label>
          <input id="seo_keywords" name="seo_keywords" value="{{ old('seo_keywords', $article->seo_keywords) }}" maxlength="255"
                 placeholder="laravel, api, beginners" class="{{ $inputClass }}">
          <p class="mt-1 text-xs text-slate-400">Comma-separated. The first keyword is the focus keyword.</p>
          @error('seo_keywords')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>
        <div>
          <label for="canonical_url" class="{{ $labelClass }}">Canonical URL <span class="text-slate-400 font-normal">(optional)</span></label>
          <input id="canonical_url" name="canonical_url" type="url" value="{{ old('canonical_url', $article->canonical_url) }}"
                 placeholder="https://your-site.com/original-article" class="{{ $inputClass }}">
          @error('canonical_url')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <button type="submit" name="action" value="save" class="px-5 py-2.5 border border-slate-200 rounded-lg font-medium hover:bg-slate-50 transition">Save draft</button>
      <button type="submit" name="action" value="submit" class="px-5 py-2.5 bg-[#FF5C3D] text-white rounded-lg font-semibold hover:opacity-90 transition">Save &amp; submit for review</button>
      <a href="{{ route('member.content.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
  </div>

  <aside class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-[#101E33]">SEO checklist</h2>
        <span class="text-sm font-semibold {{ $seoScore >= 70 ? 'text-green-600' : ($seoScore >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $seoScore }}%</span>
      </div>
      <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
        <div class="h-full {{ $seoScore >= 70 ? 'bg-green-500' : ($seoScore >= 50 ? 'bg-amber-500' : 'bg-red-400') }}" style="width: {{ $seoScore }}%"></div>
      </div>

      @if($editing)
        <ul class="mt-4 space-y-2">
          @foreach($article->seoChecklist() as $check)
            <li class="flex items-start gap-2 text-sm {{ $check['passed'] ? 'text-green-700' : 'text-slate-600' }}">
              <span class="mt-0.5 w-4 h-4 rounded-full flex items-center justify-center text-[10px] {{ $check['passed'] ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-400' }}">{{ $check['passed'] ? '✓' : '•' }}</span>
              <span>{{ $check['label'] }}<span class="block text-xs text-slate-400">{{ $check['hint'] }}</span></span>
            </li>
          @endforeach
        </ul>
      @else
        <ul class="mt-4 space-y-2 text-sm text-slate-600">
          <li>• SEO title between 30 and 60 characters.</li>
          <li>• Meta description between 120 and 160 characters.</li>
          <li>• One focus keyword used in the title and body.</li>
          <li>• A readable slug, excerpt and cover image.</li>
          <li>• At least 600 characters of useful content.</li>
        </ul>
        <p class="mt-3 text-xs text-slate-400">Save the draft to see your live score and detailed checklist.</p>
      @endif
    </div>

    @if($editing)
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="font-bold text-[#101E33] mb-3">Review status</h2>
        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $article->statusLabel() }}</span>
        <p class="mt-3 text-sm text-slate-500">This topic is reviewed by {{ $article->requiresSuperAdminApproval() ? 'a super administrator' : 'an admin' }}.</p>
        @if($article->review_notes)
          <div class="mt-4 rounded-xl bg-slate-50 p-3 text-sm text-slate-600">
            <span class="font-semibold text-[#101E33]">Reviewer feedback:</span> {{ $article->review_notes }}
          </div>
        @endif
        @if($article->isPublished())
          <a href="{{ route('articles.show', $article) }}" class="mt-4 inline-block text-sm text-[#FF5C3D] hover:underline">View published article →</a>
        @endif
      </div>
    @endif

    <div class="bg-[#101E33] text-slate-300 rounded-2xl p-6">
      <h2 class="font-bold text-white mb-2">Writing tips</h2>
      <ul class="text-sm space-y-2">
        <li>• Lead with the problem your reader wants to solve.</li>
        <li>• Use short paragraphs and descriptive subheadings.</li>
        <li>• Explain jargon and include real examples.</li>
        <li>• End with a clear takeaway or next step.</li>
      </ul>
    </div>
  </aside>
</form>
