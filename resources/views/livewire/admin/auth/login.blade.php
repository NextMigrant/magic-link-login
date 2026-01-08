<div>
    <flux:card>
        @if(session()->get('status') !== 'login-email-sent' )
        <form wire:submit="login" class="flex flex-col gap-6">
            <!-- Email Address -->
            <flux:input wire:model="email" :label="__('Email address')" type="email" required autofocus
                autocomplete="email" placeholder="email@example.com" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Log in') }}</flux:button>
            </div>
        </form>
        <x-action-message :on="'login-failed'" class="mt-5 font-semibold text-center text-red-500">
            {{ $loginFailMessage }}
        </x-action-message>
        @else
        <div class="space-y-5 text-center">
            <flux:heading size="xl">Check your email</flux:heading>

            <flux:text size="xl" variant="strong">We've sent a temporary login link.</flux:text>

            <flux:text size="xl" variant="strong">Please <flux:link
                    href="https://mail.google.com/mail/u/0/#search/from%3A({{ urlencode(config('mail.from.address')) }})">check your
                    inbox</flux:link> at {{ $email }}.
            </flux:text>

            <flux:link href="{{route('admin.login-page')}}" variant="subtle" wire:navigate>Back to login</flux:link>
        </div>
        @endif
    </flux:card>
</div>
