{{ Arr::get($value, 'document_address.company_name') }} {{ Arr::get($value, 'document_address.department_name') }} {{ Arr::get($value, 'document_address.name') }}{{ Arr::get($formSelects['honorifics'], Arr::get($value, 'document_address.honorific')) }}