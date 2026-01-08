<x-mail::layout>

<x-slot:header>
<x-mail::header url="{{config('app.admin_url')}}">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

## Your login link for {{ config('app.name') }} command center:

<x-mail::button :url="$loginLink">
Login to dashboard
</x-mail::button>


___
This link will only be valid for the next 15 minutes. If you're having trouble clicking the button, copy and paste the URL below into your web browser: [{{$loginLink}}]({{$loginLink}})

<x-slot:footer>
<x-mail::footer>
Sincerely<br>
{{ config('app.name') }} Team
</x-mail::footer>
</x-slot:footer>

</x-mail::layout>
