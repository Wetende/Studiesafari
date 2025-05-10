{{-- Text Lesson Specific Fields --}}
{{-- $lesson is passed --}}

<div class="mb-3">
    <label for="lessonShortDescriptionText" class="form-label text-dark-1">Short Description (Optional Summary)</label>
    <textarea class="form-control rich-text-editor" id="lessonShortDescriptionText" name="short_description" rows="3">{{ old('short_description', $lesson->short_description) }}</textarea>
    @error('short_description') <div class="text-danger mt-1 text-13">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="lessonContentText" class="form-label text-dark-1">Main Content <span class="text-red-1">*</span></label>
    <textarea class="form-control rich-text-editor" id="lessonContentText" name="content" rows="10">{{ old('content', $lesson->content) }}</textarea>
    @error('content') <div class="text-danger mt-1 text-13">{{ $message }}</div> @enderror
</div> 