@component('mail::message')
# Hi

You have been invited to join the tea
**{{$invitation->team->name}}**.
Because you are already registerd to the platform, you just
need accept or reject the invitation
[Register for free]({{$url}}).

@component('mail::button', ['url' => $url])
Go to dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
