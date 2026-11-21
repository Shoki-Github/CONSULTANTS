<h1>New consultation request</h1>

<dl>
    <dt>Name</dt>
    <dd>{{ $contactMessage->name }}</dd>
    <dt>Organisation</dt>
    <dd>{{ $contactMessage->organisation }}</dd>
    <dt>Organisation type</dt>
    <dd>{{ $contactMessage->organisation_type }}</dd>
    <dt>Email</dt>
    <dd>{{ $contactMessage->email }}</dd>
    <dt>Phone</dt>
    <dd>{{ $contactMessage->phone ?: 'Not provided' }}</dd>
    <dt>Service of interest</dt>
    <dd>{{ $contactMessage->service_of_interest }}</dd>
    <dt>Preferred language</dt>
    <dd>{{ $contactMessage->preferred_language }}</dd>
    <dt>Message</dt>
    <dd>{{ $contactMessage->message }}</dd>
</dl>
