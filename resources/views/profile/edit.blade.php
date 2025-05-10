<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Subscription Status Section --}}
            @if (Auth::user()->hasActiveSubscription())
                @php
                    $activeSubscription = Auth::user()->activeSubscription();
                    $activeTier = $activeSubscription?->tier; // Use the tier relationship we added
                @endphp
                @if ($activeSubscription && $activeTier)
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <div class="max-w-xl">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Subscription Status') }}
                            </h2>
                    
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Your current subscription details.') }}
                            </p>

                            <div class="mt-6 space-y-4">
                                <div>
                                    <x-input-label for="tier_name" :value="__('Current Plan')" />
                                    <p class="mt-1 font-medium text-gray-900">{{ $activeTier->name }}</p>
                                </div>

                                <div>
                                    <x-input-label for="expiry_date" :value="__('Expires On')" />
                                    <p class="mt-1 text-gray-700">{{ $activeSubscription->expires_at ? $activeSubscription->expires_at->format('F j, Y') : 'Lifetime' }}</p>
                                </div>

                                @if($activeTier->features && count($activeTier->features) > 0)
                                    <div>
                                        <x-input-label for="features" :value="__('Plan Features')" />
                                        <ul class="list-disc list-inside mt-1 text-sm text-gray-600">
                                            @foreach($activeTier->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Renewal Button --}}
                                @if ($activeSubscription->expires_at)
                                    {{-- Optionally, show only if nearing expiry: e.g., $activeSubscription->expires_at->diffInDays(now()) < 30 --}}
                                    <div class="mt-4">
                                        <a href="{{ route('subscriptions.showSubscribeForm', $activeTier->id) }}"
                                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Renew Plan') }}
                                        </a>
                                    </div>
                                @endif
                                 {{-- Add Cancel Button later if needed --}}
                            </div>
                        </div>
                    </div>
                @endif
            @else
                 <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Subscription Status') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('You do not have an active subscription.') }}
                        </p>
                         <div class="mt-4">
                             <a href="{{ route('pricing.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('View Pricing Plans') }}
                            </a>
                         </div>
                    </div>
                </div>
            @endif
            {{-- End Subscription Status Section --}}

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
