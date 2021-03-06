@component('mail::message')
# Hi

You have been invited to join the tea
**{{$invitation->team->name}}**.
Because you are not signed up to the platform, please
[Register for free]({{$url}}), then you can accept or reject the invitation in your team management console.

@component('mail::button', ['url' => $url])
Register for free
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
