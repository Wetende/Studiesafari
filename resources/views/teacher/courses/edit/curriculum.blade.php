@extends('teacher.courses.edit.layout')

@section('tab-content')
    <div class="curriculum-tab-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-500 text-dark-1">Course Curriculum</h5>
            <div>
                <button type="button" class="button -md -purple-1 text-white me-2" data-bs-toggle="modal" data-bs-target="#searchMaterialsModal">
                    Search Materials
                </button>
                <button type="button" class="button -md -blue-1 text-white" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                    New Section
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert -green-1 text-white bg-green-1 mb-30">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert -red-1 text-white bg-red-1 mb-30">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert -blue-1 text-white bg-blue-1 mb-30">
                {{ session('info') }}
            </div>
        @endif

        {{-- Sections Listing --}}
        <div id="courseSectionsAccordion" class="accordion accordion-flush curriculum-accordion" data-reorder-url="{{ route('teacher.courses.sections.reorder', $course) }}">
            @forelse ($course->sections as $section)
                <div class="accordion-item card -dark-bg-light-2 mb-30 section-item" data-section-id="{{ $section->id }}">
                    <h2 class="accordion-header" id="headingSection{{ $section->id }}">
                        <button class="accordion-button collapsed fw-500 text-dark-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSection{{ $section->id }}" aria-expanded="false" aria-controls="collapseSection{{ $section->id }}">
                            <span class="section-title-text">{{ $section->title }}</span> 
                            <span class="badge bg-light text-dark-1 ms-2">{{ $section->lessons->count() }} L / {{ $section->quizzes->count() }} Q / {{ $section->assignments->count() }} A</span>
                            <span class="drag-handle button -sm -light-1 text-dark-1 ms-auto me-3"><i class="fas fa-arrows-alt"></i></span>
                        </button>
                    </h2>
                    <div id="collapseSection{{ $section->id }}" class="accordion-collapse collapse" aria-labelledby="headingSection{{ $section->id }}" data-bs-parent="#courseSectionsAccordion">
                        <div class="accordion-body pt-15 px-30 pb-30">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="button -sm -outline-dark-1 text-dark-1 me-2" data-bs-toggle="modal" data-bs-target="#editSectionModal{{ $section->id }}">
                                    Edit Section
                                </button>
                                <form action="{{ route('teacher.courses.sections.destroy', [$course, $section]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section and all its contents?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button -sm -red-1 text-white">Delete Section</button>
                                </form>
                            </div>
                            <p class="text-14 text-dark-2 mb-20">{{ $section->description }}</p>
                            
                            {{-- Content within section (Lessons, Quizzes, Assignments) --}}
                            <ul class="list-group section-content-list mb-20" data-reorder-url="{{ route('teacher.courses.sections.content.reorder', [$course, $section]) }}">
                                @php $contentItems = collect()->concat($section->lessons)->concat($section->quizzes)->concat($section->assignments)->sortBy('order'); @endphp 
                                @forelse ($contentItems as $item)
                                    <li class="list-group-item d-flex justify-content-between align-items-center content-item {{ strtolower(class_basename($item)) }}-item" 
                                        data-item-id="{{ $item->id }}" 
                                        data-item-type="{{ strtolower(class_basename($item)) }}">
                                        <span>
                                            @if($item instanceof \App\Models\Lesson) <i class="fas fa-file-alt text-blue-1 me-2"></i> Lesson: @endif
                                            @if($item instanceof \App\Models\Quiz) <i class="fas fa-question-circle text-purple-1 me-2"></i> Quiz: @endif
                                            @if($item instanceof \App\Models\Assignment) <i class="fas fa-clipboard-check text-green-1 me-2"></i> Assignment: @endif
                                            {{ $item->title }}
                                        </span>
                                        <span class="drag-handle button -xs -light-1 text-dark-1"><i class="fas fa-arrows-alt"></i></span>
                                        {{-- TODO: Add edit/delete buttons for content items --}}
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-dark-2">No content yet in this section.</li>
                                @endforelse
                            </ul>

                            <div class="mt-20">
                                {{-- Button to open "Select lesson type" modal (from Subphase 3.4) --}}
                                <button type="button" class="button -sm -outline-blue-1 text-blue-1 me-2" data-bs-toggle="modal" data-bs-target="#selectLessonTypeModal{{ $section->id }}">Add Lesson</button>
                                {{-- TODO: Buttons for Add Quiz, Add Assignment to this section --}}
                                {{-- These will also trigger modals for creation or linking existing ones --}}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Edit Section Modal --}}
                @include('teacher.courses.edit.modals._edit_section_modal', ['course' => $course, 'section' => $section])
            @empty
                <div class="text-center py-5">
                    <p class="text-16 text-dark-2">No sections created yet.</p>
                    <p class="text-14 text-dark-2 mb-20">Start building your course by adding a new section.</p>
                    <button type="button" class="button -md -blue-1 text-white" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                        Create Your First Section
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Add Section Modal --}}
    @include('teacher.courses.edit.modals._add_section_modal', ['course' => $course])
    {{-- Search Materials Modal --}}
    @include('teacher.courses.edit.modals._search_materials_modal', ['course' => $course])
    
    {{-- "Select Lesson Type" Modal (one for each section) --}}
    @foreach($course->sections as $section)
        @include('teacher.courses.edit.modals._select_lesson_type_modal', ['course' => $course, 'section' => $section])
    @endforeach

@endsection

@push('tab-scripts')
<script>
    // Basic SortableJS setup (requires SortableJS library to be included globally or on this page)
    // This is a structural placeholder. Full JS implementation is more involved.
    document.addEventListener('DOMContentLoaded', function () {
        const sectionsAccordion = document.getElementById('courseSectionsAccordion');
        if (sectionsAccordion && typeof Sortable !== 'undefined') {
            new Sortable(sectionsAccordion, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'bg-light-2',
                // onEnd: function (evt) { /* AJAX call to reorderSections route */ }
            });

            document.querySelectorAll('.section-content-list').forEach(list => {
                new Sortable(list, {
                    animation: 150,
                    handle: '.drag-handle',
                    group: 'shared-content', // Allows dragging between sections if needed, though reorder logic is per-section
                    // onEnd: function (evt) { /* AJAX call to reorderSectionContent route */ }
                });
            });
        }

        // AJAX for reordering would go here, using the data-reorder-url attributes
        // and sending the new order of IDs.
    });
</script>
@endpush 