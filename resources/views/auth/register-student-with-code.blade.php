<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold">Student Registration with Code</h2>
        <p class="text-sm text-gray-600">Please enter your classroom code and set a password.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('register.student.store') }}">
        @csrf

        <!-- Classroom Code -->
        <div class="mt-4">
            <x-input-label for="classroom_code" :value="__('Classroom Code')" />
            <x-text-input id="classroom_code" class="block mt-1 w-full" type="text" name="classroom_code" :value="old('classroom_code')" required autofocus />
            <x-input-error :messages="$errors->get('classroom_code')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3">
                {{ __('Register') }}
            </x-primary-button>
        </div>

        <div class="mt-4 text-center">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register.student.code-check') }}">
                {{ __('Back') }}
            </a>
        </div>
    </form>
</x-guest-layout> 