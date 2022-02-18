<div class="dispCorp">
  <p class="dispCompany">{{ Arr::get($value, 'document_common_setting.company_name') }}</p>
  <p class="dispEtc01">{{ Arr::get($value, 'document_common_setting.supplement1') }}</p>
  <p class="dispEtc02">{{ Arr::get($value, 'document_common_setting.supplement2') }}</p>
  <p class="dispPostal">
    @if(Arr::get($value, 'document_common_setting.zip_code'))
    {{ sprintf("〒%s-%s", substr($value['document_common_setting']['zip_code'], 0, 3), substr($value['document_common_setting']['zip_code'], 3)) }}
    @endif
  </p>
  <p class="dispCorpAddress">
    {{ Arr::get($value, 'document_common_setting.address1') }}
    @if(Arr::get($value, 'document_common_setting.address2'))
      <br>{{ Arr::get($value, 'document_common_setting.address1') }}
    @endif
  </p>
  <p class="dispCorpContact">
    @if(Arr::get($value, 'document_common_setting.tel'))
      {{ sprintf("TEL:%s", $value['document_common_setting']['tel']) }}
    @endif
    @if(Arr::get($value, 'document_common_setting.fax'))
      {{ sprintf(" / FAX:%s", $value['document_common_setting']['fax']) }}
    @endif
  </p>
  @if(Arr::get($value, 'manager'))
    <p class="dispManager">担当 {{ Arr::get($value, 'manager') }}</p>
  @endif
</div>