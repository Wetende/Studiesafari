{{-- Stream Lesson Specific Fields --}}
{{-- $lesson is passed --}}

@props(['lesson', 'lessonType', 'course', 'section', 'errors'])

<div class="space-y-4">
    <div>
        <label for="stream_url_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stream URL (e.g., Zoom, Google Meet)</label>
        <input type="url" name="stream_url" id="stream_url_{{ $lessonType }}" value="{{ old('stream_url', $lesson->stream_url ?? '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('stream_url')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="stream_password_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stream Password (Optional)</label>
        <input type="text" name="stream_password" id="stream_password_{{ $lessonType }}" value="{{ old('stream_password', $lesson->stream_password ?? '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('stream_password')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="stream_start_time_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stream Start Date & Time</label>
        <input type="datetime-local" name="stream_start_time" id="stream_start_time_{{ $lessonType }}" value="{{ old('stream_start_time', $lesson->stream_start_time ? (new DateTime($lesson->stream_start_time))->format('Y-m-d\\TH:i') : '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('stream_start_time')
            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="stream_details_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stream Details (Instructions, Agenda, etc.)</label>
        <textarea name="stream_details" id="stream_details_{{ $lessonType }}" rows="5" placeholder="Enter rich text content for stream details..."
                  class="rich-text-editor mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">{{ old('stream_details', $lesson->stream_details ?? '') }}</textarea>
        @error('stream_details')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-start">
        <div class="flex items-center h-5">
            <input id="is_recorded_{{ $lessonType }}" name="is_recorded" type="checkbox" value="1" {{ old('is_recorded', $lesson->is_recorded ?? false) ? 'checked' : '' }}
                   onchange="document.getElementById('recording_url_container_{{ $lessonType }}').classList.toggle('hidden', !this.checked)"
                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
        </div>
        <div class="ml-3 text-sm">
            <label for="is_recorded_{{ $lessonType }}" class="font-medium text-gray-700 dark:text-gray-300">Is this stream recorded / Will a recording be available?</label>
        </div>
    </div>
    @error('is_recorded')
    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <div id="recording_url_container_{{ $lessonType }}" class="{{ old('is_recorded', $lesson->is_recorded ?? false) ? '' : 'hidden' }}">
        <label for="recording_url_{{ $lessonType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recording URL (Optional)</label>
        <input type="url" name="recording_url" id="recording_url_{{ $lessonType }}" value="{{ old('recording_url', $lesson->recording_url ?? '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
        @error('recording_url')
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure the recording URL field visibility is correctly set on page load
        const isRecordedCheckbox = document.getElementById('is_recorded_{{ $lessonType }}');
        if (isRecordedCheckbox) {
            document.getElementById('recording_url_container_{{ $lessonType }}').classList.toggle('hidden', !isRecordedCheckbox.checked);
        }
    });
</script>
@endpush 