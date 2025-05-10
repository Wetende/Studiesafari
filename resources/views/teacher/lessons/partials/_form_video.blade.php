{{-- Video Lesson Specific Fields --}}
{{-- $lesson is passed --}}

@props(['lesson', 'lessonType', 'course', 'section', 'errors'])

<div class="space-y-4">
    <div>
        <label for="video_url_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Video URL</label>
        <input type="url" name="video_url" id="video_url_{{ $lessonType }}" value="{{ old('video_url', $lesson->video_url ?? '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('video_url')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="video_source_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Video Source</label>
        <select name="video_source" id="video_source_{{ $lessonType }}"
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
            <option value="youtube" {{ old('video_source', $lesson->video_source ?? '') == 'youtube' ? 'selected' : '' }}>YouTube</option>
            <option value="vimeo" {{ old('video_source', $lesson->video_source ?? '') == 'vimeo' ? 'selected' : '' }}>Vimeo</option>
            <option value="html5" {{ old('video_source', $lesson->video_source ?? '') == 'html5' ? 'selected' : '' }}>HTML5 (Self-hosted/Uploaded)</option>
            <option value="embed" {{ old('video_source', $lesson->video_source ?? '') == 'embed' ? 'selected' : '' }}>Embed Code</option>
        </select>
        @error('video_source')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Conditional field for video upload if source is HTML5 --}}
    <div id="video_upload_container_{{ $lessonType }}" class="{{ old('video_source', $lesson->video_source ?? '') == 'html5' ? '' : 'hidden' }}">
        <label for="video_upload_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Video File</label>
        <input type="file" name="video_upload" id="video_upload_{{ $lessonType }}"
               class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
        @if ($lesson && $lesson->video_path)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current file: {{ basename($lesson->video_path) }}</p>
        @endif
        @error('video_upload')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
    
    {{-- Conditional field for embed code if source is embed --}}
    <div id="video_embed_code_container_{{ $lessonType }}" class="{{ old('video_source', $lesson->video_source ?? '') == 'embed' ? '' : 'hidden' }}">
        <label for="video_embed_code_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Video Embed Code</label>
        <textarea name="video_embed_code" id="video_embed_code_{{ $lessonType }}" rows="4"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">{{ old('video_embed_code', $lesson->video_embed_code ?? '') }}</textarea>
        @error('video_embed_code')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>


    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input id="enable_p_in_p_{{ $lessonType }}" name="enable_p_in_p" type="checkbox" value="1" {{ old('enable_p_in_p', $lesson->enable_p_in_p ?? false) ? 'checked' : '' }}
                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
        </div>
        <div class="ml-3 text-sm">
            <label for="enable_p_in_p_{{ $lessonType }}" class="font-medium text-gray-700 dark:text-gray-300">Enable Picture-in-Picture</label>
        </div>
    </div>
    @error('enable_p_in_p')
    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input id="enable_download_{{ $lessonType }}" name="enable_download" type="checkbox" value="1" {{ old('enable_download', $lesson->enable_download ?? false) ? 'checked' : '' }}
                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
        </div>
        <div class="ml-3 text-sm">
            <label for="enable_download_{{ $lessonType }}" class="font-medium text-gray-700 dark:text-gray-300">Enable Download</label>
            <p class="text-gray-500 dark:text-gray-400">If HTML5 video, allows direct download. For others, may show a download button if supported by the player.</p>
        </div>
    </div>
    @error('enable_download')
    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <div>
        <label for="short_description_video_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Description (Optional)</label>
        <textarea name="short_description" id="short_description_video_{{ $lessonType }}" rows="3"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">{{ old('short_description', $lesson->short_description ?? '') }}</textarea>
        @error('short_description')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="content_video_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content / Additional Details (Optional)</label>
        {{-- Rich Text Editor Placeholder --}}
        <textarea name="content" id="content_video_{{ $lessonType }}" rows="5" placeholder="Enter rich text content here..."
                  class="rich-text-editor mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">{{ old('content', $lesson->content ?? '') }}</textarea>
        @error('content')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const videoSourceSelect = document.getElementById('video_source_{{ $lessonType }}');
        const videoUploadContainer = document.getElementById('video_upload_container_{{ $lessonType }}');
        const videoEmbedCodeContainer = document.getElementById('video_embed_code_container_{{ $lessonType }}');

        function toggleVideoFields() {
            if (!videoSourceSelect) return; // Guard clause
            
            videoUploadContainer.classList.toggle('hidden', videoSourceSelect.value !== 'html5');
            videoEmbedCodeContainer.classList.toggle('hidden', videoSourceSelect.value !== 'embed');
        }

        if (videoSourceSelect) {
            videoSourceSelect.addEventListener('change', toggleVideoFields);
            // Initial call to set state based on current value (e.g., from old input or model)
            toggleVideoFields(); 
        }
    });
</script>
@endpush 