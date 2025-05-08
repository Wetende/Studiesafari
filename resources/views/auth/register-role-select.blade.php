<x-guest-layout>
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-700">Who are you?</h2>
        <p class="text-gray-500">Start your free trial today!</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />
    <x-input-error :messages="$errors->get('role')" class="mb-4 text-center text-red-500" />

    <form id="roleSelectionForm" method="POST" action="{{ route('register.select-role') }}">
        @csrf
        <input type="hidden" name="role" id="selectedRole">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Student Card -->
            <div onclick="submitRole('student')" class="cursor-pointer p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-200 ease-in-out text-center">
                {{-- Placeholder for student image --}}
                <div class="w-full h-48 bg-gray-100 rounded-md mb-4 flex items-center justify-center">
                    <span class="text-gray-400">Student Image Placeholder</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Student</h3>
                <p class="text-sm text-gray-500">Join your classmates in smart learning!</p>
            </div>

            <!-- Teacher Card -->
            <div onclick="submitRole('teacher')" class="cursor-pointer p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-200 ease-in-out text-center">
                {{-- Placeholder for teacher image --}}
                <div class="w-full h-48 bg-gray-100 rounded-md mb-4 flex items-center justify-center">
                    <span class="text-gray-400">Teacher Image Placeholder</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Teacher</h3>
                <p class="text-sm text-gray-500">For educators in a school setting!</p>
            </div>

            <!-- Parent Card -->
            <div onclick="submitRole('parent')" class="cursor-pointer p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-200 ease-in-out text-center">
                {{-- Placeholder for parent image --}}
                <div class="w-full h-48 bg-gray-100 rounded-md mb-4 flex items-center justify-center">
                    <span class="text-gray-400">Parent Image Placeholder</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Parent</h3>
                <p class="text-sm text-gray-500">Register your child and track progress!</p>
            </div>
        </div>
    </form>

    <div class="mt-8 text-center">
        <a class="text-sm text-indigo-600 hover:text-indigo-500" href="{{ route('login') }}">
            Already a member? Log in to your account
        </a>
    </div>

    <script>
        function submitRole(role) {
            document.getElementById('selectedRole').value = role;
            document.getElementById('roleSelectionForm').submit();
        }
    </script>
</x-guest-layout> 