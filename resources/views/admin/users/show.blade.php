<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}: {{ $user->name }}
            </h2>
            <div>
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    {{ __('Back to Users') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Basic Information') }}</h3>
                            
                            <div class="mt-4 border rounded-lg overflow-hidden">
                                <dl>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Full Name') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->name }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Email Verified') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            @if($user->email_verified_at)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('Verified on') }} {{ $user->email_verified_at->format('M d, Y H:i') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('Not Verified') }}
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Roles') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                            @forelse($user->roles as $role)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 mr-2">
                                                    {{ $role->display_name }}
                                                </span>
                                            @empty
                                                <span class="text-gray-500">{{ __('No roles assigned') }}</span>
                                            @endforelse
                                        </dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Created') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->updated_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Role-Specific Information') }}</h3>
                            
                            <div class="mt-4 border rounded-lg overflow-hidden">
                                @if($user->isStudent() && $user->studentProfile)
                                    <div class="px-4 py-3 bg-blue-50 border-b border-blue-100">
                                        <h4 class="text-md font-medium text-blue-800">{{ __('Student Profile') }}</h4>
                                    </div>
                                    <dl>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->studentProfile->date_of_birth ? $user->studentProfile->date_of_birth->format('M d, Y') : 'Not specified' }}
                                            </dd>
                                        </div>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('School Name') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->studentProfile->school_name ?: 'Not specified' }}
                                            </dd>
                                        </div>
                                    </dl>
                                @endif

                                @if($user->isTeacher() && $user->teacherProfile)
                                    <div class="px-4 py-3 bg-green-50 border-b border-green-100">
                                        <h4 class="text-md font-medium text-green-800">{{ __('Teacher Profile') }}</h4>
                                    </div>
                                    <dl>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Bio') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->teacherProfile->bio ?: 'Not specified' }}
                                            </dd>
                                        </div>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Qualifications') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->teacherProfile->qualifications ?: 'Not specified' }}
                                            </dd>
                                        </div>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('School Affiliation') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->teacherProfile->school_affiliation ?: 'Not specified' }}
                                            </dd>
                                        </div>
                                    </dl>
                                @endif

                                @if($user->isParent() && $user->parentProfile)
                                    <div class="px-4 py-3 bg-purple-50 border-b border-purple-100">
                                        <h4 class="text-md font-medium text-purple-800">{{ __('Parent Profile') }}</h4>
                                    </div>
                                    <dl>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Occupation') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->parentProfile->occupation ?: 'Not specified' }}
                                            </dd>
                                        </div>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Relationship to Student') }}</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ $user->parentProfile->relationship_to_student ?: 'Not specified' }}
                                            </dd>
                                        </div>
                                    </dl>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if(Auth::id() !== $user->id)
                        <div class="mt-6 flex justify-end">
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    {{ __('Delete User') }}
                                </button>
                            </form>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 