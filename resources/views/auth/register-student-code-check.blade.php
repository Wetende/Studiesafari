<x-guest-layout>
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-700">Student Signup</h2>
        <p class="text-gray-500">Do you have a code to join the classroom?</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />
    <x-input-error :messages="$errors->get('has_code')" class="mb-4 text-center text-red-500" />

    <form id="codeCheckForm" method="POST" action="{{ route('register.student.handle-code-check') }}">
        @csrf
        <input type="hidden" name="has_code" id="hasCodeValue">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10">
            <!-- Yes, I have a code Card -->
            <div onclick="submitCodeCheck('yes')" class="cursor-pointer p-6 bg-white rounded-lg border-2 border-green-400 shadow-md hover:shadow-lg hover:border-green-500 transition-all duration-200 ease-in-out text-center">
                {{-- Placeholder for "Yes" image --}}
                <div class="w-full h-56 bg-gray-100 rounded-md mb-4 flex items-center justify-center">
                    <span class="text-gray-400">'Yes' Image Placeholder (e.g., Happy Elephant)</span>
                </div>
                <h3 class="text-2xl font-semibold text-green-600">Yes</h3>
                <p class="text-sm text-gray-500 mt-1">I have a classroom code!</p>
            </div>

            <!-- No, I don't have a code Card -->
            <div onclick="submitCodeCheck('no')" class="cursor-pointer p-6 bg-white rounded-lg border-2 border-red-400 shadow-md hover:shadow-lg hover:border-red-500 transition-all duration-200 ease-in-out text-center">
                {{-- Placeholder for "No" image --}}
                <div class="w-full h-56 bg-gray-100 rounded-md mb-4 flex items-center justify-center">
                    <span class="text-gray-400">'No' Image Placeholder (e.g., Waving Elephant)</span>
                </div>
                <h3 class="text-2xl font-semibold text-red-600">No</h3>
                <p class="text-sm text-gray-500 mt-1">I need to create a new account.</p>
            </div>
        </div>
    </form>

    <div class="mt-8 text-center">
        <p class="text-sm text-gray-500">Already have an account? 
            <a class="font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('login') }}">
                Sign in instead
            </a>
        </p>
    </div>
    <div class="mt-4 text-center">
         <a class="text-sm text-indigo-600 hover:text-indigo-500" href="{{ route('register') }}">
                &larr; Back to role selection
            </a>
    </div>

    <script>
        function submitCodeCheck(value) {
            document.getElementById('hasCodeValue').value = value;
            document.getElementById('codeCheckForm').submit();
        }
    </script>
</x-guest-layout> 